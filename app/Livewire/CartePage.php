<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use App\Helpers\GestionarCarrito;
use App\Livewire\Partials\Navbar;
use Livewire\Component;

#[Title('Carrito - CBRICENHO')]
class CartePage extends Component
{

    public $cart_items = [];
    public $grand_total;

    public function mount(){
        $this->cart_items = gestionarCarrito::getCartItemsFromCookie();
        $this->grand_total = gestionarCarrito::calculateGrandTotal($this->cart_items);
    }

    public function removeItem($producto_id){
        $this->cart_items = gestionarCarrito::removeCartItem($producto_id);
        $this->grand_total = gestionarCarrito::calculateGrandTotal($this->cart_items);
        $this->dispatch('update-cart-count', total_count: count($this->cart_items))->to(Navbar::class);
    }

    public function incrementar($producto_id){
        $this->cart_items = gestionarCarrito::incremenQuantityToCartItem($producto_id);
        $this->grand_total = gestionarCarrito::calculateGrandTotal($this->cart_items);
        

    }

    public function decrementar($producto_id){
        $this->cart_items = gestionarCarrito::decrementQuantityToCartItem($producto_id);
        $this->grand_total = gestionarCarrito::calculateGrandTotal($this->cart_items);
        

    }

 


    public function render()
    {
        return view('livewire.carte-page');
    }
}
