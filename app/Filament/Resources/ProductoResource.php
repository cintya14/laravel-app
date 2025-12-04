<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Support\Markdown;
use Illuminate\Support\Str;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PHPUnit\Framework\Reorderable;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Producto')->schema([
                        TextInput::make('nombre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function(string $operation, $state, Set $set){
                                if($operation !== 'create'){
                                    return;
                                }
                                $set('slug', Str::slug($state));
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->disabled()
                            ->dehydrated()
                            ->unique(Producto::class, 'slug', ignoreRecord: true),
                           
                        MarkdownEditor::make('descripcion')
                            ->columnSpanFull()
                            ->fileAttachmentsDirectory('productos')
                                   
                    ])->columns(2),

                    Section::make('Imagenes')->schema([
                        FileUpload::make('imagenes')
                            ->multiple()
                            ->directory('productos')
                            ->maxFiles(5)
                            ->Reorderable()
                    ])
                ])->columnSpan(2),
                Group::make()->schema([
                    Section::make('Precio')->schema([
                       TextInput::make('precio')
                                ->label('Precio')
                                ->numeric()
                                ->minValue(0)                 // ⬅️ no permite < 0 (validación)
                                ->step(0.01)                  // input number con 2 decimales
                                ->required()
                                ->prefix('S/ ')
                                ->rules(['numeric', 'gte:0'])
                                ->validationMessages([
                                'gte' => 'El precio no puede ser negativo.',
                                'numeric' => 'El precio debe ser numérico.',
                            ])
      
                    ]),
                     
                    Section::make('Asociations')->schema([
                        Select::make('categoria_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('categoria', 'nombre'),
                    

                   
                        Select::make('marca_id')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->relationship('marca', 'nombre'),
                    ]),

                    Section::make('Estado y Stock')->schema([
                        Toggle::make('en_stock')
                            ->label('¿En stock?')
                            ->required()
                            ->default(true)
                            ->reactive(),

                        Toggle::make('activo')
                            ->label('Activo')
                            ->required()
                            ->default(true),

                        Toggle::make('destacado')
                            ->label('Destacado')
                            ->required(),

                        Toggle::make('en_venta')
                            ->label('En venta')
                            ->required(),
                        
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('stock_disponible')
                                    ->label('Stock Disponible')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->required()
                                    ->step(1)
                                    ->helperText('Unidades en inventario')
                                    ->visible(fn ($get) => $get('en_stock') == true),
                                
                                TextInput::make('stock_minimo')
                                    ->label('Stock Mínimo')
                                    ->numeric()
                                    ->default(5)
                                    ->minValue(0)
                                    ->required()
                                    ->step(1)
                                    ->helperText('Alerta cuando baje a este nivel')
                                    ->visible(fn ($get) => $get('en_stock') == true),
                                
                                Placeholder::make('stock_reservado')
                                    ->label('Stock Reservado')
                                    ->content(function ($record) {
                                        return $record ? $record->stock_reservado : 0;
                                    })
                                    ->helperText('Reservado en carritos')
                                    ->visible(fn ($get) => $get('en_stock') == true),
                            ])
                    ])
                    
                ])->columnSpan(1)
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('categoria.nombre')
                    ->sortable(),
                TextColumn::make('marca.nombre')
                    ->sortable(),
                TextColumn::make('precio')
                    ->money('sol')
                    ->sortable(),
                TextColumn::make('stock_disponible')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->color(function ($record) {
                        if ($record->stock_disponible <= 0) return 'danger';
                        if ($record->stock_disponible <= $record->stock_minimo) return 'warning';
                        return 'success';
                    }),
                    
                TextColumn::make('stock_reservado')
                    ->label('Reservado')
                    ->numeric()
                    ->sortable(),
                    
                TextColumn::make('stock_minimo')
                    ->label('Mínimo')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('stock_real')
                    ->label('Stock Real')
                    ->state(function ($record) {
                        return $record->stock_disponible - $record->stock_reservado;
                    })
                    ->numeric()
                    ->color(function ($record) {
                        $stockReal = $record->stock_disponible - $record->stock_reservado;
                        if ($stockReal <= 0) return 'danger';
                        if ($stockReal <= $record->stock_minimo) return 'warning';
                        return 'success';
                    })
                    ->sortable(),
                IconColumn::make('activo')
                    ->boolean(),
                IconColumn::make('destacado')
                    ->boolean(),
                IconColumn::make('en_stock')
                    ->boolean(),
                IconColumn::make('en_venta')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('categoria')
                    ->relationship('categoria', 'nombre'),
                SelectFilter::make('marca')
                    ->relationship('marca', 'nombre')
                    
            ])  
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    
                  Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action): void {
                        /** @var \App\Models\Producto|null $record */
                        $record = $action->getRecord(); // obtenemos el registro desde la acción
                        if (! $record) {
                            return;
                        }

                        // Si el producto tiene ventas asociadas, NO permitir borrar
                        if ($record->orderItems()->exists()) {
                            $action->halt();

                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('El producto tiene ventas asociadas y no puede eliminarse.')
                                ->danger()
                                ->send();
                             }
            }),
    ]),
])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                //Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}
