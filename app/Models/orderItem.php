<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'producto_id',
        'cantidad',
        'monto_unitario',
        'monto_total',
    ];
    

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

}
