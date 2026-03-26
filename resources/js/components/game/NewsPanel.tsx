export default function NewsPanel() {
    const placeholderNews = {
        left: {
            headline: "Progressive Coalition Hails Presidential Action on Oil Crisis",
            body: "Environmental advocates praised the administration's decision, calling it a 'critical step toward a sustainable future.' Labor unions echoed the sentiment, urging further investment in green energy jobs.",
        },
        center: {
            headline: "Markets React to White House Policy Shift",
            body: "Financial analysts are closely watching the economic indicators as the administration navigates the complex balance between growth and regulation. Business leaders express cautious optimism.",
        },
        right: {
            headline: "Conservative Groups Criticize Administration's Approach",
            body: "Fiscal conservatives argue the spending priorities fail to address core economic concerns. Several advocacy groups have announced plans to mobilize opposition ahead of the midterms.",
        },
    };

    const placeholderReactions = {
        young: "Young voters are increasingly engaged with environmental issues, with college campuses reporting heightened political activity around climate policy.",
        suburban: "Suburban voters continue to weigh economic concerns against social issues, with many expressing frustration over rising costs of living.",
        rural: "Rural communities remain focused on agricultural policy and infrastructure, with mixed reactions to federal intervention in local economies.",
        minority: "Minority communities are watching closely for policies that address economic inequality and healthcare access, organizers say.",
    };

    return (
        <div className="space-y-6">
            <div>
                <h3 className="font-semibold mb-3">Media Coverage</h3>
                <div className="grid md:grid-cols-3 gap-4">
                    <div className="p-4 rounded-lg border border-l-4 border-l-red-500 bg-card">
                        <p className="text-xs uppercase tracking-wide text-muted-foreground mb-1">Left-Leaning</p>
                        <h4 className="font-medium text-sm mb-2">{placeholderNews.left.headline}</h4>
                        <p className="text-xs text-muted-foreground">{placeholderNews.left.body}</p>
                    </div>
                    <div className="p-4 rounded-lg border border-l-4 border-l-gray-500 bg-card">
                        <p className="text-xs uppercase tracking-wide text-muted-foreground mb-1">Center</p>
                        <h4 className="font-medium text-sm mb-2">{placeholderNews.center.headline}</h4>
                        <p className="text-xs text-muted-foreground">{placeholderNews.center.body}</p>
                    </div>
                    <div className="p-4 rounded-lg border border-l-4 border-l-blue-500 bg-card">
                        <p className="text-xs uppercase tracking-wide text-muted-foreground mb-1">Right-Leaning</p>
                        <h4 className="font-medium text-sm mb-2">{placeholderNews.right.headline}</h4>
                        <p className="text-xs text-muted-foreground">{placeholderNews.right.body}</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 className="font-semibold mb-3">Voter Reactions</h3>
                <div className="grid md:grid-cols-2 gap-3">
                    <div className="p-3 rounded-lg bg-muted/50">
                        <p className="text-xs font-medium uppercase text-muted-foreground mb-1">Young Voters</p>
                        <p className="text-sm">{placeholderReactions.young}</p>
                    </div>
                    <div className="p-3 rounded-lg bg-muted/50">
                        <p className="text-xs font-medium uppercase text-muted-foreground mb-1">Suburban Voters</p>
                        <p className="text-sm">{placeholderReactions.suburban}</p>
                    </div>
                    <div className="p-3 rounded-lg bg-muted/50">
                        <p className="text-xs font-medium uppercase text-muted-foreground mb-1">Rural Voters</p>
                        <p className="text-sm">{placeholderReactions.rural}</p>
                    </div>
                    <div className="p-3 rounded-lg bg-muted/50">
                        <p className="text-xs font-medium uppercase text-muted-foreground mb-1">Minority Communities</p>
                        <p className="text-sm">{placeholderReactions.minority}</p>
                    </div>
                </div>
            </div>
        </div>
    );
}
