<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * Atributos asignables en masa.
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'is_admin',           // <- importante
    ];

    /**
     * Atributos ocultos.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',   // <- importante
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Agrega esto en tu modelo User.php
    public function addresses() {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Filament: ¿puede acceder al panel?
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;   // <- regla única y clara
    }
}
