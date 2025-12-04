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
    public $payment_method = 'cod';

    public function placeOrder()
    {
        Log::info('=== CLIC EN PAGAR ===', [
            'payment_method' => $this->payment_method,
            'user' => auth()->id()
        ]);
        
        // Validación
        $this->validate([
            'first_name' => 'required|min:2',
            'last_name' => 'required|min:2',
            'phone' => 'required',
            'street' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip_code' => 'required',
            'payment_method' => 'required|in:cod,mercado_pago',
        ]);
        
        try {
            $cart_items = GestionarCarrito::getCartItemsFromCookie();
            
            if(empty($cart_items)) {
                return redirect('/cart')->with('error', 'Carrito vacío');
            }
            
            // ============================================
            // MERCADO PAGO
            // ============================================
            if($this->payment_method === 'mercado_pago') {
                try {
                    // PRIMERO: Crear orden con estado pendiente
                    $order = Order::create([
                        'user_id' => auth()->id(),
                        'total_general' => GestionarCarrito::calculateGrandTotal($cart_items),
                        'metodo_pago' => 'mercado_pago',
                        'estado_pago' => 'pendiente',
                        'estado_pedido' => 'nuevo',
                    ]);
                    
                    // Dirección
                    Direccion::create([
                        'nombre' => $this->first_name,
                        'apellido' => $this->last_name,
                        'telefono' => $this->phone,
                        'direccion_calle' => $this->street,
                        'ciudad' => $this->city,
                        'pais' => $this->state,
                        'codigo_postal' => $this->zip_code,
                        'order_id' => $order->id,
                    ]);
                    
                    // Items
                    foreach($cart_items as $item) {
                        $order->items()->create([
                            'producto_id' => $item['producto_id'],
                            'cantidad' => $item['cantidad'],
                            'monto_unitario' => $item['monto_unitario'],
                            'monto_total' => $item['monto_total'],
                        ]);
                    }
                    
                    Log::info('✅ Orden creada (pendiente de pago)', ['id' => $order->id]);
                    
                    // Configurar Mercado Pago
                    MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
                    
                    Log::info('=== CONFIGURACIÓN MP ===', [
                        'app_url' => config('app.url'),
                        'order_id' => $order->id,
                    ]);
                    
                    $client = new PreferenceClient();
                    
                    // Items para MP
                    $mp_items = [];
                    foreach($cart_items as $item) {
                        $mp_items[] = [
                            'title' => $item['nombre'],
                            'quantity' => (int)$item['cantidad'],
                            'unit_price' => (float)$item['monto_unitario'],
                            'currency_id' => 'PEN',
                        ];
                    }
                    
                    Log::info('📦 Items para MP:', ['items' => $mp_items]);
                    
                    // Crear preferencia
                    $preferenceData = [
                        'items' => $mp_items,
                        'payer' => [
                            'name' => $this->first_name,
                            'surname' => $this->last_name,
                            'email' => 'test_user_2522779816@testuser.com',
                        ],
                        'back_urls' => [
                            'success' => config('app.url') . '/success/' . $order->id,
                            'failure' => config('app.url') . '/cancel',
                            'pending' => config('app.url') . '/success/' . $order->id,
                        ],
                        'auto_return' => 'approved',
                        'external_reference' => (string)$order->id,
                        'notification_url' => config('app.url') . '/webhook/mercadopago',
                        'binary_mode' => true,
                    ];
                    
                    Log::info('📄 Creando preferencia MP:', $preferenceData);
                    
                    $preference = $client->create($preferenceData);
                    
                    Log::info('✅ Preferencia MP creada exitosamente', [
                        'preference_id' => $preference->id,
                        'init_point' => $preference->init_point,
                        'order_id' => $order->id
                    ]);
                    
                    // NO limpiar carrito todavía, se limpiará después del pago
                    
                    return redirect()->away($preference->init_point);
                    
                } catch (MPApiException $e) {
                    Log::error('❌ ERROR MERCADO PAGO API', [
                        'status_code' => $e->getStatusCode(),
                        'message' => $e->getMessage(),
                        'api_response' => $e->getApiResponse(),
                    ]);
                    
                    // Eliminar orden fallida si se creó
                    if (isset($order)) {
                        $order->delete();
                        Log::info('🗑️ Orden eliminada por error en MP', ['order_id' => $order->id]);
                    }
                    
                    return back()->with('error', 'Error de Mercado Pago: ' . $e->getMessage());
                    
                } catch (\Exception $e) {
                    Log::error('❌ ERROR GENERAL MP', [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ]);
                    
                    // Eliminar orden fallida si se creó
                    if (isset($order)) {
                        $order->delete();
                    }
                    
                    return back()->with('error', 'Error al procesar el pago: ' . $e->getMessage());
                }
            } 
            // ============================================
            // CONTRA ENTREGA
            // ============================================
            else {
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'total_general' => GestionarCarrito::calculateGrandTotal($cart_items),
                    'metodo_pago' => $this->payment_method,
                    'estado_pago' => 'pendiente',
                    'estado_pedido' => 'nuevo',
                ]);
                
                // Dirección
                Direccion::create([
                    'nombre' => $this->first_name,
                    'apellido' => $this->last_name,
                    'telefono' => $this->phone,
                    'direccion_calle' => $this->street,
                    'ciudad' => $this->city,
                    'pais' => $this->state,
                    'codigo_postal' => $this->zip_code,
                    'order_id' => $order->id,
                ]);
                
                // Items
                foreach($cart_items as $item) {
                    $order->items()->create([
                        'producto_id' => $item['producto_id'],
                        'cantidad' => $item['cantidad'],
                        'monto_unitario' => $item['monto_unitario'],
                        'monto_total' => $item['monto_total'],
                    ]);
                }
                
                Log::info('✅ Orden COD creada', ['id' => $order->id]);
                
                GestionarCarrito::clearCartItems();
                Mail::to(Auth::user()->email)->send(new OrderPlaced($order));
                
                return redirect()->route('success', ['order_id' => $order->id]);
            }
            
        } catch (\Exception $e) {
            Log::error('❌ Error general en checkout: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    
    public function render()
    {
        $cart_items = GestionarCarrito::getCartItemsFromCookie();
        $total = GestionarCarrito::calculateGrandTotal($cart_items);
        
        return view('livewire.checkout-page', [
            'cart_items' => $cart_items,
            'total' => $total,
        ]);
    }
}