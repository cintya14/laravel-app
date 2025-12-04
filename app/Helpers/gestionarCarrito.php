<?php

namespace App\Helpers;  

use App\Models\StockReserva;

use Illuminate\Support\Facades\Cookie;
use App\Models\Producto;

class GestionarCarrito {


//agregar item a carrito
static public function addItemToCart($producto_id){
    // VERIFICAR STOCK
    $producto = Producto::findOrFail($producto_id);
    
    if (!$producto->tieneStock(1)) {
        throw new \Exception("No hay suficiente stock para {$producto->nombre}");
    }
    
    // RESERVAR STOCK
    $sessionId = session()->getId();
    $userId = auth()->id();
    $reserva = $producto->reservarStock(1, $sessionId, $userId);
    
    $cart_items = self::getCartItemsFromCookie();
    $existing_item = null;

    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            $existing_item = $key;
            break;
        }
    }

    if($existing_item !== null) {
        // Liberar reserva anterior y crear una nueva con la cantidad actualizada
        if (isset($cart_items[$existing_item]['stock_reserva_id'])) {
            $producto->liberarStockReservado(
                $cart_items[$existing_item]['cantidad'], 
                $cart_items[$existing_item]['stock_reserva_id'],
                "Actualización de cantidad en carrito"
            );
        }
        
        // Nueva reserva con cantidad +1
        $nuevaReserva = $producto->reservarStock($cart_items[$existing_item]['cantidad'] + 1, $sessionId, $userId);
        
        $cart_items[$existing_item]['cantidad']++;
        $cart_items[$existing_item]['monto_total'] = $cart_items[$existing_item]['cantidad'] * $cart_items[$existing_item]['monto_unitario'];
        $cart_items[$existing_item]['stock_reserva_id'] = $nuevaReserva->id;
        
    } else {
        $producto = Producto::where('id', $producto_id)->first([
            'id',
            'nombre',
            'precio',
            'imagenes',
            'slug',
        ]);

        if($producto){
            // Cambia 'imagen' por 'imagenes'
            $cart_items[] =[
                'producto_id'=> $producto_id,
                'nombre' => $producto->nombre,
                'slug' => $producto->slug,
                'imagenes' => $producto->imagenes[0] ?? null,  // ← Cambiado a 'imagenes'
                'cantidad' => 1,
                'monto_unitario' => $producto->precio, 
                'monto_total' => $producto->precio,
                'stock_reserva_id' => $reserva->id,
            ];

            
        }
    }

    self::addCartItemsToCookie($cart_items);
    return count($cart_items);
}

//cortar.


//agregar item a carrito
static public function addItemToCartWithQty($producto_id, $qty = 1){
    // VERIFICAR STOCK
    $producto = Producto::findOrFail($producto_id);
    
    if (!$producto->tieneStock($qty)) {
        throw new \Exception("No hay suficiente stock para {$producto->nombre}");
    }
    
    // RESERVAR STOCK
    $sessionId = session()->getId();
    $userId = auth()->id();
    $reserva = $producto->reservarStock($qty, $sessionId, $userId);
    
    $cart_items = self::getCartItemsFromCookie();
    $existing_item = null;

    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            $existing_item = $key;
            break;
        }
    }

    if($existing_item !== null) {
        // Liberar reserva anterior
        if (isset($cart_items[$existing_item]['stock_reserva_id'])) {
            $producto->liberarStockReservado(
                $cart_items[$existing_item]['cantidad'], 
                $cart_items[$existing_item]['stock_reserva_id'],
                "Actualización de cantidad en carrito"
            );
        }
        
        // Nueva reserva con la nueva cantidad
        $nuevaReserva = $producto->reservarStock($qty, $sessionId, $userId);
        
        $cart_items[$existing_item]['cantidad'] = $qty;
        $cart_items[$existing_item]['monto_total'] = $qty * $cart_items[$existing_item]['monto_unitario'];
        $cart_items[$existing_item]['stock_reserva_id'] = $nuevaReserva->id;
        
    } else {
        $producto = Producto::where('id', $producto_id)->first([
            'id',
            'nombre',
            'precio',
            'imagenes',
            'slug',
        ]);

        if($producto){
            $cart_items[] =[
                'producto_id'=> $producto_id,
                'nombre' => $producto->nombre,
                'slug' => $producto->slug,
                'imagen' => $producto->imagenes[0] ?? null,
                'cantidad' => $qty,
                'monto_unitario' => $producto->precio, 
                'monto_total' => $producto->precio * $qty,
                'stock_reserva_id' => $reserva->id,
            ];
        }
    }

    self::addCartItemsToCookie($cart_items);
    return count($cart_items);
}

//eliminar item del carrito

static public function removeCartItem($producto_id) {
    $cart_items = self::getCartItemsFromCookie();

    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            // LIBERAR STOCK RESERVADO
            if (isset($item['stock_reserva_id'])) {
                $producto = Producto::find($producto_id);
                if ($producto) {
                    $producto->liberarStockReservado(
                        $item['cantidad'], 
                        $item['stock_reserva_id'],
                        "Eliminado del carrito"
                    );
                }
            }
            
            unset($cart_items[$key]); // Mantener los items que no son el eliminado
        }
    }

    self::addCartItemsToCookie($cart_items);
    return $cart_items;
}



