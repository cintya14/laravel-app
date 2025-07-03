<?php

namespace App\Livewire;

use App\Helpers\GestionarCarrito;
use App\Models\Producto;
use App\Models\Marca;
use App\Models\Categoria;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Livewire\Partials\Navbar;







#[Title('Productos - CEBRICENHO')]
class ProductosPage extends Component{
  
   
    use WithPagination;
      
    #[Url]
    public $select_categorias=[];
    #[Url]
    public $select_marcas=[];

    #[Url]
    public $destacado;

    #[Url]
    public $en_oferta;

    #[Url]
    public $rango_precio = 10000;

    #[Url]
    public $ordenar = 'ultimo'; 

    public function addToCart($producto_id){
        $total_count = GestionarCarrito::addItemToCart($producto_id);
        $this->dispatch('update-cart-count', total_count: $total_count)->to(Navbar::class);

            

    }   

    public function render()
    {
        $productQuery= Producto::query()->where('activo', 1);
        if(!empty($this->select_categorias)){
            $productQuery->whereIn('categoria_id', $this->select_categorias);
        }

        if(!empty($this->select_marcas)){
            $productQuery->whereIn('marca_id', $this->select_marcas);
        }

        if($this->destacado)
        {
            $productQuery->where('destacado', 1);
        }

        if($this->en_oferta)
        {
            $productQuery->where('en_oferta', 1);
        }

        if($this->rango_precio )
        {
            $productQuery->whereBetween('precio' ,[0,  $this->rango_precio]);
        }

        if($this->ordenar == 'ultimo')
        {
            $productQuery->latest();
        }

        if($this->ordenar == 'precio')
        {
            $productQuery->orderBy('precio');
        }


        return view('livewire.productos-page', [
            'productos'=> $productQuery->paginate(9),
            'marcas'=> Marca::where('activo', 1)->get(['id', 'nombre', 'slug']),
            'categorias'=> Categoria::where('activo', 1)->get(['id', 'nombre', 'slug']),
        ]);
    }
}
