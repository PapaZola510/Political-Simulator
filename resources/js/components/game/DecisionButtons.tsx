interface Decision {
    id: string;
    label: string;
    effects: {
        approval?: number;
        stability?: number;
        party_support?: number;
    };
}

interface EventPanelProps {
    title: string;
    description: string;
}

export default function EventPanel({ title, description }: EventPanelProps) {
    return (
        <div className="space-y-4">
            <h2 className="text-xl font-semibold">{title}</h2>
            <p className="text-muted-foreground leading-relaxed">{description}</p>
        </div>
    );
}

interface DecisionButtonsProps {
    decisions: Decision[];
    eventId: number;
    onDecision: (decisionId: string, eventId: number) => void;
    disabled: boolean;
}

export function DecisionButtons({ decisions, eventId, onDecision, disabled }: DecisionButtonsProps) {
    return (
        <div className="space-y-3">
            <h3 className="font-medium text-sm uppercase tracking-wide text-muted-foreground">
                Choose Your Response
            </h3>
            <div className="grid gap-2">
                {decisions.map((decision) => (
                    <button
                        key={decision.id}
                        onClick={() => onDecision(decision.id, eventId)}
                        disabled={disabled}
                        className="w-full rounded-lg border border-border bg-card px-4 py-3 text-left transition-colors hover:bg-accent hover:text-accent-foreground disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span className="font-medium">{decision.label}</span>
                        <div className="mt-1 flex flex-wrap gap-2 text-xs">
                            {decision.effects.approval !== undefined && (
                                <span className={decision.effects.approval >= 0 ? 'text-green-500' : 'text-red-500'}>
                                    Approval {decision.effects.approval >= 0 ? '+' : ''}{decision.effects.approval}
                                </span>
                            )}
                            {decision.effects.stability !== undefined && (
                                <span className={decision.effects.stability >= 0 ? 'text-green-500' : 'text-red-500'}>
                                    Stability {decision.effects.stability >= 0 ? '+' : ''}{decision.effects.stability}
                                </span>
                            )}
                            {decision.effects.party_support !== undefined && (
                                <span className={decision.effects.party_support >= 0 ? 'text-green-500' : 'text-red-500'}>
                                    Party {decision.effects.party_support >= 0 ? '+' : ''}{decision.effects.party_support}
                                </span>
                            )}
                        </div>
                    </button>
                ))}
            </div>
        </div>
    );
}
