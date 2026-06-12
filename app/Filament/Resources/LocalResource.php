<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocalResource\Pages;
use App\Models\Local;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LocalResource extends Resource
{
    protected static ?string $model = Local::class;

    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Locales';
    protected static ?string $modelLabel      = 'local';
    protected static ?string $pluralModelLabel = 'locales';
    protected static ?int $navigationSort = 1;

    /** Muestra cuántos locales están pendientes de revisión en el menú. */
    public static function getNavigationBadge(): ?string
    {
        $pendientes = static::getModel()::where('estado', 'pendiente')->count();
        return $pendientes > 0 ? (string) $pendientes : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Datos del local')->schema([
                Forms\Components\TextInput::make('nombre')->required()->maxLength(100),
                Forms\Components\Select::make('tipo')
                    ->options(['externo' => 'Externo', 'interno' => 'Interno'])->required(),
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nombre')->searchable()->preload()->label('Categoría'),
                Forms\Components\TextInput::make('horario')->maxLength(100),
                Forms\Components\Textarea::make('descripcion')->maxLength(500)->columnSpanFull(),
                Forms\Components\TextInput::make('direccion')->label('Dirección')->maxLength(200),
                Forms\Components\TextInput::make('punto_entrega')->maxLength(100),
                Forms\Components\TextInput::make('whatsapp')->maxLength(20),
                Forms\Components\TextInput::make('yape')->maxLength(20),
                Forms\Components\TextInput::make('plin')->maxLength(20),
            ])->columns(2),

            Forms\Components\Section::make('Estado de la cuenta')->schema([
                Forms\Components\Select::make('estado')->options([
                    'pendiente'  => 'Pendiente',
                    'aprobado'   => 'Aprobado',
                    'rechazado'  => 'Rechazado',
                    'suspendido' => 'Suspendido',
                ])->required(),
                Forms\Components\Toggle::make('activo')->label('Visible para estudiantes'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('tipo')->badge()
                    ->color(fn (string $state) => $state === 'externo' ? 'warning' : 'info'),
                Tables\Columns\TextColumn::make('user.name')->label('Emprendedor')->searchable(),
                Tables\Columns\TextColumn::make('categoria.nombre')->label('Categoría')
                    ->badge()->toggleable(),
                Tables\Columns\TextColumn::make('estado')->badge()->color(fn (string $state) => match ($state) {
                    'aprobado'   => 'success',
                    'pendiente'  => 'warning',
                    'rechazado'  => 'danger',
                    'suspendido' => 'gray',
                    default      => 'gray',
                }),
                Tables\Columns\IconColumn::make('activo')->boolean()->label('Visible'),
                Tables\Columns\TextColumn::make('rating_promedio')->label('Rating')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Registrado')
                    ->dateTime('d/m/Y')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')->options([
                    'pendiente'  => 'Pendiente',
                    'aprobado'   => 'Aprobado',
                    'rechazado'  => 'Rechazado',
                    'suspendido' => 'Suspendido',
                ]),
                Tables\Filters\SelectFilter::make('tipo')->options([
                    'externo' => 'Externo',
                    'interno' => 'Interno',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('aprobar')
                    ->label('Aprobar')->icon('heroicon-o-check-circle')->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Local $record) => $record->estado !== 'aprobado')
                    ->action(fn (Local $record) => $record->update(['estado' => 'aprobado', 'activo' => true])),
                Tables\Actions\Action::make('rechazar')
                    ->label('Rechazar')->icon('heroicon-o-x-circle')->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Local $record) => $record->estado !== 'rechazado')
                    ->action(fn (Local $record) => $record->update(['estado' => 'rechazado', 'activo' => false])),
                Tables\Actions\Action::make('suspender')
                    ->label('Suspender')->icon('heroicon-o-pause-circle')->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Local $record) => $record->estado === 'aprobado')
                    ->action(fn (Local $record) => $record->update(['estado' => 'suspendido', 'activo' => false])),
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
            'index'  => Pages\ListLocals::route('/'),
            'create' => Pages\CreateLocal::route('/create'),
            'edit'   => Pages\EditLocal::route('/{record}/edit'),
        ];
    }
}
