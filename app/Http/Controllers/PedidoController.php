<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PedidoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'local_id'    => 'required|exists:locals,id',
            'producto_id' => 'nullable|exists:productos,id',
            'cantidad'    => 'required|integer|min:1',
            'nota'        => 'nullable|string|max:300',
            'hora_recojo' => 'nullable|date',
        ]);

        $producto = $data['producto_id'] ? Producto::find($data['producto_id']) : null;
        $local    = Local::findOrFail($data['local_id']);

        $total = $producto ? $producto->precio * $data['cantidad'] : $local->precio_min ?? 0;

        $pedido = Pedido::create([
            ...$data,
            'user_id' => Auth::id(),
            'total'   => $total,
            'estado'  => 'pendiente',
        ]);

        $whatsappMsg = urlencode(
            "Hola, soy " . Auth::user()->name . ", reservé " .
            ($producto?->nombre ?? 'un pedido') . " (S/ {$total}) para recoger" .
            ($pedido->hora_recojo ? " a las " . $pedido->hora_recojo->format('H:i') : "") .
            ". Pedido #{$pedido->id}"
        );

        return back()->with([
            'status'      => 'Pedido creado correctamente.',
            'whatsapp_url' => "https://wa.me/{$local->whatsapp}?text={$whatsappMsg}",
        ]);
    }

    public function calificar(Request $request, Pedido $pedido)
    {
        abort_if($pedido->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'estrellas'   => 'required|integer|min:1|max:5',
            'comentario'  => 'nullable|string|max:500',
        ]);

        Resena::updateOrCreate(
            ['user_id' => Auth::id(), 'pedido_id' => $pedido->id],
            ['local_id' => $pedido->local_id, ...$data]
        );

        $local = $pedido->local;
        $avg   = Resena::where('local_id', $local->id)->avg('estrellas');
        $count = Resena::where('local_id', $local->id)->count();
        $local->update(['rating_promedio' => round($avg, 2), 'total_resenas' => $count]);

        return back()->with('status', '¡Gracias por tu reseña!');
    }
}
