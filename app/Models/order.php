<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;  
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
protected $fillable =[
'user_id',
'total_general',
'metodo_pago',
'estado_pago',
'estado_pedido',
'moneda',
'costo_envio',
'metodo_envio',
'notas', 
];

public function user(){
    return $this->belongsTo(User::class);

}
public function items(){
    return $this->hasMany(OrderItem::class);    
}

public function direccion(){
    return $this->hasOne(Direccion::class);    
}
}
