<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Marca;
use Livewire\Attributes\Title;
use Livewire\Component;
#[Title('Home Page - CBRICENHO')]

class HomePage extends Component
{
    public function render()
    {
        $marcas = Marca::where('activo', 1)->get();
        $categorias = Categoria::where('activo', 1)->get();
        return view('livewire.home-page', [
             'marcas' => $marcas,
             'categorias' => $categorias,
             ]);
        
      
    } 
}
