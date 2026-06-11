import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Components/AppLayout';
import LocalCard from '@/Components/LocalCard';

const PRECIOS = [{ label: 'Hasta S/ 10', value: 10 }, { label: 'Hasta S/ 15', value: 15 }];

export default function LocalesInternosIndex({ locals, filters }) {
    const [precioMax, setPrecioMax] = useState(filters.precio_max ?? '');

    const apply = (overrides = {}) => {
        router.get(route('locales.internos'), { precio_max: precioMax, ...overrides }, {
            preserveState: true, replace: true,
        });
    };

    return (
        <>
            <Head title="Locales Internos" />
            <AppLayout title="Locales Internos">
                <div className="pt-3 pb-2">
                    <p className="text-sm text-slate-500 leading-relaxed">
                        Comida casera preparada por compañeros UTP dentro del campus. Paga por Yape o Plin.
                    </p>
                </div>

                {/* Filtros precio */}
                <div className="flex gap-2 pb-3">
                    {PRECIOS.map(p => {
                        const active = String(precioMax) === String(p.value);
                        return (
                            <button
                                key={p.value}
                                onClick={() => {
                                    const v = active ? '' : p.value;
                                    setPrecioMax(v);
                                    apply({ precio_max: v });
                                }}
                                className={`px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors ${
                                    active ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-600 border-slate-200'
                                }`}
                            >
                                {p.label}
                            </button>
                        );
                    })}
                </div>

                <p className="text-xs text-slate-500 mb-3">
                    {locals.length} {locals.length === 1 ? 'emprendedor disponible' : 'emprendedores disponibles'} hoy
                </p>

                <div className="space-y-3">
                    {locals.length === 0 ? (
                        <div className="text-center py-16 text-slate-400">
                            <span className="text-4xl block mb-3">🍱</span>
                            <p className="text-sm">No hay emprendedores con oferta hoy</p>
                            <p className="text-xs mt-1">Vuelve a revisar más tarde</p>
                        </div>
                    ) : (
                        locals.map(local => (
                            <LocalCard
                                key={local.id}
                                local={local}
                                href={route('locales.internos.show', local.id)}
                            />
                        ))
                    )}
                </div>
            </AppLayout>
        </>
    );
}
