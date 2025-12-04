<?php

namespace App\Livewire;

use Livewire\Component;

class ProductSearch extends Component
{

     public $search = '';
    public function render()
    {
        return view('livewire.product-search');
    }
}
