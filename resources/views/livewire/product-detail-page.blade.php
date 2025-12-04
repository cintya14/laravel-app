<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
  <section class="overflow-hidden bg-white py-11 font-poppins dark:bg-gray-800">
    <div class="max-w-6xl px-4 py-4 mx-auto lg:py-8 md:px-6">
      <div class="flex flex-wrap -mx-4">
        <div class="w-full mb-8 md:w-1/2 md:mb-0" x-data="{ mainImage: '{{ asset('storage/'. $producto->imagenes[0] ?? '') }}' }">
          <div class="sticky top-0 z-50 overflow-hidden ">
            <div class="relative mb-6 lg:mb-10 lg:h-2/4 ">
              <img x-bind:src="mainImage" alt="" class="object-cover w-full lg:h-full ">
            </div>
            <div class="flex-wrap hidden md:flex ">
              @foreach ($producto->imagenes as $imagen)
                <div class="w-1/2 p-2 sm:w-1/4" x-on:click="mainImage='{{ asset('storage/'. $imagen) }}'">
                  <img src="{{asset('storage/'.$imagen)}}" alt="{{$producto->nombre}}" class="object-cover w-full lg:h-20 cursor-pointer hover:border hover:border-blue-500">
                </div>
              @endforeach
            </div>
            <div class="px-6 pb-6 mt-6 border-t border-gray-300 dark:border-gray-400 ">
              <div class="flex flex-wrap items-center mt-6">
                <span class="mr-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="w-4 h-4 text-gray-700 dark:text-gray-400 bi bi-truck" viewBox="0 0 16 16">
                    <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z">
                    </path>
                  </svg>
                </span>
                <h2 class="text-lg font-bold text-gray-700 dark:text-gray-400">Envío gratis</h2>
              </div>
            </div>
          </div>
        </div>
        <div class="w-full px-4 md:w-1/2 ">
          <div class="lg:pl-20">
            <div class="mb-8 [&>ul]:list-disc [&>ul]:ml-4">
              <h2 class="max-w-xl mb-6 text-2xl font-bold dark:text-gray-400 md:text-4xl">
                {{$producto->nombre}} 
              </h2>
              
              @php
                $stockReal = $producto->stock_real;
              @endphp
              
              <!-- BLOQUE DE ESTADO DE STOCK -->
              <div class="mb-4 p-4 rounded-lg 
                   {{ $stockReal > 0 
                      ? ($stockReal <= $producto->stock_minimo 
                         ? 'bg-yellow-50 border-2 border-yellow-300 dark:bg-yellow-900/20 dark:border-yellow-700' 
                         : 'bg-green-50 border-2 border-green-300 dark:bg-green-900/20 dark:border-green-700') 
                      : 'bg-red-50 border-2 border-red-300 dark:bg-red-900/20 dark:border-red-700' }}">
                
                @if($stockReal > 0)
                  @if($stockReal <= $producto->stock_minimo)
                    <div class="flex items-start">
                      <span class="text-yellow-600 dark:text-yellow-400 mr-3 mt-1 text-2xl">⚠️</span>
                      <div>
                        <p class="font-bold text-yellow-800 dark:text-yellow-300 text-xl">¡Últimas unidades disponibles!</p>
                        <p class="text-sm text-yellow-700 dark:text-yellow-400 mt-1">
                          Aprovecha esta oportunidad antes de que se agote.
                        </p>
                      </div>
                    </div>
                  @else
                    <div class="flex items-start">
                      <span class="text-green-600 dark:text-green-400 mr-3 mt-1 text-2xl">✅</span>
                      <div>
                        <p class="font-bold text-green-800 dark:text-green-300 text-xl">Disponible</p>
                        <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                          Envío inmediato. Realiza tu pedido ahora.
                        </p>
                      </div>
                    </div>
                  @endif
                @else
                  <div class="flex items-start">
                    <span class="text-red-600 dark:text-red-400 mr-3 mt-1 text-2xl">❌</span>
                    <div>
                      <p class="font-bold text-red-800 dark:text-red-300 text-xl">Producto agotado</p>
                      <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                        No tenemos stock disponible actualmente. Consulta productos similares o contáctanos para más información.
                      </p>
                    </div>
                  </div>
                @endif
              </div>
              
              <p class="inline-block mb-6 text-4xl font-bold text-gray-700 dark:text-gray-400 ">
                <span> {{Number::currency($producto->precio, 'PEN')}} </span>
              </p>
              
              <p class="max-w-md text-gray-700 dark:text-gray-400">
                {!! Str::markdown($producto->descripcion)!!}   
              </p>
              
              @if($stockReal > 0)
                <!-- SELECTOR DE CANTIDAD -->
                <div class="w-32 mb-8 ">
                  <label for="" class="w-full pb-1 text-xl font-semibold text-gray-700 border-b border-blue-300 dark:border-gray-600 dark:text-gray-400">
                    Cantidad
                  </label>
                  
                  <div class="relative flex flex-row w-full h-10 mt-6 bg-transparent rounded-lg">
                    <button wire:click='decrementar' 
                            class="w-20 h-full text-gray-600 bg-gray-300 rounded-l outline-none 
                                   {{ $cantidad <= 1 ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-gray-400' }}"
                            {{ $cantidad <= 1 ? 'disabled' : '' }}>
                      <span class="m-auto text-2xl font-thin">-</span>
                    </button>
                    
                    <input type="number" 
                           wire:model='cantidad' 
                           readonly 
                           class="flex items-center w-full font-semibold text-center text-gray-700 placeholder-gray-700 
                                  bg-gray-300 outline-none dark:text-gray-400 dark:placeholder-gray-400 
                                  dark:bg-gray-900 focus:outline-none text-md"
                           value="{{ $cantidad }}">
                    
                    <button wire:click='incrementar' 
                            class="w-20 h-full text-gray-600 bg-gray-300 rounded-r outline-none 
                                   {{ $cantidad >= $max_cantidad ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:bg-gray-400' }}"
                            {{ $cantidad >= $max_cantidad ? 'disabled' : '' }}>
                      <span class="m-auto text-2xl font-thin">+</span>
                    </button>
                  </div>
                </div>
                
                <!-- BOTÓN AGREGAR AL CARRITO -->
                <div class="flex flex-wrap items-center gap-4">
                  <button wire:click='addToCart({{$producto->id}})' 
                          class="w-full p-4 rounded-md lg:w-2/5 bg-blue-500 text-gray-50 hover:bg-blue-600 dark:bg-blue-500 dark:hover:bg-blue-700">
                    <span wire:loading.remove wire:target='addToCart({{$producto->id}})'> 
                      Agregar al carrito 
                    </span> 
                    <span wire:loading wire:target='addToCart({{$producto->id}})'> 
                      Agregando... 
                    </span>
                  </button>
                </div>
              @else
                <!-- MENSAJE DE PRODUCTO AGOTADO -->
                <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg text-center">
                  <p class="text-lg font-semibold text-gray-700 dark:text-gray-300">
                    Este producto no está disponible en este momento
                  </p>
                  <a href="/products" class="inline-block mt-4 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Ver otros productos
                  </a>
                </div>
              @endif
              
              <!-- Mensajes de error/success -->
              @if(session()->has('error'))
                <div class="w-full p-3 mt-4 text-sm text-red-700 bg-red-100 rounded-lg dark:bg-red-900/30 dark:text-red-400">
                  {{ session('error') }}
                </div>
              @endif
              
              @if(session()->has('success'))
                <div class="w-full p-3 mt-4 text-sm text-green-700 bg-green-100 rounded-lg dark:bg-green-900/30 dark:text-green-400">
                  {{ session('success') }}
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>