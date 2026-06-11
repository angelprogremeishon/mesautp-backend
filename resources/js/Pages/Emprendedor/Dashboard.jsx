import { Head, useForm, router } from '@inertiajs/react';
import { useState } from 'react';
import { Plus, Package, TrendingUp, CheckCircle2, Clock } from 'lucide-react';
import AppLayout from '@/Components/AppLayout';
import StarRating from '@/Components/StarRating';

const ESTADO_BADGE = {
    pendiente:  { label: 'Pendiente', cls: 'bg-amber-50 text-amber-700 border-amber-200' },
    confirmado: { label: 'Confirmado', cls: 'bg-blue-50 text-blue-700 border-blue-200' },
    listo:      { label: 'Listo', cls: 'bg-green-50 text-green-700 border-green-200' },
    entregado:  { label: 'Entregado', cls: 'bg-slate-50 text-slate-500 border-slate-200' },
    cancelado:  { label: 'Cancelado', cls: 'bg-red-50 text-red-600 border-red-200' },
};

export default function EmprendedorDashboard({ local, pedidos, stats }) {
    const [tab, setTab] = useState('pedidos');
    const productoForm  = useForm({ nombre: '', descripcion: '', precio: '', cantidad_disponible: 1, es_menu_dia: true });

    const confirmar = (id) => router.post(route('emprendedor.pedidos.confirmar', id));
    const listo     = (id) => router.post(route('emprendedor.pedidos.listo', id));

    return (
        <>
            <Head title="Mi Panel" />
            <AppLayout title="Mi Panel de Emprendedor">
                {/* Stats */}
                <div className="pt-3 grid grid-cols-3 gap-2">
                    {[
                        { label: 'Pedidos hoy', val: stats.pedidos_hoy, icon: Package },
                        { label: 'Esta semana', val: stats.pedidos_semana, icon: TrendingUp },
                        { label: 'Ingresos hoy', val: `S/ ${Number(stats.ingresos_hoy).toFixed(0)}`, icon: CheckCircle2 },
                    ].map(s => (
                        <div key={s.label} className="bg-white rounded-xl p-3 border border-slate-100 text-center">
                            <s.icon size={18} className="mx-auto mb-1 text-orange-500" />
                            <p className="text-lg font-bold text-slate-900">{s.val}</p>
                            <p className="text-[10px] text-slate-400">{s.label}</p>
                        </div>
                    ))}
                </div>

                {/* Local info */}
                <div className="mt-3 bg-white rounded-xl p-4 border border-slate-100">
                    <div className="flex items-center gap-3">
                        <div className="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
                            <span className="text-xl">🍽️</span>
                        </div>
                        <div className="flex-1 min-w-0">
                            <p className="font-semibold text-slate-900 truncate">{local.nombre}</p>
                            <p className="text-xs text-slate-500 capitalize">{local.tipo} · {local.estado}</p>
                        </div>
                        <StarRating value={local.rating_promedio} count={local.total_resenas} />
                    </div>
                </div>

                {/* Tabs */}
                <div className="mt-3 flex border-b border-slate-200">
                    {[['pedidos', 'Pedidos'], ['productos', 'Mis Productos']].map(([key, label]) => (
                        <button
                            key={key}
                            onClick={() => setTab(key)}
                            className={`flex-1 py-2.5 text-sm font-semibold border-b-2 transition-colors ${
                                tab === key ? 'border-orange-600 text-orange-600' : 'border-transparent text-slate-400'
                            }`}
                        >
                            {label}
                        </button>
                    ))}
                </div>

                {/* Tab: Pedidos */}
                {tab === 'pedidos' && (
                    <div className="mt-3 space-y-2">
                        {pedidos.length === 0 ? (
                            <div className="text-center py-12 text-slate-400">
                                <Clock size={32} className="mx-auto mb-2 opacity-40" />
                                <p className="text-sm">Sin pedidos aún</p>
                            </div>
                        ) : pedidos.map(p => {
                            const badge = ESTADO_BADGE[p.estado];
                            return (
                                <div key={p.id} className="bg-white rounded-xl p-3 border border-slate-100">
                                    <div className="flex items-center justify-between mb-1">
                                        <p className="font-semibold text-sm text-slate-900">
                                            {p.producto?.nombre ?? 'Pedido'} × {p.cantidad}
                                        </p>
                                        <span className={`text-[10px] font-semibold border rounded-full px-2 py-0.5 ${badge.cls}`}>
                                            {badge.label}
                                        </span>
                                    </div>
                                    <p className="text-xs text-slate-500">{p.user?.name} · S/ {Number(p.total).toFixed(2)}</p>
                                    {p.nota && <p className="text-xs text-slate-400 mt-0.5">"{p.nota}"</p>}

                                    {p.estado === 'pendiente' && (
                                        <button onClick={() => confirmar(p.id)}
                                            className="mt-2 w-full h-8 bg-blue-600 text-white text-xs font-semibold rounded-lg">
                                            Confirmar pedido
                                        </button>
                                    )}
                                    {p.estado === 'confirmado' && (
                                        <button onClick={() => listo(p.id)}
                                            className="mt-2 w-full h-8 bg-green-600 text-white text-xs font-semibold rounded-lg">
                                            Marcar como listo
                                        </button>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                )}

                {/* Tab: Productos */}
                {tab === 'productos' && (
                    <div className="mt-3">
                        {/* Formulario nuevo producto */}
                        <div className="bg-orange-50 border border-orange-100 rounded-xl p-4 mb-3">
                            <p className="text-sm font-semibold text-slate-900 mb-3">Agregar producto</p>
                            <form onSubmit={e => { e.preventDefault(); productoForm.post(route('emprendedor.productos.store'), { onSuccess: () => productoForm.reset() }); }}
                                className="space-y-2">
                                <input type="text" placeholder="Nombre del plato"
                                    value={productoForm.data.nombre}
                                    onChange={e => productoForm.setData('nombre', e.target.value)}
                                    className="w-full h-10 border border-slate-200 rounded-lg px-3 text-sm bg-white" required />
                                <div className="flex gap-2">
                                    <input type="number" placeholder="Precio S/" min="0" step="0.50"
                                        value={productoForm.data.precio}
                                        onChange={e => productoForm.setData('precio', e.target.value)}
                                        className="flex-1 h-10 border border-slate-200 rounded-lg px-3 text-sm bg-white" required />
                                    <input type="number" placeholder="Cantidad" min="1"
                                        value={productoForm.data.cantidad_disponible}
                                        onChange={e => productoForm.setData('cantidad_disponible', e.target.value)}
                                        className="w-24 h-10 border border-slate-200 rounded-lg px-3 text-sm bg-white" />
                                </div>
                                <button type="submit" disabled={productoForm.processing}
                                    className="w-full h-10 bg-orange-600 text-white text-sm font-semibold rounded-lg flex items-center justify-center gap-2 disabled:opacity-60">
                                    <Plus size={14} /> Publicar oferta
                                </button>
                            </form>
                        </div>

                        {/* Lista productos */}
                        <div className="space-y-2">
                            {local.productos?.map(p => (
                                <div key={p.id} className="bg-white rounded-xl p-3 border border-slate-100 flex items-center gap-3">
                                    <div className="flex-1">
                                        <p className="font-semibold text-sm text-slate-900">{p.nombre}</p>
                                        <p className="text-xs text-slate-500">S/ {Number(p.precio).toFixed(2)} · {p.cantidad_disponible} disp.</p>
                                    </div>
                                    <span className={`text-xs font-medium px-2 py-0.5 rounded-full border ${p.disponible ? 'bg-green-50 text-green-700 border-green-200' : 'bg-slate-50 text-slate-400 border-slate-200'}`}>
                                        {p.disponible ? 'Activo' : 'Oculto'}
                                    </span>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </AppLayout>
        </>
    );
}
