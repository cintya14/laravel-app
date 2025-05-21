<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int | string |array $columnSpan = 'full';
    protected static ?int $sort = 1;
    public function table(Table $table): Table
    {
        return $table
            ->query(OrderResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('orden ID')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_general')
                    ->label('Total')
                    ->money('sol', true)
                    ->sortable()
                    ->searchable(),
                TextColumn::make('estado_pedido')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'nuevo' => 'info',
                        'procesado' => 'success',
                        'enviado' => 'danger',
                        'entregado' => 'success',
                        'cancelado' => 'danger',   
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'nuevo' => 'heroicon-m-sparkles',
                        'procesado' => 'heroicon-m-arrow-path',
                        'enviado' => 'heroicon-m-truck',
                        'entregado' => 'heroicon-m-check-badge',
                        'cancelado' => 'heroicon-m-x-circle',   
                    })
                    ->sortable(),
                TextColumn::make('metodo_pago')
                    ->label('Método de pago')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('estado_pago')
                    ->label('Estado de pago')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de pedido')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])

            ->actions([
                Tables\Actions\Action::make('Ver')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-o-eye')
                    ->color('primary')
            ]);
    }
}
