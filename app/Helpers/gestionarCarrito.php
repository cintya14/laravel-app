<?php

namespace App\Helpers;  
use Illuminate\Support\Facades\Cookie;
use App\Models\Producto;

class GestionarCarrito {


//agregar item a carrito
static public function addItemToCart($producto_id){
    $cart_items = self::getCartItemsFromCookie();
    $existing_item = null;

    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            $existing_item = $key;
            break;
        }
    }

    if($existing_item !== null) {
        $cart_items[$existing_item]['cantidad']++; // Incrementar cantidad si el item ya existe
        $cart_items[$existing_item]['monto_total'] = $cart_items[$existing_item]['cantidad'] * 
        $cart_items[$existing_item]['monto_unitario']; // Actualizar monto total
    }else{
        $producto = Producto::where('id', $producto_id)->first([
            'id',
            'nombre',
            'precio',
            'imagenes',
        ]);

        if($producto){
            $cart_items[] =[
                'producto_id'=> $producto_id,
                'nombre' => $producto->nombre,
                'imagenes' => $producto->imagenes[0],
                'cantidad' => 1,
                'monto_unitario' => $producto->precio, 
                'monto_total' => $producto->precio // Inicializar subtotal
            ];
        }



    }

    self::addCartItemsToCookie($cart_items);
    return count($cart_items);

}

//cortar.


//agregar item a carrito
static public function addItemToCartWithQty($producto_id, $qty = 1){
    $cart_items = self::getCartItemsFromCookie();
    $existing_item = null;

    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            $existing_item = $key;
            break;
        }
    }

    if($existing_item !== null) {
        $cart_items[$existing_item]['cantidad'] = $qty; // Incrementar cantidad si el item ya existe
        $cart_items[$existing_item]['monto_total'] = $cart_items[$existing_item]['cantidad'] * 
        $cart_items[$existing_item]['monto_unitario']; // Actualizar monto total
    }else{
        $producto = Producto::where('id', $producto_id)->first([
            'id',
            'nombre',
            'precio',
            'imagenes',
        ]);

        if($producto){
            $cart_items[] =[
                'producto_id'=> $producto_id,
                'nombre' => $producto->nombre,
                'imagenes' => $producto->imagenes[0],
                'cantidad' => $qty,
                'monto_unitario' => $producto->precio, 
                'monto_total' => $producto->precio // Inicializar subtotal
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
    Cookie::queue(Cookie::forget('cart_items')); // 30 dias
}

//obtener el carrito
static public function getCartItemsFromCookie(){
    $cart_items = json_decode(Cookie::get('cart_items'), true);
    if(!$cart_items) {
        $cart_items = [];
    }

    return $cart_items;
}

//incrementar cantidad de un item del carrito

static public function incremenQuantityToCartItem($producto_id) {
    $cart_items = self::getCartItemsFromCookie();


    foreach ($cart_items as $key => $item) {
        if ($item['producto_id'] == $producto_id) {
            $cart_items[$key]['cantidad']++; // Incrementar cantidad
            $cart_items[$key]['monto_total'] = $cart_items[$key]['cantidad'] * $cart_items[$key]
            ['monto_unitario']; // Actualizar subtotal
            
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
                $cart_items[$key]['cantidad']--; // Decrementar cantidad
                $cart_items[$key]['monto_total'] = $cart_items[$key]['cantidad'] * $cart_items[$key]['monto_unitario']; // Actualizar subtotal
            }
        }
    }

    self::addCartItemsToCookie($cart_items);
    return $cart_items;
}


//calcular el total del carrito

static public function calculateGrandTotal($items) {
    return array_sum(array_column($items, 'monto_total')); 

}


    
}


