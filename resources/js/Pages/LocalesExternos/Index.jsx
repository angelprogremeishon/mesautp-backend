import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Search, SlidersHorizontal } from 'lucide-react';
import AppLayout from '@/Components/AppLayout';
import LocalCard from '@/Components/LocalCard';

const CATEGORIAS = ['Todos', 'Criolla', 'Pollo', 'Fusión', 'Mariscos', 'Menú del día'];
const PRECIOS    = [{ label: 'Hasta S/ 10', value: 10 }, { label: 'Hasta S/ 15', value: 15 }];

export default function LocalesExternosIndex({ locals, filters }) {
    const [buscar, setBuscar]       = useState(filters.buscar ?? '');
    const [categoria, setCategoria] = useState(filters.categoria ?? '');
    const [precioMax, setPrecioMax] = useState(filters.precio_max ?? '');

    const applyFilters = (overrides = {}) => {
        router.get(route('locales.externos'), {
            buscar:     buscar,
            categoria:  categoria,
            precio_max: precioMax,
            ...overrides,
        }, { preserveState: true, replace: true });
    };

    return (
        <>
            <Head title="Locales Externos" />
            <AppLayout title="Locales Externos">
                {/* Search */}
                <div className="pt-3 pb-2">
                    <div className="relative">
                        <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="search"
                            value={buscar}
                            onChange={e => setBuscar(e.target.value)}
                            onKeyDown={e => e.key === 'Enter' && applyFilters({ buscar: e.target.value })}
                            placeholder="Buscar local o menú del día..."
                            className="w-full h-10 pl-9 pr-4 rounded-xl bg-white border border-slate-200 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500"
                        />
                    </div>
                </div>

                {/* Filtros categoría */}
                <div className="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                    {CATEGORIAS.map(cat => {
                        const val    = cat === 'Todos' ? '' : cat;
                        const active = categoria === val;
                        return (
                            <button
                                key={cat}
                                onClick={() => { setCategoria(val); applyFilters({ categoria: val }); }}
                                className={`shrink-0 px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors ${
                                    active
                                        ? 'bg-orange-600 text-white border-orange-600'
                                        : 'bg-white text-slate-600 border-slate-200'
                                }`}
                            >
                                {cat}
                            </button>
                        );
                    })}
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
                                    applyFilters({ precio_max: v });
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

                {/* Resultado */}
                <p className="text-xs text-slate-500 mb-3">
                    {locals.length} {locals.length === 1 ? 'local encontrado' : 'locales encontrados'} cerca de UTP Ate
                </p>

                {/* Lista */}
                <div className="space-y-3">
                    {locals.length === 0 ? (
                        <div className="text-center py-16 text-slate-400">
                            <span className="text-4xl block mb-3">🔍</span>
                            <p className="text-sm">No encontramos locales con esos filtros</p>
                        </div>
                    ) : (
                        locals.map(local => (
                            <LocalCard
                                key={local.id}
                                local={local}
                                href={route('locales.externos.show', local.id)}
                            />
                        ))
                    )}
                </div>
            </AppLayout>
        </>
    );
}
