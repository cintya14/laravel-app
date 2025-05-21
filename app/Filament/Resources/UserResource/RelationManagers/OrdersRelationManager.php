<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
               
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('id')
                    ->label('orden ID')
                    ->searchable(),

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
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([

                Tables\Actions\Action::make('ver')
                    ->url(fn (Order $record): string => OrderResource::getUrl('view',['record'=> $record]))
                    ->color('info')
                    ->icon('heroicon-o-eye'),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
