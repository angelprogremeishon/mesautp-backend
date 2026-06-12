<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Local;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with(['local', 'producto', 'resena'])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();
        return response()->json($pedidos);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'local_id'    => 'required|exists:locals,id',
            'producto_id' => 'nullable|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
            'nota'        => 'nullable|string|max:300',
        ]);

        $producto = $data['producto_id'] ? Producto::find($data['producto_id']) : null;
        $local    = Local::findOrFail($data['local_id']);
        $total    = $producto ? $producto->precio * $data['cantidad'] : ($local->precio_min ?? 0);

        $pedido = Pedido::create([
            ...$data,
            'user_id' => Auth::id(),
            'total'   => $total,
            'estado'  => 'pendiente',
        ]);

        $msg = urlencode(
            "Hola, soy " . Auth::user()->name .
            ", reservé " . ($producto?->nombre ?? 'un pedido') .
            " (S/ {$total}). Pedido #{$pedido->id}"
        );

        return response()->json([
            'pedido'       => $pedido->load('producto'),
            'whatsapp_url' => "https://wa.me/{$local->whatsapp}?text={$msg}",
        ], 201);
    }

    public function calificar(Request $request, Pedido $pedido)
    {
        abort_if($pedido->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'estrellas'  => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        Resena::updateOrCreate(
            ['user_id' => Auth::id(), 'pedido_id' => $pedido->id],
            ['local_id' => $pedido->local_id, ...$data]
        );

        $local = $pedido->local;
        $local->update([
            'rating_promedio' => round(Resena::where('local_id', $local->id)->avg('estrellas'), 2),
            'total_resenas'   => Resena::where('local_id', $local->id)->count(),
        ]);

        return response()->json(['message' => '¡Gracias por tu reseña!']);
    }
}
