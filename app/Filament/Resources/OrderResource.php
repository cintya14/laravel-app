<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\DireccionRelationManager;

use App\Models\Order;
use App\Models\Producto;
use Doctrine\DBAL\Exception\InvalidColumnType\ColumnScaleRequired;
use Doctrine\DBAL\Schema\Column;
use Filament\Actions\SelectAction;
use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Number;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Tables\Columns\SelectColumn;


use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort = 5;

  public static function getModelLabel(): string
    {
        return 'Pedido';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Pedidos';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Informacion de pedidos')->schema([
                        Select::make('user_id')
                            ->label('Cliente')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('metodo_pago')
                            ->label('Metodo de pago')
                            ->options([
                                'Tarjeta de credito' => 'Tarjeta de credito',
                                'Pago contra entrega' => 'Pago contra entrega',
                            ])
                            ->required()
                            ->preload(),
                        Select::make('estado_pago')
                                ->label('Estado de pago')
                            ->options([
                                'pendiente' => 'Pendiente',
                                'completado' => 'Completado',
                                'fallido' => 'Fallido',
                            ])
                            ->default('pendiente')
                            ->required(),
                           
                        ToggleButtons::make('estado_pedido')
                            ->label('Estado')
                            ->inline()
                            ->default('new')
                            ->required()
                            ->options([
                                'nuevo' => 'Nuevo',
                                'proceso' => 'En proceso',
                                'enviado' => 'Enviado',
                                'entregado' => 'Entregado',
                                'cancelado' => 'Cancelado',
                            ])

                            ->colors([
                                'nuevo' => 'info',
                                'proceso' => 'warning',
                                'enviado' => 'success',
                                'entregado' => 'success',
                                'cancelado' => 'danger',
                            ])
                            ->icons([
                                'nuevo' => 'heroicon-m-sparkles',
                                'proceso' => 'heroicon-m-arrow-path',
                                'enviado' => 'heroicon-o-truck',
                                'entregado' => 'heroicon-m-check-badge',
                                'cancelado' => 'heroicon-o-x-circle',
                            ]),

                            select::make('moneda')
                            ->options([
                                'sol' => 'Sol',
                                'dolar' => 'Dolar', 

                            ])
                            ->default('sol')
                            ->required(),

                            select::make('metodo_envio')
                            ->options([
                                'Delivery' => 'Delivery',
                                'Recoger en tienda' => 'Recoger en tienda',
                            ]),
                            Textarea::make('notas')
                            ->columnSpanFull()

                    
                        ])->Columns(2),

                    Section::make('Detalle de pedido')->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Select::make('producto_id')
                                    ->relationship('producto', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->columnSpan(4)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set)=>$set('monto_unitario', Producto::find($state)?->precio ?? 0))
                                    ->afterStateUpdated(fn ($state, Set $set)=>$set('monto_total', Producto::find($state)?->precio ?? 0)),
                                Forms\Components\TextInput::make('cantidad')
                                    ->required()
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(2)
                                    ->reactive()
                                    ->afterStateUpdated(fn ($state, Set $set, Get $get)=>$set('monto_total', $state * $get('monto_unitario'))),
                                Forms\Components\TextInput::make('monto_unitario')
                                    ->label('Precio unitario')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(10000),
                                
                                Forms\Components\TextInput::make('monto_total')
                                    ->required()
                                    ->numeric()
                                    ->dehydrated()
                                    ->columnSpan(3)
                            ])->Columns(12),
                            
                             Placeholder::make('total_general_placeholder')
                                ->label('Total')
                                ->content(function (Get $get, Set $set) {
                                    $total = 0;
                                
                                    if (!$repeaters = $get('items')) {
                                        return $total;
                                    }

                                    foreach ($repeaters as $key => $repeater) {
                                        $total += $get("items.{$key}.monto_total");
                                    }
                                    $set('total_general', $total);
                                    return Number::currency($total, 'sol');

                                }),
                                Hidden::make('total_general')
                                ->default(0)
                            
                        ])

                ])->columnSpanFull()
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Cliente')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_general')
                    ->label('Total')
                    ->numeric()
                    ->money('sol')
                    ->sortable(),
                   
                TextColumn::make('metodo_pago')
                    ->label('Metodo de pago')
                    ->sortable(),

                TextColumn::make('moneda')
                    ->label('Moneda') 
                    ->sortable()
                    ->searchable(),

                TextColumn::make('metodo_envio')
                    ->label('Metodo de envio')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('estado_pago')
                    ->label('Estado de pago')
                    ->searchable()
                    ->sortable(),
                
                    
                SelectColumn::make('estado_pedido')
                    ->label('Estado')
                    ->options([
                        'nuevo' => 'nuevo',
                        'proceso' => 'proceso',
                        'enviado' => 'Enviado',
                        'entregado' => 'Entregado',
                        'cancelado' => 'Cancelado',     
                    ])
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),
                TextColumn::make('updated_at')
                    ->label('Fecha de actualizacion')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault:true),  
            ])
            ->filters([
                //
            ])
            ->actions([
                tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
               
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DireccionRelationManager::class,
         
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function getNavigationBadgeColor(): string|array|null
    {
        
        return static::getModel()::count() > 10 ? 'success' : 'danger';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
