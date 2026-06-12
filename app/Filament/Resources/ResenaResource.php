<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResenaResource\Pages;
use App\Models\Resena;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ResenaResource extends Resource
{
    protected static ?string $model = Resena::class;

    protected static ?string $navigationIcon  = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Moderación';
    protected static ?string $modelLabel      = 'reseña';
    protected static ?string $pluralModelLabel = 'reseñas';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('local.nombre')->label('Local')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('user.name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('estrellas')->label('Estrellas')->badge()
                    ->color('warning')->formatStateUsing(fn ($state) => "{$state} ★"),
                Tables\Columns\TextColumn::make('comentario')->limit(70)->wrap()->placeholder('— sin comentario —'),
                Tables\Columns\TextColumn::make('created_at')->label('Fecha')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estrellas')->options([
                    5 => '5 ★', 4 => '4 ★', 3 => '3 ★', 2 => '2 ★', 1 => '1 ★',
                ]),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->label('Eliminar'),
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
            'index' => Pages\ListResenas::route('/'),
        ];
    }
}
