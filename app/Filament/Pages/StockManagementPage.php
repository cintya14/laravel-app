<?php
namespace App\Filament\Pages;

use App\Models\Producto;
use App\Models\StockMovimiento;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class StockManagementPage extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static string $view = 'filament.pages.stock-management-page';
    protected static ?string $navigationGroup = 'Inventario';
    protected static ?string $navigationLabel = 'Gestión de Stock';
    protected static ?int $navigationSort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(Producto::query()->with('categoria', 'marca'))
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable(),
                    
                TextColumn::make('marca.nombre')
                    ->label('Marca')
                    ->sortable(),
                
                TextInputColumn::make('stock_disponible')
                    ->label('Stock Disp.')
                    ->rules(['numeric', 'min:0'])
                    ->sortable()
                    ->updateStateUsing(function ($record, $state) {
                        $oldStock = $record->stock_disponible;
                        $record->stock_disponible = $state;
                        $record->save();
                        
                        if ($oldStock != $state) {
                            $diferencia = $state - $oldStock;
                            $tipo = $diferencia > 0 ? 'entrada' : 'salida';
                            
                            StockMovimiento::create([
                                'producto_id' => $record->id,
                                'tipo' => $tipo,
                                'cantidad' => abs($diferencia),
                                'stock_anterior' => $oldStock,
                                'stock_nuevo' => $state,
                                'motivo' => 'Ajuste manual desde gestión de stock',
                                'user_id' => auth()->id(),
                            ]);
                        }
                        
                        Notification::make()
                            ->title('Stock actualizado')
                            ->body("Stock de {$record->nombre} actualizado a {$state}")
                            ->success()
                            ->send();
                    }),
                    
                TextInputColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->rules(['numeric', 'min:0'])
                    ->sortable()
                    ->updateStateUsing(function ($record, $state) {
                        $record->stock_minimo = $state;
                        $record->save();
                        
                        Notification::make()
                            ->title('Stock mínimo actualizado')
                            ->success()
                            ->send();
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('stock_reservado')
                    ->label('Reservado')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('stock_real')
                    ->label('Stock Real')
                    ->state(function ($record) {
                        return $record->stock_disponible - $record->stock_reservado;
                    })
                    ->numeric()
                    ->sortable()
                    ->color(function ($record) {
                        $real = $record->stock_disponible - $record->stock_reservado;
                        if ($real <= 0) return 'danger';
                        if ($real <= $record->stock_minimo) return 'warning';
                        return 'success';
                    }),
                    
                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('PEN')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\Filter::make('sin_stock')
                    ->label('Sin stock disponible')
                    ->query(fn (Builder $query): Builder => 
                        $query->where('stock_disponible', '<=', 0)),
                    
                \Filament\Tables\Filters\Filter::make('stock_bajo')
                    ->label('Stock bajo')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereRaw('stock_disponible <= stock_minimo AND stock_disponible > 0')),
                    
                \Filament\Tables\Filters\SelectFilter::make('categoria')
                    ->relationship('categoria', 'nombre'),
                    
                \Filament\Tables\Filters\SelectFilter::make('marca')
                    ->relationship('marca', 'nombre'),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('agregar_stock')
                    ->label('+ Stock')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        TextInput::make('cantidad')
                            ->label('Cantidad a agregar')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(10),
                    ])
                    ->action(function (array $data, Producto $record): void {
                        $cantidad = $data['cantidad'];
                        $stockAnterior = $record->stock_disponible;
                        $record->increment('stock_disponible', $cantidad);
                        
                        StockMovimiento::create([
                            'producto_id' => $record->id,
                            'tipo' => 'entrada',
                            'cantidad' => $cantidad,
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo' => $record->stock_disponible,
                            'motivo' => 'Reposición manual',
                            'user_id' => auth()->id(),
                        ]);
                        
                        Notification::make()
                            ->title('Stock agregado')
                            ->body("Se agregaron {$cantidad} unidades a {$record->nombre}")
                            ->success()
                            ->send();
                    }),
            ]);
    }
    
    // Mantener estos métodos para el badge de navegación
    public static function getNavigationBadge(): ?string
    {
        $bajoStock = Producto::whereRaw('stock_disponible <= stock_minimo')->count();
        return $bajoStock > 0 ? (string)$bajoStock : null;
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        $bajoStock = Producto::whereRaw('stock_disponible <= stock_minimo')->count();
        return $bajoStock > 0 ? 'warning' : 'success';
    }
}