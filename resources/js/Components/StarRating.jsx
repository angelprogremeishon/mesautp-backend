import { Star } from 'lucide-react';

export default function StarRating({ value = 0, count, size = 14 }) {
    return (
        <span className="flex items-center gap-1">
            <Star size={size} className="text-amber-400 fill-amber-400" />
            <span className="text-sm font-semibold text-slate-800">{Number(value).toFixed(1)}</span>
            {count !== undefined && (
                <span className="text-xs text-slate-400">({count})</span>
            )}
        </span>
    );
}
