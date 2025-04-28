<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direccion extends Model
{
    use HasFactory;
    protected $table = 'direcciones';
    protected $fillable = [
        'order_id',
        'nombre',
        'apellido',
        'telefono',
        'direccion_calle',
        'ciudad',
        'pais',
        'codigo_postal',
    ];
   
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getFullNombreAttribute()
    {
        return "{$this->nombre} {$this->apellido}";
    }
}
