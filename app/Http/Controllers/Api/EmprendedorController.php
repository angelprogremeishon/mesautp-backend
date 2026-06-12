<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Local;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EmprendedorController extends Controller
{
    public function dashboard()
    {
        $user  = Auth::user();
        $local = $user->local;

        if (!$local) {
            return response()->json(['local' => null]);
        }

        $pedidos = Pedido::where('local_id', $local->id)
            ->with('user', 'producto')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return response()->json([
            'local'   => $local->load('productos'),
            'pedidos' => $pedidos,
            'stats'   => [
                'pedidos_hoy'    => Pedido::where('local_id', $local->id)->whereDate('created_at', today())->count(),
                'pedidos_semana' => Pedido::where('local_id', $local->id)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'ingresos_hoy'   => Pedido::where('local_id', $local->id)->whereDate('created_at', today())->where('estado', 'entregado')->sum('total'),
            ],
        ]);
    }

    public function registrar(Request $request)
    {
        $data = $request->validate([
            'nombre'           => 'required|string|max:100',
            'tipo'             => 'required|in:externo,interno',
            'descripcion'      => 'nullable|string|max:500',
            'categoria'        => 'nullable|string|max:50',
            'codigo_matricula' => 'nullable|string|max:20',
            'ciclo_carrera'    => 'nullable|string|max:120',
            'direccion'        => 'nullable|string|max:200',
            'punto_entrega'    => 'nullable|string|max:100',
            'horario'          => 'nullable|string|max:100',
            'precio_min'       => 'nullable|numeric|min:0',
            'precio_max'       => 'nullable|numeric|min:0',
            'yape'             => 'nullable|string|max:20',
            'plin'             => 'nullable|string|max:20',
            'whatsapp'         => 'nullable|string|max:20',
            'foto'             => 'nullable|image|max:2048',
        ]);

        // Resolver la categoría (texto) a su id, creándola si no existe.
        if (!empty($data['categoria'])) {
            $data['categoria_id'] = Categoria::firstOrCreate(['nombre' => $data['categoria']])->id;
        }
        unset($data['categoria']);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('locales', 'public');
        }

        $local = Local::create([
            ...$data,
            'user_id' => Auth::id(),
            'estado'  => 'pendiente',
        ]);

        Auth::user()->update(['role' => 'emprendedor']);

        return response()->json(['local' => $local, 'message' => 'Registro enviado. Te avisaremos cuando sea aprobado.'], 201);
    }

    public function actualizarLocal(Request $request)
    {
        $local = Auth::user()->local;
        abort_if(!$local, 404);

        $data = $request->validate([
            'nombre'        => 'required|string|max:100',
            'descripcion'   => 'nullable|string|max:500',
            'categoria'     => 'nullable|string|max:50',
            'direccion'     => 'nullable|string|max:200',
            'punto_entrega' => 'nullable|string|max:100',
            'horario'       => 'nullable|string|max:100',
            'precio_min'    => 'nullable|numeric|min:0',
            'precio_max'    => 'nullable|numeric|min:0',
            'yape'          => 'nullable|string|max:20',
            'plin'          => 'nullable|string|max:20',
            'whatsapp'      => 'nullable|string|max:20',
            'foto'          => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($local->foto) Storage::disk('public')->delete($local->foto);
            $data['foto'] = $request->file('foto')->store('locales', 'public');
        }

        $local->update($data);

        return response()->json(['local' => $local->fresh(), 'message' => 'Local actualizado.']);
    }

    public function guardarProducto(Request $request)
    {
        $local = Auth::user()->local;
        abort_if(!$local, 404);

        $data = $request->validate([
            'nombre'             => 'required|string|max:100',
            'descripcion'        => 'nullable|string|max:300',
            'precio'             => 'required|numeric|min:0',
            'cantidad_disponible'=> 'required|integer|min:0',
            'es_menu_dia'        => 'boolean',
            'foto'               => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('productos', 'public');
        }

        $producto = Producto::create([
            ...$data,
            'local_id'   => $local->id,
            'disponible' => $data['cantidad_disponible'] > 0,
        ]);

        return response()->json(['producto' => $producto, 'message' => 'Producto agregado.'], 201);
    }

    public function confirmarPedido(Pedido $pedido)
    {
        abort_if($pedido->local_id !== Auth::user()->local?->id, 403);
        $pedido->update(['estado' => 'confirmado']);
        return response()->json(['pedido' => $pedido->fresh()]);
    }

    public function marcarListo(Pedido $pedido)
    {
        abort_if($pedido->local_id !== Auth::user()->local?->id, 403);
        $pedido->update(['estado' => 'listo']);
        return response()->json(['pedido' => $pedido->fresh()]);
    }

    public function calificaciones()
    {
        $local = Auth::user()->local;

        if (!$local) {
            return response()->json(['local' => null]);
        }

        $resenas = Resena::where('local_id', $local->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $total = $resenas->count();

        // Distribución de estrellas (5 → 1) en porcentaje.
        $distribucion = collect([5, 4, 3, 2, 1])->mapWithKeys(function ($estrella) use ($resenas, $total) {
            $cantidad = $resenas->where('estrellas', $estrella)->count();
            return [$estrella => $total > 0 ? round($cantidad * 100 / $total) : 0];
        });

        return response()->json([
            'promedio'     => round((float) $local->rating_promedio, 1),
            'total'        => $total,
            'distribucion' => $distribucion,
            'resenas'      => $resenas,
        ]);
    }
}
