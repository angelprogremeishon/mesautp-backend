import { Head, router, usePage, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { MapPin, Clock } from 'lucide-react';
import AppLayout from '@/Components/AppLayout';
import StarRating from '@/Components/StarRating';

export default function LocalesExternosShow({ local }) {
    const [reservando, setReservando] = useState(null);
    const { data, setData, post, processing, reset, errors } = useForm({
        local_id:    local.id,
        producto_id: null,
        cantidad:    1,
        nota:        '',
    });

    const abrirReserva = (producto) => {
        setData(prev => ({ ...prev, producto_id: producto.id, cantidad: 1, nota: '' }));
        setReservando(producto);
    };

    const confirmarReserva = () => {
        post(route('pedidos.store'), {
            onSuccess: (page) => {
                setReservando(null);
                reset();
                if (page.props.flash?.whatsapp_url) {
                    window.open(page.props.flash.whatsapp_url, '_blank');
                }
            },
        });
    };

    return (
        <>
            <Head title={local.nombre} />
            <AppLayout
                title={local.nombre}
                back={() => router.visit(route('locales.externos'))}
            >
                {/* Hero imagen */}
                <div className="-mx-4 h-52 bg-slate-100">
                    <img
                        src={local.foto_url ?? '/images/local-placeholder.jpg'}
                        alt={local.nombre}
                        className="w-full h-full object-cover"
                    />
                </div>

                {/* Info principal */}
                <div className="pt-4 pb-2">
                    <div className="flex items-start justify-between gap-3">
                        <div>
                            <h2 className="text-xl font-bold text-slate-900">{local.nombre}</h2>
                            <p className="text-sm text-slate-500 mt-0.5">{local.categoria}</p>
                        </div>
                        <StarRating value={local.rating_promedio} count={local.total_resenas} size={16} />
                    </div>

                    {local.descripcion && (
                        <p className="mt-3 text-sm text-slate-600 leading-relaxed">{local.descripcion}</p>
                    )}

                    <div className="mt-3 space-y-1.5">
                        {local.distancia_metros && (
                            <div className="flex items-center gap-2 text-sm text-slate-500">
                                <MapPin size={14} className="shrink-0 text-orange-500" />
                                {local.direccion ?? 'Cerca de UTP Ate'}
                                {` • ${local.distancia_metros}m`}
                            </div>
                        )}
                        {local.horario && (
                            <div className="flex items-center gap-2 text-sm text-slate-500">
                                <Clock size={14} className="shrink-0 text-orange-500" />
                                {local.horario}
                            </div>
                        )}
                    </div>

                    {/* Pagos y contacto */}
                    {(local.yape || local.plin || local.whatsapp) && (
                        <div className="mt-3 flex gap-2 flex-wrap">
                            {local.whatsapp && (
                                <a
                                    href={`https://wa.me/${local.whatsapp}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-full text-xs font-semibold text-green-700"
                                >
                                    WhatsApp
                                </a>
                            )}
                            {local.yape && (
                                <span className="px-3 py-1.5 bg-purple-50 border border-purple-200 rounded-full text-xs font-semibold text-purple-700">
                                    Yape {local.yape}
                                </span>
                            )}
                            {local.plin && (
                                <span className="px-3 py-1.5 bg-blue-50 border border-blue-200 rounded-full text-xs font-semibold text-blue-700">
                                    Plin {local.plin}
                                </span>
                            )}
                        </div>
                    )}
                </div>

                {/* Menú */}
                {local.productos?.length > 0 && (
                    <section className="mt-2">
                        <h3 className="font-bold text-slate-900 mb-3">Menú del día</h3>
                        <div className="space-y-2">
                            {local.productos.map(p => (
                                <div key={p.id} className="bg-white rounded-xl p-3 border border-slate-100 flex items-center gap-3">
                                    {p.foto && (
                                        <img
                                            src={`/storage/${p.foto}`}
                                            alt={p.nombre}
                                            className="w-16 h-16 rounded-lg object-cover shrink-0"
                                        />
                                    )}
                                    <div className="flex-1 min-w-0">
                                        <p className="font-semibold text-sm text-slate-900 truncate">{p.nombre}</p>
                                        {p.descripcion && (
                                            <p className="text-xs text-slate-500 truncate">{p.descripcion}</p>
                                        )}
                                        <p className="text-sm font-bold text-orange-600 mt-1">
                                            S/ {Number(p.precio).toFixed(2)}
                                        </p>
                                    </div>
                                    <button
                                        onClick={() => abrirReserva(p)}
                                        className="shrink-0 px-3 py-1.5 bg-orange-600 text-white text-xs font-semibold rounded-lg active:scale-95 transition-transform"
                                    >
                                        Reservar
                                    </button>
                                </div>
                            ))}
                        </div>
                    </section>
                )}

                {/* Reseñas */}
                {local.resenas?.length > 0 && (
                    <section className="mt-4 mb-2">
                        <h3 className="font-bold text-slate-900 mb-3">
                            Reseñas ({local.resenas.length})
                        </h3>
                        <div className="space-y-2">
                            {local.resenas.slice(0, 5).map(r => (
                                <div key={r.id} className="bg-white rounded-xl p-3 border border-slate-100">
                                    <div className="flex items-center gap-2 mb-1">
                                        <span className="text-xs font-semibold text-slate-700">{r.user?.name}</span>
                                        <StarRating value={r.estrellas} size={12} />
                                    </div>
                                    {r.comentario && (
                                        <p className="text-xs text-slate-500">{r.comentario}</p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </section>
                )}
            </AppLayout>

            {/* Bottom sheet reserva */}
            {reservando && (
                <div className="fixed inset-0 z-50 flex items-end">
                    <div
                        className="absolute inset-0 bg-slate-900/50"
                        onClick={() => setReservando(null)}
                    />
                    <div className="relative bg-white w-full rounded-t-3xl p-6 max-w-md mx-auto shadow-2xl">
                        <div className="w-8 h-1 bg-slate-200 rounded-full mx-auto mb-5" />
                        <h3 className="font-bold text-slate-900 text-lg mb-1">
                            {reservando.nombre}
                        </h3>
                        <p className="text-sm text-orange-600 font-semibold mb-5">
                            S/ {Number(reservando.precio).toFixed(2)} por unidad
                        </p>

                        <div className="space-y-3 mb-6">
                            <div>
                                <label className="text-sm font-medium text-slate-700 block mb-1.5">
                                    Cantidad
                                </label>
                                <div className="flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() => setData('cantidad', Math.max(1, data.cantidad - 1))}
                                        className="w-9 h-9 rounded-lg border border-slate-200 text-slate-700 font-bold text-lg flex items-center justify-center"
                                    >
                                        −
                                    </button>
                                    <span className="text-lg font-bold text-slate-900 w-8 text-center">
                                        {data.cantidad}
                                    </span>
                                    <button
                                        type="button"
                                        onClick={() => setData('cantidad', data.cantidad + 1)}
                                        className="w-9 h-9 rounded-lg border border-slate-200 text-slate-700 font-bold text-lg flex items-center justify-center"
                                    >
                                        +
                                    </button>
                                    <span className="text-sm text-slate-500 ml-1">
                                        Total: <strong className="text-slate-900">
                                            S/ {(Number(reservando.precio) * data.cantidad).toFixed(2)}
                                        </strong>
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label className="text-sm font-medium text-slate-700 block mb-1.5">
                                    Nota (opcional)
                                </label>
                                <input
                                    type="text"
                                    value={data.nota}
                                    onChange={e => setData('nota', e.target.value)}
                                    placeholder="Sin cebolla, extra ají..."
                                    className="w-full h-10 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                />
                            </div>
                        </div>

                        <button
                            onClick={confirmarReserva}
                            disabled={processing}
                            className="w-full h-12 bg-orange-600 text-white font-semibold rounded-xl disabled:opacity-60 active:scale-[0.98] transition-transform"
                        >
                            {processing ? 'Creando reserva...' : 'Reservar y coordinar por WhatsApp'}
                        </button>
                    </div>
                </div>
            )}
        </>
    );
}
