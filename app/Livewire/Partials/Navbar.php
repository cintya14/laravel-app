<?php

namespace App\Livewire\Partials;

use Livewire\Component;
use App\Helpers\GestionarCarrito;
use Livewire\Attributes\On;

class Navbar extends Component

{
    public $total_count = 0;

    public function mount(){
    $this->total_count = count(GestionarCarrito::getCartItemsFromCookie());
}

#[On('update-cart-count')]
public function updateCartCount($total_count)
{
    $this->total_count = $total_count;
}



    public function render()
    {
        return view('livewire.partials.navbar');
    }
}
