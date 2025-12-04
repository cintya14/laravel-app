<div class="w-full max-w-[85rem] py-10 px-4 sm:px-6 lg:px-8 mx-auto">
  <section class="py-10 bg-gray-50 font-poppins dark:bg-gray-800 rounded-lg">
    <div class="px-4 py-4 mx-auto max-w-7xl lg:py-6 md:px-6">
     <div class="relative mb-4">
    <input 
        type="text" 
        wire:model.live="search"
        placeholder="Buscar productos..." 
        class="w-full pl-10 pr-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300"
    >
    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.4-4.4a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </div>
</div>


      <div class="flex flex-wrap mb-24 -mx-3">
        <div class="w-full pr-2 lg:w-1/4 lg:block">
          
          <div class="p-4 mb-5 bg-white border border-gray-200 dark:border-gray-900 dark:bg-gray-900">
            <h2 class="text-2xl font-bold dark:text-gray-400"> Categorias</h2>
           
            <div class="w-16 pb-2 mb-6 border-b border-rose-600 dark:border-gray-400"></div>
            <ul>
              @foreach ($categorias as $categoria)
                <li class="mb-4" wire:key="{{ $categoria->id }}">
                  <label for="{{$categoria->slug}}" class="flex items-center dark:text-gray-300">
                    <input type="checkbox" wire:model.live ="select_categorias" id="{{$categoria->slug }}" value="{{$categoria->id}}"  class="w-4 h-4 mr-2">
                    <span class="text-lg dark:text-gray-400">{{ $categoria->nombre }}</span>
                  </label>
                </li>
              @endforeach
             
              
            </ul>

          </div>
          <div class="p-4 mb-5 bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-900">
            <h2 class="text-2xl font-bold dark:text-gray-400">Marcas</h2>
            <div class="w-16 pb-2 mb-6 border-b border-rose-600 dark:border-gray-400"></div>
            <ul>
              
              
            @foreach ($marcas as $marca)
              <li class="mb-4" wire:key="{{ $marca->id }}">
                <label for="{{$marca->slug}}" class="flex items-center dark:text-gray-300">
                  <input type="checkbox" wire:model.live="select_marcas" id="{{$marca->slug}}" value="{{$marca->id}}" class="w-4 h-4 mr-2">
                  <span class="text-lg dark:text-gray-400">{{$marca->nombre}}</span>
                </label>
              </li>
            @endforeach
                

            </ul>
          </div>
          <div class="p-4 mb-5 bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-900">
            <h2 class="text-2xl font-bold dark:text-gray-400">Product Status</h2>
            <div class="w-16 pb-2 mb-6 border-b border-rose-600 dark:border-gray-400"></div>
            <ul>
              <li class="mb-4">
                <label for="destacados" class="flex items-center dark:text-gray-300">
                  <input type="checkbox" id ="destacados" wire:model.live="destacado" value="1" class="w-4 h-4 mr-2">
                  <span class="text-lg dark:text-gray-400">Destacados</span>
                </label>
              </li>
              <li class="mb-4">
                <label for="en:oferta" class="flex items-center dark:text-gray-300">
                  <input type="checkbox" id="en_oferta" wire:model.live="en_oferta" value="1" class="w-4 h-4 mr-2">
                  <span class="text-lg dark:text-gray-400">en oferta</span>
                </label>
              </li>
            </ul>
          </div>

          <div class="p-4 mb-5 bg-white border border-gray-200 dark:bg-gray-900 dark:border-gray-900">
            <h2 class="text-2xl font-bold dark:text-gray-400">Price</h2>
            <div class="w-16 pb-2 mb-6 border-b border-rose-600 dark:border-gray-400"></div>
            <div>
              <div class="font-semibold" > {{Number::currency($rango_precio, 'sol')}} </div>
              <input type="range" wire:model.live='rango_precio' class="w-full h-1 mb-4 bg-blue-100 rounded appearance-none cursor-pointer" max="10000" value="10000" step="10">
              <div class="flex justify-between ">
                <span class="inline-block text-lg font-bold text-blue-400 ">{{Number::currency(10, 'sol')}} </span>
                <span class="inline-block text-lg font-bold text-blue-400 ">{{Number::currency(10000, 'sol')}} </span>
              </div>
            </div>
          </div>
        </div>
        <div class="w-full px-3 lg:w-3/4">
          <div class="flex flex-wrap items-center ">

           @foreach ($productos as $producto)
          <div class="w-full px-3 mb-6 sm:w-1/2 md:w-1/3" wire:key="{{ $producto->id }}">
            <div class="border border-gray-300 dark:border-gray-700 relative">
              
              @php
                $stockReal = $producto->stock_real;
              @endphp
              
              <!-- BADGE DE STOCK -->
              @if($stockReal <= 0)
                <div class="absolute top-2 left-2 z-10 bg-red-500 text-white px-3 py-1 rounded text-sm font-bold shadow-lg">
                  AGOTADO
                </div>
                <div class="absolute inset-0 bg-gray-900 bg-opacity-50 z-5 flex items-center justify-center">
                  <span class="text-white text-xl font-bold bg-red-600 px-4 py-2 rounded">SIN STOCK</span>
                </div>
              @elseif($stockReal <= $producto->stock_minimo)
                <div class="absolute top-2 left-2 z-10 bg-yellow-500 text-white px-3 py-1 rounded text-sm font-bold shadow-lg">
                  ¡ÚLTIMAS UNIDADES!
                </div>
              @endif
              
              <div class="relative bg-gray-200 {{ $stockReal <= 0 ? 'opacity-60' : '' }}">
                <a href="/products/{{$producto->slug}}" class="">
                  <img src="{{ asset('storage/'. $producto->imagenes[0]) }}" alt="{{$producto->nombre}}" 
                      class="object-cover w-full h-56 mx-auto {{ $stockReal <= 0 ? 'grayscale' : '' }}">
                </a>
              </div>
              
              <div class="p-3 ">
                <div class="flex items-center justify-between gap-2 mb-2">
                  <h3 class="text-xl font-medium dark:text-gray-400">
                    {{$producto->nombre}}
                  </h3>
                </div>
                
                <p class="text-lg ">
                  <span class="text-green-600 dark:text-green-600">{{Number::currency($producto->precio, 'PEN')}} </span>
                </p>
                
                <!-- INFO DE STOCK -->
                <div class="mt-2">
                  @if($stockReal > 0)
                    @if($stockReal <= $producto->stock_minimo)
                      <p class="text-sm text-yellow-600 dark:text-yellow-400 font-semibold">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        ¡Últimas unidades disponibles!
                      </p>
                    @else
                      <p class="text-sm text-green-600 dark:text-green-400">
                        <i class="fas fa-check-circle mr-1"></i>
                        Disponible
                      </p>
                    @endif
                  @else
                    <p class="text-sm text-red-600 dark:text-red-400 font-semibold">
                      <i class="fas fa-times-circle mr-1"></i>
                      Producto agotado
                    </p>
                  @endif
                </div>
              </div>
              
              <div class="flex justify-center p-4 border-t border-gray-300 dark:border-gray-700">
                @if($stockReal > 0)
                  <a wire:click.prevent='addToCart({{$producto->id}})' 
                    href="#" 
                    class="text-gray-500 flex items-center space-x-2 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-300">
                    
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                        class="w-4 h-4 bi bi-cart3" viewBox="0 0 16 16">
                      <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"></path>
                    </svg>
                    
                    <span wire:loading.remove wire:target='addToCart({{$producto->id}})'>Agregar al carrito</span>
                    <span wire:loading wire:target='addToCart({{$producto->id}})'>Agregando...</span>
                  </a>
                @else
                  <span class="text-gray-400 dark:text-gray-600 flex items-center space-x-2 cursor-not-allowed">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" 
                        class="w-4 h-4 text-gray-400" viewBox="0 0 16 16">
                      <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"></path>
                    </svg>
                    <span class="font-semibold">No disponible</span>
                  </span>
                @endif
              </div>
              
              <!-- Mensaje de error si hay -->
              @if(session()->has('error_stock_' . $producto->id))
                <div class="p-2 m-2 text-xs text-red-700 bg-red-100 rounded dark:bg-red-900/30 dark:text-red-400">
                  {{ session('error_stock_' . $producto->id) }}
                </div>
              @endif
            </div>
          </div>
          @endforeach

          </div>
          <!-- pagination start -->
          <div class="flex justify-end mt-6">
            {{ $productos->links() }}
          </div>
          <!-- pagination end -->
        </div>
      </div>
    </div>
  </section>

  <!-- Mensaje de éxito global -->
  @if(session()->has('success_cart'))
    <div class="fixed bottom-4 right-4 z-50 p-4 text-sm text-green-700 bg-green-100 rounded-lg shadow-lg dark:bg-green-900/30 dark:text-green-400">
      {{ session('success_cart') }}
    </div>
  @endif
</div>