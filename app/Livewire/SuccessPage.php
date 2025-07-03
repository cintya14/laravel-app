<?php


namespace App\Livewire;

use Livewire\Attributes\Title;
use App\Models\Order;
use Livewire\Attributes\Url;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient; // Cambiado a PaymentClient
use Livewire\Component;

#[Title('Success Page')]
class SuccessPage extends Component
{
    #[Url]
    public $payment_id; // Cambiado de session_id a payment_id
    #[Url]
    public $payment_status; // Nuevo parámetro para el estado
    public $order;

    public function mount($order_id)
    {
        $this->order = Order::with('direccion')
            ->where('user_id', auth('web')->user()->id)
            ->where('id', $order_id)
            ->first();
            
        // Solo procesar si tenemos payment_id y el pago aún está pendiente
        if (!$this->order) {
            return redirect()->route('home');
        }

        if ($this->payment_id && $this->order->estado_pago == 'pendiente') {
            $this->verifyPayment();
        }
    }

       private function verifyPayment()
    {
        try {
            // Configurar Mercado Pago
            MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
            $client = new PaymentClient();
            $payment = $client->get($this->payment_id);

            // Actualizar el estado del pago en la orden
            $this->order->estado_pago = $this->mapPaymentStatus($payment->status);
            $this->order->save();

            // Si el pago falló, redirigir a cancel
            if ($this->order->estado_pago == 'fallido') {
                return redirect()->route('cancel');
            }

        } catch (\Exception $e) {
            // Loggear el error
            logger()->error('Error al verificar el pago: ' . $e->getMessage());
            // Podrías decidir no hacer nada o mostrar un mensaje
        }
    }

    private function mapPaymentStatus($mpStatus)
    {
        return match($mpStatus) {
            'approved' => 'completado',
            'pending', 'in_process' => 'pendiente',
            'rejected', 'cancelled' => 'fallido',
            default => 'pendiente',
        };
    }

    public function render()
    {
        // Redirigir solo si el pago falló
        if ($this->order->estado_pago === 'fallido') {
            return redirect()->route('cancel');
        }

        return view('livewire.success-page', [
            'order' => $this->order,
        ]);
    }
}
