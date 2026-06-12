<?php

namespace App\Filament\Widgets;

use App\Models\Local;
use App\Models\Pedido;
use App\Models\Resena;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $pendientes = Local::where('estado', 'pendiente')->count();

        return [
            Stat::make('Usuarios', User::count())
                ->description(User::where('role', 'emprendedor')->count() . ' emprendedores')
                ->icon('heroicon-o-users'),

            Stat::make('Locales por revisar', $pendientes)
                ->description($pendientes > 0 ? 'Requieren tu aprobación' : 'Todo al día')
                ->color($pendientes > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('Locales activos', Local::where('estado', 'aprobado')->count())
                ->color('success')
                ->icon('heroicon-o-building-storefront'),

            Stat::make('Pedidos', Pedido::count())
                ->description(Pedido::whereDate('created_at', today())->count() . ' hoy')
                ->icon('heroicon-o-shopping-bag'),

            Stat::make('Reseñas', Resena::count())
                ->icon('heroicon-o-star'),
        ];
    }
}
