<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Usuario';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Usuarios';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('email')
                ->label('Correo')
                ->required()
                ->email()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Forms\Components\DateTimePicker::make('email_verified_at')
                ->label('Email verificado'),

            // Control de admin SIN roles (usamos el flag is_admin)
            Forms\Components\Toggle::make('is_admin')
                ->label('Es administrador')
                ->default(false)
                ->disabled(fn (?User $record) =>
                    // No permitir que un usuario se quite a sí mismo el admin
                    $record?->id === auth()->id()
                ),

            Forms\Components\TextInput::make('password')
                ->label('Contraseña')
                ->password()
                // Solo es obligatoria al crear
                ->required(fn (Page $livewire): bool => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                ->minLength(8)
                // Solo “deshidrata” (guarda) si viene con valor
                ->dehydrated(fn ($state) => filled($state))
                // Siempre guardar hasheado
                ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // OJO: usar "Tables\Columns" (no "tables\Columns")
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),

                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label('Email verificado')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado en')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Solo admins')
                    ->placeholder('Todos')
                    ->trueLabel('Admins')
                    ->falseLabel('No admins')
                    ->queries(
                        true: fn (Builder $q) => $q->where('is_admin', true),
                        false: fn (Builder $q) => $q->where('is_admin', false),
                        blank: fn (Builder $q) => $q
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),

                    // Ocultamos el botón Eliminar si el registro es admin
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (User $record) => ! $record->is_admin)
                        // Doble seguro: si por algo aparece, validamos otra vez
                        ->before(function (Tables\Actions\DeleteAction $action, User $record) {
                            if ($record->is_admin) {
                                $action->halt();
                                notify()->warning('No se permite eliminar usuarios administradores.');
                            }
                        }),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Recomendación: desactivar el borrado masivo por seguridad
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
