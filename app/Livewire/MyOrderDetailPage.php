<?php

namespace App\Livewire;

use App\Models\OrderItem;
use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Direccion;
use App\Models\Order;

#[Title('Detalle de Pedido')]
class MyOrderDetailPage extends Component
{
    public $order_id;
    public function mount($order_id)
    {
        $this->order_id = $order_id;
    }

    public function render()
    {
        $order_items = OrderItem::with('producto')->where('order_id', $this->order_id)->get();
        $direccion = Direccion::where('order_id', $this->order_id)->first();
        $order = order::where('id', $this->order_id)->first();
        return view('livewire.my-order-detail-page', [
            'order_items' => $order_items,
            'direccion' => $direccion,
            'order' => $order,
        ]);
    }
}
