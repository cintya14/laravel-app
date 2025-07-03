<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

use App\Models\Order;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Webhook recibido', $request->all());
if ($request->input('type') !== 'payment') {
        return response()->json(['status' => 'ignored']);
    }

    try {
        $payment_id = $request->input('data.id');
        MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
        $client = new PaymentClient();
        $payment = $client->get($payment_id);

        // DEBUG: Loggear estado del pago
        Log::info('Estado del pago', [
            'payment_id' => $payment_id,
            'status' => $payment->status,
            'external_reference' => $payment->external_reference
        ]);

        $order = Order::find($payment->external_reference);
        
        if (!$order) {
            Log::error('Orden no encontrada', ['external_reference' => $payment->external_reference]);
            return response()->json(['status' => 'order_not_found']);
        }

        $order->estado_pago = $this->mapPaymentStatus($payment->status);
        $order->payment_id = $payment_id;
        $order->save();

        Log::info('Orden actualizada', [
            'order_id' => $order->id,
            'new_status' => $order->estado_pago
        ]);

    } catch (\Exception $e) {
        Log::error('Error procesando webhook: ' . $e->getMessage());
    }

    return response()->json(['status' => 'ok']);
}

    private function mapPaymentStatus($mpStatus)
    {
        return match($mpStatus) {
            'approved' => 'completado',
            'pending', 'in_process', 'authorized' => 'pendiente',
            'rejected', 'cancelled', 'refunded', 'charged_back' => 'fallido',
            default => 'pendiente',
        };
    }
}
