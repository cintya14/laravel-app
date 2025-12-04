<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockReserva extends Model {
    use HasFactory;
    protected $fillable = ['producto_id', 'session_id', 'user_id', 'cantidad', 'expira_en', 'estado'];
    protected $casts = ['expira_en' => 'datetime'];
    
    public function producto() { return $this->belongsTo(Producto::class); }
    public function user() { return $this->belongsTo(User::class); }
    
    public function scopeActivas($query) {
        return $query->where('estado', 'activa')->where('expira_en', '>', now());
    }
    
    public function scopeExpiradas($query) {
        return $query->where('estado', 'activa')->where('expira_en', '<=', now());
    }
}