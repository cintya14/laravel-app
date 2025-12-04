{{-- resources/views/filament/widgets/quick-stock-actions.blade.php --}}
<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Acciones Rápidas de Stock</h3>
        <a href="{{ route('filament.admin.pages.stock-management') }}" 
           class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
            Ir a Gestión de Stock
        </a>
    </div>
    
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <a href="{{ route('filament.admin.pages.stock-management') . '?tableFilters[stock_bajo][value]=1' }}" 
           class="p-4 text-center bg-yellow-50 rounded-lg hover:bg-yellow-100">
            <div class="text-2xl font-bold text-yellow-600">
                {{ \App\Models\Producto::whereRaw('stock_disponible <= stock_minimo AND stock_disponible > 0')->count() }}
            </div>
            <div class="mt-2 text-sm font-medium text-yellow-700">Productos con stock bajo</div>
        </a>
        
        <a href="{{ route('filament.admin.pages.stock-management') . '?tableFilters[sin_stock][value]=1' }}" 
           class="p-4 text-center bg-red-50 rounded-lg hover:bg-red-100">
            <div class="text-2xl font-bold text-red-600">
                {{ \App\Models\Producto::where('stock_disponible', '<=', 0)->count() }}
            </div>
            <div class="mt-2 text-sm font-medium text-red-700">Productos sin stock</div>
        </a>
        
        <a href="{{ route('filament.admin.pages.stock-management') . '?tableFilters[con_reservas][value]=1' }}" 
           class="p-4 text-center bg-blue-50 rounded-lg hover:bg-blue-100">
            <div class="text-2xl font-bold text-blue-600">
                {{ \App\Models\Producto::where('stock_reservado', '>', 0)->count() }}
            </div>
            <div class="mt-2 text-sm font-medium text-blue-700">Con stock reservado</div>
        </a>
    </div>
</div>