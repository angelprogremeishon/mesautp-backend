import { Link, usePage } from '@inertiajs/react';
import { UtensilsCrossed, ShoppingBag, Store } from 'lucide-react';

const tabs = [
    { href: '/locales-externos', label: 'Externos',  icon: Store },
    { href: '/locales-internos', label: 'Internos',  icon: ShoppingBag },
    { href: '/emprendedor',      label: 'Mi Panel',  icon: UtensilsCrossed },
];

export default function BottomNav() {
    const { url } = usePage();

    return (
        <nav
            className="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50"
            style={{ paddingBottom: 'env(safe-area-inset-bottom, 0px)' }}
        >
            <div className="flex max-w-md mx-auto">
                {tabs.map(({ href, label, icon: Icon }) => {
                    const active = url.startsWith(href);
                    return (
                        <Link
                            key={href}
                            href={href}
                            className="flex-1 flex flex-col items-center justify-center py-2 gap-0.5 min-h-[56px]"
                        >
                            <Icon
                                size={22}
                                className={active ? 'text-orange-600' : 'text-slate-400'}
                                strokeWidth={active ? 2.5 : 1.8}
                            />
                            <span className={`text-[10px] font-medium ${active ? 'text-orange-600' : 'text-slate-400'}`}>
                                {label}
                            </span>
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
