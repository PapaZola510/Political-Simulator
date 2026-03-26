import * as d3 from 'd3-geo';
import { useEffect, useRef, useState, useMemo } from 'react';
import * as topojson from 'topojson-client';

interface State {
    name: string;
    abbr: string;
    fips: string;
    color: 'blue' | 'red' | 'swing';
    type?: string;
}

interface StateBand {
    band: string;
    is_competitive: boolean;
}

interface StateOutlookMapProps {
    states: State[];
    stateReactions: Record<string, number>;
    stateBands?: Record<string, StateBand>;
}

interface StateInfo {
    fips: string;
    name: string;
    abbr: string;
    band: string;
    isCompetitive: boolean;
    color: string;
    score: number;
}

interface StateWithScore {
    fips: string;
    name: string;
    abbr: string;
    score: number;
    color: string;
    band: string;
}

const fipsToAbbr: Record<string, string> = {
    '01': 'AL', '02': 'AK', '04': 'AZ', '05': 'AR', '06': 'CA', '08': 'CO', '09': 'CT',
    '10': 'DE', '11': 'DC', '12': 'FL', '13': 'GA', '15': 'HI', '16': 'ID', '17': 'IL',
    '18': 'IN', '19': 'IA', '20': 'KS', '21': 'KY', '22': 'LA', '23': 'ME', '24': 'MD',
    '25': 'MA', '26': 'MI', '27': 'MN', '28': 'MS', '29': 'MO', '30': 'MT', '31': 'NE',
    '32': 'NV', '33': 'NH', '34': 'NJ', '35': 'NM', '36': 'NY', '37': 'NC', '38': 'ND',
    '39': 'OH', '40': 'OK', '41': 'OR', '42': 'PA', '44': 'RI', '45': 'SC', '46': 'SD',
    '47': 'TN', '48': 'TX', '49': 'UT', '50': 'VT', '51': 'VA', '53': 'WA', '54': 'WV',
    '55': 'WI', '56': 'WY'
};

const abbrToFips: Record<string, string> = Object.fromEntries(
    Object.entries(fipsToAbbr).map(([fips, abbr]) => [abbr, fips])
);

const bandColors: Record<string, string> = {
    'strongly_supports': 'rgb(64, 193, 0)',
    'supports': 'rgb(156, 211, 0)',
    'leans_support': 'rgb(207, 221, 0)',
    'neutral': 'rgb(255, 230, 0)',
    'leans_oppose': 'rgb(239, 182, 0)',
    'opposes': 'rgb(224, 136, 0)',
    'strongly_opposes': 'rgb(198, 55, 0)',
};

const bandLabels: Record<string, string> = {
    'strongly_supports': 'Strongly Supports',
    'supports': 'Supports',
    'leans_support': 'Leans Support',
    'neutral': 'Neutral',
    'leans_oppose': 'Leans Oppose',
    'opposes': 'Opposes',
    'strongly_opposes': 'Strongly Opposes',
};

const bandBgColors: Record<string, string> = {
    'strongly_supports': 'rgba(64, 193, 0, 0.2)',
    'supports': 'rgba(156, 211, 0, 0.2)',
    'leans_support': 'rgba(207, 221, 0, 0.15)',
    'neutral': 'rgba(255, 230, 0, 0.15)',
    'leans_oppose': 'rgba(239, 182, 0, 0.15)',
    'opposes': 'rgba(224, 136, 0, 0.2)',
    'strongly_opposes': 'rgba(198, 55, 0, 0.2)',
};

