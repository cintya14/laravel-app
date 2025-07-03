<?php
namespace App\Livewire;

use App\Helpers\GestionarCarrito;
use App\Models\Direccion;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use Illuminate\Support\Facades\Log;
use MercadoPago\Exceptions\MPApiException;
use App\Mail\OrderPlaced;
use Illuminate\Support\Facades\Mail;



#[Title('Checkout')]
class CheckoutPage extends Component
{
    public $first_name;
    public $last_name;
    public $phone;
    public $street;
    public $city;
    public $state;
    public $zip_code;
    public $payment_method;

    public function placeOrder()
    {
        Log::info('Se ejecutó placeOrder');
       
        $this->validate([
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'phone' => 'required',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required',
        ]);

        try {
            $cart_items = GestionarCarrito::getCartItemsFromCookie();
            
            // Crear la orden primero
            $order = new Order();
            $order->user_id = auth('web')->user()->id;
            $order->total_general = GestionarCarrito::calculateGrandTotal($cart_items);
            $order->metodo_pago = $this->payment_method;
            $order->estado_pago = 'pendiente';
            $order->estado_pedido = 'nuevo';
            $order->moneda = 'PEN'; // Cambiado a PEN
            $order->metodo_envio = 'enviar pedido por ' . auth('web')->user()->nombre;
            $order->save();

            // Guardar dirección
            $address = new Direccion();
            $address->nombre = $this->first_name;
            $address->apellido = $this->last_name;
            $address->telefono = $this->phone;
            $address->direccion_calle = $this->street;
            $address->ciudad = $this->city;
            $address->pais = $this->state;
            $address->codigo_postal = $this->zip_code;
            $address->order_id = $order->id;
            $address->save();

            // Guardar items del pedido
            $order->items()->createMany($cart_items);

            $redirect_url = '';

            if($this->payment_method === 'mercado_pago') {
                // Configurar Mercado Pago
                MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
                
                $client = new PreferenceClient();
                
                // Preparar items para Mercado Pago
                $preference_items = [];
                foreach($cart_items as $item) {
                    $preference_items[] = [
                        'title' => $item['nombre'],
                        'quantity' => (int)$item['cantidad'],
                        'unit_price' => (float)$item['monto_unitario'],
                        'currency_id' => 'PEN', // Cambiado a PEN
                    ];
                }
$base_url = config('app.url');

Log::debug('Preferencia enviada a MP:', [
    'items' => $preference_items,
    'payer' => [
        'name' => $this->first_name,
        'surname' => $this->last_name,
        'email' => auth('web')->user()->email,
    ],
  'back_urls' => [
    'success' => $base_url . '/success/' . $order->id,
    'failure' => $base_url . '/cancel',
    'pending' => $base_url . '/success/' . $order->id,
],
    'auto_return' => 'approved',
    'external_reference' => (string)$order->id,
    'notification_url' => $base_url . '/webhook/mercadopago',
]);

$preference = $client->create([
    'items' => $preference_items,
    'payer' => [
        'name' => $this->first_name,
        'surname' => $this->last_name,
        'email' => auth('web')->user()->email,
    ],
   'back_urls' => [
    'success' => $base_url . '/success/' . $order->id,
    'failure' => $base_url . '/cancel',
    'pending' => $base_url . '/success/' . $order->id,
],
    'auto_return' => 'approved',
    'external_reference' => (string)$order->id,
    'notification_url' => $base_url . '/webhook/mercadopago',
]);

            
                $redirect_url = $preference->init_point;
                Log::info('Preference URL generado: ' . $redirect_url);

            } else {
                $redirect_url = route('success', ['order_id' => $order->id]);
            }
            Log::info('Redirigiendo a: ' . $redirect_url);
            GestionarCarrito::clearCartItems();
            Mail::to(request()->user())->send(new  OrderPlaced($order));

            
            return redirect($redirect_url);
            

} catch (MPApiException $e) {
    Log::error('Mercado Pago API Error: ' . $e->getMessage());
    Log::error('Respuesta completa de la API: ' . json_encode($e->getApiResponse(), JSON_PRETTY_PRINT));
    return back()->with('error', 'Error al procesar el pago con Mercado Pago.');
} catch (\Exception $e) {
    Log::error('Error general en checkout: ' . $e->getMessage());
    return back()->with('error', 'Error inesperado al procesar tu pedido.');
}

    }

    public function render()
    {
        $cart_items = GestionarCarrito::getCartItemsFromCookie();
        $grand_total = GestionarCarrito::calculateGrandTotal($cart_items);
        return view('livewire.checkout-page', [
            'cart_items' => $cart_items,
            'total' => $grand_total,
        ]);
    }
}