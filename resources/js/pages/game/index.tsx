import { Head, usePage, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import StateOutlookMap from '@/components/game/StateOutlookMap';
import StatsPanel from '@/components/game/StatsPanel';
import USMap from '@/components/game/USMap';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

function LoadingOverlay({ message }: { message: string }) {
    return (
        <div className="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
            <div className="bg-card rounded-xl border shadow-2xl p-8 flex flex-col items-center">
                <div className="animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent mb-4" />
                <span className="text-lg font-medium">{message}</span>
            </div>
        </div>
    );
}

interface GameState {
    month: number;
    year: number;
    turn: number;
    phase: 'dashboard' | 'situation' | 'news' | 'voter_reaction' | 'state_outlook' | 'zen';
    approval: number;
    stability: number;
    party_support: number;
    last_decision: string | null;
    current_event_id: number;
    prev_approval: number | null;
    prev_stability: number | null;
    prev_party_support: number | null;
    state_reactions?: Record<string, number>;
    state_bands?: Record<string, { band: string; is_competitive: boolean }>;
    is_zen_month?: boolean;
}

interface Decision {
    id: string;
    label: string;
    effects: {
        approval?: number;
        stability?: number;
        party_support?: number;
    };
    news: {
        left: { headline: string; body: string };
        center: { headline: string; body: string };
        right: { headline: string; body: string };
    };
    voter_reactions: {
        suburban: string;
        rural: string;
        young: string;
        minority: string;
    };
}

interface Event {
    title: string;
    description: string;
    decisions: Decision[];
}

interface State {
    name: string;
    abbr: string;
    fips: string;
    color: 'blue' | 'red' | 'swing';
}

interface VoterGroup {
    id: string;
    name: string;
    color: string;
    border: string;
    text: string;
}

interface President {
    id: number;
    name: string;
    gender: string;
    party: 'democrat' | 'republican';
    age_group: string;
    background: string;
    home_region: string;
    ideology: string;
    support_strength: string;
    voter_modifiers: Record<string, number>;
    starting_stats: {
        approval: number;
        stability: number;
        party_support: number;
    };
}

interface PageProps {
    gameState: GameState;
    states: State[];
    voterGroups: VoterGroup[];
    phase: string;
    president?: President;
    currentEvent?: Event;
    currentDecision?: Decision;
    [key: string]: any;
}

const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];

const formatIdeology = (ideology: string, party: string): string => {
    const partyLabel = party === 'republican' ? 'Republican' : 'Democrat';
    const ideologyLabel = ideology.charAt(0).toUpperCase() + ideology.slice(1);
    return `${ideologyLabel} ${partyLabel}`;
};

