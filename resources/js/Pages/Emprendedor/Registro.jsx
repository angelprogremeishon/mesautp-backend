import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Components/AppLayout';

export default function EmprendedorRegistro() {
    const { data, setData, post, processing, errors } = useForm({
        nombre:        '',
        tipo:          'externo',
        descripcion:   '',
        categoria:     '',
        direccion:     '',
        punto_entrega: '',
        horario:       '',
        precio_min:    '',
        precio_max:    '',
        yape:          '',
        plin:          '',
        whatsapp:      '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('emprendedor.registrar'));
    };

    return (
        <>
            <Head title="Registrar mi negocio" />
            <AppLayout title="Registrar mi negocio">
                <div className="pt-4">
                    <p className="text-sm text-slate-500 mb-6">
                        Registra tu local o emprendimiento para aparecer en MesaUTP. Revisaremos tu solicitud en 24 horas.
                    </p>

                    <form onSubmit={submit} className="space-y-4">
                        {/* Tipo */}
                        <div>
                            <label className="text-sm font-medium text-slate-700 block mb-2">Tipo de emprendedor</label>
                            <div className="flex gap-2">
                                {[['externo', '🏪 Local externo'], ['interno', '🎓 Emprendedor UTP']].map(([val, label]) => (
                                    <button
                                        key={val}
                                        type="button"
                                        onClick={() => setData('tipo', val)}
                                        className={`flex-1 py-2.5 rounded-xl border text-sm font-semibold transition-colors ${
                                            data.tipo === val
                                                ? 'bg-orange-600 text-white border-orange-600'
                                                : 'bg-white text-slate-600 border-slate-200'
                                        }`}
                                    >
                                        {label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {[
                            { label: 'Nombre del local / negocio', key: 'nombre', required: true, placeholder: 'Ej: Doña Lucha, Menú de Carlos...' },
                            { label: 'Categoría', key: 'categoria', placeholder: 'Criolla, Pollo, Menú del día...' },
                            { label: 'Descripción breve', key: 'descripcion', placeholder: 'Qué ofreces, especialidades...' },
                        ].map(f => (
                            <div key={f.key}>
                                <label className="text-sm font-medium text-slate-700 block mb-1.5">{f.label}</label>
                                <input
                                    type="text"
                                    value={data[f.key]}
                                    onChange={e => setData(f.key, e.target.value)}
                                    placeholder={f.placeholder}
                                    required={f.required}
                                    className="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500"
                                />
                                {errors[f.key] && <p className="mt-1 text-xs text-red-500">{errors[f.key]}</p>}
                            </div>
                        ))}

                        {data.tipo === 'externo' ? (
                            <div>
                                <label className="text-sm font-medium text-slate-700 block mb-1.5">Dirección</label>
                                <input type="text" value={data.direccion}
                                    onChange={e => setData('direccion', e.target.value)}
                                    placeholder="Jr. Las Flores 123, frente a UTP"
                                    className="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                            </div>
                        ) : (
                            <div>
                                <label className="text-sm font-medium text-slate-700 block mb-1.5">Punto de entrega en campus</label>
                                <input type="text" value={data.punto_entrega}
                                    onChange={e => setData('punto_entrega', e.target.value)}
                                    placeholder="Patio principal, pasillo piso 3..."
                                    className="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                            </div>
                        )}

                        <div>
                            <label className="text-sm font-medium text-slate-700 block mb-1.5">Horario de atención</label>
                            <input type="text" value={data.horario}
                                onChange={e => setData('horario', e.target.value)}
                                placeholder="Lun–Vie 12:00–15:00"
                                className="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                        </div>

                        <div className="flex gap-2">
                            <div className="flex-1">
                                <label className="text-sm font-medium text-slate-700 block mb-1.5">Precio mínimo (S/)</label>
                                <input type="number" min="0" step="0.5" value={data.precio_min}
                                    onChange={e => setData('precio_min', e.target.value)}
                                    className="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                            </div>
                            <div className="flex-1">
                                <label className="text-sm font-medium text-slate-700 block mb-1.5">Precio máximo (S/)</label>
                                <input type="number" min="0" step="0.5" value={data.precio_max}
                                    onChange={e => setData('precio_max', e.target.value)}
                                    className="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                            </div>
                        </div>

                        <div className="flex gap-2">
                            {[['yape', 'N° Yape'], ['plin', 'N° Plin'], ['whatsapp', 'WhatsApp']].map(([key, label]) => (
                                <div key={key} className="flex-1">
                                    <label className="text-xs font-medium text-slate-700 block mb-1">{label}</label>
                                    <input type="tel" value={data[key]}
                                        onChange={e => setData(key, e.target.value)}
                                        className="w-full h-11 border border-slate-200 rounded-xl px-3 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500" />
                                </div>
                            ))}
                        </div>

                        <button type="submit" disabled={processing}
                            className="w-full h-12 bg-orange-600 text-white font-semibold rounded-xl text-sm disabled:opacity-60 mt-2">
                            {processing ? 'Enviando...' : 'Enviar solicitud de registro'}
                        </button>
                    </form>
                </div>
            </AppLayout>
        </>
    );
}
