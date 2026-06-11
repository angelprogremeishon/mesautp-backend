<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Local;
use Illuminate\Http\Request;

class LocalesController extends Controller
{
    public function externos(Request $request)
    {
        $query = Local::aprobados()->externos()
            ->with(['categoria', 'productos' => fn($q) => $q->where('disponible', true)])
            ->withCount('resenas');

        if ($request->filled('categoria')) {
            $query->whereHas('categoria', fn($q) => $q->where('nombre', 'like', '%' . $request->categoria . '%'));
        }
        if ($request->filled('precio_max')) {
            $query->where('precio_min', '<=', $request->precio_max);
        }
        if ($request->filled('buscar')) {
            $query->where('nombre', 'like', '%' . $request->buscar . '%');
        }

        $locals = $query->orderBy('rating_promedio', 'desc')->get();

        return response()->json([
            'data'    => $locals,
            'filters' => $request->only(['categoria', 'precio_max', 'buscar']),
        ]);
    }

    public function externoShow(Local $local)
    {
        abort_if($local->tipo !== 'externo' || $local->estado !== 'aprobado', 404);
        $local->load(['productos' => fn($q) => $q->where('disponible', true), 'resenas.user']);
        return response()->json($local);
    }

    public function internos(Request $request)
    {
        $query = Local::aprobados()->internos()
            ->with(['categoria', 'productos' => fn($q) => $q->where('disponible', true)])
            ->withCount('resenas');

        if ($request->filled('precio_max')) {
            $query->where('precio_min', '<=', $request->precio_max);
        }

        $locals = $query->orderBy('rating_promedio', 'desc')->get();

        return response()->json([
            'data'    => $locals,
            'filters' => $request->only(['precio_max']),
        ]);
    }

    public function internoShow(Local $local)
    {
        abort_if($local->tipo !== 'interno' || $local->estado !== 'aprobado', 404);
        $local->load(['productos' => fn($q) => $q->where('disponible', true), 'resenas.user', 'user']);
        return response()->json($local);
    }
}
