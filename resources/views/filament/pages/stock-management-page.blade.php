<x-filament::page>
    <div class="p-6 bg-white rounded-lg shadow mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">📊 Resumen de Stock</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="text-sm text-red-600">Sin Stock</div>
                <div class="text-2xl font-bold text-red-700">{{ \App\Models\Producto::where('stock_disponible', '<=', 0)->count() }}</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                <div class="text-sm text-yellow-600">Stock Bajo</div>
                <div class="text-2xl font-bold text-yellow-700">{{ \App\Models\Producto::whereRaw('stock_disponible <= stock_minimo AND stock_disponible > 0')->count() }}</div>
            </div>
            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                <div class="text-sm text-green-600">Stock Normal</div>
                <div class="text-2xl font-bold text-green-700">{{ \App\Models\Producto::whereRaw('stock_disponible > stock_minimo')->count() }}</div>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                <div class="text-sm text-blue-600">Total Reservado</div>
                <div class="text-2xl font-bold text-blue-700">{{ \App\Models\Producto::sum('stock_reservado') }}</div>
            </div>
        </div>
    </div>
    
    {{ $this->table }}
    
    <x-filament-actions::modals />
</x-filament::page>