<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Login')]
class LoginPage extends Component
{
    public $email;
    public $password;

    public function save()
    {
        $this->validate([
            'email'    => 'required|email|max:255|exists:users,email',
            'password' => 'required|min:6|max:255',
        ]);

        // Autenticación en guard del FRONT
        if (! Auth::guard('web')->attempt([
            'email'    => $this->email,
            'password' => $this->password,
        ])) {
            session()->flash('error', 'Estas credenciales no coinciden con nuestros registros.');
            return;
        }

        // Seguridad: regenerar la sesión
        session()->regenerate();

        // Si hay una intended hacia /admin, limpiarla
        if (Session::has('url.intended') && str_contains(Session::get('url.intended'), '/admin')) {
            Session::forget('url.intended');
        }

        // Redirigir SIEMPRE al home del cliente (ajusta a tu ruta)
        return redirect()->to('/');
 // o: return redirect()->to('/');
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
