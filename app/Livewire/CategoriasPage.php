<?php

namespace App\Livewire;

use App\Models\Categoria;
use Livewire\Component;

class CategoriasPage extends Component
{
    public function render()
    {
        $categorias = Categoria::where('activo', 1)->get();   
        return view('livewire.categorias-page',[
            'categorias' => $categorias

        ]);
            
    }
}
