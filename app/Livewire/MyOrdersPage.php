<?php

namespace App\Livewire;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;

#[Title('Mis Pedidos')]
class MyOrdersPage extends Component
{
    use WithPagination;
    public function render()
    {
        $my_orders = Order::where('user_id', auth('web')->id())->latest()->paginate(5);
        return view('livewire.my-orders-page',[

            'orders' => $my_orders,
        ]);
        
    }
}
