<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Illuminate\Support\Number;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('nuevo pedido', Order::query()->where('estado_pedido', 'nuevo')->count()),
            Stat::make('pedido procesado', Order::query()->where('estado_pedido', 'procesado')->count()),
            Stat::make('pedido enviado', Order::query()->where('estado_pedido', 'enviado')->count()),
            stat::make('precio Promedio', Number::currency(Order::query()->avg('total_general')?? 0, 'sol')),
          
        ];
    }
}
