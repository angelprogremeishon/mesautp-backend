<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $modelLabel      = 'usuario';
    protected static ?string $pluralModelLabel = 'usuarios';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nombre')->required()->maxLength(255),
            Forms\Components\TextInput::make('email')->email()->required()
                ->unique(ignoreRecord: true)->maxLength(255),
            Forms\Components\Select::make('role')->label('Rol')->options([
                'consumidor'  => 'Consumidor',
                'emprendedor' => 'Emprendedor',
                'admin'       => 'Administrador',
            ])->required()->default('consumidor')->native(false),
            // El modelo castea 'password' => 'hashed', así que NO se hashea aquí.
            // Sólo se guarda cuando se escribe algo (obligatorio al crear, opcional al editar).
            Forms\Components\TextInput::make('password')->label('Contraseña')->password()
                ->dehydrated(fn ($state) => filled($state))
                ->required(fn (string $operation) => $operation === 'create')
                ->revealable()->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nombre')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('Rol')->badge()
                    ->color(fn (string $state) => match ($state) {
                        'admin'       => 'danger',
                        'emprendedor' => 'info',
                        default       => 'gray',
                    }),
                Tables\Columns\IconColumn::make('email_verified_at')->label('Verificado')
                    ->boolean()->getStateUsing(fn (User $r) => $r->email_verified_at !== null),
                Tables\Columns\TextColumn::make('created_at')->label('Registrado')
                    ->dateTime('d/m/Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')->label('Rol')->options([
                    'consumidor'  => 'Consumidor',
                    'emprendedor' => 'Emprendedor',
                    'admin'       => 'Administrador',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
