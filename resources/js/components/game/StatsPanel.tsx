interface StatBarProps {
    label: string;
    value: number;
    color: 'green' | 'blue' | 'purple';
    delta?: number;
}

export default function StatsPanel({ label, value, color, delta }: StatBarProps) {
    const colorClasses = {
        green: 'bg-green-500',
        blue: 'bg-blue-500',
        purple: 'bg-purple-500',
    };

    const bgColorClasses = {
        green: 'bg-green-950',
        blue: 'bg-blue-950',
        purple: 'bg-purple-950',
    };

    const textColorClasses = {
        green: 'text-green-600',
        blue: 'text-blue-600',
        purple: 'text-purple-600',
    };

    return (
        <div className="space-y-2">
            <div className="flex justify-between text-sm items-center">
                <span className="font-medium">{label}</span>
                <div className="flex items-center gap-2">
                    {delta !== undefined && delta !== 0 && (
                        <span className={`text-sm font-medium ${delta > 0 ? 'text-green-600' : 'text-red-600'}`}>
                            {delta > 0 ? '\u{1F7E2}' : '\u{1F534}'} {delta > 0 ? '+' : ''}{delta}%
                        </span>
                    )}
                    <span className={`font-semibold ${textColorClasses[color]}`}>{value}%</span>
                </div>
            </div>
            <div className={`h-3 w-full rounded-full ${bgColorClasses[color]}`}>
                <div
                    className={`h-full rounded-full ${colorClasses[color]} transition-all duration-500`}
                    style={{ width: `${value}%` }}
                />
            </div>
        </div>
    );
}
