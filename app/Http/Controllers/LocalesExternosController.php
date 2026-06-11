<?php

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LocalesExternosController extends Controller
{
    public function index(Request $request)
    {
        $query = Local::aprobados()->externos()
            ->with(['productos' => fn($q) => $q->where('disponible', true)])
            ->withCount('resenas');

        if ($request->filled('categoria')) {
            $query->where('categoria', 'like', '%' . $request->categoria . '%');
        }

        if ($request->filled('precio_max')) {
            $query->where('precio_min', '<=', $request->precio_max);
        }

        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $locals = $query->orderBy('rating_promedio', 'desc')->get();

        return Inertia::render('LocalesExternos/Index', [
            'locals'    => $locals,
            'filters'   => $request->only(['categoria', 'precio_max', 'buscar']),
        ]);
    }

    public function show(Local $local)
    {
        $local->load(['productos' => fn($q) => $q->where('disponible', true), 'resenas.user']);

        return Inertia::render('LocalesExternos/Show', [
            'local' => $local,
        ]);
    }
}
