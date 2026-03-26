import { useEffect, useState, useMemo } from 'react';
import * as d3 from 'd3-geo';
import * as topojson from 'topojson-client';

interface State {
    name: string;
    abbr: string;
}

interface Props {
    states: State[];
    stateReactions: Record<string, number>;
    stateBands?: Record<string, { band: string; is_competitive: boolean }>;
}

interface GeoFeature {
    id: string | number;
    properties: { name: string; postal?: string };
    geometry: unknown;
}

function topoFeature(topology: unknown, obj: unknown) {
    return topojson.feature(topology as any, obj as any) as { type: 'FeatureCollection'; features: GeoFeature[] };
}

export default function StateOutlookMap({ states, stateReactions, stateBands }: Props) {
    const [mapPaths, setMapPaths] = useState<{ fips: string; path: string; name: string; abbr: string }[]>([]);
    const [loading, setLoading] = useState(true);
    const [hoveredState, setHoveredState] = useState<string | null>(null);

    const fipsToAbbr: Record<string, string> = {
        '01': 'AL', '02': 'AK', '04': 'AZ', '05': 'AR', '06': 'CA', '08': 'CO', '09': 'CT',
        '10': 'DE', '12': 'FL', '13': 'GA', '15': 'HI', '16': 'ID', '17': 'IL', '18': 'IN',
        '19': 'IA', '20': 'KS', '21': 'KY', '22': 'LA', '23': 'ME', '24': 'MD', '25': 'MA',
        '26': 'MI', '27': 'MN', '28': 'MS', '29': 'MO', '30': 'MT', '31': 'NE', '32': 'NV',
        '33': 'NH', '34': 'NJ', '35': 'NM', '36': 'NY', '37': 'NC', '38': 'ND', '39': 'OH',
        '40': 'OK', '41': 'OR', '42': 'PA', '44': 'RI', '45': 'SC', '46': 'SD', '47': 'TN',
        '48': 'TX', '49': 'UT', '50': 'VT', '51': 'VA', '53': 'WA', '54': 'WV', '55': 'WI', '56': 'WY'
    };

    useEffect(() => {
        async function loadMap() {
            try {
                const response = await fetch('https://cdn.jsdelivr.net/npm/us-atlas@3/states-10m.json');
                if (!response.ok) throw new Error('Failed to load');
                
                const topology = await response.json();
                const statesData = topoFeature(topology, topology.objects.states);
                const projection = d3.geoAlbersUsa().fitSize([600, 380], statesData);
                const pathGenerator = d3.geoPath().projection(projection);

                const paths = statesData.features.map((f: GeoFeature) => {
                    const fips = String(f.id).padStart(2, '0');
                    const abbr = fipsToAbbr[fips] || f.properties.postal || '';
                    return {
                        fips,
                        path: pathGenerator(f as any) || '',
                        name: f.properties.name,
                        abbr
                    };
                });

                setMapPaths(paths);
                setLoading(false);
            } catch (err) {
                console.error('Failed to load map:', err);
                setLoading(false);
            }
        }
        loadMap();
    }, []);

    const getReactionColor = (abbr: string): string => {
        const reaction = stateReactions[abbr] ?? 50;
        const band = stateBands?.[abbr]?.band;
        
        if (reaction >= 75 || band === 'strongly_supports') return '#1e40af';  // Strong blue (dark)
        if (reaction >= 65 || band === 'supports') return '#3b82f6';  // Blue
        if (reaction >= 55 || band === 'leans_support') return '#93c5fd';  // Light blue
        if (reaction >= 45 || band === 'neutral') return '#9ca3af';  // Gray
        if (reaction >= 35 || band === 'leans_oppose') return '#fca5a5';  // Light red
        if (reaction >= 25 || band === 'opposes') return '#ef4444';  // Red
        return '#991b1b';  // Strong red (dark)
    };

    const sortedStates = useMemo(() => {
        return [...states].sort((a, b) => {
            const aReaction = stateReactions[a.abbr] ?? 50;
            const bReaction = stateReactions[b.abbr] ?? 50;
            return bReaction - aReaction;
        });
    }, [states, stateReactions]);

    const topSupporters = sortedStates.slice(0, 5);
    const topOpposers = sortedStates.slice(-5).reverse();

    if (loading) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            </div>
        );
    }

    return (
        <div className="grid md:grid-cols-3 gap-4">
            <div className="md:col-span-2">
                <div className="flex justify-center">
                    <svg 
                        viewBox="0 0 600 380" 
                        className="w-full max-w-2xl h-auto"
                    >
                        <g>
                            {mapPaths.map((state) => {
                                const reaction = stateReactions[state.abbr] ?? 50;
                                const isHovered = hoveredState === state.abbr;
                                return (
                                    <path
                                        key={state.fips}
                                        d={state.path}
                                        fill={getReactionColor(state.abbr)}
                                        stroke="#fff"
                                        strokeWidth={isHovered ? 2 : 0.5}
                                        opacity={isHovered ? 1 : 0.9}
                                        className="transition-all duration-150 cursor-pointer"
                                        onMouseEnter={() => setHoveredState(state.abbr)}
                                        onMouseLeave={() => setHoveredState(null)}
                                    />
                                );
                            })}
                        </g>
                    </svg>
                </div>
                <div className="flex flex-wrap justify-center mt-3 gap-x-4 gap-y-1 text-xs">
                    <div className="flex items-center gap-1">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#1e40af' }} />
                        <span>Strong Support</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#3b82f6' }} />
                        <span>Supports</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#93c5fd' }} />
                        <span>Leans Support</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#9ca3af' }} />
                        <span>Neutral</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#fca5a5' }} />
                        <span>Leans Oppose</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#ef4444' }} />
                        <span>Opposes</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-3 h-3 rounded" style={{ backgroundColor: '#991b1b' }} />
                        <span>Strong Oppose</span>
                    </div>
                </div>
            </div>
            <div className="space-y-3">
                <div>
                    <h4 className="font-semibold text-blue-600 mb-1 text-sm">Top 5 Supporters</h4>
                    <div className="space-y-0.5">
                        {topSupporters.map((state) => {
                            const reaction = stateReactions[state.abbr] ?? 50;
                            return (
                                <div key={state.abbr} className="flex justify-between items-center text-xs">
                                    <span>{state.name}</span>
                                    <span className="font-medium text-blue-600">{reaction.toFixed(1)}</span>
                                </div>
                            );
                        })}
                    </div>
                </div>
                <div>
                    <h4 className="font-semibold text-red-600 mb-1 text-sm">Top 5 Opposers</h4>
                    <div className="space-y-0.5">
                        {topOpposers.map((state) => {
                            const reaction = stateReactions[state.abbr] ?? 50;
                            return (
                                <div key={state.abbr} className="flex justify-between items-center text-xs">
                                    <span>{state.name}</span>
                                    <span className="font-medium text-red-600">{reaction.toFixed(1)}</span>
                                </div>
                            );
                        })}
                    </div>
                </div>
            </div>
        </div>
    );
}
