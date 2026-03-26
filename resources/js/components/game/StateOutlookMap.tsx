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
                const projection = d3.geoAlbersUsa().fitSize([800, 500], statesData);
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
        
        if (reaction >= 75 || band === 'strongly_supports') return 'rgb(0, 100, 255)';  // Strong blue
        if (reaction >= 65 || band === 'supports') return 'rgb(50, 150, 255)';  // Blue
        if (reaction >= 55 || band === 'leans_support') return 'rgb(100, 200, 255)';  // Light blue
        if (reaction >= 45 || band === 'neutral') return 'rgb(200, 200, 200)';  // Gray
        if (reaction >= 35 || band === 'leans_oppose') return 'rgb(255, 150, 150)';  // Light red
        if (reaction >= 25 || band === 'opposes') return 'rgb(255, 100, 100)';  // Red
        return 'rgb(255, 50, 50)';  // Strong red
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

    if (loading) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            </div>
        );
    }

    return (
        <div className="grid md:grid-cols-3 gap-6">
            <div className="md:col-span-2">
                <svg 
                    viewBox="0 0 800 500" 
                    className="w-full h-auto"
                >
                    <g>
                        {mapPaths.map((state) => {
                            const reaction = stateReactions[state.abbr] ?? 0;
                            const isHovered = hoveredState === state.abbr;
                            return (
                                <path
                                    key={state.fips}
                                    d={state.path}
                                    fill={getReactionColor(state.abbr)}
                                    stroke="#fff"
                                    strokeWidth={isHovered ? 2 : 1}
                                    opacity={isHovered ? 1 : 0.9}
                                    className="transition-all duration-150 cursor-pointer"
                                    onMouseEnter={() => setHoveredState(state.abbr)}
                                    onMouseLeave={() => setHoveredState(null)}
                                />
                            );
                        })}
                    </g>
                </svg>
                <div className="flex justify-center mt-4 gap-4 text-xs">
                    <div className="flex items-center gap-1">
                        <div className="w-4 h-4 rounded" style={{ backgroundColor: 'rgb(0, 100, 255)' }} />
                        <span>Strong Support (75+)</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-4 h-4 rounded" style={{ backgroundColor: 'rgb(200, 200, 200)' }} />
                        <span>Neutral (45-54)</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-4 h-4 rounded" style={{ backgroundColor: 'rgb(255, 50, 50)' }} />
                        <span>Strong Opposition (&lt;25)</span>
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
