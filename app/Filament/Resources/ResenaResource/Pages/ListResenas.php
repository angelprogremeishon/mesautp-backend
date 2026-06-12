<?php

namespace App\Filament\Resources\ResenaResource\Pages;

use App\Filament\Resources\ResenaResource;
use Filament\Resources\Pages\ListRecords;

class ListResenas extends ListRecords
{
    protected static string $resource = ResenaResource::class;

    // Las reseñas las crean los clientes desde la app, no el admin:
    // por eso esta vista es sólo de moderación (listar / eliminar).
    protected function getHeaderActions(): array
    {
        return [];
    }
}
