<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Dom\Text;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\form;

class DireccionRelationManager extends RelationManager
{
    protected static string $relationship = 'direccion';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telefono')
                    ->required()
                    ->tel()
                    ->maxLength(20),
                Forms\Components\TextInput::make('pais')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ciudad')
                    ->required()
                   
                    ->maxLength(255),
                Forms\Components\TexTarea::make('direccion_calle')
                    ->required()
                    ->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('direccion_calle')
            ->columns([
                TextColumn::make('full_nombre')
                    ->label('Nombre Completo'),
                TextColumn::make('telefono')
                    ->label('Teléfono'),
                TextColumn::make('pais')
                    ->label('País'),
                TextColumn::make('ciudad')
                    ->label('Ciudad'),
                TextColumn::make('direccion_calle')
                    ->label('Dirección Calle'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
