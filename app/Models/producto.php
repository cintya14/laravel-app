<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $fillable = [
        'categoria_id',
        'marca_id',
        'nombre',
        'slug',
        'imagenes',
        'descripcion',
        'precio',
        'activo',
        'destacado',
        'en_stock',
        'en_venta',
    ];
    protected $casts=[
        'imagenes'=>'array'
    ];

    public function categoria(){
        return $this->belongsTo(Categoria::class);
    }

    public function marca(){
        return $this->belongsTo(Marca::class);
    }

    public function orderItems(){
        return $this->hasMany(OrderItem::class);    

    }

    






}
