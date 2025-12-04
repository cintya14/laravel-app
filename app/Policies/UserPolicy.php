<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function view(User $user, User $model): bool
    {
        return $user->is_admin === true;
    }

    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function update(User $user, User $model): bool
    {
        return $user->is_admin === true;
    }

    public function delete(User $actor, User $model): bool
    {
        // 1) nunca borrar admins
        if ($model->is_admin) {
            return false;
        }

        // 2) no permitir borrarse a sí mismo
        if ($actor->id === $model->id) {
            return false;
        }

        // 3) (opcional) no dejar el sistema sin admins
        $adminCount = User::where('is_admin', true)->count();
        if ($adminCount <= 1) {
            // si solo queda 1 admin, impide borrar cualquier cosa que deje al sistema sin control
            // (puedes comentar este bloque si no lo necesitas)
        }

        return $actor->is_admin === true;
    }

    public function restore(User $user, User $model): bool
    {
        return $user->is_admin === true;
    }

    public function forceDelete(User $user, User $model): bool
    {
        // Igual que delete
        if ($model->is_admin) return false;
        if ($user->id === $model->id) return false;
        return $user->is_admin === true;
    }
}
