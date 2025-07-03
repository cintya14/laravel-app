<x-mail::message>
# ¡Pedido realizado exitosamente!

Gracias por tu compra. Tu pedido ha sido recibido y está siendo procesado : {{$order->id}}.

<x-mail::button :url="$url">
Ver mi pedido
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
