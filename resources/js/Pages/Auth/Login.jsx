import { Head, useForm } from '@inertiajs/react';

export default function Login({ status, errors }) {
    const { data, setData, post, processing } = useForm({ email: '' });

    const submit = (e) => {
        e.preventDefault();
        post(route('login.send'));
    };

    return (
        <>
            <Head title="Ingresa" />
            <div className="min-h-dvh bg-orange-600 flex flex-col">
                {/* Hero */}
                <div className="flex-1 flex flex-col items-center justify-center px-6 pt-16 pb-8 text-center">
                    <div className="w-20 h-20 bg-white rounded-3xl flex items-center justify-center shadow-lg mb-6">
                        <span className="text-4xl">🍽️</span>
                    </div>
                    <h1 className="text-3xl font-extrabold text-white font-display">MesaUTP</h1>
                    <p className="mt-3 text-orange-100 text-base max-w-xs leading-relaxed">
                        Comida económica para la comunidad UTP Ate
                    </p>
                </div>

                {/* Card */}
                <div className="bg-white rounded-t-3xl px-6 pt-8 pb-10 shadow-2xl">
                    <h2 className="text-xl font-bold text-slate-900 mb-1">Ingresa a tu cuenta</h2>
                    <p className="text-sm text-slate-500 mb-6">Usa tu correo institucional UTP para acceder</p>

                    {status && (
                        <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                            {status}
                        </div>
                    )}

                    {errors?.token && (
                        <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
                            {errors.token}
                        </div>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-slate-700 mb-1.5">
                                Correo institucional
                            </label>
                            <input
                                type="email"
                                value={data.email}
                                onChange={e => setData('email', e.target.value)}
                                placeholder="tu.nombre@utp.edu.pe"
                                className="w-full h-12 px-4 rounded-xl border border-slate-200 text-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                inputMode="email"
                                autoComplete="email"
                                required
                            />
                            {errors?.email && <p className="mt-1.5 text-xs text-red-500">{errors.email}</p>}
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full h-12 bg-orange-600 text-white font-semibold rounded-xl text-sm disabled:opacity-60 active:scale-[0.98] transition-transform"
                        >
                            {processing ? 'Enviando...' : 'Recibir enlace de acceso'}
                        </button>
                    </form>

                    <p className="mt-6 text-center text-xs text-slate-400">
                        Solo disponible para la comunidad UTP Ate
                    </p>
                </div>
            </div>
        </>
    );
}
