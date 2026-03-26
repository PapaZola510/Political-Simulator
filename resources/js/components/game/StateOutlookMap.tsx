import { useMemo } from 'react';

interface State {
    name: string;
    abbr: string;
}

interface Props {
    states: State[];
    stateReactions: Record<string, number>;
    stateBands?: Record<string, { band: string; is_competitive: boolean }>;
}

export default function StateOutlookMap({ states, stateReactions, stateBands }: Props) {
    const getColor = (reaction: number) => {
        if (reaction >= 15) return 'bg-blue-600';
        if (reaction >= 8) return 'bg-blue-400';
        if (reaction >= 3) return 'bg-blue-300';
        if (reaction > -3) return 'bg-gray-400';
        if (reaction > -8) return 'bg-red-300';
        if (reaction > -15) return 'bg-red-400';
        return 'bg-red-600';
    };

    const getGradientColor = (reaction: number) => {
        const normalized = (reaction + 25) / 50;
        const r = Math.round(255 * normalized);
        const b = Math.round(255 * (1 - normalized));
        return `rgb(${r}, 100, ${b})`;
    };

    const sortedStates = useMemo(() => {
        return [...states].sort((a, b) => {
            const aReaction = stateReactions[a.abbr] || 0;
            const bReaction = stateReactions[b.abbr] || 0;
            return bReaction - aReaction;
        });
    }, [states, stateReactions]);

    const topSupporters = sortedStates.slice(0, 5);
    const topOpposers = sortedStates.slice(-5).reverse();

    return (
        <div className="grid md:grid-cols-3 gap-6">
            <div className="md:col-span-2">
                <div className="grid grid-cols-5 gap-1">
                    {states.map((state) => {
                        const reaction = stateReactions[state.abbr] || 0;
                        const band = stateBands?.[state.abbr];
                        
                        return (
                            <div
                                key={state.abbr}
                                className="relative group"
                            >
                                <div
                                    className="aspect-square rounded flex items-center justify-center text-white text-xs font-medium cursor-pointer transition-transform hover:scale-110"
                                    style={{ backgroundColor: getGradientColor(reaction) }}
                                    title={`${state.name}: ${reaction > 0 ? '+' : ''}${reaction}`}
                                >
                                    {state.abbr}
                                </div>
                                <div className="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 bg-black/80 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-10 pointer-events-none">
                                    <span className="font-medium">{state.name}</span>: {reaction > 0 ? '+' : ''}{reaction}
                                    {band?.is_competitive && ' (Swing)'}
                                </div>
                            </div>
                        );
                    })}
                </div>
                <div className="flex justify-center mt-4 gap-4 text-xs">
                    <div className="flex items-center gap-1">
                        <div className="w-4 h-4 rounded" style={{ backgroundColor: getGradientColor(25) }} />
                        <span>Strong Support</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-4 h-4 rounded" style={{ backgroundColor: getGradientColor(0) }} />
                        <span>Neutral</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-4 h-4 rounded" style={{ backgroundColor: getGradientColor(-25) }} />
                        <span>Strong Opposition</span>
                    </div>
                </div>
            </div>
            <div className="space-y-4">
                <div>
                    <h4 className="font-semibold text-blue-600 mb-2">Top 5 Supporters</h4>
                    <div className="space-y-1">
                        {topSupporters.map((state) => {
                            const reaction = stateReactions[state.abbr] || 0;
                            return (
                                <div key={state.abbr} className="flex justify-between items-center text-sm">
                                    <span>{state.name}</span>
                                    <span className="font-medium text-blue-600">+{reaction}</span>
                                </div>
                            );
                        })}
                    </div>
                </div>
                <div>
                    <h4 className="font-semibold text-red-600 mb-2">Top 5 Opposers</h4>
                    <div className="space-y-1">
                        {topOpposers.map((state) => {
                            const reaction = stateReactions[state.abbr] || 0;
                            return (
                                <div key={state.abbr} className="flex justify-between items-center text-sm">
                                    <span>{state.name}</span>
                                    <span className="font-medium text-red-600">{reaction}</span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
}