export default function StateOutlookMap({ states, stateReactions, stateBands }: StateOutlookMapProps) {
    const svgRef = useRef<SVGSVGElement>(null);
    const [paths, setPaths] = useState<{ fips: string; path: string; name: string; abbr: string }[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [hoveredState, setHoveredState] = useState<StateInfo | null>(null);

    const topStates = useMemo(() => {
        const stateMap = new Map(states.map(s => [s.abbr.toUpperCase(), s]));
        
        const allStates: StateWithScore[] = Object.entries(stateReactions).map(([fips, score]) => {
            const abbr = fipsToAbbr[fips] || '';
            const stateData = stateMap.get(abbr);
            const name = stateData?.name || abbr;
            
            let band = 'neutral';
            if (score >= 75) band = 'strongly_supports';
            else if (score >= 65) band = 'supports';
            else if (score >= 55) band = 'leans_support';
            else if (score >= 45) band = 'neutral';
            else if (score >= 35) band = 'leans_oppose';
            else if (score >= 25) band = 'opposes';
            else band = 'strongly_opposes';
            
            return {
                fips,
                name,
                abbr,
                score,
                color: getBandColor(score),
                band,
            };
        });

        const sorted = [...allStates].sort((a, b) => b.score - a.score);
        
        return {
            topSupport: sorted.slice(0, 5),
            topOppose: sorted.slice(-5).reverse(),
        };
    }, [states, stateReactions]);

    useEffect(() => {
        async function loadMap() {
            try {
                const response = await fetch('https://cdn.jsdelivr.net/npm/us-atlas@3/states-10m.json');

                if (!response.ok) {
                    throw new Error('Failed to load map data');
                }

                const topology = await response.json();
                
                const statesData: any = topojson.feature(topology, topology.objects.states);
                const projection = d3.geoAlbersUsa().fitSize([960, 600], statesData);
                const pathGenerator = d3.geoPath().projection(projection);

                const statePaths = statesData.features.map((f: any) => {
                    const feature = f as { id: string | number; properties: { name: string; postal?: string } };
                    const fips = String(feature.id).padStart(2, '0');
                    const abbr = fipsToAbbr[fips] || feature.properties.postal || '';

                    return {
                        fips,
                        path: pathGenerator(f) || '',
                        name: feature.properties.name,
                        abbr
                    };
                });

                setPaths(statePaths);
                setLoading(false);
            } catch (err) {
                setError(err instanceof Error ? err.message : 'Failed to load map');
                setLoading(false);
            }
        }

        loadMap();
    }, []);

    function getBandColor(reaction: number): string {
        if (reaction >= 50) {
            const t = (reaction - 50) / 50;
            const r = Math.round(255 * (1 - t) + 0 * t);
            const g = Math.round(230 * (1 - t) + 180 * t);
            const b = Math.round(0 * (1 - t) + 0 * t);
            return `rgb(${r}, ${g}, ${b})`;
        } else {
            const t = (50 - reaction) / 50;
            const r = Math.round(255 * (1 - t) + 180 * t);
            const g = Math.round(230 * (1 - t) + 0 * t);
            const b = Math.round(0 * (1 - t) + 0 * t);
            return `rgb(${r}, ${g}, ${b})`;
        }
    }

    const getStateInfo = (fips: string, name: string, abbr: string): StateInfo => {
        const bandInfo = stateBands?.[fips];
        const reaction = stateReactions[fips] ?? 50;
        
        let band = bandInfo?.band || 'neutral';
        let isCompetitive = bandInfo?.is_competitive || false;
        
        if (!bandInfo) {
            if (reaction >= 75) band = 'strongly_supports';
            else if (reaction >= 65) band = 'supports';
            else if (reaction >= 55) band = 'leans_support';
            else if (reaction >= 45) band = 'neutral';
            else if (reaction >= 35) band = 'leans_oppose';
            else if (reaction >= 25) band = 'opposes';
            else band = 'strongly_opposes';
        }

        return {
            fips,
            name,
            abbr,
            band,
            isCompetitive,
            color: getBandColor(reaction),
            score: Math.round(reaction),
        };
    };

    if (loading) {
        return (
            <div className="flex items-center justify-center h-64">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="text-center py-8 text-red-500">
                <p>Failed to load map: {error}</p>
            </div>
        );
    }

    return (
        <div className="flex flex-col lg:flex-row gap-6">
            <div className="flex-1">
                <svg 
                    ref={svgRef} 
                    viewBox="0 0 960 600" 
                    className="w-full h-auto max-h-[400px]"
                    style={{ background: 'transparent' }}
                >
                    <g>
                        {paths.map((state) => (
                            <g key={state.fips}>
                                <path
                                    d={state.path}
                                    fill={getBandColor(stateReactions[state.fips] ?? 50)}
                                    stroke="#fff"
                                    strokeWidth="1"
                                    className="transition-all duration-150 cursor-pointer hover:opacity-80 hover:stroke-2"
                                    onMouseEnter={() => setHoveredState(getStateInfo(state.fips, state.name, state.abbr))}
                                    onMouseLeave={() => setHoveredState(null)}
                                />
                            </g>
                        ))}
                    </g>
                </svg>

                {hoveredState && (
                    <div className="mt-4 p-4 rounded-lg border border-border bg-card">
                        <div className="flex items-center gap-2 mb-2">
                            <h3 className="text-lg font-bold">{hoveredState.name} ({hoveredState.abbr})</h3>
                            {hoveredState.isCompetitive && (
                                <span className="px-2 py-0.5 text-xs font-medium bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300 rounded">
                                    Competitive
                                </span>
                            )}
                        </div>
                        <div className="flex items-center gap-3">
                            <p className="text-sm font-medium" style={{ color: hoveredState.color }}>
                                {bandLabels[hoveredState.band] || hoveredState.band}
                            </p>
                            <span className="text-lg font-bold" style={{ color: hoveredState.color }}>
                                {hoveredState.score}
                            </span>
                        </div>
                    </div>
                )}

                <div className="mt-6 grid grid-cols-4 md:grid-cols-7 gap-2 text-center">
                    <div className="p-2 rounded-lg border" style={{ backgroundColor: bandBgColors['strongly_supports'], borderColor: bandColors['strongly_supports'] }}>
                        <p className="text-lg font-bold" style={{ color: bandColors['strongly_supports'] }}>
                            {Object.keys(stateReactions).filter(fips => {
                                const score = stateReactions[fips] ?? 50;
                                return score >= 75;
                            }).length}
                        </p>
                        <p className="text-xs text-muted-foreground">Strong Sup</p>
                    </div>
                    <div className="p-2 rounded-lg border" style={{ backgroundColor: bandBgColors['supports'], borderColor: bandColors['supports'] }}>
                        <p className="text-lg font-bold" style={{ color: bandColors['supports'] }}>
                            {Object.keys(stateReactions).filter(fips => {
                                const score = stateReactions[fips] ?? 50;
                                return score >= 65 && score < 75;
                            }).length}
                        </p>
                        <p className="text-xs text-muted-foreground">Sup</p>
                    </div>
                    <div className="p-2 rounded-lg border" style={{ backgroundColor: bandBgColors['leans_support'], borderColor: bandColors['leans_support'] }}>
                        <p className="text-lg font-bold" style={{ color: bandColors['leans_support'] }}>
                            {Object.keys(stateReactions).filter(fips => {
                                const score = stateReactions[fips] ?? 50;
                                return score >= 55 && score < 65;
                            }).length}
                        </p>
                        <p className="text-xs text-muted-foreground">Leans Sup</p>
                    </div>
                    <div className="p-2 rounded-lg border" style={{ backgroundColor: bandBgColors['neutral'], borderColor: bandColors['neutral'] }}>
                        <p className="text-lg font-bold" style={{ color: bandColors['neutral'] }}>
                            {Object.keys(stateReactions).filter(fips => {
                                const score = stateReactions[fips] ?? 50;
                                return score >= 45 && score < 55;
                            }).length}
                        </p>
                        <p className="text-xs text-muted-foreground">Neutral</p>
                    </div>
                    <div className="p-2 rounded-lg border" style={{ backgroundColor: bandBgColors['leans_oppose'], borderColor: bandColors['leans_oppose'] }}>
                        <p className="text-lg font-bold" style={{ color: bandColors['leans_oppose'] }}>
                            {Object.keys(stateReactions).filter(fips => {
                                const score = stateReactions[fips] ?? 50;
                                return score >= 35 && score < 45;
                            }).length}
                        </p>
                        <p className="text-xs text-muted-foreground">Leans Opp</p>
                    </div>
                    <div className="p-2 rounded-lg border" style={{ backgroundColor: bandBgColors['opposes'], borderColor: bandColors['opposes'] }}>
                        <p className="text-lg font-bold" style={{ color: bandColors['opposes'] }}>
                            {Object.keys(stateReactions).filter(fips => {
                                const score = stateReactions[fips] ?? 50;
                                return score >= 25 && score < 35;
                            }).length}
                        </p>
                        <p className="text-xs text-muted-foreground">Opp</p>
                    </div>
                    <div className="p-2 rounded-lg border" style={{ backgroundColor: bandBgColors['strongly_opposes'], borderColor: bandColors['strongly_opposes'] }}>
                        <p className="text-lg font-bold" style={{ color: bandColors['strongly_opposes'] }}>
                            {Object.keys(stateReactions).filter(fips => {
                                const score = stateReactions[fips] ?? 50;
                                return score < 25;
                            }).length}
                        </p>
                        <p className="text-xs text-muted-foreground">Strong Opp</p>
                    </div>
                </div>
                
                <div className="mt-4 flex items-center justify-center gap-3 flex-wrap">
                    <div className="flex items-center gap-1">
                        <div className="w-5 h-3 rounded-sm" style={{ backgroundColor: bandColors['strongly_supports'] }}></div>
                        <span className="text-xs text-muted-foreground">Strongly Supports</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-5 h-3 rounded-sm" style={{ backgroundColor: bandColors['supports'] }}></div>
                        <span className="text-xs text-muted-foreground">Supports</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-5 h-3 rounded-sm" style={{ backgroundColor: bandColors['leans_support'] }}></div>
                        <span className="text-xs text-muted-foreground">Leans Support</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-5 h-3 rounded-sm" style={{ backgroundColor: bandColors['neutral'] }}></div>
                        <span className="text-xs text-muted-foreground">Neutral</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-5 h-3 rounded-sm" style={{ backgroundColor: bandColors['leans_oppose'] }}></div>
                        <span className="text-xs text-muted-foreground">Leans Oppose</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-5 h-3 rounded-sm" style={{ backgroundColor: bandColors['opposes'] }}></div>
                        <span className="text-xs text-muted-foreground">Opposes</span>
                    </div>
                    <div className="flex items-center gap-1">
                        <div className="w-5 h-3 rounded-sm" style={{ backgroundColor: bandColors['strongly_opposes'] }}></div>
                        <span className="text-xs text-muted-foreground">Strongly Opposes</span>
                    </div>
                </div>
            </div>

            <div className="w-full lg:w-64 space-y-4">
                <div className="rounded-lg border bg-card p-4">
                    <h3 className="text-sm font-semibold mb-3 text-green-600 dark:text-green-400">Top 5 Supporters</h3>
                    <div className="space-y-2">
                        {topStates.topSupport.map((state, idx) => (
                            <div key={state.fips} className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <span className="text-xs text-muted-foreground w-4">{idx + 1}.</span>
                                    <span className="font-medium text-sm">{state.name}</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <div 
                                        className="w-3 h-3 rounded-sm" 
                                        style={{ backgroundColor: state.color }}
                                    />
                                    <span className="font-bold text-sm" style={{ color: state.color }}>
                                        {state.score}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="rounded-lg border bg-card p-4">
                    <h3 className="text-sm font-semibold mb-3 text-red-600 dark:text-red-400">Top 5 Opposers</h3>
                    <div className="space-y-2">
                        {topStates.topOppose.map((state, idx) => (
                            <div key={state.fips} className="flex items-center justify-between">
                                <div className="flex items-center gap-2">
                                    <span className="text-xs text-muted-foreground w-4">{idx + 1}.</span>
                                    <span className="font-medium text-sm">{state.name}</span>
                                </div>
                                <div className="flex items-center gap-2">
                                    <div 
                                        className="w-3 h-3 rounded-sm" 
                                        style={{ backgroundColor: state.color }}
                                    />
                                    <span className="font-bold text-sm" style={{ color: state.color }}>
                                        {state.score}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </div>
    );
}
