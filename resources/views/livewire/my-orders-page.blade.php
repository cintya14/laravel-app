<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
  <h1 class="text-4xl font-bold text-slate-500"> Mis pedidos</h1>
  <div class="flex flex-col bg-white p-5 rounded mt-4 shadow-lg">
    <div class="-m-1.5 overflow-x-auto">
      <div class="p-1.5 min-w-full inline-block align-middle">
        <div class="overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Pedido</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Fecha</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Estado de pedido</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase">Estado de pago</th>
                <th scope="col" class="px-6 py-3 text-start text-xs font-medium text-gray-500 uppercase"> Monto de pedido</th>
                <th scope="col" class="px-6 py-3 text-end text-xs font-medium text-gray-500 uppercase">Accion</th>
              </tr>
            </thead>
            <tbody>
              @foreach($orders as $order)

              @php
              $estado = '';
              $estado_pago = '';
              if ($order->estado_pedido == 'nuevo'){
                $estado = '<span class = "bg-blue-500 py-1 px-3 rounded text-white shadow">Nuevo</span>';
              }  
              if ($order->estado_pedido == 'proceso'){
                $estado = '<span class = "bg-orange-500 py-1 px-3 rounded text-white shadow"> En Proceso</span>';
              } 

              if ($order->estado_pedido == 'enviado'){
                $estado = '<span class = "bg-yellow-500 py-1 px-3 rounded text-white shadow">Enviado</span>';
              }
              if ($order->estado_pedido == 'entregado'){
                $estado = '<span class = "bg-green-500 py-1 px-3 rounded text-white shadow">Entregado</span>';
              }
              if ($order->estado_pedido == 'cancelado'){
                $estado = '<span class = "bg-red-500 py-1 px-3 rounded text-white shadow">Cancelado</span>';
              }
              switch ($order->estado_pago) {
                      case 'pendiente':
                          $estado_pago = '<span class="bg-yellow-500 py-1 px-3 rounded text-white shadow">Pendiente</span>';
                          break;
                      case 'completado':
                          $estado_pago = '<span class="bg-green-500 py-1 px-3 rounded text-white shadow">Pagado</span>';
                          break;
                      case 'fallido':
                          $estado_pago = '<span class="bg-red-500 py-1 px-3 rounded text-white shadow">Rechazado</span>';
                          break;
                      default:
                          $estado_pago = '<span class="bg-gray-500 py-1 px-3 rounded text-white shadow">Desconocido</span>';
                  }


              @endphp

              <tr class="odd:bg-white even:bg-gray-100 dark:odd:bg-slate-900 dark:even:bg-slate-800" wire:key='{{ $order->id }}'>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-800 dark:text-gray-200"> {{$order->id}} </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"> {{$order->created_at->format('d-m-y')}} </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"> {!! $estado !!} </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"> {!!$estado_pago!!} </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 dark:text-gray-200"> {{Number::currency($order->total_general, 'PEN')}} </td>
                <td class="px-6 py-4 whitespace-nowrap text-end text-sm font-medium">
                  <a href="/my-orders/{{$order->id}}" class="bg-slate-600 text-white py-2 px-4 rounded-md hover:bg-slate-500">ver detalles</a>
                </td>
              </tr>
              @endforeach

             

            </tbody>
          </table>
        </div>
      </div>
       {{$orders->links()}}
    </div>
  </div>
</div>