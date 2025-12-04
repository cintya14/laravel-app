<?php

namespace App\Filament\Widgets;

use App\Models\Producto;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockAlertsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalProductos = Producto::count();
        $sinStock = Producto::where('stock_disponible', '<=', 0)->count();
        $stockBajo = Producto::whereRaw('stock_disponible <= stock_minimo')
            ->where('stock_disponible', '>', 0)
            ->count();
        $stockNormal = $totalProductos - $sinStock - $stockBajo;
        
        return [
            Stat::make('Productos sin stock', $sinStock)
                ->description('Agotados')
                ->color('danger')
                ->icon('heroicon-o-exclamation-circle'),
                
            Stat::make('Stock bajo', $stockBajo)
                ->description('Por debajo del mínimo')
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle'),
                
            Stat::make('Stock normal', $stockNormal)
                ->description('Por encima del mínimo')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}