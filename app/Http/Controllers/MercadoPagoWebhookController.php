<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Direccion;
use App\Helpers\GestionarCarrito;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Payment\PaymentClient;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('🔔 Webhook MP recibido', $request->all());
        
        // Solo procesar notificaciones de pago
        if ($request->input('type') !== 'payment') {
            return response()->json(['status' => 'ignored'], 200);
        }

        try {
            $payment_id = $request->input('data.id');
            
            // Obtener detalles del pago desde Mercado Pago
            MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));
            $client = new PaymentClient();
            $payment = $client->get($payment_id);

            Log::info('💳 Detalles del pago', [
                'payment_id' => $payment_id,
                'status' => $payment->status,
                'external_reference' => $payment->external_reference ?? 'null',
            ]);

            // CASO 1: Pago APROBADO - Crear orden si no existe
            if ($payment->status === 'approved') {
                
                // Buscar si ya existe una orden con este payment_id
                $existingOrder = Order::where('payment_id', $payment_id)->first();
                
                if ($existingOrder) {
                    Log::info('⚠️ Orden ya existe para este pago', ['order_id' => $existingOrder->id]);
                    return response()->json(['status' => 'already_processed'], 200);
                }
                
                // Obtener external_reference (order_id temporal o user_id)
                $external_ref = $payment->external_reference;
                
                if (!$external_ref) {
                    Log::error('❌ No hay external_reference en el pago');
                    return response()->json(['error' => 'missing_reference'], 400);
                }
                
                // Buscar orden pendiente por external_reference
                $order = Order::where('id', $external_ref)
                             ->where('estado_pago', 'pendiente')
                             ->first();
                
                if ($order) {
                    // OPCIÓN A: Actualizar orden existente
                    $order->update([
                        'estado_pago' => 'completado',
                        'payment_id' => $payment_id,
                    ]);
                    
                    Log::info('✅ Orden actualizada a completado', [
                        'order_id' => $order->id,
                        'payment_id' => $payment_id
                    ]);
                    
                } else {
                    Log::error('❌ No se encontró orden pendiente', [
                        'external_reference' => $external_ref
                    ]);
                    
                    // Podrías crear una nueva orden aquí si tienes los datos guardados
                    // Por ahora, solo registramos el error
                }
                
                return response()->json(['status' => 'success'], 200);
            }
            
            // CASO 2: Pago RECHAZADO/CANCELADO - Marcar como fallido
            if (in_array($payment->status, ['rejected', 'cancelled', 'refunded', 'charged_back'])) {
                
                $external_ref = $payment->external_reference;
                
                if ($external_ref) {
                    $order = Order::find($external_ref);
                    
                    if ($order) {
                        $order->update([
                            'estado_pago' => 'fallido',
                            'payment_id' => $payment_id,
                        ]);
                        
                        Log::info('❌ Orden marcada como fallida', [
                            'order_id' => $order->id,
                            'reason' => $payment->status
                        ]);
                    }
                }
                
                return response()->json(['status' => 'failed'], 200);
            }
            
            // CASO 3: Pago PENDIENTE - Actualizar estado
            if (in_array($payment->status, ['pending', 'in_process', 'authorized'])) {
                
                $external_ref = $payment->external_reference;
                
                if ($external_ref) {
                    $order = Order::find($external_ref);
                    
                    if ($order) {
                        $order->update([
                            'estado_pago' => 'pendiente',
                            'payment_id' => $payment_id,
                        ]);
                        
                        Log::info('⏳ Orden en estado pendiente', [
                            'order_id' => $order->id,
                            'status' => $payment->status
                        ]);
                    }
                }
                
                return response()->json(['status' => 'pending'], 200);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('❌ Error procesando webhook MP', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}