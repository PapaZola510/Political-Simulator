# Political Simulator — US Presidential  Simulator

A single-player narrative-driven political strategy game built with Laravel. Step into the Oval Office and govern the United States through 24 months of escalating crises, each demanding a decision that ripples across public opinion, government stability, and your own party.

## How It Works

You play as the President of the United States. Every turn, you face a new national crisis — from border surges and cyberattacks to Supreme Court flashpoints and pandemic outbreaks. You choose how to respond, and the game calculates the consequences across three core metrics:

- **Approval** — Public confidence in your leadership
- **Stability** — The functioning of your administration and government
- **Party Support** — Loyalty and alignment within your own party

If any stat drops to 25 or below, you lose (Impeachment / Overthrown / 25th Amendment). Survive all 24 turns and you win a full term.

## Features

- **15 hand-authored crises** covering foreign policy, domestic affairs, economic shocks, and more
- **AI-powered consequences** (Claude via Anthropic API) — decisions are evaluated contextually, not by keyword matching
- **Dynamic consequence system** — sustained low stats trigger special repercussion crises; every 4th turn generates a consequence crisis based on your decision history
- **News coverage** from three ideological outlets (left, center, right)
- **12 voter group reactions** (students, corporate executives, rural farmers, retirees, etc.)
- **50-state support map** showing how each state leans after your decisions
- **Zen Months** (10% chance) — quiet turns with no crisis, an opportunity to rebuild
- **Midterm elections** at the midpoint
- **Preset presidents** (Trump, Biden, AOC, DeSantis, Harris, Vance, Haley, Newsom) or fully custom (name, party, ideology, age, background, gender)
- **Polarization system** — polarizing presidents (Trump, AOC) see amplified state-level reactions; centrists (Haley, Newsom) get muted swings
- **Diminishing returns** on stat gains above 60/75 to prevent farming
