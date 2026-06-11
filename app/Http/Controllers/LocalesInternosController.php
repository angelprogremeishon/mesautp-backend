<?php

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocalesInternosController extends Controller
{
    public function index(Request $request)
    {
        $query = Local::aprobados()->internos()
            ->with(['productos' => fn($q) => $q->where('disponible', true)->where('cantidad_disponible', '>', 0)])
            ->withCount('resenas');

        if ($request->filled('precio_max')) {
            $query->where('precio_min', '<=', $request->precio_max);
        }

        $locals = $query->orderBy('rating_promedio', 'desc')->get();

        return Inertia::render('LocalesInternos/Index', [
            'locals'  => $locals,
            'filters' => $request->only(['precio_max']),
        ]);
    }

    public function show(Local $local)
    {
        $local->load(['productos' => fn($q) => $q->where('disponible', true)->where('cantidad_disponible', '>', 0), 'resenas.user', 'user']);

        return Inertia::render('LocalesInternos/Show', [
            'local' => $local,
        ]);
    }
}
