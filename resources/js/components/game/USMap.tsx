import * as d3 from 'd3-geo';
import { useEffect, useRef, useState } from 'react';
import * as topojson from 'topojson-client';

interface GeoFeatureProperties {
    name: string;
    postal?: string;
}

interface GeoFeature {
    id: string | number;
    properties: GeoFeatureProperties;
    geometry: unknown;
    type: 'Feature';
}

interface FeatureCollection {
    type: 'FeatureCollection';
    features: GeoFeature[];
}

function topoFeature(topology: unknown, obj: unknown): FeatureCollection {
    return topojson.feature(topology as Parameters<typeof topojson.feature>[0], obj as Parameters<typeof topojson.feature>[1]) as FeatureCollection;
}

interface StateInfo {
    fips: string;
    name: string;
    abbr: string;
    votes: number;
    alignment: 'Democrat' | 'Republican' | 'Swing';
    dem: number;
    rep: number;
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

const stateData: Record<string, { votes: number; dem: number; rep: number }> = {
    '01': { votes: 9, dem: 35, rep: 65 },
    '02': { votes: 3, dem: 40, rep: 60 },
    '04': { votes: 11, dem: 49, rep: 51 },
    '05': { votes: 6, dem: 34, rep: 66 },
    '06': { votes: 54, dem: 62, rep: 38 },
    '08': { votes: 10, dem: 56, rep: 44 },
    '09': { votes: 7, dem: 58, rep: 42 },
    '10': { votes: 3, dem: 57, rep: 43 },
    '11': { votes: 3, dem: 90, rep: 10 },
    '12': { votes: 30, dem: 46, rep: 54 },
    '13': { votes: 16, dem: 49, rep: 51 },
    '15': { votes: 4, dem: 65, rep: 35 },
    '16': { votes: 4, dem: 32, rep: 68 },
    '17': { votes: 19, dem: 60, rep: 40 },
    '18': { votes: 11, dem: 39, rep: 61 },
    '19': { votes: 6, dem: 44, rep: 56 },
    '20': { votes: 6, dem: 38, rep: 62 },
    '21': { votes: 8, dem: 33, rep: 67 },
    '22': { votes: 8, dem: 38, rep: 62 },
    '23': { votes: 4, dem: 52, rep: 48 },
    '24': { votes: 10, dem: 65, rep: 35 },
    '25': { votes: 11, dem: 66, rep: 34 },
    '26': { votes: 15, dem: 50, rep: 50 },
    '27': { votes: 10, dem: 52, rep: 48 },
    '28': { votes: 6, dem: 37, rep: 63 },
    '29': { votes: 10, dem: 41, rep: 59 },
    '30': { votes: 4, dem: 40, rep: 60 },
    '31': { votes: 5, dem: 39, rep: 61 },
    '32': { votes: 6, dem: 50, rep: 50 },
    '33': { votes: 4, dem: 52, rep: 48 },
    '34': { votes: 14, dem: 58, rep: 42 },
    '35': { votes: 5, dem: 54, rep: 46 },
    '36': { votes: 28, dem: 61, rep: 39 },
    '37': { votes: 16, dem: 48, rep: 52 },
    '38': { votes: 3, dem: 30, rep: 70 },
    '39': { votes: 17, dem: 44, rep: 56 },
    '40': { votes: 7, dem: 32, rep: 68 },
    '41': { votes: 8, dem: 56, rep: 44 },
    '42': { votes: 19, dem: 50, rep: 50 },
    '44': { votes: 4, dem: 63, rep: 37 },
    '45': { votes: 9, dem: 43, rep: 57 },
    '46': { votes: 3, dem: 35, rep: 65 },
    '47': { votes: 11, dem: 36, rep: 64 },
    '48': { votes: 40, dem: 44, rep: 56 },
    '49': { votes: 6, dem: 35, rep: 65 },
    '50': { votes: 3, dem: 70, rep: 30 },
    '51': { votes: 13, dem: 53, rep: 47 },
    '53': { votes: 12, dem: 58, rep: 42 },
    '54': { votes: 4, dem: 25, rep: 75 },
    '55': { votes: 10, dem: 50, rep: 50 },
    '56': { votes: 3, dem: 25, rep: 75 },
};

const colorMap: Record<string, string> = {
    blue: '#3b82f6',
    red: '#ef4444',
    swing: '#eab308',
};

const alignmentColor: Record<string, string> = {
    Democrat: 'text-blue-600',
    Republican: 'text-red-600',
    Swing: 'text-yellow-600',
};

export default function USMap() {
    const svgRef = useRef<SVGSVGElement>(null);
    const [paths, setPaths] = useState<{ fips: string; path: string; name: string; abbr: string }[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [hoveredState, setHoveredState] = useState<StateInfo | null>(null);

    useEffect(() => {
        async function loadMap() {
            try {
                const response = await fetch('https://cdn.jsdelivr.net/npm/us-atlas@3/states-10m.json');

                if (!response.ok) {
throw new Error('Failed to load map data');
}

                const topology = await response.json();
                
                const statesData = topoFeature(topology, topology.objects.states);
                const projection = d3.geoAlbersUsa().fitSize([960, 600], statesData);
                const pathGenerator = d3.geoPath().projection(projection);

                const statePaths = statesData.features.map((f: GeoFeature) => {
                    const feature = f as GeoFeature;
                    const fips = String(feature.id).padStart(2, '0');
                    const abbr = fipsToAbbr[fips] || feature.properties.postal || '';

                    return {
                        fips,
                        path: pathGenerator(feature as unknown as Parameters<typeof pathGenerator>[0]) || '',
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

    const getStateColor = (fips: string): string => {
        const alignment = getStateAlignment(fips);

        if (alignment === 'Democrat') {
return colorMap.blue;
}

        if (alignment === 'Republican') {
return colorMap.red;
}

        return colorMap.swing;
    };

    const getStateAlignment = (fips: string): 'Democrat' | 'Republican' | 'Swing' => {
        const data = stateData[fips];

        if (!data) {
return 'Swing';
}

        const diff = data.dem - data.rep;

        if (diff > 5) {
return 'Democrat';
}

        if (diff < -5) {
return 'Republican';
}

        return 'Swing';
    };

    const getStateInfo = (fips: string, name: string, abbr: string): StateInfo => {
        const data = stateData[fips] || { votes: 0, dem: 0, rep: 0 };

        return {
            fips,
            name,
            abbr,
            votes: data.votes,
            alignment: getStateAlignment(fips),
            dem: data.dem,
            rep: data.rep,
        };
    };

    const blueVotes = Object.keys(stateData).filter(fips => getStateAlignment(fips) === 'Democrat').reduce((sum, fips) => sum + (stateData[fips]?.votes || 0), 0);
    
    const redVotes = Object.keys(stateData).filter(fips => getStateAlignment(fips) === 'Republican').reduce((sum, fips) => sum + (stateData[fips]?.votes || 0), 0);
    
    const swingVotes = Object.keys(stateData).filter(fips => getStateAlignment(fips) === 'Swing').reduce((sum, fips) => sum + (stateData[fips]?.votes || 0), 0);

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
        <div>
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
                                fill={getStateColor(state.fips)}
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
                    <div className="flex items-center justify-between mb-3">
                        <h3 className="text-lg font-bold">{hoveredState.name} ({hoveredState.abbr})</h3>
                        <span className={`font-semibold ${alignmentColor[hoveredState.alignment]}`}>
                            {hoveredState.alignment}
                        </span>
                    </div>
                    <div className="flex items-center gap-6 text-sm">
                        <span className="text-blue-600 font-medium">Dem {hoveredState.dem}%</span>
                        <span className="text-red-600 font-medium">Rep {hoveredState.rep}%</span>
                        <span className="ml-auto font-bold text-base">{hoveredState.votes} Electoral Votes</span>
                    </div>
                </div>
            )}

            <div className="mt-6 grid grid-cols-3 gap-4 text-center">
                <div className="p-3 rounded-lg bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800">
                    <p className="text-2xl font-bold text-blue-600">{blueVotes}</p>
                    <p className="text-sm text-blue-700 dark:text-blue-300">Democrat</p>
                </div>
                <div className="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800">
                    <p className="text-2xl font-bold text-yellow-600">{swingVotes}</p>
                    <p className="text-sm text-yellow-700 dark:text-yellow-300">Swing</p>
                </div>
                <div className="p-3 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                    <p className="text-2xl font-bold text-red-600">{redVotes}</p>
                    <p className="text-sm text-red-700 dark:text-red-300">Republican</p>
                </div>
            </div>
            <p className="text-center text-sm text-muted-foreground mt-3">
                Total: {blueVotes + swingVotes + redVotes} Electoral Votes
            </p>
        </div>
    );
}
