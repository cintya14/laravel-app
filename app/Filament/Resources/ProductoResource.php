<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
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
                            ->numeric()
                            ->required()
                            ->prefix('sol'),
      
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

                    Section::make('estado')->schema([
                        Toggle::make('en_estock')
                        ->required()
                        ->default(true),

                        Toggle::make('activo')
                        ->required()
                        ->default(true),

                        Toggle::make('destacado')
                        ->required(),

                        Toggle::make('en_venta')
                        ->required()
                                          
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