//agregar items al cookie
static public  function addCartItemsToCookie($cart_items) {
    Cookie::queue('cart_items', json_encode($cart_items), 60*24*30); // 30 dias
}

//clear los items del carrito
static public function clearCartItems() {
    $cart_items = self::getCartItemsFromCookie();
    
    // LIBERAR TODAS LAS RESERVAS
    foreach ($cart_items as $item) {
        if (isset($item['stock_reserva_id'])) {
            $producto = Producto::find($item['producto_id']);
            if ($producto) {
                $producto->liberarStockReservado(
                    $item['cantidad'], 
                    $item['stock_reserva_id'],
                    "Carrito vaciado"
                );
            }
        }
    }
    
    Cookie::queue(Cookie::forget('cart_items'));
}

//obtener el carrito
static public function getCartItemsFromCookie(){
    $cart_items = json_decode(Cookie::get('cart_items'), true);
    if(!$cart_items) {
        $cart_items = [];
    }

    // NORMALIZAR LOS ITEMS: convertir 'imagen' a 'imagenes'
    foreach ($cart_items as &$item) {
        // Si existe 'imagen' pero no 'imagenes', convertir
        if (isset($item['imagen']) && !isset($item['imagenes'])) {
            $item['imagenes'] = $item['imagen'];
            unset($item['imagen']);
        }
        // Si no existe ninguna, poner una por defecto
        if (!isset($item['imagenes'])) {
            $item['imagenes'] = 'default.jpg';
        }
    }

    return $cart_items;
}

//incrementar cantidad de un item del carrito

static public function incremenQuantityToCartItem($producto_id) {
    // VERIFICAR STOCK
    $producto = Producto::findOrFail($producto_id);
    
    if (!$producto->tieneStock(1)) {
        throw new \Exception("No hay más stock disponible para {$producto->nombre}");
    }
    
    $cart_items = self::getCartItemsFromCookie();
    
    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            // Liberar reserva anterior
            if (isset($cart_items[$key]['stock_reserva_id'])) {
                $producto->liberarStockReservado(
                    $cart_items[$key]['cantidad'], 
                    $cart_items[$key]['stock_reserva_id'],
                    "Incremento de cantidad en carrito"
                );
            }
            
            // Nueva reserva con cantidad +1
            $sessionId = session()->getId();
            $userId = auth()->id();
            $nuevaReserva = $producto->reservarStock($cart_items[$key]['cantidad'] + 1, $sessionId, $userId);
            
            $cart_items[$key]['cantidad']++;
            $cart_items[$key]['monto_total'] = $cart_items[$key]['cantidad'] * $cart_items[$key]['monto_unitario'];
            $cart_items[$key]['stock_reserva_id'] = $nuevaReserva->id;
            break;
        }
    }

    self::addCartItemsToCookie($cart_items);
    return $cart_items;
}

//decrementar cantidad de un item del carrito

static public function decrementQuantityToCartItem($producto_id) {
    $cart_items = self::getCartItemsFromCookie();

    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            if ($cart_items[$key]['cantidad'] > 1) {
                // Liberar reserva anterior
                if (isset($cart_items[$key]['stock_reserva_id'])) {
                    $producto = Producto::find($producto_id);
                    $producto->liberarStockReservado(
                        $cart_items[$key]['cantidad'], 
                        $cart_items[$key]['stock_reserva_id'],
                        "Decremento de cantidad en carrito"
                    );
                    
                    // Nueva reserva con cantidad -1
                    $sessionId = session()->getId();
                    $userId = auth()->id();
                    $nuevaReserva = $producto->reservarStock($cart_items[$key]['cantidad'] - 1, $sessionId, $userId);
                    
                    $cart_items[$key]['stock_reserva_id'] = $nuevaReserva->id;
                }
                
                $cart_items[$key]['cantidad']--;
                $cart_items[$key]['monto_total'] = $cart_items[$key]['cantidad'] * $cart_items[$key]['monto_unitario'];
            }
            break;
        }
    }

    self::addCartItemsToCookie($cart_items);
    return $cart_items;
}


//calcular el total del carrito

static public function calculateGrandTotal($items) {
    return array_sum(array_column($items, 'monto_total')); 

}

// NUEVO MÉTODO: Limpiar reservas expiradas
static public function limpiarReservasExpiradas() {
    $reservasExpiradas = StockReserva::expiradas()->get();
    $liberadas = 0;
    
    foreach ($reservasExpiradas as $reserva) {
        $producto = $reserva->producto;
        $producto->liberarStockReservado($reserva->cantidad, $reserva->id, "Reserva expirada");
        $reserva->update(['estado' => 'expirada']);
        $liberadas++;
    }
    
    return $liberadas;
}


    
}


