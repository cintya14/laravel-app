<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderResource\Widgets\OrderStats;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

 
    protected function getHeaderWidgets(): array{
        return [
            OrderStats::class,
        ];
    }

    public function getTabs(): array
    {
        return [
            null => Tab::make('Todos'),
            'nuevo' => Tab::make()->query(fn($query) => $query->where('estado_pedido', 'nuevo')),
            'proceso' => Tab::make()->query(fn($query) => $query->where('estado_pedido', 'proceso')),
            'enviado' => Tab::make()->query(fn($query) => $query->where('estado_pedido', 'enviado')),
            'entregado' => Tab::make()->query(fn($query) => $query->where('estado_pedido', 'entregado')),
            'cancelado' => Tab::make()->query(fn($query) => $query->where('estado_pedido', 'cancelado')),

        ];
        
    }
}
