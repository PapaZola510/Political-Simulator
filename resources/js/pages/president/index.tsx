import { Head, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';

export default function PresidentIndex() {
    const [type, setType] = useState<'preset' | 'custom' | 'load'>('preset');
    const [preset, setPreset] = useState<string>('');
    const [saves, setSaves] = useState<any[]>([]);
    const [loading, setLoading] = useState(false);
    const [custom, setCustom] = useState({
        name: '',
        gender: 'male',
        party: 'democrat',
        age_group: '50s',
        background: 'governor',
        home_region: 'midwest',
        ideology: 'traditional',
        support_strength: 'comfortable',
    });

    const fetchSaves = async () => {
        try {
            const res = await fetch('/game/saves');
            const data = await res.json();
            setSaves(data);
        } catch {
            console.error('Failed to fetch saves');
        }
    };

    useEffect(() => {
        const load = async () => {
            if (type === 'load') {
                await fetchSaves();
            }
        };
        load();
    }, [type]);

    const handleSubmit = () => {
        if (type === 'preset' && !preset) {
            alert('Please select a preset president');
            return;
        }

        if (type === 'custom' && !custom.name.trim()) {
            alert('Please enter a name');
            return;
        }

        setLoading(true);
        router.post('/president/select', {
            type,
            ...(type === 'preset' ? { preset } : custom),
            auto_save: true,
        });
    };

    const handleLoadSave = async (saveId: number) => {
        setLoading(true);
        try {
            const response = await fetch('/game/load', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({ save_id: saveId }),
            });
            
            if (response.ok || response.redirected) {
                window.location.assign('/');
            } else {
                alert('Failed to load save');
                setLoading(false);
            }
        } catch {
            alert('Failed to load save');
            setLoading(false);
        }
    };

    const handleDeleteSave = async (saveId: number, e: React.MouseEvent) => {
        e.stopPropagation();
        if (!confirm('Delete this save? This cannot be undone.')) {
            return;
        }
        
        try {
            await fetch(`/game/saves/${saveId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            fetchSaves();
        } catch {
            alert('Failed to delete save');
        }
    };

    const formatDate = (dateStr: string) => {
        const date = new Date(dateStr);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    };

    return (
        <AppLayout>
            <Head title="Select President" />
            <div className="container max-w-4xl mx-auto py-8">
                <h1 className="text-3xl font-bold text-center mb-8">Choose Your President</h1>

                <div className="flex gap-4 justify-center mb-8">
                    <button
                        onClick={() => setType('preset')}
                        className={`px-6 py-3 rounded-lg font-medium transition-colors ${
                            type === 'preset'
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted hover:bg-muted/80'
                        }`}
                    >
                        Use Preset
                    </button>
                    <button
                        onClick={() => setType('custom')}
                        className={`px-6 py-3 rounded-lg font-medium transition-colors ${
                            type === 'custom'
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted hover:bg-muted/80'
                        }`}
                    >
                        Create Custom
                    </button>
                    <button
                        onClick={() => setType('load')}
                        className={`px-6 py-3 rounded-lg font-medium transition-colors ${
                            type === 'load'
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted hover:bg-muted/80'
                        }`}
                    >
                        Load Game
                    </button>
                </div>

                {type === 'load' ? (
                    <div className="space-y-4">
                        <h2 className="text-xl font-semibold mb-4">Saved Games</h2>
                                {saves.length === 0 ? (
                                    <p className="text-muted-foreground text-center py-8">No saves found.</p>
                                ) : (
                                    <div className="space-y-3">
                                        {saves.map((save) => (
                                            <div
                                                key={save.id}
                                                className="flex items-center justify-between p-4 rounded-xl border border-border hover:border-primary/50 cursor-pointer transition-colors"
                                                onClick={() => handleLoadSave(save.id)}
                                            >
                                                <div>
                                                    <h3 className="font-semibold text-lg">{save.save_name}</h3>
                                                    <p className="text-sm text-muted-foreground">
                                                        {save.president_name} - {formatDate(save.created_at)}
                                                    </p>
                                                </div>
                                                <button
                                                    onClick={(e) => handleDeleteSave(save.id, e)}
                                                    className="px-3 py-1 text-sm rounded-lg bg-red-100 text-red-700 hover:bg-red-200 transition-colors"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        ))}
                                    </div>
                                )}
                        <div className="mt-6 p-4 rounded-lg bg-muted/50 text-sm text-muted-foreground">
                            <p>Click on a save to load it. Click "Delete" to remove a save.</p>
                        </div>
                    </div>
                ) : type === 'preset' ? (
                    <div className="space-y-4">
                        <div className="grid md:grid-cols-2 gap-6">
                            <button
                                onClick={() => setPreset('biden')}
                                className={`p-6 rounded-xl border-2 text-left transition-all ${
                                    preset === 'biden'
                                        ? 'border-blue-500 bg-blue-50 dark:bg-blue-950/30'
                                        : 'border-border hover:border-blue-300'
                                }`}
                            >
                                <div className="flex items-center gap-4 mb-4">
                                    <div className="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
                                        JB
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-bold">Joe Biden</h3>
                                        <p className="text-sm text-muted-foreground">Democratic Party</p>
                                    </div>
                                </div>
                                <div className="text-sm space-y-1">
                                    <p><span className="text-muted-foreground">Background:</span> Senator</p>
                                    <p><span className="text-muted-foreground">Ideology:</span> Traditional Democrat</p>
                                    <p><span className="text-muted-foreground">Support:</span> Comfortable (60%)</p>
                                </div>
                            </button>

                            <button
                                onClick={() => setPreset('trump')}
                                className={`p-6 rounded-xl border-2 text-left transition-all ${
                                    preset === 'trump'
                                        ? 'border-red-500 bg-red-50 dark:bg-red-950/30'
                                        : 'border-border hover:border-red-300'
                                }`}
                            >
                                <div className="flex items-center gap-4 mb-4">
                                    <div className="w-16 h-16 rounded-full bg-red-500 flex items-center justify-center text-white text-2xl font-bold">
                                        DT
                                    </div>
                                    <div>
                                        <h3 className="text-xl font-bold">Donald Trump</h3>
                                        <p className="text-sm text-muted-foreground">Republican Party</p>
                                    </div>
                                </div>
                                <div className="text-sm space-y-1">
                                    <p><span className="text-muted-foreground">Background:</span> Business</p>
                                    <p><span className="text-muted-foreground">Ideology:</span> Hardcore Republican</p>
                                    <p><span className="text-muted-foreground">Support:</span> Landslide (70%)</p>
                                </div>
                            </button>
                        </div>
                    </div>
                ) : (
                    <div className="space-y-6 bg-card rounded-xl border border-sidebar-border/70 p-6">
                        <div className="grid md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-medium mb-2">President Name</label>
                                <input
                                    type="text"
                                    value={custom.name}
                                    onChange={(e) => setCustom({ ...custom, name: e.target.value })}
                                    placeholder="Enter name"
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                />
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">Gender</label>
                                <select
                                    value={custom.gender}
                                    onChange={(e) => setCustom({ ...custom, gender: e.target.value })}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                >
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">Party</label>
                                <select
                                    value={custom.party}
                                    onChange={(e) => setCustom({ ...custom, party: e.target.value })}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                >
                                    <option value="democrat">Democrat</option>
                                    <option value="republican">Republican</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">Age Group</label>
                                <select
                                    value={custom.age_group}
                                    onChange={(e) => setCustom({ ...custom, age_group: e.target.value })}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                >
                                    <option value="40s">40s</option>
                                    <option value="50s">50s</option>
                                    <option value="60-70">60-70</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">Background/Career</label>
                                <select
                                    value={custom.background}
                                    onChange={(e) => setCustom({ ...custom, background: e.target.value })}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                >
                                    <option value="military">Military</option>
                                    <option value="business">Business</option>
                                    <option value="law">Law</option>
                                    <option value="governor">Governor</option>
                                    <option value="senator">Senator</option>
                                    <option value="congress">Congress</option>
                                    <option value="outsider">Outsider</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">Home Region</label>
                                <select
                                    value={custom.home_region}
                                    onChange={(e) => setCustom({ ...custom, home_region: e.target.value })}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                >
                                    <option value="southern">Southern</option>
                                    <option value="west_coast">West Coast</option>
                                    <option value="east_coast">East Coast</option>
                                    <option value="rural">Rural</option>
                                    <option value="midwest">Midwest</option>
                                    <option value="latino">Latino</option>
                                    <option value="urban">Urban</option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">
                                    Ideology (for {custom.party === 'democrat' ? 'Democrats' : 'Republicans'})
                                </label>
                                <select
                                    value={custom.ideology}
                                    onChange={(e) => setCustom({ ...custom, ideology: e.target.value })}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                >
                                    <option value="hardcore">
                                        Hardcore {custom.party === 'democrat' ? 'Progressive' : 'Conservative'}
                                    </option>
                                    <option value="traditional">
                                        Traditional {custom.party}
                                    </option>
                                    <option value="swing">
                                        Swing/Moderate
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label className="block text-sm font-medium mb-2">Strength of Support</label>
                                <select
                                    value={custom.support_strength}
                                    onChange={(e) => setCustom({ ...custom, support_strength: e.target.value })}
                                    className="w-full p-3 rounded-lg border border-border bg-background"
                                >
                                    <option value="landslide">Landslide Support (70%+ party)</option>
                                    <option value="comfortable">Comfortable (60%+ party)</option>
                                    <option value="razor_thin">Razor-Thin (50%+ party)</option>
                                    <option value="electoral_weakness">Electoral Weakness (45%+ party)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                )}

                {type !== 'load' && (
                    <div className="mt-8 flex justify-center">
                        <Button
                            onClick={handleSubmit}
                            className="px-8 py-6 text-lg"
                            disabled={loading}
                        >
                            {loading ? 'Starting...' : 'Start Game'}
                        </Button>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
