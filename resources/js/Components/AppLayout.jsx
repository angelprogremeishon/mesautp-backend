import { Link, usePage, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { LogOut, ChevronLeft } from 'lucide-react';
import BottomNav from './BottomNav';

export default function AppLayout({ title, back, actions, children }) {
    const { auth, flash } = usePage().props;
    const [toast, setToast] = useState(null);

    useEffect(() => {
        if (flash?.status) {
            setToast(flash.status);
            const t = setTimeout(() => setToast(null), 4500);
            return () => clearTimeout(t);
        }
    }, [flash?.status]);

    const logout = () => router.post(route('logout'));

    return (
        <div className="min-h-dvh bg-slate-50 flex flex-col">
            {/* Status bar placeholder */}
            <div className="h-11 bg-white" />

            {/* Top bar */}
            <header className="sticky top-0 z-40 bg-white border-b border-slate-100">
                <div className="max-w-md mx-auto flex items-center h-14 px-4 gap-3">
                    {back && (
                        <button
                            onClick={back}
                            className="p-1 -ml-1 text-slate-600 active:text-slate-900"
                        >
                            <ChevronLeft size={22} />
                        </button>
                    )}
                    <h1 className="flex-1 font-bold text-slate-900 text-lg font-display truncate">
                        {title}
                    </h1>
                    {actions && <div className="flex items-center gap-2">{actions}</div>}
                    {!back && auth?.user && (
                        <button
                            onClick={logout}
                            className="p-2 -mr-1 text-slate-400 active:text-slate-700 transition-colors"
                            title="Cerrar sesión"
                        >
                            <LogOut size={18} />
                        </button>
                    )}
                </div>
            </header>

            {/* Flash toast */}
            {toast && (
                <div className="max-w-md mx-auto w-full px-4 pt-3 animate-fade-in">
                    <div className="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-700 flex items-center justify-between gap-3">
                        <span className="flex-1">{toast}</span>
                        <button
                            onClick={() => setToast(null)}
                            className="text-green-400 hover:text-green-600 shrink-0 leading-none"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            )}

            {/* Content */}
            <main className="flex-1 max-w-md mx-auto w-full px-4 pb-24">
                {children}
            </main>

            <BottomNav />
        </div>
    );
}
