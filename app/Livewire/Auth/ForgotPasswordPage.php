<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Recuperar Contraseña')]
class ForgotPasswordPage extends Component
{
    public $email;
    public function save(){
        $this->validate([
            'email' => 'required|email|exists:users,email|max:255',
        ]);

        $status = Password::sendResetLink(
            ['email' => $this->email]);

            if ($status === Password::RESET_LINK_SENT) {
                session()->flash('success', 'Enlace de restablecimiento de contraseña enviado a su correo electrónico.');
                $this->email = ''; // Limpiar el campo de correo electrónico


       }  
    }


    public function render()
    {
        return view('livewire.auth.forgot-password-page');
    }
}
