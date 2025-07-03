<?php

use App\Livewire\Auth\ForgotPasswordPage;
use App\Livewire\Auth\LoginPage;
use App\Livewire\Auth\RegisterPage;
use App\Livewire\Auth\ResetPasswordPage;
use App\Livewire\Cancelpage;
use App\Livewire\CartePage;
use App\Livewire\CategoriasPage;
use App\Livewire\CheckoutPage;
use App\Livewire\HomePage;
use App\Livewire\MyOrderDetailPage;
use App\Livewire\MyOrdersPage;
use App\Livewire\ProductDetailPage;
use App\Livewire\ProductosPage;
use App\Livewire\SuccessPage;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MercadoPagoWebhookController;

Route::get('/', HomePage::class);
Route::get('/categories', CategoriasPage ::class);
Route::get('/products', ProductosPage::class);
Route::get('/cart', CartePage::class);
Route::get('/products/{slug}',ProductDetailPage::class);


Route::post('/webhook/mercadopago', [MercadoPagoWebhookController::class, 'handle']);

Route::middleware('guest')->group(function(){
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class);
    Route::get('/forgot', ForgotPasswordPage::class)->name('password.request');
    Route::get('/reset/{token}', ResetPasswordPage::class)->name('password.reset');

});

Route::middleware('auth')->group(function(){

    Route::get('/logout', function () {
        auth('web')->logout();
        return redirect('/');
    });
    Route::get('/checkout', CheckoutPage::class);
    Route::get('/my-orders', MyOrdersPage::class);
    Route::get('/my-orders/{order_id}', MyOrderDetailPage::class)->name('my-orders.show');
    Route::get('/success/{order_id}', SuccessPage::class)->name('success');
    Route::get('/cancel', Cancelpage::class)->name('cancel');

});

