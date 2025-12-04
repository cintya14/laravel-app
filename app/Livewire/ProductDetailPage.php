<?php

namespace App\Livewire;

use App\Models\Producto;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Helpers\GestionarCarrito;
use App\Livewire\Partials\Navbar;

#[Title('Product Detail - CBRICENHO')]
class ProductDetailPage extends Component {
    public $slug;
    public $cantidad = 1;
    public $max_cantidad = 1;
    
    public function mount($slug)
    {
        $this->slug = $slug;
        $producto = Producto::where('slug', $this->slug)->firstOrFail();
        $this->max_cantidad = max(1, $producto->stock_real);
    }
    
    public function incrementar(){
        $producto = Producto::where('slug', $this->slug)->firstOrFail();
        
        if($this->cantidad < $producto->stock_real) {
            $this->cantidad++;
        } else {
            session()->flash('error', 'Has alcanzado el máximo disponible para este producto.');
        }
    }
    
    public function decrementar(){
        if($this->cantidad > 1){
            $this->cantidad--;
        }
    }
    
    public function addToCart($producto_id){
        $producto = Producto::find($producto_id);
        
        // Validar stock usando stock_real
        if (!$producto || $producto->stock_real <= 0) {
            session()->flash('error', '❌ Este producto está agotado.');
            return;
        }
        
        if (!$producto->tieneStockPara($this->cantidad)) {
            session()->flash('error', 
                '❌ Stock insuficiente. Solo hay ' . $producto->stock_real . ' unidades disponibles.');
            return;
        }
        
        try {
            $total_count = GestionarCarrito::addItemToCartWithQty($producto_id, $this->cantidad);
            $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);
            session()->flash('success', '✅ Producto agregado al carrito!');
            
            // Resetear cantidad
            $this->cantidad = 1;
            
            // Actualizar max_cantidad después de agregar al carrito
            $producto->refresh();
            $this->max_cantidad = max(1, $producto->stock_real);
            
        } catch (\Exception $e) {
            session()->flash('error', '❌ ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        $producto = Producto::where('slug', $this->slug)->firstOrFail();
        
        return view('livewire.product-detail-page',[
            'producto' => $producto,
        ]);
    }
}