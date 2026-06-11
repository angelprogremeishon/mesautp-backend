<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EmprendedorController extends Controller
{
    public function dashboard()
    {
        $local = Auth::user()->local;

        if (!$local) {
            return Inertia::render('Emprendedor/Registro');
        }

        $pedidos = Pedido::where('local_id', $local->id)
            ->with('user', 'producto')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        $stats = [
            'pedidos_hoy'    => Pedido::where('local_id', $local->id)->whereDate('created_at', today())->count(),
            'pedidos_semana' => Pedido::where('local_id', $local->id)->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'ingresos_hoy'   => Pedido::where('local_id', $local->id)->whereDate('created_at', today())->where('estado', 'entregado')->sum('total'),
        ];

        return Inertia::render('Emprendedor/Dashboard', [
            'local'   => $local->load('productos'),
            'pedidos' => $pedidos,
            'stats'   => $stats,
        ]);
    }

    public function registrar(Request $request)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:100',
            'tipo'           => 'required|in:externo,interno',
            'descripcion'    => 'nullable|string|max:500',
            'categoria'      => 'nullable|string|max:50',
            'direccion'      => 'nullable|string|max:200',
            'punto_entrega'  => 'nullable|string|max:100',
            'horario'        => 'nullable|string|max:100',
            'precio_min'     => 'nullable|numeric|min:0',
            'precio_max'     => 'nullable|numeric|min:0',
            'yape'           => 'nullable|string|max:20',
            'plin'           => 'nullable|string|max:20',
            'whatsapp'       => 'nullable|string|max:20',
            'foto'           => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('locales', 'public');
        }

        $data['user_id'] = Auth::id();
        $data['estado']  = 'pendiente';

        Local::create($data);

        return redirect()->route('emprendedor.dashboard')
            ->with('status', 'Registro enviado. Te avisaremos cuando sea aprobado.');
    }

    public function actualizarLocal(Request $request)
    {
        $local = Auth::user()->local;
        $data  = $request->validate([
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

        return back()->with('status', 'Local actualizado.');
    }

    public function guardarProducto(Request $request)
    {
        $local = Auth::user()->local;
        $data  = $request->validate([
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

        $data['local_id']  = $local->id;
        $data['disponible'] = $data['cantidad_disponible'] > 0;

        Producto::create($data);

        return back()->with('status', 'Producto agregado.');
    }

    public function confirmarPedido(Pedido $pedido)
    {
        abort_if($pedido->local_id !== Auth::user()->local?->id, 403);
        $pedido->update(['estado' => 'confirmado']);
        return back();
    }

    public function marcarListo(Pedido $pedido)
    {
        abort_if($pedido->local_id !== Auth::user()->local?->id, 403);
        $pedido->update(['estado' => 'listo']);
        return back();
    }
}