export default function GamePage() {
    const { gameState, states, voterGroups, phase, president, currentEvent, currentDecision, scenarios } = usePage<PageProps>().props;
    const state = gameState;
    const currentPhase = phase;
    const event = currentEvent;
    const decision = currentDecision;

    const [loadingResponse, setLoadingResponse] = useState(false);
    const [loadingStateOutlook, setLoadingStateOutlook] = useState(false);
    const [loadingVoterReaction, setLoadingVoterReaction] = useState(false);
    const [forcePopup, setForcePopup] = useState<{type: 'impeach' | 'overthrow' | 'amendment' | 'midterm'} | null>(null);
    const [showSaveModal, setShowSaveModal] = useState(false);
    const [showLoadModal, setShowLoadModal] = useState(false);
    const [saves, setSaves] = useState<any[]>([]);
    const [saveName, setSaveName] = useState('');

    // eslint-disable-next-line react-hooks/exhaustive-deps
    useEffect(() => {
        setLoadingResponse(false);
        setLoadingStateOutlook(false);
        setLoadingVoterReaction(false);
    }, [currentPhase]);

    const monthName = months[(state.month - 1) % 12];

    const toggleAiContent = async () => {
        const response = await fetch('/game/toggle-ai-content', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        const data = await response.json();
        if (data.skip_ai_content !== undefined) {
            window.location.reload();
        }
    };

    const setScenario = async (eventId: number | null) => {
        await fetch('/game/set-scenario', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({ event_id: eventId }),
        });
        window.location.reload();
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Presidential Office', href: '/' }]}>
            <Head title={`${monthName} ${state.year} - PolSim`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">
                            {president ? `${president.name}'s White House` : 'Presidential Office'} - {monthName} {state.year}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {president ? `${president.party?.charAt(0).toUpperCase() + president.party?.slice(1)} President` : ''} | {currentPhase?.replace('_', ' ').toUpperCase()}
                        </p>
                        {currentPhase !== 'midterm' && (
                            <p className="text-xs text-muted-foreground/70 mt-1">
                                {24 - (state.turn || 0)} months until midterm elections
                            </p>
                        )}
                    </div>
                    <div className="flex items-center gap-4">
                        <select
                            value={(state as any).forced_event_id || ''}
                            onChange={(e) => {
                                const value = e.target.value;
                                if (value === 'consequence') {
                                    fetch('/game/force-consequence', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                        },
                                    }).then(() => window.location.reload());
                                } else if (value === 'consequence') {
                                    fetch('/game/force-consequence', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                        },
                                    }).then(() => window.location.reload());
                                } else {
                                    setScenario(value ? parseInt(value) : null);
                                }
                            }}
                            className="text-xs px-2 py-1 rounded-full border bg-background"
                        >
                            <option value="">Random Scenario</option>
                            {scenarios.map((s: any) => (
                                <option key={s.id} value={s.id}>{s.title}</option>
                            ))}
                            <option value="consequence">Force Consequence (Test)</option>
                        </select>
                        <button
                            onClick={toggleAiContent}
                            className={`text-xs px-3 py-1 rounded-full border ${
                                (state as any).skip_ai_content 
                                    ? 'bg-amber-100 border-amber-400 text-amber-800' 
                                    : 'bg-green-100 border-green-400 text-green-800'
                            }`}
                        >
                            {(state as any).skip_ai_content ? 'AI: OFF' : 'AI: ON'}
                        </button>
                        <select
                            onChange={(e) => {
                                const value = e.target.value;
                                if (value === 'impeach') {
                                    setForcePopup({ type: 'impeach' });
                                } else if (value === 'overthrow') {
                                    setForcePopup({ type: 'overthrow' });
                                } else if (value === 'amendment') {
                                    setForcePopup({ type: 'amendment' });
                                } else if (value === 'midterm') {
                                    setForcePopup({ type: 'midterm' });
                                }
                                e.target.value = '';
                            }}
                            className="text-xs px-2 py-1 rounded-full border bg-purple-100 border-purple-400 text-purple-800"
                        >
                            <option value="">Debug Popups</option>
                            <option value="impeach">Force Impeach</option>
                            <option value="overthrow">Force Overthrow</option>
                            <option value="amendment">Force 25th</option>
                            <option value="midterm">Force Midterm</option>
                        </select>
                        <button
                            onClick={() => {
                                setSaveName(`Save - ${new Date().toLocaleString()}`);
                                setShowSaveModal(true);
                            }}
                            className="text-xs px-3 py-1 rounded-full border bg-blue-100 border-blue-400 text-blue-800"
                        >
                            Save
                        </button>
                        <button
                            onClick={async () => {
                                const res = await fetch('/game/saves');
                                const data = await res.json();
                                setSaves(data);
                                setShowLoadModal(true);
                            }}
                            className="text-xs px-3 py-1 rounded-full border bg-green-100 border-green-400 text-green-800"
                        >
                            Load
                        </button>
                        <button
                            onClick={() => {
                                if (confirm('Clear ALL game data? This cannot be undone!')) {
                                    fetch('/game/clear-data', {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                        },
                                    }).then(() => window.location.assign('/president'));
                                }
                            }}
                            className="text-xs px-3 py-1 rounded-full border bg-red-100 border-red-400 text-red-800"
                        >
                            Clear Data
                        </button>
                    </div>
                </div>
                
                {/* Force Popup Overlay */}
                {forcePopup && (
                    <div className="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-[60]">
                        {forcePopup.type === 'impeach' && (
                            <div className="bg-card rounded-2xl border-2 border-red-500 shadow-2xl max-w-lg w-full mx-4">
                                <div className="bg-red-600 px-6 py-4">
                                    <h2 className="text-3xl font-bold text-white text-center">IMPEACHED</h2>
                                </div>
                                <div className="p-6 text-center">
                                    <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                        <span className="text-3xl">{"\u{1F6A8}"}</span>
                                    </div>
                                    <h3 className="text-xl font-bold mb-2">Congress Has Impeached the President</h3>
                                    <p className="text-muted-foreground mb-4">Your approval rating dropped to 25%. With public confidence completely eroded, Congress has moved to impeach and remove you from office.</p>
                                    <div className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 mb-6">
                                        <span className="font-semibold">Approval Rating:</span>
                                        <span className="font-bold">25%</span>
                                    </div>
                                    <div className="border-t pt-4">
                                        <Button onClick={() => router.post('/game/reset')} className="w-full h-12 text-lg bg-red-600 hover:bg-red-700">
                                            Start New Presidency
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        )}
                        {forcePopup.type === 'overthrow' && (
                            <div className="bg-card rounded-2xl border-2 border-red-500 shadow-2xl max-w-lg w-full mx-4">
                                <div className="bg-red-600 px-6 py-4">
                                    <h2 className="text-3xl font-bold text-white text-center">OVERTHROWN</h2>
                                </div>
                                <div className="p-6 text-center">
                                    <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                        <span className="text-3xl">{"\u{1F6A8}"}</span>
                                    </div>
                                    <h3 className="text-xl font-bold mb-2">The Government Has Been Overthrown</h3>
                                    <p className="text-muted-foreground mb-4">Your government stability has collapsed to 25%. With the government in chaos and institutions failing, a coup has overthrown your administration.</p>
                                    <div className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 mb-6">
                                        <span className="font-semibold">Government Stability:</span>
                                        <span className="font-bold">25%</span>
                                    </div>
                                    <div className="border-t pt-4">
                                        <Button onClick={() => router.post('/game/reset')} className="w-full h-12 text-lg bg-red-600 hover:bg-red-700">
                                            Start New Presidency
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        )}
                        {forcePopup.type === 'amendment' && (
                            <div className="bg-card rounded-2xl border-2 border-red-500 shadow-2xl max-w-lg w-full mx-4">
                                <div className="bg-red-600 px-6 py-4">
                                    <h2 className="text-3xl font-bold text-white text-center">25TH AMENDMENT INVOKED</h2>
                                </div>
                                <div className="p-6 text-center">
                                    <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                        <span className="text-3xl">{"\u{1F6A8}"}</span>
                                    </div>
                                    <h3 className="text-xl font-bold mb-2">The Vice President Takes Office</h3>
                                    <p className="text-muted-foreground mb-4">Your party support has plummeted to 25%. With your own party abandoning you, the Cabinet has invoked the 25th Amendment. Your Vice President has assumed the presidency.</p>
                                    <div className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 mb-6">
                                        <span className="font-semibold">Party Support:</span>
                                        <span className="font-bold">25%</span>
                                    </div>
                                    <div className="border-t pt-4">
                                        <Button onClick={() => router.post('/game/reset')} className="w-full h-12 text-lg bg-red-600 hover:bg-red-700">
                                            Start New Presidency
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        )}
                        {forcePopup.type === 'midterm' && (
                            <div className="bg-card rounded-2xl border-2 border-blue-500 shadow-2xl max-w-lg w-full mx-4">
                                <div className="bg-gradient-to-r from-red-600 via-purple-600 to-blue-600 px-6 py-4">
                                    <h2 className="text-3xl font-bold text-white text-center">MIDTERM SEASON</h2>
                                </div>
                                <div className="p-6 text-center">
                                    <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-red-100 to-blue-100 dark:from-red-900/30 dark:to-blue-900/30 flex items-center justify-center">
                                        <span className="text-3xl">{"\u{1F3C6}"}</span>
                                    </div>
                                    <h3 className="text-xl font-bold mb-2">Your First Term Has Ended</h3>
                                    <p className="text-muted-foreground mb-4">Your party is now preparing for the midterm elections. Candidates are convening and planning their campaigns across the nation.</p>
                                    <div className="p-4 rounded-lg bg-muted/50 mb-6">
                                        <p className="text-sm font-medium mb-2">Your Final Standing:</p>
                                        <div className="flex justify-center gap-6 text-sm">
                                            <div><span className="text-muted-foreground">Approval:</span><span className="font-bold ml-1">{state.approval}%</span></div>
                                            <div><span className="text-muted-foreground">Stability:</span><span className="font-bold ml-1">{state.stability}%</span></div>
                                            <div><span className="text-muted-foreground">Party:</span><span className="font-bold ml-1">{state.party_support}%</span></div>
                                        </div>
                                    </div>
                                    <div className="border-t pt-4">
                                        <Button onClick={() => router.post('/game/reset')} className="w-full h-12 text-lg bg-gradient-to-r from-red-600 to-blue-600 hover:opacity-90">
                                            Start New Presidency
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        )}
                    </div>
                )}

                {/* DASHBOARD PHASE */}
                {currentPhase === 'dashboard' && (
                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="lg:col-span-1 space-y-6">
                            {president && (
                                <div className="rounded-xl border border-sidebar-border/70 bg-card p-4">
                                    <h2 className="text-lg font-semibold mb-3">President Profile</h2>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Name:</span>
                                            <span className="font-medium">{president.name}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Gender:</span>
                                            <span className="font-medium capitalize">{president.gender}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Party:</span>
                                            <span className="font-medium capitalize">{president.party}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Ideology:</span>
                                            <span className="font-medium">{formatIdeology(president.ideology, president.party)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Age:</span>
                                            <span className="font-medium">{president.age_group}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Background:</span>
                                            <span className="font-medium capitalize">{president.background}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Home Region:</span>
                                            <span className="font-medium capitalize">{(president.home_region || '').replace('_', ' ')}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Support:</span>
                                            <span className="font-medium capitalize">{(president.support_strength || '').replace('_', ' ')}</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                            <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                                <h2 className="text-lg font-semibold mb-4">Your Standing</h2>
                                <div className="space-y-4">
                                    <StatsPanel 
                                        label="Approval Rating" 
                                        value={state.approval} 
                                        color="green" 
                                        delta={state.prev_approval !== null ? state.approval - state.prev_approval : undefined}
                                    />
                                    <StatsPanel 
                                        label="Government Stability" 
                                        value={state.stability} 
                                        color="blue" 
                                        delta={state.prev_stability !== null ? state.stability - state.prev_stability : undefined}
                                    />
                                    <StatsPanel 
                                        label="Party Support" 
                                        value={state.party_support} 
                                        color="purple" 
                                        delta={state.prev_party_support !== null ? state.party_support - state.prev_party_support : undefined}
                                    />
                                </div>
                            </div>

                            <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                                <h2 className="text-lg font-semibold mb-4">Voter Opinions</h2>
                                <div className="space-y-3 text-sm max-h-[400px] overflow-y-auto pr-2">
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-pink-600">Student Activists</p>
                                        <p className="text-muted-foreground mt-1">Anxious for bold progressive action on issues they care about.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-purple-600">Young Urban Professionals</p>
                                        <p className="text-muted-foreground mt-1">Focused on economic stability and career opportunities.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-indigo-600">Young Conservatives</p>
                                        <p className="text-muted-foreground mt-1">Skeptical of government overreach, want free market solutions.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-orange-600">Working-Class Urban Labor</p>
                                        <p className="text-muted-foreground mt-1">Concerned about jobs, wages, and cost of living.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-teal-600">Suburban Families</p>
                                        <p className="text-muted-foreground mt-1">Prioritizing schools, safety, and household budgets.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-green-700">Rural Farmers</p>
                                        <p className="text-muted-foreground mt-1">Worried about trade, regulations, and weather impacts.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-amber-700">Small Business Owners</p>
                                        <p className="text-muted-foreground mt-1">Watching taxes, regulations, and consumer spending closely.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-gray-700 dark:text-gray-300">Corporate Executives</p>
                                        <p className="text-muted-foreground mt-1">Analyzing policy impacts on markets and profit margins.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-cyan-600">Public Sector Workers</p>
                                        <p className="text-muted-foreground mt-1">Concerned about government funding and job security.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-red-600">Retirees & Seniors</p>
                                        <p className="text-muted-foreground mt-1">Focused on healthcare, Social Security, and stability.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-violet-600">Minority Communities</p>
                                        <p className="text-muted-foreground mt-1">Interested in equity, opportunity, and representation.</p>
                                    </div>
                                    <div className="p-3 rounded-lg bg-muted/30">
                                        <p className="font-bold text-slate-600 dark:text-slate-400">Independent Voters</p>
                                        <p className="text-muted-foreground mt-1">Waiting to see results before forming opinions.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="lg:col-span-2 space-y-6">
                            <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                                <h2 className="text-lg font-semibold mb-4">Electoral Map</h2>
                                <USMap />
                            </div>

                            <Button 
                                onClick={() => router.post('/game/advance')}
                                className="w-full h-12 text-lg"
                            >
                                Advance to Next Month
                            </Button>
                        </div>
                    </div>
                )}

                {/* SITUATION ROOM PHASE */}
                {currentPhase === 'situation' && (event || (state as any).consequence) && (
                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="lg:col-span-1 space-y-6">
                            {president && (
                                <div className="rounded-xl border border-sidebar-border/70 bg-card p-4">
                                    <h2 className="text-lg font-semibold mb-3">President Profile</h2>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Name:</span>
                                            <span className="font-medium">{president.name}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Gender:</span>
                                            <span className="font-medium capitalize">{president.gender}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Party:</span>
                                            <span className="font-medium capitalize">{president.party}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Ideology:</span>
                                            <span className="font-medium">{formatIdeology(president.ideology, president.party)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Age:</span>
                                            <span className="font-medium">{president.age_group}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Background:</span>
                                            <span className="font-medium capitalize">{president.background}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Home Region:</span>
                                            <span className="font-medium capitalize">{(president.home_region || '').replace('_', ' ')}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Support:</span>
                                            <span className="font-medium capitalize">{(president.support_strength || '').replace('_', ' ')}</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                            <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                                <h2 className="text-lg font-semibold mb-4">Your Standing</h2>
                                <div className="space-y-4">
                                    <StatsPanel 
                                        label="Approval Rating" 
                                        value={state.approval} 
                                        color="green" 
                                        delta={state.prev_approval !== null ? state.approval - state.prev_approval : undefined}
                                    />
                                    <StatsPanel 
                                        label="Government Stability" 
                                        value={state.stability} 
                                        color="blue" 
                                        delta={state.prev_stability !== null ? state.stability - state.prev_stability : undefined}
                                    />
                                    <StatsPanel 
                                        label="Party Support" 
                                        value={state.party_support} 
                                        color="purple" 
                                        delta={state.prev_party_support !== null ? state.party_support - state.prev_party_support : undefined}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="lg:col-span-2 space-y-6">
                            <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                                {(state as any).consequence ? (
                                    <>
                                        <div className="bg-amber-100 dark:bg-amber-900/30 border border-amber-300 dark:border-amber-700 rounded-lg p-3 mb-4">
                                            <p className="text-xs font-semibold text-amber-800 dark:text-amber-200 uppercase tracking-wide">
                                                {"\u{26A0}"} Consequence of Your Actions
                                            </p>
                                        </div>
                                        <h2 className="text-lg font-semibold mb-4">Consequence</h2>
                                        <div className="space-y-4">
                                            <h3 className="text-xl font-bold text-amber-700 dark:text-amber-400">
                                                {(state as any).consequence.title}
                                            </h3>
                                            <p className="text-muted-foreground">
                                                {(state as any).consequence.description}
                                            </p>
                                        </div>
                                    </>
                                ) : (
                                    <>
                                        <h2 className="text-lg font-semibold mb-4">Situation Room</h2>
                                        <div className="space-y-4">
                                            <h3 className="text-xl font-bold">{event?.title}</h3>
                                            <p className="text-muted-foreground">{event?.description}</p>
                                        </div>
                                    </>
                                )}
                                <div className="mt-6 pt-6 border-t">
                                    <h4 className="font-medium text-sm uppercase tracking-wide text-muted-foreground mb-3">
                                        Your Response
                                    </h4>
                                    <div className="">
                                        {loadingResponse && <LoadingOverlay message="Getting media and cameras ready..." />}
                                        <form onSubmit={(e) => {
                                            e.preventDefault();
                                            const formData = new FormData(e.currentTarget);
                                            const response = formData.get('player_response') as string;

                                            if (response.trim()) {
                                                setLoadingResponse(true);
                                                router.post('/game/custom-decision', { 
                                                    response: response,
                                                    event_id: state.current_event_id 
                                                });
                                            }
                                        }}>
                                            <textarea
                                                name="player_response"
                                                placeholder={((state as any).consequence ? "How will you address this consequence?" : "Type your response to this situation...")}
                                                className="w-full h-32 p-3 rounded-lg border border-border bg-background text-sm resize-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                                disabled={loadingResponse}
                                            />
                                            <Button 
                                                type="submit" 
                                                className="w-full h-12 text-lg mt-4"
                                                disabled={loadingResponse}
                                            >
                                                {loadingResponse ? (
                                                    <span className="flex items-center gap-2">
                                                        <span className="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent" />
                                                        Submitting...
                                                    </span>
                                                ) : 'Submit Response'}
                                            </Button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* ZEN MONTH PHASE */}
                {currentPhase === 'zen' && (
                    <div className="grid gap-6 lg:grid-cols-3">
                        <div className="lg:col-span-1 space-y-6">
                            {president && (
                                <div className="rounded-xl border border-sidebar-border/70 bg-card p-4">
                                    <h2 className="text-lg font-semibold mb-3">President Profile</h2>
                                    <div className="space-y-1 text-sm">
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Name:</span>
                                            <span className="font-medium">{president.name}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Gender:</span>
                                            <span className="font-medium capitalize">{president.gender}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Party:</span>
                                            <span className="font-medium capitalize">{president.party}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Ideology:</span>
                                            <span className="font-medium">{formatIdeology(president.ideology, president.party)}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Age:</span>
                                            <span className="font-medium">{president.age_group}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Background:</span>
                                            <span className="font-medium capitalize">{president.background}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Home Region:</span>
                                            <span className="font-medium capitalize">{(president.home_region || '').replace('_', ' ')}</span>
                                        </div>
                                        <div className="flex justify-between">
                                            <span className="text-muted-foreground">Support:</span>
                                            <span className="font-medium capitalize">{(president.support_strength || '').replace('_', ' ')}</span>
                                        </div>
                                    </div>
                                </div>
                            )}
                            <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                                <h2 className="text-lg font-semibold mb-4">Your Standing</h2>
                                <div className="space-y-4">
                                    <StatsPanel 
                                        label="Approval Rating" 
                                        value={state.approval} 
                                        color="green" 
                                        delta={state.prev_approval !== null ? state.approval - state.prev_approval : undefined}
                                    />
                                    <StatsPanel 
                                        label="Government Stability" 
                                        value={state.stability} 
                                        color="blue" 
                                        delta={state.prev_stability !== null ? state.stability - state.prev_stability : undefined}
                                    />
                                    <StatsPanel 
                                        label="Party Support" 
                                        value={state.party_support} 
                                        color="purple" 
                                        delta={state.prev_party_support !== null ? state.party_support - state.prev_party_support : undefined}
                                    />
                                </div>
                            </div>
                        </div>

                        <div className="lg:col-span-2 space-y-6">
                            <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                                <div className="flex items-center gap-3 mb-4">
                                    <span className="text-3xl">🌿</span>
                                    <h2 className="text-xl font-bold">Free Month - Your Choice</h2>
                                </div>
                                <p className="text-muted-foreground mb-4">
                                    It's a calm month in Washington. No crises are demanding your immediate attention. 
                                    This is your chance to take initiative on issues you care about.
                                </p>
                                <div className="bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800 rounded-lg p-4 mb-6">
                                    <p className="text-sm text-green-800 dark:text-green-200">
                                        <strong>Pro tip:</strong> Use this time to shape your legacy.
                                    </p>
                                </div>
                                <div className="mt-6 pt-6 border-t">
                                    <h4 className="font-medium text-sm uppercase tracking-wide text-muted-foreground mb-3">
                                        What's Your Initiative?
                                    </h4>
                                    <div className="">
                                        {loadingResponse && <LoadingOverlay message="Getting media and cameras ready..." />}
                                        <form onSubmit={(e) => {
                                            e.preventDefault();
                                            const formData = new FormData(e.currentTarget);
                                            const response = formData.get('player_response') as string;

                                            if (response.trim()) {
                                                setLoadingResponse(true);
                                                router.post('/game/custom-decision', { 
                                                    response: response,
                                                    event_id: 0
                                                });
                                            }
                                        }}>
                                            <textarea
                                                name="player_response"
                                                placeholder="What would you like to do this month?"
                                                className="w-full h-32 p-3 rounded-lg border border-border bg-background text-sm resize-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                                disabled={loadingResponse}
                                            />
                                            <Button 
                                                type="submit" 
                                                className="w-full h-12 text-lg mt-4"
                                                disabled={loadingResponse}
                                            >
                                                {loadingResponse ? (
                                                    <span className="flex items-center gap-2">
                                                        <span className="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent" />
                                                        Submitting...
                                                    </span>
                                                ) : 'Announce Initiative'}
                                            </Button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* NEWS ROOM PHASE */}
                {currentPhase === 'news' && decision && (
                    <div className="space-y-6">
                        <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                            <h2 className="text-xl font-bold mb-4">News Room - Media Coverage</h2>
                            <p className="text-muted-foreground mb-6">
                                Your decision: <span className="font-semibold text-foreground">"{(state as any).player_raw_response || state.last_decision}"</span>
                            </p>
                            <div className="grid md:grid-cols-3 gap-4">
                                <div className="p-4 rounded-lg border border-l-4 border-l-blue-500 bg-card">
                                    <p className="text-xs uppercase tracking-wide text-blue-500 font-semibold mb-2">The People's Herald</p>
                                    <h4 className="font-bold mb-2">{decision.news.left.headline}</h4>
                                    <p className="text-sm text-muted-foreground">{decision.news.left.body}</p>
                                </div>
                                <div className="p-4 rounded-lg border border-l-4 border-l-gray-500 bg-card">
                                    <p className="text-xs uppercase tracking-wide text-gray-500 font-semibold mb-2">The Civic Report</p>
                                    <h4 className="font-bold mb-2">{decision.news.center.headline}</h4>
                                    <p className="text-sm text-muted-foreground">{decision.news.center.body}</p>
                                </div>
                                <div className="p-4 rounded-lg border border-l-4 border-l-red-500 bg-card">
                                    <p className="text-xs uppercase tracking-wide text-red-500 font-semibold mb-2">The Patriot Post</p>
                                    <h4 className="font-bold mb-2">{decision.news.right.headline}</h4>
                                    <p className="text-sm text-muted-foreground">{decision.news.right.body}</p>
                                </div>
                            </div>
                        </div>

                        <div className="">
                            {loadingStateOutlook && <LoadingOverlay message="Watching local politicians react..." />}
                            <Button 
                                onClick={() => {
                                    setLoadingStateOutlook(true);
                                    router.post('/game/state-outlook');
                                }}
                                className="w-full h-12 text-lg"
                                disabled={loadingStateOutlook}
                            >
                                {loadingStateOutlook ? (
                                    <span className="flex items-center gap-2">
                                        <span className="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent" />
                                        Processing...
                                    </span>
                                ) : 'View State Outlook'}
                            </Button>
                        </div>
                    </div>
                )}

                {/* STATE OUTLOOK PHASE */}
                {currentPhase === 'state_outlook' && decision && (
                    <div className="space-y-6">
                        <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                            <h2 className="text-xl font-bold mb-4">State Outlook</h2>
                            <p className="text-muted-foreground mb-4">
                                Your decision: <span className="font-semibold text-foreground">"{(state as any).player_raw_response || state.last_decision}"</span>
                            </p>
                            <p className="text-sm text-muted-foreground mb-4">
                                How each state reacted to your decision.
                            </p>
                            <StateOutlookMap states={states} stateReactions={state.state_reactions || {}} stateBands={state.state_bands} />
                        </div>

                        <div className="">
                            {loadingVoterReaction && <LoadingOverlay message="Checking Twitter for public response..." />}
                            <Button 
                                onClick={() => {
                                    setLoadingVoterReaction(true);
                                    router.post('/game/voter-reactions');
                                }}
                                className="w-full h-12 text-lg"
                                disabled={loadingVoterReaction}
                            >
                                {loadingVoterReaction ? (
                                    <span className="flex items-center gap-2">
                                        <span className="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent" />
                                        Processing...
                                    </span>
                                ) : 'View Voter Reactions'}
                            </Button>
                        </div>
                    </div>
                )}

                {/* VOTER REACTION PHASE */}
                {currentPhase === 'voter_reaction' && decision && (
                    <div className="space-y-6">
                        <div className="rounded-xl border border-sidebar-border/70 bg-card p-6">
                            <h2 className="text-xl font-bold mb-4">Voter Reaction Room</h2>
                            <p className="text-muted-foreground mb-6">
                                Your decision: <span className="font-semibold text-foreground">"{(state as any).player_raw_response || state.last_decision}"</span>
                            </p>
                            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {voterGroups && voterGroups.map((group) => {
                                    const reaction = (decision.voter_reactions as any)?.[group.id];
                                    const supportRating = typeof reaction === 'object' && reaction !== null ? reaction.support : 50;
                                    const reactionText = typeof reaction === 'object' && reaction !== null ? reaction.reaction : 'Loading...';
                                    const ratingColor = supportRating < 40 ? 'text-red-500' : supportRating < 60 ? 'text-orange-500' : 'text-green-500';
                                    
                                    const emojis: Record<string, string> = {
                                        'students': '\u{1F393}',
                                        'yuppie': '\u{1F4BC}',
                                        'young_conservatives': '\u{1F6E1}',
                                        'working_class': '\u{1F3E2}',
                                        'suburban': '\u{1F3E0}',
                                        'rural': '\u{1F33E}',
                                        'small_business': '\u{1F3EA}',
                                        'corporate': '\u{1F4C8}',
                                        'public_sector': '\u{1F3DB}',
                                        'retirees': '\u{1F496}',
                                        'minorities': '\u{1F46A}',
                                        'independents': '\u{1F9D1}',
                                    };
                                    
                                    return (
                                        <div 
                                            key={group.id} 
                                            className="p-4 rounded-lg bg-muted/30 min-h-[140px]"
                                        >
                                            <div className="flex items-center gap-2 mb-2">
                                                <span className="text-xl">{emojis[group.id] || '\u{1F464}'}</span>
                                                <p className="text-base font-bold">{group.name}</p>
                                            </div>
                                            <div className="mb-2">
                                                <span className={`text-lg font-bold ${ratingColor}`}>{supportRating}/100</span>
                                            </div>
                                            <p className="text-sm text-muted-foreground leading-relaxed whitespace-pre-line">
                                                {reactionText}
                                            </p>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        <Button 
                            onClick={() => router.post('/game/dashboard')}
                            className="w-full h-12 text-lg"
                        >
                            Return to Dashboard
                        </Button>
                    </div>
                )}

                {/* GAME OVER PHASE */}
                {currentPhase === 'game_over' && (state as any).game_over && (
                    <div className="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
                        <div className="bg-card rounded-2xl border-2 border-red-500 shadow-2xl max-w-lg w-full mx-4 overflow-hidden">
                            <div className="bg-red-600 px-6 py-4">
                                <h2 className="text-3xl font-bold text-white text-center">
                                    {(state as any).game_over.title}
                                </h2>
                            </div>
                            <div className="p-6 text-center">
                                <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                                    <span className="text-3xl">{"\u{1F6A8}"}</span>
                                </div>
                                <h3 className="text-xl font-bold mb-2">
                                    {(state as any).game_over.headline}
                                </h3>
                                <p className="text-muted-foreground mb-4">
                                    {(state as any).game_over.message}
                                </p>
                                <div className="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 mb-6">
                                    <span className="font-semibold">{(state as any).game_over.stat}:</span>
                                    <span className="font-bold">{(state as any).game_over.stat_value}%</span>
                                </div>
                                <div className="border-t pt-4">
                                    <p className="text-sm text-muted-foreground mb-4">
                                        Your presidency has come to an end. Shamefully.
                                    </p>
                                    <Button 
                                        onClick={() => router.post('/game/reset')}
                                        className="w-full h-12 text-lg bg-red-600 hover:bg-red-700"
                                    >
                                        Start New Presidency
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* SAVE MODAL */}
                {showSaveModal && (
                    <div className="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-[60]">
                        <div className="bg-card rounded-2xl border shadow-2xl max-w-md w-full mx-4 p-6">
                            <h2 className="text-xl font-bold mb-4">Save Game</h2>
                            <div className="mb-4">
                                <label className="block text-sm font-medium mb-2">Save Name</label>
                                <input
                                    type="text"
                                    value={saveName}
                                    onChange={(e) => setSaveName(e.target.value)}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                    placeholder="Enter save name"
                                />
                            </div>
                            <div className="flex gap-3">
                                <Button
                                    onClick={async () => {
                                        if (!saveName.trim()) {
                                            alert('Please enter a save name');
                                            return;
                                        }
                                        await fetch('/game/save', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                            },
                                            body: JSON.stringify({ save_name: saveName }),
                                        });
                                        setShowSaveModal(false);
                                        alert('Game saved!');
                                    }}
                                    className="flex-1"
                                >
                                    Save
                                </Button>
                                <Button
                                    variant="outline"
                                    onClick={() => setShowSaveModal(false)}
                                    className="flex-1"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </div>
                )}

                {/* LOAD MODAL */}
                {showLoadModal && (
                    <div className="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-[60]">
                        <div className="bg-card rounded-2xl border shadow-2xl max-w-md w-full mx-4 p-6">
                            <h2 className="text-xl font-bold mb-4">Load Game</h2>
                            {saves.length === 0 ? (
                                <p className="text-muted-foreground text-center py-8">No saves found.</p>
                            ) : (
                                <div className="space-y-2 max-h-80 overflow-y-auto">
                                    {saves.map((save) => (
                                        <div
                                            key={save.id}
                                            className="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 cursor-pointer"
                                            onClick={async () => {
                                                await fetch('/game/load', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                                    },
                                                    body: JSON.stringify({ save_id: save.id }),
                                                });
                                                window.location.reload();
                                            }}
                                        >
                                            <div>
                                                <p className="font-medium">{save.save_name}</p>
                                                <p className="text-xs text-muted-foreground">
                                                    {save.president_name} - {new Date(save.created_at).toLocaleString()}
                                                </p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                            <div className="mt-4">
                                <Button
                                    variant="outline"
                                    onClick={() => setShowLoadModal(false)}
                                    className="w-full"
                                >
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    </div>
                )}

                {/* MIDTERM PHASE */}
                {currentPhase === 'midterm' && (
                    <div className="fixed inset-0 flex items-center justify-center bg-black/50 backdrop-blur-sm z-50">
                        <div className="bg-card rounded-2xl border-2 border-blue-500 shadow-2xl max-w-lg w-full mx-4 overflow-hidden">
                            <div className="bg-gradient-to-r from-red-600 via-purple-600 to-blue-600 px-6 py-4">
                                <h2 className="text-3xl font-bold text-white text-center">
                                    MIDTERM SEASON
                                </h2>
                            </div>
                            <div className="p-6 text-center">
                                <div className="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-r from-red-100 to-blue-100 dark:from-red-900/30 dark:to-blue-900/30 flex items-center justify-center">
                                    <span className="text-3xl">{"\u{1F3C6}"}</span>
                                </div>
                                <h3 className="text-xl font-bold mb-2">
                                    Your First Term Has Ended
                                </h3>
                                <p className="text-muted-foreground mb-4">
                                    Your party is now preparing for the midterm elections. Candidates are convening and planning their campaigns across the nation.
                                </p>
                                <div className="p-4 rounded-lg bg-muted/50 mb-6">
                                    <p className="text-sm font-medium mb-2">Your Final Standing:</p>
                                    <div className="flex justify-center gap-6 text-sm">
                                        <div>
                                            <span className="text-muted-foreground">Approval:</span>
                                            <span className="font-bold ml-1">{state.approval}%</span>
                                        </div>
                                        <div>
                                            <span className="text-muted-foreground">Stability:</span>
                                            <span className="font-bold ml-1">{state.stability}%</span>
                                        </div>
                                        <div>
                                            <span className="text-muted-foreground">Party:</span>
                                            <span className="font-bold ml-1">{state.party_support}%</span>
                                        </div>
                                    </div>
                                </div>
                                <p className="text-sm text-muted-foreground mb-4">
                                    That includes you. Your fate now lies in the hands of the voters.
                                </p>
                                <div className="border-t pt-4">
                                    <p className="text-sm italic text-muted-foreground mb-4">
                                        "Come back for more soon..."
                                    </p>
                                    <Button 
                                        onClick={() => router.post('/game/reset')}
                                        className="w-full h-12 text-lg bg-gradient-to-r from-red-600 to-blue-600 hover:opacity-90"
                                    >
                                        Start New Presidency
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
