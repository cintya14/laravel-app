<?php

namespace App\Livewire;

use App\Models\Producto;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Helpers\GestionarCarrito;
use App\Livewire\Partials\Navbar;

#[Title('Product Detail - CEBRICENHO')]
class ProductDetailPage extends Component {
    public $slug;
    public $cantidad = 1;
    public function mount($slug)
    {
        $this->slug = $slug;
    }

    public function incrementar(){
        $this->cantidad++;
    }

    public function decrementar(){
        if($this->cantidad > 1){
            $this->cantidad--;
        }
    }
      public function addToCart($producto_id){
        $total_count = GestionarCarrito::addItemToCartWithQty($producto_id, $this->cantidad);
        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);


            

    }   
   

    public function render()
    {
        return view('livewire.product-detail-page',[
            'producto'=> Producto::where('slug', $this->slug)->firstOrFail(),
        ]);
    }
}
