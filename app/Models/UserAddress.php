<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model {
    use HasFactory;
    protected $fillable = [
        'user_id', 'nombre', 'apellido', 'telefono', 'direccion',
        'ciudad', 'departamento', 'codigo_postal', 'predeterminada'
    ];
    
    public function user() { return $this->belongsTo(User::class); }
    
    public static function boot() {
        parent::boot();
        static::creating(function ($address) {
            if ($address->predeterminada) {
                self::where('user_id', $address->user_id)->update(['predeterminada' => false]);
            }
        });
        static::updating(function ($address) {
            if ($address->predeterminada) {
                self::where('user_id', $address->user_id)
                    ->where('id', '!=', $address->id)
                    ->update(['predeterminada' => false]);
            }
        });
    }
}