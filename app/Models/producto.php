<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'categoria_id',
        'marca_id',
        'nombre',
        'slug',
        'imagenes',
        'descripcion',
        'precio',
        'stock_disponible',  // AÑADIDO
        'stock_reservado',   // AÑADIDO
        'stock_minimo',      // AÑADIDO
        'activo',
        'destacado',
        'en_stock',
        'en_venta',
    ];
    
    protected $casts = [
        'imagenes' => 'array'
    ];

    // RELACIONES
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function stockReservas()
    {
        return $this->hasMany(StockReserva::class);
    }
    
    public function stockMovimientos()
    {
        return $this->hasMany(StockMovimiento::class)->latest();
    }

    // ACCESORES PARA STOCK
    /**
     * Stock realmente disponible (total - reservado)
     */
    public function getStockRealAttribute()
    {
        return max(0, 
            ($this->attributes['stock_disponible'] ?? 0) - 
            ($this->attributes['stock_reservado'] ?? 0)
        );
    }
    
    /**
     * Stock total sin reservas (acceso directo a columna)
     */
    public function getStockTotalAttribute()
    {
        return $this->attributes['stock_disponible'] ?? 0;
    }
    
    /**
     * Stock reservado (acceso directo a columna)
     */
    public function getStockReservadoAttribute()
    {
        return $this->attributes['stock_reservado'] ?? 0;
    }
    
    /**
     * Stock mínimo (acceso directo a columna)
     */
    public function getStockMinimoAttribute()
    {
        return $this->attributes['stock_minimo'] ?? 5;
    }
    
    /**
     * Verifica si tiene stock para una cantidad específica
     */
    public function tieneStock($cantidad)
    {
        return $this->stock_real >= $cantidad;
    }
    
    /**
     * Alias para consistencia (usa el mismo que tieneStock)
     */
    public function tieneStockPara($cantidad = 1)
    {
        return $this->tieneStock($cantidad);
    }

    /**
     * Etiqueta de estado de stock
     */
    public function getEstadoStockAttribute()
    {
        $stockReal = $this->stock_real;
        
        if ($stockReal <= 0) {
            return ['text' => 'Agotado', 'color' => 'red', 'icon' => '❌'];
        }
        
        if ($stockReal <= $this->stock_minimo) {
            return ['text' => 'Últimas unidades', 'color' => 'yellow', 'icon' => '⚠️'];
        }
        
        return ['text' => 'Disponible', 'color' => 'green', 'icon' => '✅'];
    }

     public function getStockDisponibleAttribute()
    {
        return $this->attributes['stock_disponible'] ?? 0;
    }
    
  

    // MÉTODOS DE GESTIÓN DE STOCK
    public function reservarStock($cantidad, $sessionId = null, $userId = null)
    {
        if (!$this->tieneStock($cantidad)) {
            throw new \Exception("Stock insuficiente: {$this->nombre}");
        }
        
        $reserva = StockReserva::create([
            'producto_id' => $this->id,
            'session_id' => $sessionId,
            'user_id' => $userId,
            'cantidad' => $cantidad,
            'expira_en' => now()->addMinutes(30),
            'estado' => 'activa',
        ]);
        
        $this->increment('stock_reservado', $cantidad);
        $this->registrarMovimiento('reserva', $cantidad, "Reserva para " . ($userId ? "usuario $userId" : "sesión $sessionId"));
        
        return $reserva;
    }
    
    public function liberarStockReservado($cantidad, $reservaId = null, $motivo = null)
    {
        $this->decrement('stock_reservado', $cantidad);
        
        if ($reservaId) {
            StockReserva::where('id', $reservaId)->update(['estado' => 'liberada']);
        }
        
        $this->registrarMovimiento('liberacion', $cantidad, $motivo ?? "Liberación", null, $reservaId);
    }
    
    public function consumirStock($cantidad, $orderId = null, $userId = null, $reservaId = null)
    {
        if ($reservaId) {
            $this->liberarStockReservado($cantidad, $reservaId, "Consumido para orden $orderId");
        }
        
        $this->decrement('stock_disponible', $cantidad);
        $this->registrarMovimiento('salida', $cantidad, "Venta - Orden $orderId", $orderId, $reservaId, $userId);
        
        if ($this->stock_real <= $this->stock_minimo) {
            \Log::warning("Stock bajo: {$this->nombre} - Stock: {$this->stock_real}");
        }
    }
    
    public function agregarStock($cantidad, $motivo = "Reposición", $userId = null)
    {
        $this->increment('stock_disponible', $cantidad);
        $this->registrarMovimiento('entrada', $cantidad, $motivo, null, null, $userId);
    }
    
    private function registrarMovimiento($tipo, $cantidad, $motivo, $orderId = null, $reservaId = null, $userId = null)
    {
        StockMovimiento::create([
            'producto_id' => $this->id,
            'tipo' => $tipo,
            'cantidad' => $cantidad,
            'stock_anterior' => $this->getOriginal('stock_disponible'),
            'stock_nuevo' => $this->attributes['stock_disponible'] ?? 0,
            'motivo' => $motivo,
            'user_id' => $userId ?? auth()->id(),
            'order_id' => $orderId,
            'stock_reserva_id' => $reservaId,
            'notas' => "Stock reservado: " . ($this->attributes['stock_reservado'] ?? 0),
        ]);
    }

    // MÉTODOS ADICIONALES ÚTILES
    /**
     * Verifica si el producto está disponible para venta
     */
    public function estaDisponible()
    {
        return $this->activo && $this->en_venta && $this->stock_real > 0;
    }
    
    /**
     * Actualiza múltiples valores de stock a la vez
     */
    public function actualizarStock($disponible = null, $reservado = null, $minimo = null)
    {
        $updates = [];
        
        if (!is_null($disponible)) {
            $updates['stock_disponible'] = $disponible;
        }
        
        if (!is_null($reservado)) {
            $updates['stock_reservado'] = $reservado;
        }
        
        if (!is_null($minimo)) {
            $updates['stock_minimo'] = $minimo;
        }
        
        if (!empty($updates)) {
            $this->update($updates);
        }
    }
}