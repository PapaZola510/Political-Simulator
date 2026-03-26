<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use App\Services\AIService;
use App\Services\GameService;
use App\Models\Game;
use App\Models\Consequence;
use App\Models\GameSave;
use App\Models\President;

class GameController extends Controller
{
    protected array $events = [
        1 => [
            'title' => 'Oil Prices Surge',
            'description' => 'Global oil prices have increased sharply due to geopolitical tensions, causing fuel costs to rise dramatically across the nation. Trucking companies and airlines are sounding the alarm.',
            'scenario_tags' => ['fossil_energy', 'economy', 'consumer', 'labor', 'inflation'],
            'decisions' => [
                [
                    'id' => 'subsidize',
                    'label' => 'Subsidize Fuel',
                    'decision_tags' => ['consumer_relief', 'industry_subsidy', 'fossil_energy'],
                    'effects' => ['approval' => 5, 'stability' => -3, 'party_support' => 2],
                    'news' => [
                        'left' => [
                            'headline' => 'Progressive Groups Hail Fuel Subsidy as Win for Working Families',
                            'body' => 'Environmental advocates praised the administration\'s decision to subsidize fuel costs, calling it "a necessary step to protect consumers during this crisis." Labor unions echoed the sentiment, though they urged additional investment in green energy alternatives.'
                        ],
                        'center' => [
                            'headline' => 'Markets React Cautiously to Fuel Subsidy Announcement',
                            'body' => 'Financial analysts are watching the economic impact closely as the administration balances consumer relief with fiscal responsibility. Business leaders express mixed reactions, with some welcoming the stability and others concerned about long-term implications.'
                        ],
                        'right' => [
                            'headline' => 'Conservatives Slam Fuel Subsidy as "Big Government" Overreach',
                            'body' => 'Fiscal conservatives argue the subsidy represents unnecessary government intervention in the free market. Several advocacy groups have announced plans to mobilize opposition, warning that the policy could increase national debt and discourage private investment.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'Student activists appreciate the short-term relief but are frustrated the administration isn\'t accelerating the transition to renewable energy. Campus environmental groups plan to pressure for bolder climate action.',
                        'yuppie' => 'Young urban professionals benefit from lower commuting costs but worry about inflation. Many are questioning whether subsidies are the right long-term solution.',
                        'young_conservatives' => 'Conservative youth are divided. Some appreciate the market support while others argue it\'s government overreach that distorts the economy.',
                        'working_class' => 'Blue-collar workers in urban areas are relieved at the pump. Union leaders have praised the decision as protecting working families.',
                        'suburban' => 'Suburban families welcome the immediate relief but remain skeptical about long-term fiscal implications. Household budgets are breathing easier.',
                        'rural' => 'Rural communities heavily dependent on fuel for agriculture welcome the subsidy. Farmers see this as crucial for keeping food production costs manageable.',
                        'small_business' => 'Small business owners appreciate the relief but worry about the precedent. "We need sustainable solutions, not endless subsidies," said one restaurant owner.',
                        'corporate' => 'Corporate executives are cautious. While some benefit from reduced transportation costs, others are concerned about long-term fiscal policy implications.',
                        'public_sector' => 'Public sector workers appreciate the focus on helping ordinary citizens. Teachers and government employees see this as responsive governance.',
                        'retirees' => 'Senior citizens on fixed incomes benefit from lower fuel costs. Many see this as a practical response to an external crisis.',
                        'minorities' => 'Minority communities benefit from reduced transportation costs. Community advocates note that lower-income families feel the greatest relief.',
                        'independents' => 'Independent voters are giving the administration mixed reviews. Some appreciate decisive action while others question the fiscal responsibility.'
                    ]
                ],
                [
                    'id' => 'nothing',
                    'label' => 'Do Nothing',
                    'decision_tags' => ['no_action'],
                    'effects' => ['approval' => -3, 'stability' => 1, 'party_support' => 0],
                    'news' => [
                        'left' => [
                            'headline' => 'Progressives Decry Administration\'s Inaction on Fuel Crisis',
                            'body' => 'Environmental and consumer advocacy groups are criticizing the administration\'s decision to remain on the sidelines. "The American people deserve leadership, not indifference," stated a coalition of progressive organizations.'
                        ],
                        'center' => [
                            'headline' => 'Analysts Question White House Strategy on Energy Prices',
                            'body' => 'Economic experts are divided on the administration\'s hands-off approach. While some praise the restraint, others warn that inaction could erode public trust during a national crisis.'
                        ],
                        'right' => [
                            'headline' => 'Free Market Advocates Support President\'s Decision to Let Markets Self-Correct',
                            'body' => 'Conservative commentators are largely supportive of the president\'s market-driven approach. "Sometimes the best government is no government," argued a prominent talk show host. Business leaders appreciate the signal that Washington won\'t intervene.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'Student activists are furious, calling this a betrayal of climate commitments. Campus protests are being organized to demand renewable energy investment.',
                        'yuppie' => 'Career-focused young professionals are frustrated as commuting costs eat into their budgets. Many are questioning the administration\'s priorities.',
                        'young_conservatives' => 'Young conservatives largely approve of the hands-off approach, praising the president for not interfering with market forces.',
                        'working_class' => 'Blue-collar workers are struggling with high fuel costs. Union leaders are calling for congressional action to address the crisis.',
                        'suburban' => 'Suburban families are angry as their household budgets are squeezed. Many are demanding the administration take action.',
                        'rural' => 'Rural communities feel abandoned as agricultural fuel costs soar. Local officials are urging federal intervention.',
                        'small_business' => 'Small business owners are concerned about transportation costs eating into profits. "Every dollar at the pump is a dollar not going to wages," noted one trucking company owner.',
                        'corporate' => 'Corporate leaders appreciate the absence of new regulations. Market analysts note that some sectors are benefiting from price volatility.',
                        'public_sector' => 'Public sector workers are frustrated as they too feel the pinch of rising costs. Morale is being affected by perceptions of government inaction.',
                        'retirees' => 'Senior citizens on fixed incomes are disproportionately affected. Many are cutting back on other expenses to afford fuel.',
                        'minorities' => 'Minority communities in urban areas are hit hardest. Community organizers are calling for targeted relief efforts.',
                        'independents' => 'Independent voters are losing patience. Polls show declining approval as families struggle with rising costs of living.'
                    ]
                ],
                [
                    'id' => 'renewables',
                    'label' => 'Invest in Renewables',
                    'decision_tags' => ['clean_energy', 'infrastructure', 'long_term', 'climate'],
                    'effects' => ['approval' => 2, 'stability' => -1, 'party_support' => -2],
                    'news' => [
                        'left' => [
                            'headline' => 'Climate Advocates Celebrate Major Renewable Energy Investment',
                            'body' => 'Environmental organizations are praising the administration\'s bold move toward renewable energy. "This is the leadership we\'ve been waiting for," said a prominent climate activist. The announcement has energized the progressive base.'
                        ],
                        'center' => [
                            'headline' => 'Renewable Investment Draws Mixed Economic Reactions',
                            'body' => 'While environmentalists applaud the initiative, economists warn of short-term challenges. "It\'s a long-term play with short-term costs," noted one financial expert. Investors are closely monitoring the economic impact.'
                        ],
                        'right' => [
                            'headline' => 'GOP Leaders Blast Renewable Investment as "Fiscal Irresponsibility"',
                            'body' => 'Republican lawmakers are unified in their criticism, calling the investment "a gift to green energy elites at taxpayers\' expense." Conservative media has been relentless in its coverage of alleged waste and inefficiency.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'Student activists are thrilled! Campus celebrations broke out after the announcement. "This is the change we\'ve been fighting for," said one environmental group leader.',
                        'yuppie' => 'Young professionals generally support the vision but worry about immediate economic impacts. Many hope the investment creates new jobs.',
                        'young_conservatives' => 'Young conservatives are upset, viewing this as wasteful spending. Social media is filled with criticism from the right-leaning youth.',
                        'working_class' => 'Blue-collar workers are skeptical. While some see potential for clean energy jobs, others worry about losing traditional energy sector employment.',
                        'suburban' => 'Educated suburban voters appreciate the long-term thinking but are concerned about immediate costs. They want a balanced approach.',
                        'rural' => 'Rural communities feel neglected as the investment doesn\'t address their immediate fuel concerns. Agricultural groups are requesting separate assistance.',
                        'small_business' => 'Small business owners are divided. Some see opportunity in a green economy while others worry about energy transition costs.',
                        'corporate' => 'Corporate executives in renewable sectors are celebrating. Traditional energy companies are reassessing their strategies.',
                        'public_sector' => 'Public sector workers see potential for government jobs in the renewable sector. Teacher unions are cautiously optimistic about STEM education funding.',
                        'retirees' => 'Senior citizens are skeptical about the long timeline. Many question why current costs are being sacrificed for future benefits.',
                        'minorities' => 'Urban minority communities appreciate the vision but need immediate relief. Community leaders are calling for a two-pronged approach.',
                        'independents' => 'Independent voters are giving the administration the benefit of the doubt. They want to see results before passing judgment.'
                    ]
                ]
            ]
        ],
        2 => [
            'title' => 'Healthcare Crisis',
            'description' => 'A severe flu outbreak has overwhelmed hospitals in multiple states. Medical supplies are running low and healthcare workers are demanding emergency funding.',
            'scenario_tags' => ['healthcare', 'public_sector', 'labor', 'economy', 'housing'],
            'decisions' => [
                [
                    'id' => 'emergency_funding',
                    'label' => 'Emergency Funding',
                    'decision_tags' => ['healthcare', 'emergency_spending', 'public_sector', 'consumer_relief'],
                    'effects' => ['approval' => 6, 'stability' => -4, 'party_support' => 3],
                    'news' => [
                        'left' => [
                            'headline' => 'Progressive Leaders Praise Swift Action on Healthcare Emergency',
                            'body' => 'Democratic lawmakers are celebrating the president\'s decisive response to the healthcare crisis. "This is what government should do," stated a prominent progressive senator. Public health advocates are calling it a model for future responses.'
                        ],
                        'center' => [
                            'headline' => 'Healthcare Funding Bipartisan Support Despite Fiscal Concerns',
                            'body' => 'Even traditionally fiscal-hawkish representatives have expressed support for the emergency measure. "Some situations transcend politics," acknowledged one Republican senator. However, concerns about the deficit remain.'
                        ],
                        'right' => [
                            'headline' => 'Conservative Critics Question Size and Scope of Healthcare Package',
                            'body' => 'Fiscal conservatives are raising red flags about the emergency spending. "This sets a dangerous precedent for deficit spending," warned a conservative think tank. Some worry about long-term fiscal implications.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'College students are relieved that campus health services will have more resources. Many see this as proof that government can act decisively in emergencies.',
                        'yuppie' => 'Young professionals worry about healthcare costs. "This gives me hope that when I need care, it will be there," said one city worker.',
                        'young_conservatives' => 'Young conservatives are questioning the price tag but acknowledge the need. "There had to be a better way," commented one college Republican.',
                        'working_class' => 'Blue-collar workers without good insurance are deeply grateful. Union health funds will stretch further with federal support.',
                        'suburban' => 'Suburban parents are relieved that hospitals have resources. School districts are coordinating with health officials on campus outbreaks.',
                        'rural' => 'Rural hospitals, often under-resourced, welcome the influx of federal support. Rural communities feel their voices were heard.',
                        'small_business' => 'Small business owners with employee health plans are relieved. "This takes pressure off our bottom line," noted one manufacturer.',
                        'corporate' => 'Corporate executives appreciate the stabilization of the healthcare sector. Reduced panic means faster return to normal business operations.',
                        'public_sector' => 'Public sector workers, including nurses and EMTs, are celebrating the support for frontline workers. Morale is improving.',
                        'retirees' => 'Senior citizens are the most vulnerable and most grateful. Many have family members in overwhelmed hospitals.',
                        'minorities' => 'Minority communities hit hardest by the outbreak are praising the targeted response. Community health centers are receiving critical supplies.',
                        'independents' => 'Independent voters approve of the decisive action. "Finally, our government is doing something right," noted one swing voter.'
                    ]
                ],
                [
                    'id' => 'state_response',
                    'label' => 'Let States Handle It',
                    'decision_tags' => ['federalism', 'no_action', 'states_rights'],
                    'effects' => ['approval' => -2, 'stability' => 0, 'party_support' => 1],
                    'news' => [
                        'left' => [
                            'headline' => 'Progressives Assail Federal Inaction on Health Crisis',
                            'body' => 'Liberal commentators are questioning the president\'s decision to defer to states. "Healthcare should never be a postcode lottery," argued an editorial. Public health experts are warning of uneven responses across states.'
                        ],
                        'center' => [
                            'headline' => 'States Scramble as Federal Government Takes Back Seat',
                            'body' => 'Governors are expressing frustration as they face the crisis with limited federal support. "We need Washington to be a partner, not a spectator," stated one governor. The uneven response is drawing criticism.'
                        ],
                        'right' => [
                            'headline' => 'Conservatives Applaud States\' Rights Approach',
                            'body' => 'GOP leaders are praising the administration\'s respect for federalism. "States know their communities best," argued a Republican senator. Conservative media is calling this a victory for limited government.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'Student activists are angry. "Why should where you live determine if you live?" reads campus protest signs. Health justice advocates are organizing.',
                        'yuppie' => 'Young professionals worry about their mobile careers. "What happens when I move to a state that didn\'t prepare?" asks one remote worker.',
                        'young_conservatives' => 'Young conservatives support the federalist approach. "States should control their own healthcare decisions," they argue.',
                        'working_class' => 'Workers in states with weak health infrastructure are terrified. Union leaders are calling this a dereliction of duty.',
                        'suburban' => 'Suburban voters in well-funded states feel secure. Those in underprepared states are anxious. The response is becoming a local issue.',
                        'rural' => 'Rural states with limited healthcare infrastructure are struggling. Some rural communities feel abandoned by the federal government.',
                        'small_business' => 'Small business owners are frustrated by the patchwork approach. Employee healthcare decisions are becoming impossible to plan.',
                        'corporate' => 'Large corporations with national health plans are less affected. They note that state-level variation complicates benefits administration.',
                        'public_sector' => 'State and local public health workers are overwhelmed. They\'re being asked to do more with less federal support.',
                        'retirees' => 'Senior citizens are frightened. Those in wealthy states feel protected; those elsewhere feel vulnerable. Medicare concerns are mounting.',
                        'minorities' => 'Minority communities in less wealthy states are disproportionately affected. Health advocates warn of widening disparities.',
                        'independents' => 'Independent voters are critical of the approach. "This isn\'t leadership," said one focus group participant. Approval ratings are slipping.'
                    ]
                ],
                [
                    'id' => 'volunteer_program',
                    'label' => 'Launch Volunteer Program',
                    'decision_tags' => ['community', 'volunteer', 'low_cost', 'civic_engagement'],
                    'effects' => ['approval' => 3, 'stability' => -1, 'party_support' => 0],
                    'news' => [
                        'left' => [
                            'headline' => 'Community Volunteers to Join Healthcare Response Effort',
                            'body' => 'Progressive organizations are mobilizing volunteers nationwide. "This is American spirit at its finest," said a community organizer. The initiative is being praised as a model of civic engagement.'
                        ],
                        'center' => [
                            'headline' => 'Volunteer Program Offers Community-Led Healthcare Response',
                            'body' => 'Experts see merit in the grassroots approach, though they note its limitations. "Volunteers can help, but they can\'t replace trained professionals," cautioned one public health expert. The effort is gaining broad support.'
                        ],
                        'right' => [
                            'headline' => 'Volunteer Initiative Draws Support, But Questions Remain',
                            'body' => 'Conservatives are cautiously supportive of the volunteer effort. "Community action is what America does best," acknowledged a conservative commentator. However, some are asking if this is enough.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'Students are volunteering in record numbers. Campus medical programs are coordinating with hospitals. This is the largest student volunteer effort in decades.',
                        'yuppie' => 'Young professionals are donating skills and time. Many are using their professional expertise in healthcare administration volunteer roles.',
                        'young_conservatives' => 'Young conservatives are embracing the volunteer spirit. "This is how Americans should help each other," they argue.',
                        'working_class' => 'Blue-collar workers are helping where they can, but many can\'t afford to take time off. Volunteer fatigue is becoming an issue.',
                        'suburban' => 'Suburban communities are organizing volunteer networks. Neighborhood associations are coordinating meals and transportation for healthcare workers.',
                        'rural' => 'Rural areas struggle to recruit volunteers due to sparse populations. Some rural leaders feel the program doesn\'t account for their challenges.',
                        'small_business' => 'Small business owners are releasing employees to volunteer. Some are providing supplies and resources to community efforts.',
                        'corporate' => 'Corporate volunteer programs are being activated. Many companies are paid-release programs for employee volunteering.',
                        'public_sector' => 'Public sector workers appreciate the community support. Firefighters and teachers are joining medical volunteer efforts.',
                        'retirees' => 'Retirees are the backbone of the volunteer force. Many healthcare retirees are returning to help. "This is what we trained for," said one retired nurse.',
                        'minorities' => 'Minority communities are actively participating. Community centers are becoming volunteer coordination hubs. Cultural/language barriers are being addressed.',
                        'independents' => 'Independent voters appreciate the community-based approach. "This shows what we can do when we work together," is the common sentiment.'
                    ]
                ]
            ]
        ],
        3 => [
            'title' => 'Tech Layoffs',
            'description' => 'Major technology companies have announced significant layoffs, affecting tens of thousands of workers. Economists warn of potential ripple effects on the housing market.',
            'scenario_tags' => ['tech', 'jobs', 'layoffs', 'housing', 'economy', 'labor'],
            'decisions' => [
                [
                    'id' => 'retraining',
                    'label' => 'Job Retraining Program',
                    'decision_tags' => ['labor', 'education', 'workforce_development', 'jobs'],
                    'effects' => ['approval' => 4, 'stability' => -2, 'party_support' => 2],
                    'news' => [
                        'left' => [
                            'headline' => 'Administration Launches Ambitious Worker Retraining Initiative',
                            'body' => 'Labor advocates are praising the comprehensive retraining program. "We\'re investing in people, not just profits," stated a union leader. The initiative is being hailed as a progressive approach to economic disruption.'
                        ],
                        'center' => [
                            'headline' => 'Retraining Program Draws Praise, Though Implementation Questions Remain',
                            'body' => 'Economists generally support the initiative but note implementation challenges. "Programs like this have a mixed track record," noted one labor expert. Businesses are watching to see how quickly workers can be retrained.'
                        ],
                        'right' => [
                            'headline' => 'Conservatives Question Effectiveness of Government Retraining',
                            'body' => 'Fiscal conservatives are skeptical about the program\'s cost and effectiveness. "These programs rarely deliver on their promises," argued a conservative economist. Business groups want tax incentives instead.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'College students are watching closely. "Will there be programs for us when we graduate?" is the big question. Career services are overcrowded.',
                        'yuppie' => 'Tech workers are cautiously optimistic. Many are signing up for retraining while job hunting. The quality of programs is being scrutinized.',
                        'young_conservatives' => 'Young conservatives question government competence in job training. "Let the market handle it," they argue on social media.',
                        'working_class' => 'Blue-collar workers are interested in upskilling opportunities. "Tech isn\'t for everyone, but we need pathways," said one union official.',
                        'suburban' => 'Suburban families with laid-off relatives are relieved. The programs offer hope, though program quality varies widely.',
                        'rural' => 'Rural communities hope to attract remote workers through training. "Tech jobs can be done from anywhere," noted one rural development official.',
                        'small_business' => 'Small businesses hope retrained workers will fill local gaps. "We need skilled workers," said one manufacturer.',
                        'corporate' => 'Tech companies are cooperating with training programs. Some are offering apprenticeship opportunities alongside government programs.',
                        'public_sector' => 'Community colleges are overwhelmed with new enrollment. Public education advocates are pushing for more funding.',
                        'retirees' => 'Senior citizens are concerned about their children and grandchildren. Retirement accounts have been affected by tech stock declines.',
                        'minorities' => 'Minority communities see opportunity for economic mobility. Tech training programs are actively recruiting underrepresented groups.',
                        'independents' => 'Swing voters appreciate the investment in workers. "At least they\'re trying something," is the common sentiment.'
                    ]
                ],
                [
                    'id' => 'tax_breaks',
                    'label' => 'Tax Breaks for Hiring',
                    'decision_tags' => ['corporate_tax', 'business_subsidy', 'market_approach'],
                    'effects' => ['approval' => 1, 'stability' => -3, 'party_support' => -1],
                    'news' => [
                        'left' => [
                            'headline' => 'Progressive Groups Critical of Tax Breaks for Tech Giants',
                            'body' => 'Labor advocates are questioning why corporations should get tax breaks after laying off workers. "They\'re being rewarded for their failures," argued a consumer advocate. Progressive groups are organizing opposition.'
                        ],
                        'center' => [
                            'headline' => 'Tax Incentives Aim to Stimulate Tech Hiring',
                            'body' => 'Business analysts see merit in the approach, though they note it\'s an indirect solution. "It could work, but it\'s not targeted at displaced workers," noted one economist. The market impact remains to be seen.'
                        ],
                        'right' => [
                            'headline' => 'Conservatives Back Tax Incentive Approach to Job Creation',
                            'body' => 'Free market advocates are praising the pro-business stance. "This is how you stimulate growth," argued a Chamber of Commerce spokesperson. Business leaders are appreciative.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'Graduating students are cynical. "Why reward companies that laid people off?" is the common complaint on campus.',
                        'yuppie' => 'Laid-off tech workers are frustrated. Tax breaks don\'t guarantee they\'ll be hired. "I need a job, not corporate welfare," said one affected worker.',
                        'young_conservatives' => 'Young conservatives support the market-based approach. "This encourages businesses to hire," they argue.',
                        'working_class' => 'Non-tech workers feel ignored. "What about us?" is the question from traditional industries.',
                        'suburban' => 'Affected families are angry. Tax breaks don\'t help those already laid off. "We need direct assistance," one suburban parent said.',
                        'rural' => 'Rural communities see little benefit. Tech jobs don\'t traditionally go to rural areas. Agricultural subsidies matter more here.',
                        'small_business' => 'Small businesses are confused about eligibility. "Why do big tech companies get breaks but not us?" is a common complaint.',
                        'corporate' => 'Large tech companies are ecstatic. Stock prices are rising on the news. Executive compensation debates are intensifying.',
                        'public_sector' => 'Public sector unions are critical. "We didn\'t get tax breaks during our hiring freeze," noted one public employee union.',
                        'retirees' => 'Investors are pleased as markets respond positively. But retirees are also concerned about their 401(k)s tied to tech stocks.',
                        'minorities' => 'Minority communities are skeptical. "These benefits rarely trickle down to us," argue community organizers.',
                        'independents' => 'Independent voters are divided. Some see market wisdom; others see corporate giveaways. The verdict is still out.'
                    ]
                ],
                [
                    'id' => 'delay',
                    'label' => 'Delay Action',
                    'decision_tags' => ['no_action', 'market_wait'],
                    'effects' => ['approval' => -4, 'stability' => 2, 'party_support' => 1],
                    'news' => [
                        'left' => [
                            'headline' => 'Worker Advocates Denounce Delay on Tech Layoff Response',
                            'body' => 'Labor groups are expressing outrage at the administration\'s inaction. "Every day of delay is a family struggling," stated a workers\' rights organization. Progressive leaders are calling for immediate action.'
                        ],
                        'center' => [
                            'headline' => 'Administration Faces Criticism for Delayed Response to Tech Crisis',
                            'body' => 'Political analysts see the delay as a potential vulnerability. "Voters notice when government doesn\'t act," noted one commentator. The optics of inaction are drawing negative attention.'
                        ],
                        'right' => [
                            'headline' => 'Conservatives Support Cautious Approach, Warn Against Overreaction',
                            'body' => 'GOP leaders are defending the measured response. "Not every crisis needs immediate government intervention," argued a Republican strategist. Some conservative commentators support the wait-and-see approach.'
                        ]
                    ],
                    'voter_reactions' => [
                        'students' => 'New graduates are terrified about job prospects. Campus recruiting is down. Many are considering alternative careers.',
                        'yuppie' => 'Affected workers are making career pivots without government help. The startup scene is seeing a wave of new entrepreneurs.',
                        'young_conservatives' => 'Young conservatives support the measured approach. "The market will correct itself," is their argument.',
                        'working_class' => 'Blue-collar workers feel the tech worker pain is overblown. "We\'ve always had layoffs," noted one construction worker.',
                        'suburban' => 'Suburban families are angry. Professional networking is replacing government assistance. Neighborhood support groups are forming.',
                        'rural' => 'Rural communities are unaffected. Tech layoffs don\'t impact rural economies directly. The concern is minimal.',
                        'small_business' => 'Small businesses are quietly recruiting talented workers. "There\'s a talent flood," said one small business owner.',
                        'corporate' => 'Corporate leaders are divided. Some see opportunity; others worry about consumer confidence. Tech sector executives are reassessing hiring.',
                        'public_sector' => 'Public sector workers are unaffected but watching. Many are grateful for job security.',
                        'retirees' => 'Retirees with family in tech are concerned. But overall retirement accounts are recovering, easing some worry.',
                        'minorities' => 'Minority communities in tech are hit hard. Support networks are stepping up. Community organizations are offering emergency assistance.',
                        'independents' => 'Independent voters are frustrated. Approval ratings for economic leadership are declining. "Where\'s the plan?" is the common question.'
                    ]
                ]
            ]
        ],
        4 => [
            'title' => 'Border Crisis',
            'description' => 'A surge of migrants at the southern border has overwhelmed detention facilities and sparked debates in Congress. Governors of border states have declared emergencies.',
            'scenario_tags' => ['border', 'immigration', 'enforcement', 'asylum', 'labor'],
            'decisions' => [
                [
                    'id' => 'military_deployment',
                    'label' => 'Deploy National Guard',
                    'decision_tags' => ['military', 'border', 'enforcement', 'security'],
                    'effects' => ['approval' => -3, 'stability' => 1, 'party_support' => 4],
                    'news' => [
                        'left' => ['headline' => 'Progressive Groups Condemn Military Buildup at Border', 'body' => 'Immigration advocates argue the deployment is unnecessarily aggressive and will only worsen the humanitarian situation. Civil rights organizations are demanding accountability.'],
                        'center' => ['headline' => 'National Guard Deployment Draws Mixed Reactions', 'body' => 'Border officials welcome the additional resources while humanitarian groups express concern. The cost and legal implications are being debated.'],
                        'right' => ['headline' => 'Conservatives Applaud Strong Border Action', 'body' => 'GOP leaders are praising the decisive military response. "Finally, someone taking border security seriously," said one senator.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Student activists are protesting the militarization of border policy. Campus groups are organizing solidarity events with immigrant communities.',
                        'yuppie' => 'Young professionals see this as political theater. "Both sides use this issue to fire up their base," noted one commentator.',
                        'young_conservatives' => 'Young conservatives strongly support border security. Immigration enforcement is a top priority for this demographic.',
                        'working_class' => 'Blue-collar workers are concerned about job competition. Union leaders are split on the issue.',
                        'suburban' => 'Suburban voters are divided. Security concerns compete with humanitarian values in suburban households.',
                        'rural' => 'Rural communities generally support stronger border enforcement. Border state residents are most passionate about this.',
                        'small_business' => 'Small business owners are concerned about labor shortages. Some industries rely on immigrant labor.',
                        'corporate' => 'Corporate executives want a stable immigration policy. Agriculture and tech sectors depend on skilled immigrants.',
                        'public_sector' => 'Public sector workers in border regions are overwhelmed. Federal emergency funds are being requested.',
                        'retirees' => 'Senior citizens prioritize security and order. Many support strong border measures.',
                        'minorities' => 'Minority communities are watching closely. Immigration reform advocates are pushing for humane solutions.',
                        'independents' => 'Independent voters are sympathetic to both sides. The humanitarian and security arguments both resonate.'
                    ]
                ],
                [
                    'id' => 'humane_reforms',
                    'label' => 'Pass Immigration Reform',
                    'decision_tags' => ['immigration', 'citizenship', 'asylum', 'reform'],
                    'effects' => ['approval' => 3, 'stability' => -2, 'party_support' => -3],
                    'news' => [
                        'left' => ['headline' => 'Advocates Celebrate Historic Immigration Reform', 'body' => 'Immigration rights groups are calling this a watershed moment. The path to citizenship provisions are being praised as long overdue.'],
                        'center' => ['headline' => 'Immigration Bill Clears Key Hurdles', 'body' => 'Congress is debating a comprehensive reform package. Economists are divided on the long-term impacts.'],
                        'right' => ['headline' => 'GOP Rips "Amnesty" Bill as Election Gift', 'body' => 'Conservative groups are mobilizing against the reform effort. Primary challenges are being threatened against supporting lawmakers.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Student activists are celebrating this as a major victory. Campus organizers worked hard on this campaign.',
                        'yuppie' => 'Young professionals generally support immigration reform. Many have immigrant colleagues and friends.',
                        'young_conservatives' => 'Young conservatives are furious about this. Primary challenges against "RINOs" are being discussed.',
                        'working_class' => 'Blue-collar workers are deeply divided. Some see opportunity; others fear wage depression.',
                        'suburban' => 'Educated suburban voters support reform. Community values emphasize inclusion and opportunity.',
                        'rural' => 'Rural communities are skeptical. Concerns about assimilation and resource strain are common.',
                        'small_business' => 'Small business owners are divided. Restaurant and construction industries support reform.',
                        'corporate' => 'Corporate executives strongly support immigration reform. Tech industry needs skilled workers.',
                        'public_sector' => 'Public sector workers are cautious. Implementation will require significant resources.',
                        'retirees' => 'Senior citizens are split on this issue. Some prioritize humanitarian values; others fear change.',
                        'minorities' => 'Minority communities are strongly supportive. Many see this as a civil rights victory.',
                        'independents' => 'Independent voters give cautious approval. They want to see implementation details.'
                    ]
                ],
                [
                    'id' => 'diplomatic',
                    'label' => 'Work with Mexico',
                    'decision_tags' => ['diplomacy', 'international', 'border', 'trade'],
                    'effects' => ['approval' => 1, 'stability' => 0, 'party_support' => 1],
                    'news' => [
                        'left' => ['headline' => 'Bilateral Talks Signal New Era of Cooperation', 'body' => 'Diplomatic observers are cautiously optimistic about the new agreement. Roots causes of migration are on the agenda.'],
                        'center' => ['headline' => 'US-Mexico Partnership on Border Issues Announced', 'body' => 'Both governments have committed to joint enforcement measures. Economic development in Central America is included.'],
                        'right' => ['headline' => 'Critics Question Mexican Cooperation on Border Security', 'body' => 'Conservative commentators are skeptical of the arrangement. "Mexico has every incentive to let people through," argued one pundit.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Students support diplomatic solutions. Many feel military approaches miss the root causes.',
                        'yuppie' => 'Young professionals appreciate the diplomatic approach. Business ties with Mexico matter to them.',
                        'young_conservatives' => 'Young conservatives are skeptical of Mexico\'s reliability. "Trust but verify" is the sentiment.',
                        'working_class' => 'Workers are concerned about NAFTA implications. Trade and immigration are linked in their minds.',
                        'suburban' => 'Suburban voters are cautiously optimistic. They want practical solutions over ideology.',
                        'rural' => 'Rural communities want to see enforcement results. Diplomatic talk doesn\'t satisfy them.',
                        'small_business' => 'Small businesses with cross-border ties support this. Trade facilitation is welcome.',
                        'corporate' => 'Corporate leaders strongly support good Mexican relations. Supply chains depend on it.',
                        'public_sector' => 'State department workers support this approach. Career diplomats feel validated.',
                        'retirees' => 'Retirees remember when diplomacy worked. They\'re cautiously supportive.',
                        'minorities' => 'Minority communities are supportive of Latin American cooperation. Cultural ties matter.',
                        'independents' => 'Independent voters like the moderate approach. It\'s neither extreme open borders nor military buildup.'
                    ]
                ]
            ]
        ],
        5 => [
            'title' => 'Stock Market Crash',
            'description' => 'The stock market has experienced its worst week in years, with the S&P 500 dropping 15%. Analysts are warning of potential recession as consumer confidence plummets.',
            'scenario_tags' => ['finance', 'market', 'economy', 'housing', 'consumer', 'jobs'],
            'decisions' => [
                [
                    'id' => 'market_intervention',
                    'label' => 'Emergency Market Measures',
                    'decision_tags' => ['finance', 'intervention', 'market_stabilization', 'consumer_protection'],
                    'effects' => ['approval' => 4, 'stability' => -5, 'party_support' => 2],
                    'news' => [
                        'left' => ['headline' => 'White House Acts to Stabilize Markets', 'body' => 'The administration\'s emergency measures are being praised as necessary intervention. Labor groups urge additional worker protections.'],
                        'center' => ['headline' => 'Federal Reserve Coordinates with White House on Response', 'body' => 'Unprecedented coordination between monetary and fiscal authorities. Markets are reacting positively to government action.'],
                        'right' => ['headline' => 'Free Market Advocates Warn of Government Overreach', 'body' => 'Conservative economists argue intervention delays necessary correction. "Markets need to find their bottom," they argue.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Students are worried about entering a bad job market. Career plans are being reconsidered.',
                        'yuppie' => 'Tech workers with stock portfolios are panicking. Their 401ks have taken major hits.',
                        'young_conservatives' => 'Young conservatives blame government intervention for creating bubbles. "This was inevitable," they argue.',
                        'working_class' => 'Blue-collar workers remember 2008. They\'re nervous but trying to stay calm.',
                        'suburban' => 'Suburban families are watching their retirement accounts. College savings plans are shrinking.',
                        'rural' => 'Rural communities feel distant from Wall Street. Main Street doesn\'t care about the Dow.',
                        'small_business' => 'Small business owners are worried about credit drying up. Lines of credit are being reviewed.',
                        'corporate' => 'Corporate executives are hoarding cash. Stock buybacks have been suspended.',
                        'public_sector' => 'Public sector unions are worried about budget impacts. Tax revenue projections are being revised.',
                        'retirees' => 'Retirees on fixed incomes are terrified. Their portfolios have lost years of gains.',
                        'minorities' => 'Minority communities are disproportionately affected. Wealth gaps are widening again.',
                        'independents' => 'Independent voters are blaming whoever is in power. This is a political liability.'
                    ]
                ],
                [
                    'id' => 'do_nothing',
                    'label' => 'Let Markets Correct',
                    'decision_tags' => ['no_action', 'market', 'fiscal_conservatism'],
                    'effects' => ['approval' => -5, 'stability' => 3, 'party_support' => -1],
                    'news' => [
                        'left' => ['headline' => 'Workers Left to Suffer While Markets "Correct"', 'body' => 'Progressive groups are criticizing the hands-off approach. Emergency unemployment extensions are being demanded.'],
                        'center' => ['headline' => 'White House Stays Silent as Markets Continue Decline', 'body' => 'Analysts are questioning the strategy. Voters are looking for leadership during the crisis.'],
                        'right' => ['headline' => 'Free Market purists Support Presidential Restraint', 'body' => '"This is how markets work," argue conservative commentators. Government intervention would make things worse.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Students are angry at the perceived indifference. This feels like 2008 all over again.',
                        'yuppie' => 'Tech workers are furious. They want government action to protect their investments.',
                        'young_conservatives' => 'Young conservatives support the approach. "Markets always recover," they argue.',
                        'working_class' => 'Workers are frightened. Without intervention, layoffs are coming.',
                        'suburban' => 'Suburban families feel abandoned. The president isn\'t doing anything to help.',
                        'rural' => 'Rural communities are struggling. Local banks are tightening credit requirements.',
                        'small_business' => 'Small businesses are desperate for any support. They can\'t wait for markets to recover.',
                        'corporate' => 'Corporate executives are divided. Some welcome the lack of interference; others want stability.',
                        'public_sector' => 'Public sector workers are worried. State budgets will be affected by recession.',
                        'retirees' => 'Retirees are livid. They\'ve lost decades of savings in weeks.',
                        'minorities' => 'Minority communities are hit hardest again. The racial wealth gap is growing.',
                        'independents' => 'Independent voters are losing confidence. They want to see action, not ideology.'
                    ]
                ],
                [
                    'id' => 'worker_protection',
                    'label' => 'Protect Workers First',
                    'decision_tags' => ['labor', 'workers', 'unemployment', 'consumer_relief'],
                    'effects' => ['approval' => 6, 'stability' => -3, 'party_support' => 3],
                    'news' => [
                        'left' => ['headline' => 'Worker-First Policy Draws Praise from Labor', 'body' => 'Unions are celebrating the administration\'s priorities. "Workers over Wall Street" is the messaging.'],
                        'center' => ['headline' => 'Administration Focuses on Unemployment, Not Market Bailout', 'body' => 'The policy pivot is notable - directly helping workers rather than financial institutions. Markets remain volatile.'],
                        'right' => ['headline' => 'Business Groups Blast Worker-First Approach', 'body' => 'Chamber of Commerce leaders argue the approach threatens economic recovery. Regulations are being blamed for the crash.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Students love seeing workers prioritized. This gives them hope for their future.',
                        'yuppie' => 'Young professionals are split. Some benefit from worker protections; others worry about the economy.',
                        'young_conservatives' => 'Young conservatives are angry. "Punishing business to help workers won\'t work," they argue.',
                        'working_class' => 'Blue-collar workers are thrilled. Finally, someone is on their side.',
                        'suburban' => 'Suburban families are relieved. Unemployment benefits help them survive.',
                        'rural' => 'Rural workers appreciate the help. But they\'re worried about their small local businesses.',
                        'small_business' => 'Small business owners feel abandoned. They\'re being asked to keep workers but get no support.',
                        'corporate' => 'Corporate executives are furious. Regulations and mandates are threatening their recovery.',
                        'public_sector' => 'Public sector unions are grateful. Enhanced benefits are being extended.',
                        'retirees' => 'Senior citizens are watching nervously. They remember when worker protections came with recession.',
                        'minorities' => 'Minority communities are celebrating. These protections disproportionately help them.',
                        'independents' => 'Independent voters give mixed reviews. They want workers helped but worry about deficits.'
                    ]
                ]
            ]
        ],
        6 => [
            'title' => 'Foreign Policy Crisis',
            'description' => 'Tensions with a major world power have escalated following a military incident in international waters. NATO allies are requesting American leadership on the response.',
            'scenario_tags' => ['military', 'diplomacy', 'security', 'economy', 'labor'],
            'decisions' => [
                [
                    'id' => 'military_response',
                    'label' => 'Show of Force',
                    'decision_tags' => ['military', 'security', 'strength', 'deterrence'],
                    'effects' => ['approval' => 2, 'stability' => -4, 'party_support' => 5],
                    'news' => [
                        'left' => ['headline' => 'Military Escalation Draws Progressive Ire', 'body' => 'Anti-war groups are mobilizing against the administration\'s aggressive posture. Diplomatic solutions are being urged.'],
                        'center' => ['headline' => 'US Naval Forces Deployed to Tense Region', 'body' => 'The show of force is intended to deter further aggression. Allied nations are largely supportive.'],
                        'right' => ['headline' => 'Patriotic Americans Support Strong Response', 'body' => 'Conservative media is praising the administration\'s resolve. "Peace through strength" messaging is resonant.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Student activists are organizing peace protests. "No more forever wars" is the chant.',
                        'yuppie' => 'Young professionals are anxious about potential conflict. Global stability matters to them.',
                        'young_conservatives' => 'Young conservatives strongly support military strength. Defense spending increases are welcome.',
                        'working_class' => 'Blue-collar workers are worried about military recruitment. Defense industry jobs are on their minds.',
                        'suburban' => 'Suburban families are nervous about escalation. They want de-escalation.',
                        'rural' => 'Rural communities support strong national defense. Military presence in rural areas is valued.',
                        'small_business' => 'Small businesses fear economic disruption. Trade routes are at risk.',
                        'corporate' => 'Corporate executives want stable markets. Defense contractors are celebrating.',
                        'public_sector' => 'Veterans organizations support the military posture. Veteran services matter.',
                        'retirees' => 'Retirees remember Cold War tensions. They\'re generally supportive of strength.',
                        'minorities' => 'Minority communities are concerned about being drafted. Military recruitment concerns.',
                        'independents' => 'Independent voters are cautiously supportive. They want strength but also diplomacy.'
                    ]
                ],
                [
                    'id' => 'diplomatic_first',
                    'label' => 'Pursue Diplomatic Solution',
                    'decision_tags' => ['diplomacy', 'peace', 'sanctions', 'negotiation'],
                    'effects' => ['approval' => 3, 'stability' => -1, 'party_support' => -2],
                    'news' => [
                        'left' => ['headline' => 'Diplomatic Push Praised by Peace Advocates', 'body' => 'Progressive groups are relieved by the measured approach. Sanctions are preferred over military action.'],
                        'center' => ['headline' => 'State Department Leads Crisis Response', 'body' => 'Career diplomats are taking center stage. Allied support is being actively courted.'],
                        'right' => ['headline' => 'GOP Hawks Critique "Weak" Diplomatic Focus', 'body' => 'Conservative commentators are calling for stronger action. "Appeasement" comparisons are being made.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Students are celebrating the diplomatic approach. Anti-war sentiment is strong on campus.',
                        'yuppie' => 'Young professionals support measured responses. Global markets prefer stability.',
                        'young_conservatives' => 'Young conservatives are frustrated. "Strength through peace" doesn\'t resonate with them.',
                        'working_class' => 'Workers support avoiding war. Their sons and daughters could be drafted.',
                        'suburban' => 'Suburban families appreciate the restraint. War would disrupt their lives.',
                        'rural' => 'Rural communities are divided. Defense contractors in rural areas would benefit from conflict.',
                        'small_business' => 'Small businesses strongly support diplomacy. Trade disruptions would devastate them.',
                        'corporate' => 'Corporate leaders are divided. Some want stability; defense contractors want contracts.',
                        'public_sector' => 'Foreign service officers are pleased. Their expertise is being valued.',
                        'retirees' => 'Senior citizens remember the cost of war. They\'re supportive of diplomacy.',
                        'minorities' => 'Minority communities strongly support peaceful solutions. They bear the burden of conflict.',
                        'independents' => 'Independent voters approve of the measured approach. Neither too weak nor too aggressive.'
                    ]
                ],
                [
                    'id' => 'alliance_building',
                    'label' => 'Build International Coalition',
                    'decision_tags' => ['diplomacy', 'alliances', 'international', 'multilateralism'],
                    'effects' => ['approval' => 4, 'stability' => -2, 'party_support' => 2],
                    'news' => [
                        'left' => ['headline' => 'Coalition Building Shows American Leadership', 'body' => 'Multilateral approach is being praised. Allied nations are responding positively.'],
                        'center' => ['headline' => 'US Works to Unite Allies on Joint Response', 'body' => 'Diplomatic efforts are focused on building consensus. European and Asian allies are being consulted.'],
                        'right' => ['headline' => 'Coalition Approach Seen as "Leading from Behind"', 'body' => 'Conservative critics argue this abdicates American leadership. Allies should follow, not coordinate.']
                    ],
                    'voter_reactions' => [
                        'students' => 'Students support international cooperation. Global citizenship is emphasized in education.',
                        'yuppie' => 'Young professionals who travel support coalition building. Global partnerships matter.',
                        'young_conservatives' => 'Young conservatives are skeptical of allies. "America First" is their priority.',
                        'working_class' => 'Workers are focused on domestic issues. Foreign policy is less urgent for them.',
                        'suburban' => 'Suburban voters with global connections support the approach. International business matters.',
                        'rural' => 'Rural communities are less interested in foreign affairs. Domestic issues dominate.',
                        'small_business' => 'Small businesses want stable markets. Coalition helps ensure trade continues.',
                        'corporate' => 'Corporate executives strongly support the coalition. Global markets need American leadership.',
                        'public_sector' => 'State department and intelligence workers support this. Their work is validated.',
                        'retirees' => 'Retirees remember when coalitions won wars. They support the approach.',
                        'minorities' => 'Minority communities with immigrant backgrounds support internationalism. Heritage matters.',
                        'independents' => 'Independent voters like the balanced approach. Neither isolationist nor interventionist.'
                    ]
                ]
            ]
        ]
    ];

    protected array $states = [
        [
            'name' => 'Alabama',
            'abbr' => 'AL',
            'fips' => '01',
            'color' => 'red',
            'identity' => [
                'overview' => 'Deep conservative state prioritizing traditional values, low regulation, and economic stability through industry and agriculture.',
                'leans' => [
                    'economic' => 'pro-business, low taxes',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'strongly conservative',
                    'federal' => 'prefers state control',
                ],
                'priorities' => ['jobs', 'manufacturing', 'agriculture', 'low cost of living'],
                'tensions' => ['rural vs urban development', 'economic growth vs education investment'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation', 'tax_cuts', 'manufacturing_growth'],
                'lean_support' => ['military_strength', 'border_security', 'border_enforcement'],
                'no_effect' => ['tech_regulation'],
                'lean_oppose' => ['environmental_regulation', 'minimum_wage', 'social_security'],
                'strong_oppose' => ['climate_restrictions', 'federal_expansion'],
            ],
            'tag_weights' => [
                'jobs' => 1.4,
                'fossil_expansion' => 1.5,
            ],
            'base_support' => 38,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'Alaska',
            'abbr' => 'AK',
            'fips' => '02',
            'color' => 'red',
            'identity' => [
                'overview' => 'Resource-driven state valuing energy independence, economic survival, and minimal federal interference.',
                'leans' => [
                    'economic' => 'resource-based, pro-industry',
                    'regulatory' => 'strongly anti-regulation',
                    'energy' => 'heavily fossil-dependent',
                    'social' => 'moderately conservative',
                    'federal' => 'strong anti-federal control',
                ],
                'priorities' => ['energy', 'jobs', 'infrastructure', 'cost of living'],
                'tensions' => ['environment vs economic survival'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation', 'energy_independence'],
                'lean_support' => ['military_investment', 'border_security', 'border_enforcement'],
                'no_effect' => ['education_policy'],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => ['climate_restrictions'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 2.0,
            ],
            'base_support' => 42,
            'swing_tolerance' => 10,
        ],
        [
            'name' => 'Arizona',
            'abbr' => 'AZ',
            'fips' => '04',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Swing state balancing conservative economics with growing urban diversity and immigration concerns.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'mixed',
                    'federal' => 'balanced',
                ],
                'priorities' => ['border', 'jobs', 'housing', 'cost of living'],
                'tensions' => ['urban vs rural', 'immigration vs economic growth'],
            ],
            'policy_bias' => [
                'strong_support' => ['border_security', 'border_enforcement', 'social_security'],
                'lean_support' => ['job_growth', 'business_incentives', 'infrastructure_spending'],
                'no_effect' => ['climate_policy'],
                'lean_oppose' => ['federal_expansion', 'minimum_wage'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'border_security' => 1.7,
            ],
            'base_support' => 49,
            'swing_tolerance' => 15,
        ],
        [
            'name' => 'Arkansas',
            'abbr' => 'AR',
            'fips' => '05',
            'color' => 'red',
            'identity' => [
                'overview' => 'Rural conservative state focused on agriculture, low regulation, and economic simplicity.',
                'leans' => [
                    'economic' => 'pro-business, low-cost',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['agriculture', 'jobs', 'cost of living'],
                'tensions' => ['rural stagnation vs development'],
            ],
            'policy_bias' => [
                'strong_support' => ['deregulation', 'agriculture_support', 'fossil_expansion'],
                'lean_support' => ['tax_cuts', 'border_security', 'border_enforcement'],
                'no_effect' => ['tech_policy'],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'agriculture' => 1.5,
            ],
            'base_support' => 37,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'California',
            'abbr' => 'CA',
            'fips' => '06',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Highly progressive state prioritizing climate action, regulation, and social policy.',
                'leans' => [
                    'economic' => 'mixed, innovation-driven',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'renewable-focused',
                    'social' => 'strongly progressive',
                    'federal' => 'supports federal action',
                ],
                'priorities' => ['climate', 'tech', 'housing', 'inequality'],
                'tensions' => ['economy vs cost of living', 'innovation vs regulation'],
            ],
            'policy_bias' => [
                'strong_support' => ['climate_action', 'renewable_investment', 'worker_protection'],
                'lean_support' => ['tech_growth', 'privacy_rights', 'housing_affordability'],
                'no_effect' => ['agriculture'],
                'lean_oppose' => ['tax_cuts', 'nuclear_energy', 'criminal_justice'],
                'strong_oppose' => ['fossil_expansion', 'deregulation', 'border_enforcement'],
            ],
            'tag_weights' => [
                'climate_action' => 2.0,
            ],
            'base_support' => 67,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'Colorado',
            'abbr' => 'CO',
            'fips' => '08',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Progressive-leaning state with strong environmental values and a growing tech economy.',
                'leans' => [
                    'economic' => 'mixed, innovation-driven',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'mixed but green-leaning',
                    'social' => 'progressive',
                    'federal' => 'moderate',
                ],
                'priorities' => ['climate', 'tech', 'quality of life'],
                'tensions' => ['urban vs rural energy needs'],
            ],
            'policy_bias' => [
                'strong_support' => ['renewable_investment'],
                'lean_support' => ['climate_action', 'tech_growth'],
                'no_effect' => [],
                'lean_oppose' => ['fossil_expansion', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'climate_action' => 1.6,
            ],
            'base_support' => 58,
            'swing_tolerance' => 10,
        ],
        [
            'name' => 'Connecticut',
            'abbr' => 'CT',
            'fips' => '09',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Wealthy, suburban-heavy state prioritizing stability, regulation, and public services.',
                'leans' => [
                    'economic' => 'mixed',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'neutral',
                    'social' => 'progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['economy', 'healthcare', 'education'],
                'tensions' => ['wealth inequality'],
            ],
            'policy_bias' => [
                'strong_support' => ['public_services', 'regulation'],
                'lean_support' => ['healthcare_expansion'],
                'no_effect' => ['fossil_expansion'],
                'lean_oppose' => ['tax_cuts', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [],
            'base_support' => 60,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'Delaware',
            'abbr' => 'DE',
            'fips' => '10',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Small, business-friendly state with moderate Democratic lean.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'moderate',
                    'energy' => 'neutral',
                    'social' => 'moderate',
                    'federal' => 'balanced',
                ],
                'priorities' => ['business', 'jobs'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'border_security', 'infrastructure_spending'],
                'lean_support' => ['business_incentives', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [],
            'base_support' => 57,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'Florida',
            'abbr' => 'FL',
            'fips' => '12',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Large swing state leaning conservative with strong economic and retiree priorities.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'mixed',
                    'social' => 'lean conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'economy', 'retirees', 'cost of living'],
                'tensions' => ['growth vs environment'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'tax_cuts', 'border_security', 'border_enforcement', 'social_security'],
                'lean_support' => ['deregulation', 'infrastructure_spending'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation', 'minimum_wage'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'economy' => 1.4,
            ],
            'base_support' => 48,
            'swing_tolerance' => 16,
        ],
        [
            'name' => 'Georgia',
            'abbr' => 'GA',
            'fips' => '13',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Fast-growing state balancing conservative roots with urban liberal growth.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'mixed',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'economy', 'growth'],
                'tensions' => ['urban vs rural divide'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth'],
                'lean_support' => ['business_incentives'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.5,
            ],
            'base_support' => 49,
            'swing_tolerance' => 15,
        ],
        [
            'name' => 'Hawaii',
            'abbr' => 'HI',
            'fips' => '15',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Progressive, environmentally sensitive state prioritizing sustainability, tourism, and social welfare.',
                'leans' => [
                    'economic' => 'service-based, tourism-driven',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'renewable-focused',
                    'social' => 'progressive',
                    'federal' => 'supports federal action',
                ],
                'priorities' => ['climate', 'cost of living', 'tourism', 'environment'],
                'tensions' => ['tourism vs environmental preservation'],
            ],
            'policy_bias' => [
                'strong_support' => ['climate_action', 'renewable_investment', 'environmental_protection'],
                'lean_support' => ['public_services'],
                'no_effect' => ['manufacturing'],
                'lean_oppose' => ['fossil_expansion'],
                'strong_oppose' => ['deregulation'],
            ],
            'tag_weights' => [
                'climate_action' => 2.0,
            ],
            'base_support' => 65,
            'swing_tolerance' => 6,
        ],
        [
            'name' => 'Idaho',
            'abbr' => 'ID',
            'fips' => '16',
            'color' => 'red',
            'identity' => [
                'overview' => 'Deeply conservative, rural state valuing limited government, agriculture, and self-reliance.',
                'leans' => [
                    'economic' => 'agriculture-based, pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'strongly conservative',
                    'federal' => 'strong state control',
                ],
                'priorities' => ['agriculture', 'jobs', 'low taxes'],
                'tensions' => ['growth vs preserving rural character'],
            ],
            'policy_bias' => [
                'strong_support' => ['deregulation', 'agriculture_support', 'tax_cuts'],
                'lean_support' => ['fossil_expansion', 'border_security', 'border_enforcement'],
                'no_effect' => ['tech_regulation'],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => ['federal_expansion'],
            ],
            'tag_weights' => [
                'agriculture' => 1.6,
            ],
            'base_support' => 34,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'Illinois',
            'abbr' => 'IL',
            'fips' => '17',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Urban-heavy progressive state with strong labor presence and economic inequality divide.',
                'leans' => [
                    'economic' => 'mixed, urban-driven',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'mixed',
                    'social' => 'progressive',
                    'federal' => 'supports federal action',
                ],
                'priorities' => ['jobs', 'labor', 'economy', 'inequality'],
                'tensions' => ['Chicago vs rural Illinois'],
            ],
            'policy_bias' => [
                'strong_support' => ['worker_protection', 'public_services'],
                'lean_support' => ['climate_action'],
                'no_effect' => [],
                'lean_oppose' => ['deregulation', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'worker_protection' => 1.7,
            ],
            'base_support' => 59,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'Indiana',
            'abbr' => 'IN',
            'fips' => '18',
            'color' => 'red',
            'identity' => [
                'overview' => 'Conservative-leaning manufacturing state focused on jobs, industry, and economic stability.',
                'leans' => [
                    'economic' => 'manufacturing-focused, pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'manufacturing', 'economy'],
                'tensions' => ['industry vs environmental concerns'],
            ],
            'policy_bias' => [
                'strong_support' => ['manufacturing_growth', 'deregulation', 'job_growth'],
                'lean_support' => ['fossil_expansion', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'manufacturing_growth' => 1.6,
            ],
            'base_support' => 42,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'Iowa',
            'abbr' => 'IA',
            'fips' => '19',
            'color' => 'red',
            'identity' => [
                'overview' => 'Agriculture-driven swing state balancing rural values with moderate economic concerns.',
                'leans' => [
                    'economic' => 'agriculture-based',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'lean conservative',
                    'federal' => 'balanced',
                ],
                'priorities' => ['agriculture', 'jobs', 'economy'],
                'tensions' => ['rural needs vs economic modernization'],
            ],
            'policy_bias' => [
                'strong_support' => ['agriculture_support'],
                'lean_support' => ['job_growth', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'agriculture' => 1.7,
            ],
            'base_support' => 44,
            'swing_tolerance' => 11,
        ],
        [
            'name' => 'Kansas',
            'abbr' => 'KS',
            'fips' => '20',
            'color' => 'red',
            'identity' => [
                'overview' => 'Rural conservative state prioritizing agriculture, low taxes, and economic stability.',
                'leans' => [
                    'economic' => 'agriculture-based, pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['agriculture', 'jobs', 'cost of living'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['agriculture_support', 'tax_cuts', 'deregulation'],
                'lean_support' => ['fossil_expansion', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'agriculture' => 1.6,
            ],
            'base_support' => 39,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'Kentucky',
            'abbr' => 'KY',
            'fips' => '21',
            'color' => 'red',
            'identity' => [
                'overview' => 'Conservative state with strong ties to energy and traditional industries.',
                'leans' => [
                    'economic' => 'industry-based',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-dependent',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'energy', 'economy'],
                'tensions' => ['economic decline vs modernization'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'job_growth'],
                'lean_support' => ['deregulation', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['climate_restrictions'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'fossil_expansion' => 1.7,
            ],
            'base_support' => 38,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'Louisiana',
            'abbr' => 'LA',
            'fips' => '22',
            'color' => 'red',
            'identity' => [
                'overview' => 'Energy-heavy conservative state focused on oil, jobs, and economic survival.',
                'leans' => [
                    'economic' => 'resource-based',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'strong fossil focus',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['energy', 'jobs', 'economy'],
                'tensions' => ['environmental risk vs economic need'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation'],
                'lean_support' => ['job_growth'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => ['climate_restrictions'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 2.0,
            ],
            'base_support' => 40,
            'swing_tolerance' => 10,
        ],
        [
            'name' => 'Maine',
            'abbr' => 'ME',
            'fips' => '23',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Moderate state with independent streak, valuing environment and local economies.',
                'leans' => [
                    'economic' => 'mixed',
                    'regulatory' => 'moderate',
                    'energy' => 'green-leaning',
                    'social' => 'moderate',
                    'federal' => 'balanced',
                ],
                'priorities' => ['environment', 'local economy'],
                'tensions' => ['rural vs coastal priorities'],
            ],
            'policy_bias' => [
                'strong_support' => ['environmental_protection'],
                'lean_support' => ['climate_action'],
                'no_effect' => [],
                'lean_oppose' => ['fossil_expansion', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [],
            'base_support' => 55,
            'swing_tolerance' => 12,
        ],
        [
            'name' => 'Maryland',
            'abbr' => 'MD',
            'fips' => '24',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Democratic-leaning state with strong public sector presence and focus on services.',
                'leans' => [
                    'economic' => 'mixed, government-influenced',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'mixed',
                    'social' => 'progressive',
                    'federal' => 'strong federal role',
                ],
                'priorities' => ['public services', 'jobs', 'education'],
                'tensions' => ['government vs private sector balance'],
            ],
            'policy_bias' => [
                'strong_support' => ['public_services', 'education_funding'],
                'lean_support' => ['worker_protection'],
                'no_effect' => [],
                'lean_oppose' => ['deregulation', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'public_services' => 1.6,
            ],
            'base_support' => 63,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'Massachusetts',
            'abbr' => 'MA',
            'fips' => '25',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Highly progressive state focused on education, healthcare, and strong government services.',
                'leans' => [
                    'economic' => 'mixed, innovation-driven',
                    'regulatory' => 'strongly pro-regulation',
                    'energy' => 'renewable-focused',
                    'social' => 'strongly progressive',
                    'federal' => 'supports federal action',
                ],
                'priorities' => ['healthcare', 'education', 'climate', 'innovation'],
                'tensions' => ['cost of living vs economic growth'],
            ],
            'policy_bias' => [
                'strong_support' => ['healthcare_expansion', 'education_funding', 'climate_action'],
                'lean_support' => ['worker_protection'],
                'no_effect' => [],
                'lean_oppose' => ['tax_cuts'],
                'strong_oppose' => ['deregulation', 'fossil_expansion', 'border_enforcement'],
            ],
            'tag_weights' => [
                'education_funding' => 1.7,
                'climate_action' => 1.8,
            ],
            'base_support' => 68,
            'swing_tolerance' => 6,
        ],
        [
            'name' => 'Michigan',
            'abbr' => 'MI',
            'fips' => '26',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Industrial swing state balancing labor interests with economic recovery and manufacturing.',
                'leans' => [
                    'economic' => 'manufacturing-focused',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'mixed',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'manufacturing', 'labor'],
                'tensions' => ['union jobs vs economic modernization'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'manufacturing_growth', 'opioid_crisis', 'trade_tariffs'],
                'lean_support' => ['worker_protection', 'infrastructure_spending'],
                'no_effect' => [],
                'lean_oppose' => ['deregulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'manufacturing_growth' => 1.7,
            ],
            'base_support' => 50,
            'swing_tolerance' => 16,
        ],
        [
            'name' => 'Minnesota',
            'abbr' => 'MN',
            'fips' => '27',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Progressive-leaning state with strong social policies and stable economic base.',
                'leans' => [
                    'economic' => 'mixed',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'green-leaning',
                    'social' => 'progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['healthcare', 'education', 'jobs'],
                'tensions' => ['urban vs rural divide'],
            ],
            'policy_bias' => [
                'strong_support' => ['public_services', 'worker_protection'],
                'lean_support' => ['climate_action'],
                'no_effect' => [],
                'lean_oppose' => ['fossil_expansion', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'public_services' => 1.6,
            ],
            'base_support' => 56,
            'swing_tolerance' => 10,
        ],
        [
            'name' => 'Mississippi',
            'abbr' => 'MS',
            'fips' => '28',
            'color' => 'red',
            'identity' => [
                'overview' => 'Deep conservative state focused on low regulation, traditional values, and economic survival.',
                'leans' => [
                    'economic' => 'low-cost, pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'strongly conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'cost of living', 'agriculture'],
                'tensions' => ['poverty vs development'],
            ],
            'policy_bias' => [
                'strong_support' => ['deregulation', 'tax_cuts', 'job_growth'],
                'lean_support' => ['fossil_expansion', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.5,
            ],
            'base_support' => 36,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'Missouri',
            'abbr' => 'MO',
            'fips' => '29',
            'color' => 'red',
            'identity' => [
                'overview' => 'Leaning conservative state with mix of urban and rural priorities.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'moderate to anti',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'economy', 'cost of living'],
                'tensions' => ['urban vs rural'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth'],
                'lean_support' => ['deregulation', 'tax_cuts', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.4,
            ],
            'base_support' => 43,
            'swing_tolerance' => 10,
        ],
        [
            'name' => 'Montana',
            'abbr' => 'MT',
            'fips' => '30',
            'color' => 'red',
            'identity' => [
                'overview' => 'Rural state valuing independence, natural resources, and limited government.',
                'leans' => [
                    'economic' => 'resource-based',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'anti-federal control',
                ],
                'priorities' => ['energy', 'jobs', 'land use'],
                'tensions' => ['environment vs economic use'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation', 'tax_cuts', 'nuclear_energy'],
                'lean_support' => ['job_growth', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => ['climate_restrictions'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 1.8,
            ],
            'base_support' => 41,
            'swing_tolerance' => 10,
        ],
        [
            'name' => 'Nebraska',
            'abbr' => 'NE',
            'fips' => '31',
            'color' => 'red',
            'identity' => [
                'overview' => 'Agriculture-focused conservative state prioritizing stability and low regulation.',
                'leans' => [
                    'economic' => 'agriculture-based',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'mixed',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['agriculture', 'jobs', 'cost of living'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['agriculture_support', 'deregulation'],
                'lean_support' => ['job_growth', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'agriculture' => 1.7,
            ],
            'base_support' => 38,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'Nevada',
            'abbr' => 'NV',
            'fips' => '32',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Swing state driven by tourism, service jobs, and economic volatility.',
                'leans' => [
                    'economic' => 'service-based',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'mixed',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'economy', 'tourism'],
                'tensions' => ['economic instability vs growth'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'social_security'],
                'lean_support' => ['economic_stability', 'infrastructure_spending'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.6,
            ],
            'base_support' => 50,
            'swing_tolerance' => 16,
        ],
        [
            'name' => 'New Hampshire',
            'abbr' => 'NH',
            'fips' => '33',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Independent-minded state valuing low taxes, local control, and moderation.',
                'leans' => [
                    'economic' => 'low-tax, pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'mixed',
                    'social' => 'moderate',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['taxes', 'economy', 'local control'],
                'tensions' => ['libertarian vs moderate governance'],
            ],
            'policy_bias' => [
                'strong_support' => ['tax_cuts'],
                'lean_support' => ['deregulation'],
                'no_effect' => [],
                'lean_oppose' => ['federal_expansion'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'tax_cuts' => 1.6,
            ],
            'base_support' => 52,
            'swing_tolerance' => 14,
        ],
        [
            'name' => 'New Jersey',
            'abbr' => 'NJ',
            'fips' => '34',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Urbanized, Democratic-leaning state focused on public services and cost of living.',
                'leans' => [
                    'economic' => 'mixed',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'mixed',
                    'social' => 'progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['cost of living', 'jobs', 'public services'],
                'tensions' => ['tax burden vs service quality'],
            ],
            'policy_bias' => [
                'strong_support' => ['public_services', 'worker_protection'],
                'lean_support' => ['job_growth'],
                'no_effect' => [],
                'lean_oppose' => ['deregulation', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'public_services' => 1.6,
            ],
            'base_support' => 59,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'New Mexico',
            'abbr' => 'NM',
            'fips' => '35',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Democratic-leaning state balancing energy production with strong public sector and social programs.',
                'leans' => [
                    'economic' => 'mixed, government-influenced',
                    'regulatory' => 'moderate to pro',
                    'energy' => 'mixed (fossil + renewables)',
                    'social' => 'progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['jobs', 'energy', 'public services'],
                'tensions' => ['energy economy vs environmental concerns'],
            ],
            'policy_bias' => [
                'strong_support' => ['public_services'],
                'lean_support' => ['job_growth', 'renewable_investment'],
                'no_effect' => [],
                'lean_oppose' => ['deregulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'energy' => 1.5,
            ],
            'base_support' => 55,
            'swing_tolerance' => 11,
        ],
        [
            'name' => 'New York',
            'abbr' => 'NY',
            'fips' => '36',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Deeply progressive, urban-driven state prioritizing regulation, public services, and social policy.',
                'leans' => [
                    'economic' => 'mixed, finance-driven',
                    'regulatory' => 'strongly pro-regulation',
                    'energy' => 'green-focused',
                    'social' => 'strongly progressive',
                    'federal' => 'supports federal expansion',
                ],
                'priorities' => ['economy', 'public services', 'inequality', 'climate'],
                'tensions' => ['NYC vs upstate divide'],
            ],
            'policy_bias' => [
                'strong_support' => ['public_services', 'worker_protection', 'climate_action', 'housing_affordability'],
                'lean_support' => ['economic_stability', 'minimum_wage', 'criminal_justice'],
                'no_effect' => [],
                'lean_oppose' => ['tax_cuts', 'nuclear_energy'],
                'strong_oppose' => ['deregulation', 'fossil_expansion', 'border_enforcement'],
            ],
            'tag_weights' => [
                'public_services' => 1.7,
                'climate_action' => 1.8,
            ],
            'base_support' => 64,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'North Carolina',
            'abbr' => 'NC',
            'fips' => '37',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Competitive swing state balancing economic growth, urban expansion, and traditional values.',
                'leans' => [
                    'economic' => 'pro-business growth',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'mixed',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'economy', 'cost of living'],
                'tensions' => ['urban vs rural divide'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth'],
                'lean_support' => ['business_incentives'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.5,
            ],
            'base_support' => 49,
            'swing_tolerance' => 15,
        ],
        [
            'name' => 'North Dakota',
            'abbr' => 'ND',
            'fips' => '38',
            'color' => 'red',
            'identity' => [
                'overview' => 'Energy-heavy conservative state reliant on oil production and low regulation.',
                'leans' => [
                    'economic' => 'resource-based',
                    'regulatory' => 'strongly anti-regulation',
                    'energy' => 'heavily fossil-dependent',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['energy', 'jobs', 'economy'],
                'tensions' => ['economic dependence on energy'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation'],
                'lean_support' => ['job_growth'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => ['climate_restrictions'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 2.0,
            ],
            'base_support' => 35,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'Ohio',
            'abbr' => 'OH',
            'fips' => '39',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Key swing state with strong manufacturing base and economic recovery focus.',
                'leans' => [
                    'economic' => 'industrial',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'lean conservative',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'manufacturing', 'economy'],
                'tensions' => ['industrial decline vs modernization'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'manufacturing_growth', 'trade_tariffs', 'opioid_crisis'],
                'lean_support' => ['deregulation', 'fossil_expansion', 'infrastructure_spending'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'manufacturing_growth' => 1.7,
            ],
            'base_support' => 47,
            'swing_tolerance' => 14,
        ],
        [
            'name' => 'Oklahoma',
            'abbr' => 'OK',
            'fips' => '40',
            'color' => 'red',
            'identity' => [
                'overview' => 'Deep conservative state centered on energy production and limited government.',
                'leans' => [
                    'economic' => 'resource-based',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'strong fossil focus',
                    'social' => 'strongly conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['energy', 'jobs', 'economy'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation', 'tax_cuts'],
                'lean_support' => ['job_growth'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => ['climate_restrictions'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 2.0,
            ],
            'base_support' => 34,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'Oregon',
            'abbr' => 'OR',
            'fips' => '41',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Progressive state prioritizing environment, social policy, and quality of life.',
                'leans' => [
                    'economic' => 'mixed',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'green-focused',
                    'social' => 'progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['climate', 'environment', 'quality of life'],
                'tensions' => ['urban vs rural divide'],
            ],
            'policy_bias' => [
                'strong_support' => ['climate_action', 'environmental_protection'],
                'lean_support' => ['public_services'],
                'no_effect' => [],
                'lean_oppose' => ['fossil_expansion', 'nuclear_energy'],
                'strong_oppose' => ['deregulation'],
            ],
            'tag_weights' => [
                'climate_action' => 1.9,
            ],
            'base_support' => 59,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'Pennsylvania',
            'abbr' => 'PA',
            'fips' => '42',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Genuinely conflicted swing state where energy jobs compete with environmental and suburban concerns.',
                'leans' => [
                    'economic' => 'industrial',
                    'regulatory' => 'mixed (pro-business in west, pro-regulation in suburbs)',
                    'energy' => 'conflicted (fossil-dependent east, environmental focus elsewhere)',
                    'social' => 'mixed',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'environment', 'economy', 'suburban concerns'],
                'tensions' => ['energy jobs vs environmental protection', 'urban vs rural', 'suburban moderate vs working class'],
            ],
            'policy_bias' => [
                'strong_support' => ['opioid_crisis'],
                'lean_support' => ['job_growth', 'manufacturing_growth', 'infrastructure_spending', 'trade_tariffs'],
                'no_effect' => [],
                'lean_oppose' => ['fossil_expansion'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [],
            'base_support' => 50,
            'swing_tolerance' => 16,
        ],
        [
            'name' => 'Rhode Island',
            'abbr' => 'RI',
            'fips' => '44',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Small, Democratic-leaning state focused on public services and stability.',
                'leans' => [
                    'economic' => 'mixed',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'mixed',
                    'social' => 'progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['jobs', 'public services'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['public_services'],
                'lean_support' => ['job_growth'],
                'no_effect' => [],
                'lean_oppose' => ['deregulation', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [],
            'base_support' => 61,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'South Carolina',
            'abbr' => 'SC',
            'fips' => '45',
            'color' => 'red',
            'identity' => [
                'overview' => 'Conservative state focused on economic growth, low regulation, and traditional values.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'economy', 'cost of living'],
                'tensions' => ['growth vs infrastructure needs'],
            ],
            'policy_bias' => [
                'strong_support' => ['deregulation', 'job_growth'],
                'lean_support' => ['fossil_expansion', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.5,
            ],
            'base_support' => 41,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'South Dakota',
            'abbr' => 'SD',
            'fips' => '46',
            'color' => 'red',
            'identity' => [
                'overview' => 'Rural conservative state focused on agriculture, low taxes, and limited government.',
                'leans' => [
                    'economic' => 'agriculture-based',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['agriculture', 'jobs', 'cost of living'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['agriculture_support', 'deregulation', 'tax_cuts'],
                'lean_support' => ['job_growth', 'fossil_expansion'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'agriculture' => 1.7,
            ],
            'base_support' => 36,
            'swing_tolerance' => 7,
        ],
        [
            'name' => 'Tennessee',
            'abbr' => 'TN',
            'fips' => '47',
            'color' => 'red',
            'identity' => [
                'overview' => 'Conservative state with strong business growth and manufacturing expansion.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'fossil-friendly',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'manufacturing', 'economy'],
                'tensions' => ['growth vs infrastructure'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'manufacturing_growth', 'deregulation'],
                'lean_support' => ['tax_cuts', 'fossil_expansion', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'manufacturing_growth' => 1.6,
            ],
            'base_support' => 39,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'Texas',
            'abbr' => 'TX',
            'fips' => '48',
            'color' => 'red',
            'identity' => [
                'overview' => 'Large conservative powerhouse prioritizing energy, economic growth, and minimal regulation.',
                'leans' => [
                    'economic' => 'strong pro-business',
                    'regulatory' => 'strongly anti-regulation',
                    'energy' => 'heavily fossil-focused',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['energy', 'jobs', 'economy', 'business growth'],
                'tensions' => ['urban growth vs traditional industries'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation', 'energy_independence', 'job_growth', 'border_security', 'nuclear_energy', 'defense_spending'],
                'lean_support' => ['tax_cuts', 'manufacturing_growth', 'border_enforcement', 'military_strength', 'trade_tariffs'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation', 'minimum_wage', 'pharmaceutical_pricing'],
                'strong_oppose' => ['climate_restrictions'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 2.0,
                'energy_independence' => 1.8,
                'jobs' => 1.5,
            ],
            'base_support' => 45,
            'swing_tolerance' => 12,
        ],
        [
            'name' => 'Utah',
            'abbr' => 'UT',
            'fips' => '49',
            'color' => 'red',
            'identity' => [
                'overview' => 'Conservative but stable state valuing economic growth, family structure, and business development.',
                'leans' => [
                    'economic' => 'pro-business',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'mixed',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'economy', 'quality of life'],
                'tensions' => ['growth vs environment'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'business_incentives'],
                'lean_support' => ['deregulation', 'border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.5,
            ],
            'base_support' => 40,
            'swing_tolerance' => 9,
        ],
        [
            'name' => 'Vermont',
            'abbr' => 'VT',
            'fips' => '50',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Highly progressive, rural state prioritizing environment, healthcare, and social equality.',
                'leans' => [
                    'economic' => 'mixed',
                    'regulatory' => 'strongly pro-regulation',
                    'energy' => 'renewable-focused',
                    'social' => 'strongly progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['climate', 'healthcare', 'equality'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['climate_action', 'renewable_investment', 'healthcare_expansion'],
                'lean_support' => ['public_services'],
                'no_effect' => [],
                'lean_oppose' => ['tax_cuts', 'nuclear_energy'],
                'strong_oppose' => ['fossil_expansion', 'deregulation'],
            ],
            'tag_weights' => [
                'climate_action' => 2.0,
            ],
            'base_support' => 69,
            'swing_tolerance' => 5,
        ],
        [
            'name' => 'Virginia',
            'abbr' => 'VA',
            'fips' => '51',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Competitive state balancing federal workforce, suburban growth, and mixed political identity.',
                'leans' => [
                    'economic' => 'mixed, government-influenced',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'mixed',
                    'federal' => 'strong federal presence',
                ],
                'priorities' => ['jobs', 'economy', 'public services'],
                'tensions' => ['urban vs rural divide'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth'],
                'lean_support' => ['public_services'],
                'no_effect' => [],
                'lean_oppose' => ['deregulation', 'border_enforcement'],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'jobs' => 1.5,
            ],
            'base_support' => 53,
            'swing_tolerance' => 13,
        ],
        [
            'name' => 'Washington',
            'abbr' => 'WA',
            'fips' => '53',
            'color' => 'blue',
            'identity' => [
                'overview' => 'Progressive state driven by tech economy and strong environmental priorities.',
                'leans' => [
                    'economic' => 'innovation-driven',
                    'regulatory' => 'pro-regulation',
                    'energy' => 'green-focused',
                    'social' => 'progressive',
                    'federal' => 'supports federal role',
                ],
                'priorities' => ['tech', 'climate', 'economy'],
                'tensions' => ['urban tech vs rural industries'],
            ],
            'policy_bias' => [
                'strong_support' => ['climate_action', 'tech_growth', 'renewable_investment', 'privacy_rights', 'antitrust'],
                'lean_support' => ['worker_protection', 'housing_affordability'],
                'no_effect' => [],
                'lean_oppose' => ['fossil_expansion', 'nuclear_energy', 'criminal_justice'],
                'strong_oppose' => ['deregulation'],
            ],
            'tag_weights' => [
                'climate_action' => 1.9,
                'tech_growth' => 1.6,
            ],
            'base_support' => 60,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'West Virginia',
            'abbr' => 'WV',
            'fips' => '54',
            'color' => 'red',
            'identity' => [
                'overview' => 'Energy-dependent conservative state prioritizing jobs, coal, and economic survival.',
                'leans' => [
                    'economic' => 'resource-based',
                    'regulatory' => 'anti-regulation',
                    'energy' => 'heavily fossil-dependent',
                    'social' => 'conservative',
                    'federal' => 'state-focused',
                ],
                'priorities' => ['jobs', 'energy', 'economy'],
                'tensions' => ['economic decline vs transition'],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'job_growth', 'deregulation'],
                'lean_support' => ['border_security', 'border_enforcement'],
                'no_effect' => [],
                'lean_oppose' => ['climate_restrictions'],
                'strong_oppose' => ['environmental_regulation'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 2.0,
            ],
            'base_support' => 33,
            'swing_tolerance' => 8,
        ],
        [
            'name' => 'Wisconsin',
            'abbr' => 'WI',
            'fips' => '55',
            'color' => 'swing',
            'identity' => [
                'overview' => 'Key swing state balancing manufacturing, agriculture, and political moderation.',
                'leans' => [
                    'economic' => 'mixed, industrial',
                    'regulatory' => 'moderate',
                    'energy' => 'mixed',
                    'social' => 'mixed',
                    'federal' => 'balanced',
                ],
                'priorities' => ['jobs', 'manufacturing', 'economy'],
                'tensions' => ['urban vs rural divide'],
            ],
            'policy_bias' => [
                'strong_support' => ['job_growth', 'manufacturing_growth'],
                'lean_support' => ['fossil_expansion'],
                'no_effect' => [],
                'lean_oppose' => [],
                'strong_oppose' => [],
            ],
            'tag_weights' => [
                'manufacturing_growth' => 1.6,
            ],
            'base_support' => 50,
            'swing_tolerance' => 16,
        ],
        [
            'name' => 'Wyoming',
            'abbr' => 'WY',
            'fips' => '56',
            'color' => 'red',
            'identity' => [
                'overview' => 'Small, deeply conservative state driven by energy production and minimal government.',
                'leans' => [
                    'economic' => 'resource-based',
                    'regulatory' => 'strongly anti-regulation',
                    'energy' => 'heavily fossil-dependent',
                    'social' => 'strongly conservative',
                    'federal' => 'strong state control',
                ],
                'priorities' => ['energy', 'jobs', 'economy'],
                'tensions' => [],
            ],
            'policy_bias' => [
                'strong_support' => ['fossil_expansion', 'deregulation', 'tax_cuts'],
                'lean_support' => ['job_growth'],
                'no_effect' => [],
                'lean_oppose' => ['environmental_regulation'],
                'strong_oppose' => ['climate_restrictions'],
            ],
            'tag_weights' => [
                'fossil_expansion' => 2.0,
            ],
            'base_support' => 30,
            'swing_tolerance' => 6,
        ],
    ];

    protected array $voterGroups = [
        ['id' => 'students', 'name' => 'Student Activists', 'color' => 'bg-pink-100 dark:bg-pink-900/30', 'border' => 'border-pink-300 dark:border-pink-700', 'text' => 'text-pink-700 dark:text-pink-300'],
        ['id' => 'yuppie', 'name' => 'Young Urban Professionals', 'color' => 'bg-purple-100 dark:bg-purple-900/30', 'border' => 'border-purple-300 dark:border-purple-700', 'text' => 'text-purple-700 dark:text-purple-300'],
        ['id' => 'young_conservatives', 'name' => 'Young Conservatives', 'color' => 'bg-indigo-100 dark:bg-indigo-900/30', 'border' => 'border-indigo-300 dark:border-indigo-700', 'text' => 'text-indigo-700 dark:text-indigo-300'],
        ['id' => 'working_class', 'name' => 'Working-Class Urban Labor', 'color' => 'bg-orange-100 dark:bg-orange-900/30', 'border' => 'border-orange-300 dark:border-orange-700', 'text' => 'text-orange-700 dark:text-orange-300'],
        ['id' => 'suburban', 'name' => 'Suburban Families', 'color' => 'bg-teal-100 dark:bg-teal-900/30', 'border' => 'border-teal-300 dark:border-teal-700', 'text' => 'text-teal-700 dark:text-teal-300'],
        ['id' => 'rural', 'name' => 'Rural Farmers', 'color' => 'bg-green-100 dark:bg-green-900/30', 'border' => 'border-green-300 dark:border-green-700', 'text' => 'text-green-700 dark:text-green-300'],
        ['id' => 'small_business', 'name' => 'Small Business Owners', 'color' => 'bg-amber-100 dark:bg-amber-900/30', 'border' => 'border-amber-300 dark:border-amber-700', 'text' => 'text-amber-700 dark:text-amber-300'],
        ['id' => 'corporate', 'name' => 'Corporate Executives', 'color' => 'bg-slate-100 dark:bg-slate-900/30', 'border' => 'border-slate-300 dark:border-slate-700', 'text' => 'text-slate-700 dark:text-slate-300'],
        ['id' => 'public_sector', 'name' => 'Public Sector Workers', 'color' => 'bg-cyan-100 dark:bg-cyan-900/30', 'border' => 'border-cyan-300 dark:border-cyan-700', 'text' => 'text-cyan-700 dark:text-cyan-300'],
        ['id' => 'retirees', 'name' => 'Retirees & Seniors', 'color' => 'bg-red-100 dark:bg-red-900/30', 'border' => 'border-red-300 dark:border-red-700', 'text' => 'text-red-700 dark:text-red-300'],
        ['id' => 'minorities', 'name' => 'Minority Communities', 'color' => 'bg-violet-100 dark:bg-violet-900/30', 'border' => 'border-violet-300 dark:border-violet-700', 'text' => 'text-violet-700 dark:text-violet-300'],
        ['id' => 'independents', 'name' => 'Independent Voters', 'color' => 'bg-gray-100 dark:bg-gray-900/30', 'border' => 'border-gray-300 dark:border-gray-700', 'text' => 'text-gray-700 dark:text-gray-300'],
    ];

    public function index()
    {
        $president = Session::get('president');
        
        if (!$president) {
            return redirect('/president');
        }
        
        $gameState = $this->getGameState();
        $currentPhase = $gameState['phase'] ?? 'dashboard';
        
        $data = [
            'gameState' => $gameState,
            'states' => $this->states,
            'voterGroups' => $this->voterGroups,
            'phase' => $currentPhase,
            'president' => $president,
            'scenarios' => $this->getScenariosList(),
        ];

        $eventId = $gameState['current_event_id'] ?? 0;
        
        if ($currentPhase === 'situation' && $eventId > 0) {
            $data['currentEvent'] = $this->events[$eventId] ?? null;
        }
        
        // Handle phases that need the decision array
        if (in_array($currentPhase, ['news', 'state_outlook', 'voter_reaction'])) {
            $event = $eventId > 0 ? ($this->events[$eventId] ?? null) : null;
            
            $decisionArray = null;
            if ($gameState['last_decision'] && isset($gameState['player_raw_response'])) {
                $decisionArray = [
                    'id' => 'custom',
                    'label' => $gameState['last_decision'],
                    'news' => $gameState['ai_news'] ?? [],
                    'voter_reactions' => $gameState['ai_voter_reactions'] ?? [],
                ];
            } elseif ($event) {
                foreach ($event['decisions'] as $decision) {
                    if ($decision['label'] === $gameState['last_decision']) {
                        $decisionArray = $decision;
                        break;
                    }
                }
            }
            
            $data['currentDecision'] = $decisionArray;
            
            if ($currentPhase === 'situation') {
                $data['currentEvent'] = $event;
            }
        }

        return inertia('game/index', $data);
    }

    public function advanceMonth(Request $request)
    {
        $gameState = $this->getGameState();
        
        $currentPhase = $gameState['phase'] ?? 'dashboard';
        
        // Advance logic
        if ($currentPhase === 'dashboard') {
            // First advance: just go to situation, keep month 1, turn 1
            $gameState['turn'] = 1;
        } else {
            // Subsequent advances: increment month and turn
            $gameState['month']++;
            if ($gameState['month'] > 12) {
                $gameState['month'] = 1;
                $gameState['year']++;
            }
            $gameState['turn'] = ($gameState['turn'] ?? 1) + 1;
        }
        
        // Check for pending consequences from database
        $pendingConsequence = $this->getPendingConsequence();
            
            // Randomize events - pick a random unused event
            $usedEvents = $gameState['used_events'] ?? [];
            $availableEvents = array_diff(array_keys($this->events), $usedEvents);
            
            if (empty($availableEvents)) {
                $usedEvents = [];
                $availableEvents = array_keys($this->events);
            }
            
            $forcedZen = isset($gameState['forced_event_id']) && $gameState['forced_event_id'] === 0;
            $isZenMonth = $forcedZen || ($gameState['turn'] % 4 === 0) || (mt_rand(1, 100) <= 20);
            
            if ($isZenMonth) {
                $gameState['phase'] = 'zen';
                $gameState['current_event_id'] = 0;
                $gameState['is_zen_month'] = true;
                $gameState['used_events'] = $usedEvents;
            } else {
                $gameState['phase'] = 'situation';
                
                // If there's a forced consequence, show it first
                if (isset($gameState['force_consequence'])) {
                    $gameState['current_event_id'] = -1;
                    $gameState['consequence'] = $gameState['force_consequence'];
                    unset($gameState['force_consequence']);
                    $gameState['used_events'] = $usedEvents;
                    $gameState['is_zen_month'] = false;
                } elseif ($pendingConsequence) {
                    // If there's a pending consequence, show it
                    $gameState['current_event_id'] = -1;
                    $gameState['consequence'] = $pendingConsequence;
                    $gameState['used_events'] = $usedEvents;
                    $gameState['is_zen_month'] = false;
                } elseif (isset($gameState['forced_event_id']) && $gameState['forced_event_id'] !== 0 && isset($this->events[$gameState['forced_event_id']])) {
                    // Use forced scenario if set (and not 0), otherwise pick random
                    $gameState['current_event_id'] = $gameState['forced_event_id'];
                    $gameState['used_events'] = array_merge($usedEvents, [$gameState['forced_event_id']]);
                    $gameState['is_zen_month'] = false;
                } else {
                    $availableKeys = array_keys($availableEvents);
                    $randomIndex = array_rand($availableKeys);
                    $randomEventId = $availableKeys[$randomIndex];
                    $gameState['current_event_id'] = $randomEventId;
                    $gameState['used_events'] = array_merge($usedEvents, [$randomEventId]);
                    $gameState['is_zen_month'] = false;
                }
            }
        
        $this->saveGameState($gameState);

        $eventData = $gameState['is_zen_month'] ? null : ($this->events[$gameState['current_event_id']] ?? null);

        return Inertia::render('game/index', [
            'gameState' => $gameState,
            'states' => $this->states,
            'voterGroups' => $this->voterGroups,
            'phase' => $gameState['phase'],
            'currentEvent' => $eventData,
            'president' => Session::get('president'),
            'scenarios' => $this->getScenariosList(),
        ]);
    }

    public function makeDecision(Request $request)
    {
        $validated = $request->validate([
            'decision_id' => 'required|string',
            'event_id' => 'required|integer',
        ]);

        $gameState = $this->getGameState();
        $event = $this->events[$validated['event_id']];
        
        if (!$event) {
            return redirect()->back()->with('error', 'Event not found');
        }

        $decision = collect($event['decisions'])->firstWhere('id', $validated['decision_id']);
        
        if (!$decision) {
            return redirect()->back()->with('error', 'Decision not found');
        }

        $gameState['prev_approval'] = $gameState['approval'];
        $gameState['prev_stability'] = $gameState['stability'];
        $gameState['prev_party_support'] = $gameState['party_support'];

        foreach ($decision['effects'] as $stat => $change) {
            $gameState[$stat] = $this->updateStat($gameState[$stat], $change);
        }

        $gameState['last_decision'] = $decision['label'];

        // Generate AI news reactions (or use fallback)
        if (!($gameState['skip_ai_content'] ?? false)) {
            try {
                $aiService = new AIService();
                $newsReactions = $aiService->generateNewsReactions($decision['label'], $event['title']);
                $decision['news'] = $newsReactions;
                $gameState['ai_news'] = $newsReactions;
                $gameState['ai_news_generated'] = true;
            } catch (\Exception $e) {
                $gameState['ai_news'] = $this->getFallbackNews($decision['label']);
                $gameState['ai_news_generated'] = false;
            }
        } else {
            $gameState['ai_news'] = $this->getFallbackNews($decision['label']);
            $gameState['ai_news_generated'] = false;
        }
        
        $gameState['phase'] = 'news';
        
        $this->saveGameState($gameState);

        return Inertia::render('game/index', [
            'gameState' => $gameState,
            'states' => $this->states,
            'voterGroups' => $this->voterGroups,
            'phase' => 'news',
            'currentEvent' => $event,
            'currentDecision' => $decision->toArray(),
            'president' => Session::get('president'),
            'scenarios' => $this->getScenariosList(),
        ]);
    }

    public function makeCustomDecision(Request $request)
    {
        $validated = $request->validate([
            'response' => 'required|string|min:10|max:1000',
            'event_id' => 'nullable|integer',
        ]);

        $gameState = $this->getGameState();
        $eventId = $validated['event_id'] ?? 0;
        $event = $eventId > 0 ? ($this->events[$eventId] ?? null) : null;
        $hasConsequence = isset($gameState['consequence']) && ($eventId === -1 || $gameState['current_event_id'] === -1);
        $isZenMonth = ($eventId === 0 && !$hasConsequence) || ($gameState['is_zen_month'] ?? false);
        
        $eventTitle = $hasConsequence 
            ? ('Consequence: ' . ($gameState['consequence']['title'] ?? 'Untitled Crisis'))
            : ($isZenMonth ? 'Free Month - No Crisis' : ($event['title'] ?? 'General Decision'));

        $gameState['prev_approval'] = $gameState['approval'];
        $gameState['prev_stability'] = $gameState['stability'];
        $gameState['prev_party_support'] = $gameState['party_support'];

        $president = Session::get('president');

        // Analyze player's response with AI
        try {
            $aiService = new AIService();
            
            if ($hasConsequence) {
                $consequenceTitle = $gameState['consequence']['title'] ?? 'Untitled Crisis';
                $analysis = $aiService->analyzePlayerResponse($validated['response'], $consequenceTitle, $president);
            } elseif ($isZenMonth) {
                $analysis = $aiService->analyzeZenResponse($validated['response'], $president);
            } else {
                $analysis = $aiService->analyzePlayerResponse($validated['response'], $event['title'] ?? 'General Decision', $president);
            }
            
            $statChanges = $this->calculateStatChanges($analysis, $eventTitle);
            $gameState['approval'] = $this->updateStat($gameState['approval'], $statChanges['approval']);
            $gameState['stability'] = $this->updateStat($gameState['stability'], $statChanges['stability']);
            $gameState['party_support'] = $this->updateStat($gameState['party_support'], $statChanges['party_support']);
            
            $gameState['last_decision'] = $analysis['label'];
            $gameState['player_raw_response'] = $validated['response'];
            
            // Save decision to database
            $this->saveDecisionToDatabase($validated['response'], $eventTitle, $analysis['decision_tags'] ?? [], $statChanges, $gameState['turn'] ?? 1);
            
            // Generate AI news reactions (or use fallback)
            if (!($gameState['skip_ai_content'] ?? false)) {
                if ($hasConsequence) {
                    $newsReactions = $aiService->generateNewsReactions($validated['response'], $consequenceTitle, $president);
                } elseif ($isZenMonth) {
                    $newsReactions = $aiService->generateZenNewsReactions($validated['response'], $president);
                } else {
                    $newsReactions = $aiService->generateNewsReactions($validated['response'], $event['title'] ?? 'General Decision', $president);
                }
                $gameState['ai_news'] = $newsReactions;
                $gameState['ai_news_generated'] = true;
            } else {
                $gameState['ai_news'] = $this->getFallbackNews($analysis['label']);
                $gameState['ai_news_generated'] = false;
            }
            
        } catch (\Exception $e) {
            Log::error('AI processing failed in makeCustomDecision', ['error' => $e->getMessage()]);
            // Fallback if AI fails
            $gameState['approval'] = $this->updateStat($gameState['approval'], 0);
            $gameState['stability'] = $this->updateStat($gameState['stability'], 0);
            $gameState['party_support'] = $this->updateStat($gameState['party_support'], 0);
            $gameState['last_decision'] = substr($validated['response'], 0, 50);
            $gameState['player_raw_response'] = $validated['response'];
            $gameState['ai_news'] = $this->getFallbackNews($gameState['last_decision']);
            $gameState['ai_news_generated'] = false;
        }
        
        $gameState['phase'] = 'news';
        $gameState['is_zen_month'] = false;
        
        // Clear consequence after it's been responded to
        if ($hasConsequence) {
            unset($gameState['consequence']);
        }
        
        $this->saveGameState($gameState);

        $decisionArray = [
            'id' => 'custom',
            'label' => $gameState['last_decision'],
            'effects' => [],
            'news' => $gameState['ai_news'] ?? [],
        ];

        return Inertia::render('game/index', [
            'gameState' => $gameState,
            'states' => $this->states,
            'voterGroups' => $this->voterGroups,
            'phase' => 'news',
            'currentEvent' => $event,
            'currentDecision' => $decisionArray,
            'president' => Session::get('president'),
            'scenarios' => $this->getScenariosList(),
        ]);
    }

    public function goToStateOutlook(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect('/');
        }
        
        $gameState = $this->getGameState();
        $gameState['prev_phase'] = $gameState['phase'];
        
        $eventId = $gameState['current_event_id'] ?? 0;
        $event = $eventId > 0 ? ($this->events[$eventId] ?? null) : null;
        
        // Try to find preset decision, or use custom decision
        $decisionArray = null;
        
        // Check if this is a custom decision
        if ($gameState['last_decision'] && isset($gameState['player_raw_response'])) {
            $decisionArray = [
                'id' => 'custom',
                'label' => $gameState['last_decision'],
                'news' => $gameState['ai_news'] ?? [],
            ];
        } elseif ($event) {
            // Find preset decision
            foreach ($event['decisions'] as $decision) {
                if ($decision['label'] === $gameState['last_decision']) {
                    $decisionArray = $decision;
                    break;
                }
            }
        }

        if (!$decisionArray) {
            return redirect('/');
        }

        // If AI news was generated, merge it
        if (isset($gameState['ai_news']) && $gameState['ai_news_generated']) {
            $decisionArray['news'] = $gameState['ai_news'];
        }
        
        $isZenMonth = $eventId === 0 || $gameState['is_zen_month'] ?? false;
        $eventTitle = $isZenMonth ? 'Free Month - No Crisis' : ($event['title'] ?? 'General Decision');

        $gameState['phase'] = 'state_outlook';
        
        $playerResponse = $gameState['player_raw_response'] ?? $gameState['last_decision'] ?? '';
        $president = Session::get('president');
        
        if ($gameState['skip_ai_content'] ?? false) {
            $rawReactions = $this->calculateStateReactions($decisionArray, $gameState, $eventTitle);
        } else {
            try {
                $aiService = new AIService();
                $rawReactions = $aiService->generateStateReactions($playerResponse, $president, $this->states);
            } catch (\Exception $e) {
                Log::error('AI state reactions failed, falling back to formula', ['error' => $e->getMessage()]);
                $rawReactions = $this->calculateStateReactions($decisionArray, $gameState, $eventTitle);
            }
            
            if (empty($rawReactions)) {
                $rawReactions = $this->calculateStateReactions($decisionArray, $gameState, $eventTitle);
            }
        }
        
        $stateBands = [];
        $swingStates = $this->getSwingStates();
        foreach ($this->states as $state) {
            $fips = $state['fips'];
            $score = $rawReactions[$fips] ?? 50;
            $band = $this->getStateBand($score);
            $isSwing = in_array($state['abbr'], $swingStates);
            $isCompetitive = $isSwing && in_array($band, ['neutral', 'leans_support', 'leans_oppose']);
            $stateBands[$fips] = [
                'band' => $band,
                'is_competitive' => $isCompetitive,
            ];
        }
        
        $gameState['state_reactions'] = $rawReactions;
        $gameState['state_bands'] = $stateBands;
        
        $this->saveGameState($gameState);

        return Inertia::render('game/index', [
            'gameState' => $gameState,
            'states' => $this->states,
            'voterGroups' => $this->voterGroups,
            'phase' => 'state_outlook',
            'currentEvent' => $event,
            'currentDecision' => $decisionArray,
            'president' => Session::get('president'),
            'scenarios' => $this->getScenariosList(),
        ]);
    }

    protected function getDecisionIdByLabel(array $event, string $label): ?string
    {
        foreach ($event['decisions'] as $decision) {
            if ($decision['label'] === $label) {
                return $decision['id'];
            }
        }
        return null;
    }

    public function goToVoterReactions(Request $request)
    {
        if ($request->isMethod('GET')) {
            return redirect('/');
        }
        
        $gameState = $this->getGameState();
        $gameState['prev_phase'] = $gameState['phase'];
        $gameState['phase'] = 'voter_reaction';
        
        $eventId = $gameState['current_event_id'] ?? 0;
        $event = $eventId > 0 ? ($this->events[$eventId] ?? null) : null;
        
        // Try to find preset decision, or use custom decision
        $decisionArray = null;
        
        // Check if this is a custom decision
        if ($gameState['last_decision'] && isset($gameState['player_raw_response'])) {
            $decisionArray = [
                'id' => 'custom',
                'label' => $gameState['last_decision'],
                'news' => $gameState['ai_news'] ?? [],
            ];
        } elseif ($event) {
            // Find preset decision
            foreach ($event['decisions'] as $decision) {
                if ($decision['label'] === $gameState['last_decision']) {
                    $decisionArray = $decision;
                    break;
                }
            }
        }

        if (!$decisionArray) {
            return redirect('/');
        }

        // Get news reactions for context
        $newsReactions = [
            'left' => ['headline' => ($decisionArray['news']['left']['headline'] ?? $gameState['ai_news']['left']['headline'] ?? '')],
            'center' => ['headline' => ($decisionArray['news']['center']['headline'] ?? $gameState['ai_news']['center']['headline'] ?? '')],
            'right' => ['headline' => ($decisionArray['news']['right']['headline'] ?? $gameState['ai_news']['right']['headline'] ?? '')],
        ];

        $president = Session::get('president');

        // Generate AI voter reactions (or use fallback)
        if (!($gameState['skip_ai_content'] ?? false)) {
            try {
                $aiService = new AIService();
                $rawInput = $gameState['player_raw_response'] ?? $gameState['last_decision'];
                $voterReactions = $aiService->generateVoterReactions(
                    $rawInput,
                    $event ? $event['title'] : 'General Decision',
                    $newsReactions,
                    $president
                );
                $decisionArray['voter_reactions'] = $voterReactions;
                $gameState['ai_voter_reactions'] = $voterReactions;
                $gameState['ai_voter_generated'] = true;
            } catch (\Exception $e) {
                Log::error('Voter reactions AI failed', ['error' => $e->getMessage()]);
                $decisionArray['voter_reactions'] = $this->getFallbackVoterReactions();
                $gameState['ai_voter_reactions'] = $this->getFallbackVoterReactions();
                $gameState['ai_voter_generated'] = false;
            }
        } else {
            $decisionArray['voter_reactions'] = $this->getFallbackVoterReactions();
            $gameState['ai_voter_reactions'] = $this->getFallbackVoterReactions();
            $gameState['ai_voter_generated'] = false;
        }
        
        $this->saveGameState($gameState);

        return Inertia::render('game/index', [
            'gameState' => $gameState,
            'states' => $this->states,
            'voterGroups' => $this->voterGroups,
            'phase' => 'voter_reaction',
            'currentEvent' => $event,
            'currentDecision' => $decisionArray,
            'president' => Session::get('president'),
            'scenarios' => $this->getScenariosList(),
        ]);
    }

    public function returnToDashboard(Request $request)
    {
        $gameState = $this->getGameState();
        
        // Only advance month if coming FROM voter_reaction phase (completed a decision cycle)
        $currentPhase = $gameState['phase'] ?? 'dashboard';
        $shouldAdvance = ($currentPhase === 'voter_reaction');
        
        if ($shouldAdvance) {
            $gameState['month']++;
            if ($gameState['month'] > 12) {
                $gameState['month'] = 1;
                $gameState['year']++;
            }
            $gameState['turn'] = ($gameState['turn'] ?? 0) + 1;
            
            // Check for pending consequences
            $pendingConsequence = $this->getPendingConsequence();
            
            // Randomize events - pick a random unused event for next turn
            $usedEvents = $gameState['used_events'] ?? [];
            $availableEvents = array_diff(array_keys($this->events), $usedEvents);
            
            if (empty($availableEvents)) {
                $usedEvents = [];
                $availableEvents = array_keys($this->events);
            }
            
            $forcedZen = isset($gameState['forced_event_id']) && $gameState['forced_event_id'] === 0;
            $isZenMonth = $forcedZen || ($gameState['turn'] % 4 === 0) || (mt_rand(1, 100) <= 20);
            
            if ($isZenMonth) {
                $gameState['phase'] = 'zen';
                $gameState['current_event_id'] = 0;
                $gameState['is_zen_month'] = true;
                $gameState['used_events'] = $usedEvents;
            } else {
                $gameState['phase'] = 'situation';
                
                // If there's a pending consequence, show it instead
                if ($pendingConsequence) {
                    $gameState['current_event_id'] = -1; // Special ID for consequences
                    $gameState['consequence'] = $pendingConsequence;
                    $gameState['used_events'] = $usedEvents;
                    $gameState['is_zen_month'] = false;
                } elseif (isset($gameState['forced_event_id']) && $gameState['forced_event_id'] !== 0 && isset($this->events[$gameState['forced_event_id']])) {
                    // Use forced scenario if set (and not 0), otherwise pick random
                    $gameState['current_event_id'] = $gameState['forced_event_id'];
                    $gameState['used_events'] = array_merge($usedEvents, [$gameState['forced_event_id']]);
                    $gameState['is_zen_month'] = false;
                } else {
                    $availableKeys = array_keys($availableEvents);
                    $randomIndex = array_rand($availableKeys);
                    $randomEventId = $availableKeys[$randomIndex];
                    $gameState['current_event_id'] = $randomEventId;
                    $gameState['used_events'] = array_merge($usedEvents, [$randomEventId]);
                    $gameState['is_zen_month'] = false;
                }
            }
        } else {
            $gameState['phase'] = 'dashboard';
        }
        
        $this->saveGameState($gameState);

        if ($request->isMethod('GET')) {
            return redirect('/');
        }

        $gameOver = null;
        if ($shouldAdvance) {
            $gameOver = $this->checkGameOver($gameState);
            if ($gameOver) {
                $gameState['game_over'] = $gameOver;
                $gameState['phase'] = 'game_over';
                $this->saveGameState($gameState);
                return Inertia::render('game/index', [
                    'gameState' => $gameState,
                    'states' => $this->states,
                    'voterGroups' => $this->voterGroups,
                    'phase' => 'game_over',
                    'president' => Session::get('president'),
                    'scenarios' => $this->getScenariosList(),
                ]);
            }
            
            if (($gameState['turn'] ?? 0) >= 24) {
                $gameState['phase'] = 'midterm';
                $this->saveGameState($gameState);
                return Inertia::render('game/index', [
                    'gameState' => $gameState,
                    'states' => $this->states,
                    'voterGroups' => $this->voterGroups,
                    'phase' => 'midterm',
                    'president' => Session::get('president'),
                    'scenarios' => $this->getScenariosList(),
                ]);
            }
        }

        return Inertia::render('game/index', [
            'gameState' => $gameState,
            'states' => $this->states,
            'voterGroups' => $this->voterGroups,
            'phase' => 'dashboard',
            'president' => Session::get('president'),
            'scenarios' => $this->getScenariosList(),
        ]);
    }

    public function reset()
    {
        Session::forget('game_state');
        Session::forget('president');
        return redirect('/president');
    }

    public function toggleAiContent()
    {
        $gameState = $this->getGameState();
        $gameState['skip_ai_content'] = !($gameState['skip_ai_content'] ?? false);
        $this->saveGameState($gameState);
        
        return response()->json([
            'skip_ai_content' => $gameState['skip_ai_content'],
        ]);
    }

    public function setTestScenario(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'nullable|integer',
        ]);
        
        $gameState = $this->getGameState();
        
        $eventId = $validated['event_id'] ?? null;
        
        if ($eventId === 0) {
            $gameState['forced_event_id'] = 0;
        } elseif ($eventId && isset($this->events[$eventId])) {
            $gameState['forced_event_id'] = (int)$eventId;
        } else {
            unset($gameState['forced_event_id']);
        }
        
        $this->saveGameState($gameState);
        
        $eventName = null;
        if (isset($gameState['forced_event_id'])) {
            if ($gameState['forced_event_id'] === 0) {
                $eventName = 'Zen Month - Free Choice';
            } else {
                $eventName = $this->events[$gameState['forced_event_id']]['title'] ?? null;
            }
        }
        
        return response()->json([
            'forced_event_id' => $gameState['forced_event_id'] ?? null,
            'event_name' => $eventName,
        ]);
    }

    protected function getScenariosList(): array
    {
        $scenarios = [['id' => 0, 'title' => 'Zen Month - Free Choice']];
        foreach ($this->events as $id => $event) {
            $scenarios[] = [
                'id' => $id,
                'title' => $event['title'],
            ];
        }
        return $scenarios;
    }

    public function getScenarios()
    {
        return response()->json($this->getScenariosList());
    }

    public function saveGame(Request $request)
    {
        $request->validate([
            'save_name' => 'required|string|max:50',
        ]);
        
        try {
            $gameService = new GameService();
            $game = $gameService->getActiveGame();
            
            if (!$game) {
                return response()->json(['error' => 'No active game'], 400);
            }
            
            $fullState = Session::get('game_state', []);
            
            $save = $gameService->saveGame($game, $request->save_name, $fullState);
            
            return response()->json(['success' => true, 'save_id' => $save->id]);
        } catch (\Exception $e) {
            Log::error('Failed to save game', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to save game'], 500);
        }
    }

    public function forceConsequence()
    {
        try {
            $gameService = new GameService();
            $game = $gameService->getActiveGame();
            
            if (!$game) {
                $president = Session::get('president');
                if ($president && isset($president['starting_stats'])) {
                    $game = Game::create([
                        'session_id' => Session::getId(),
                        'president_name' => $president['name'] ?? 'Unknown',
                        'president_party' => $president['party'] ?? 'independent',
                        'president_ideology' => $president['ideology'] ?? 'moderate',
                        'current_turn' => 1,
                        'current_month' => 1,
                        'current_year' => 2025,
                        'approval' => $president['starting_stats']['approval'] ?? 50,
                        'stability' => $president['starting_stats']['stability'] ?? 50,
                        'party_support' => $president['starting_stats']['party_support'] ?? 50,
                        'current_phase' => 'dashboard',
                        'is_active' => true,
                        'used_events' => [],
                    ]);
                } else {
                    return redirect('/');
                }
            }
            
            $gameState = $this->getGameState();
            $president = Session::get('president');
            
            // Get recent decisions or create a fake one for testing
            $recentDecisions = $gameService->getRecentDecisions($game, 5);
            
            if ($recentDecisions->isEmpty()) {
                // Create a fake decision for testing
                $gameService->saveDecision(
                    $game,
                    $gameState['turn'] ?? 1,
                    'Border Crisis',
                    'I will implement hardline immigration enforcement with increased border patrol',
                    ['hardline_immigration', 'enforcement', 'border_security'],
                    ['approval' => 3, 'stability' => -2, 'party_support' => 5]
                );
                $recentDecisions = $gameService->getRecentDecisions($game, 5);
            }
            
            $aiService = new AIService();
            $consequenceData = $aiService->generateConsequence($recentDecisions->toArray(), $president);
            
            if ($consequenceData && isset($consequenceData['title'])) {
                $consequence = $gameService->createConsequence(
                    $game,
                    $consequenceData['title'],
                    $consequenceData['description'],
                    $gameState['turn'] ?? 1,
                    $consequenceData['tags'] ?? []
                );
                
                // Set up state to show consequence immediately
                $gameState['current_event_id'] = -1;
                $gameState['consequence'] = [
                    'id' => $consequence->id,
                    'title' => $consequenceData['title'],
                    'description' => $consequenceData['description'],
                ];
                $gameState['phase'] = 'situation';
                $gameState['last_decision'] = 'Test Decision for Consequence';
                $gameState['player_raw_response'] = 'Test response';
                $gameState['used_events'] = $gameState['used_events'] ?? [];
                $gameState['is_zen_month'] = false;
                unset($gameState['forced_event_id']); // Clear any forced scenario
                
                $this->saveGameState($gameState);
                
                return Inertia::render('game/index', [
                    'gameState' => $gameState,
                    'states' => $this->states,
                    'voterGroups' => $this->voterGroups,
                    'phase' => 'situation',
                    'currentEvent' => null,
                    'president' => $president,
                    'scenarios' => $this->getScenariosList(),
                ]);
            }
            
            return redirect('/');
        } catch (\Exception $e) {
            Log::error('Failed to force consequence', ['error' => $e->getMessage()]);
            return redirect('/');
        }
    }

    public function clearData()
    {
        try {
            \DB::table('consequences')->delete();
            \DB::table('player_decisions')->delete();
            \DB::table('game_saves')->delete();
            \DB::table('games')->delete();
            
            Session::forget('game_state');
            Session::forget('president');
            
            return redirect('/president');
        } catch (\Exception $e) {
            Log::error('Failed to clear data', ['error' => $e->getMessage()]);
            return redirect('/');
        }
    }

    public function getSaves()
    {
        try {
            // Get all games for this session
            $games = Game::where('session_id', Session::getId())->pluck('id');
            
            if ($games->isEmpty()) {
                return response()->json([]);
            }
            
            // Get all saves for all games in this session
            $saves = GameSave::whereIn('game_id', $games)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'game_id', 'save_name', 'created_at']);
            
            // Add president name to each save
            $gamesMap = Game::whereIn('id', $games)->pluck('president_name', 'id');
            $saves = $saves->map(function ($save) use ($gamesMap) {
                $save->president_name = $gamesMap[$save->game_id] ?? 'Unknown';
                return $save;
            });
            
            return response()->json($saves);
        } catch (\Exception $e) {
            Log::error('Failed to get saves', ['error' => $e->getMessage()]);
            return response()->json([]);
        }
    }

    public function deleteSave($id)
    {
        try {
            $save = GameSave::find($id);
            if ($save) {
                $save->delete();
            }
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to delete save', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to delete save'], 500);
        }
    }

    public function loadGame(Request $request)
    {
        $request->validate([
            'save_id' => 'required|integer',
        ]);
        
        try {
            $gameService = new GameService();
            
            // Find the save first
            $save = GameSave::find($request->save_id);
            
            if (!$save) {
                return response()->json(['error' => 'Save not found'], 404);
            }
            
            // Get the game this save belongs to
            $saveGame = $save->game;
            
            if (!$saveGame) {
                return response()->json(['error' => 'Game not found'], 404);
            }
            
            // Deactivate all games for this session
            Game::where('session_id', Session::getId())->update(['is_active' => false]);
            
            // Activate this game and update its president info
            $saveGame->update([
                'is_active' => true,
                'president_name' => $saveGame->president_name,
                'president_party' => $saveGame->president_party,
                'president_ideology' => $saveGame->president_ideology,
            ]);
            
            // Build president session from the game data
            $presidentSession = [
                'name' => $saveGame->president_name,
                'party' => $saveGame->president_party,
                'ideology' => $saveGame->president_ideology,
                'starting_stats' => [
                    'approval' => $saveGame->approval,
                    'stability' => $saveGame->stability,
                    'party_support' => $saveGame->party_support,
                ],
            ];
            
            Session::put('president', $presidentSession);
            
            // Load the state snapshot
            $stateSnapshot = $gameService->loadGame($save);
            Session::put('game_state', $stateSnapshot);
            
            return redirect('/');
        } catch (\Exception $e) {
            Log::error('Failed to load game', ['error' => $e->getMessage()]);
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'Failed to load game: ' . $e->getMessage()], 500);
        }
    }

    protected function getGameState(): array
    {
        $state = Session::get('game_state');
        $president = Session::get('president');
        
        $defaultStats = [
            'approval' => $president['starting_stats']['approval'] ?? 50,
            'stability' => $president['starting_stats']['stability'] ?? $president['starting_stats']['economy'] ?? 50,
            'party_support' => $president['starting_stats']['party_support'] ?? 50,
        ];
        
        $defaultState = [
            'month' => 1,
            'year' => 2025,
            'turn' => 1,
            'phase' => 'dashboard',
            'approval' => $defaultStats['approval'],
            'stability' => $defaultStats['stability'],
            'party_support' => $defaultStats['party_support'],
            'last_decision' => null,
            'current_event_id' => 1,
            'prev_approval' => null,
            'prev_stability' => null,
            'prev_party_support' => null,
            'used_events' => [],
            'is_zen_month' => false,
            'skip_ai_content' => false,
        ];
        
        if (!$state) {
            return $defaultState;
        }

        // Merge defaults with saved state, saved state takes precedence
        return array_merge($defaultState, $state);
    }

    protected function saveGameState(array $state): void
    {
        Session::put('game_state', $state);
    }

    protected function updateStat(int $current, int $change): int
    {
        $new = $current + $change;
        return max(0, min(100, $new));
    }

    protected function checkGameOver(array $gameState): ?array
    {
        $approval = $gameState['approval'] ?? 50;
        $stability = $gameState['stability'] ?? 50;
        $partySupport = $gameState['party_support'] ?? 50;

        if ($approval <= 25) {
            return [
                'type' => 'impeachment',
                'title' => 'IMPEACHED',
                'headline' => 'Congress Has Impeached the President',
                'message' => 'Your approval rating has dropped to ' . $approval . '%. With public confidence completely eroded, Congress has moved to impeach and remove you from office.',
                'stat' => 'Approval Rating',
                'stat_value' => $approval,
            ];
        }

        if ($stability <= 25) {
            return [
                'type' => 'overthrown',
                'title' => 'OVERTHROWN',
                'headline' => 'The Government Has Been Overthrown',
                'message' => 'Your government stability has collapsed to ' . $stability . '%. With the government in chaos and institutions failing, a coup has overthrown your administration.',
                'stat' => 'Government Stability',
                'stat_value' => $stability,
            ];
        }

        if ($partySupport <= 25) {
            return [
                'type' => 'amendment',
                'title' => '25TH AMENDMENT INVOKED',
                'headline' => 'The Vice President Takes Office',
                'message' => 'Your party support has plummeted to ' . $partySupport . '%. With your own party abandoning you, the Cabinet has invoked the 25th Amendment. Your Vice President has assumed the presidency.',
                'stat' => 'Party Support',
                'stat_value' => $partySupport,
            ];
        }

        return null;
    }

    protected function saveDecisionToDatabase(string $decisionText, string $scenarioTitle, array $tags, array $statChanges, int $turn): void
    {
        try {
            $gameService = new GameService();
            $game = $gameService->getActiveGame();
            
            if ($game) {
                $gameService->saveDecision($game, $turn, $scenarioTitle, $decisionText, $tags, $statChanges);
                
                // Check if we should generate a consequence
                $this->checkAndGenerateConsequence($game, $turn);
            }
        } catch (\Exception $e) {
            Log::error('Failed to save decision to database', ['error' => $e->getMessage()]);
        }
    }

    protected function checkAndGenerateConsequence($game, int $currentTurn): void
    {
        // Only generate consequence every 3-4 turns to avoid overwhelming
        if ($currentTurn % 4 !== 0) {
            return;
        }
        
        // Don't trigger if we just had one
        if ($game->hasRecentConsequence($game, $currentTurn, 3)) {
            return;
        }
        
        $gameService = new GameService();
        $recentDecisions = $gameService->getRecentDecisions($game, 5);
        
        if ($recentDecisions->isEmpty()) {
            return;
        }
        
        $aiService = new AIService();
        $president = Session::get('president');
        
        try {
            $consequenceData = $aiService->generateConsequence($recentDecisions->toArray(), $president);
            
            if ($consequenceData && isset($consequenceData['title'])) {
                $gameService->createConsequence(
                    $game,
                    $consequenceData['title'],
                    $consequenceData['description'],
                    $currentTurn,
                    $consequenceData['tags'] ?? []
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate consequence', ['error' => $e->getMessage()]);
        }
    }

    protected function getPendingConsequence(): ?array
    {
        try {
            $gameService = new GameService();
            $game = $gameService->getActiveGame();
            
            if (!$game) {
                return null;
            }
            
            $pending = $gameService->getPendingConsequences($game)->first();
            
            if ($pending) {
                $result = [
                    'id' => $pending->id,
                    'title' => $pending->title,
                    'description' => $pending->description,
                ];
                $gameService->markConsequenceShown($pending);
                return $result;
            }
        } catch (\Exception $e) {
            Log::error('Failed to get pending consequence', ['error' => $e->getMessage()]);
        }
        
        return null;
    }

    protected function getFallbackNews(string $decisionLabel): array
    {
        return [
            'left' => [
                'headline' => 'Reaction: ' . $decisionLabel,
                'body' => 'Your decision has generated mixed reactions. Supporters praise your action while critics express concern about the implications.',
            ],
            'center' => [
                'headline' => 'Decision Analyzed: ' . $decisionLabel,
                'body' => 'Policy experts are examining the potential impact of your decision. Implementation details remain unclear.',
            ],
            'right' => [
                'headline' => 'Response: ' . $decisionLabel,
                'body' => 'Your choice has drawn significant attention. Observers await further developments.',
            ],
        ];
    }

    protected function getFallbackVoterReactions(): array
    {
        return [
            'students' => ['reaction' => 'Students are watching developments closely.', 'support' => 50],
            'yuppie' => ['reaction' => 'Young professionals are evaluating the impact.', 'support' => 50],
            'young_conservatives' => ['reaction' => 'Young conservatives are analyzing your decision.', 'support' => 50],
            'working_class' => ['reaction' => 'Working-class voters are taking notice.', 'support' => 50],
            'suburban' => ['reaction' => 'Suburban families are forming opinions.', 'support' => 50],
            'rural' => ['reaction' => 'Rural communities are reacting to news.', 'support' => 50],
            'small_business' => ['reaction' => 'Small business owners are assessing implications.', 'support' => 50],
            'corporate' => ['reaction' => 'Corporate interests are monitoring the situation.', 'support' => 50],
            'public_sector' => ['reaction' => 'Public sector workers are watching developments.', 'support' => 50],
            'retirees' => ['reaction' => 'Retirees are taking a cautious approach.', 'support' => 50],
            'minorities' => ['reaction' => 'Minority communities are evaluating the impact.', 'support' => 50],
            'independents' => ['reaction' => 'Independent voters remain undecided.', 'support' => 50],
        ];
    }

    protected function calculateStatChanges(array $analysis, string $scenarioTitle = ''): array
    {
        // AI now returns direct stat changes (-8 to +8)
        $approval = $analysis['approval'] ?? 0;
        $stability = $analysis['stability'] ?? 0;
        $partySupport = $analysis['party_support'] ?? 0;

        // Support strength affects volatility (multiplier)
        $volatility = match(Session::get('president')['support_strength'] ?? 'comfortable') {
            'landslide' => 0.7,
            'comfortable' => 0.85,
            'razor_thin' => 1.0,
            'electoral_weakness' => 1.15,
            default => 1.0,
        };

        $approval = round($approval * $volatility);
        $stability = round($stability * $volatility);
        $partySupport = round($partySupport * $volatility);

        // Cap final values
        return [
            'approval' => max(-8, min(8, $approval)),
            'stability' => max(-8, min(8, $stability)),
            'party_support' => max(-8, min(8, $partySupport)),
        ];
    }

    protected function calculateStateReactions(array $decision, array $gameState, string $eventTitle = 'General Decision'): array
    {
        $president = Session::get('president');
        $presidentParty = $president['party'] ?? null;
        
        $decisionTags = $decision['decision_tags'] ?? $this->analyzeResponseForTags($decision['label'] ?? '', $gameState['player_raw_response'] ?? '');
        
        $stabilityChange = ($gameState['stability'] ?? 50) - ($gameState['prev_stability'] ?? 50);
        
        $decisionDimensions = $this->analyzeDecisionDimensions($decisionTags);
        
        $reactions = [];
        
        foreach ($this->states as $state) {
            $fips = $state['fips'];
            $color = $state['color'];
            $baseSupport = $state['base_support'] ?? 50;
            $policyBias = $state['policy_bias'] ?? [];
            $tagWeights = $state['tag_weights'] ?? [];
            
            $strongSupport = $policyBias['strong_support'] ?? [];
            $leanSupport = $policyBias['lean_support'] ?? [];
            $leanOppose = $policyBias['lean_oppose'] ?? [];
            $strongOppose = $policyBias['strong_oppose'] ?? [];
            
            $score = $baseSupport;
            $totalBonus = 0;
            $totalPenalty = 0;
            
            $matchedStrongSupport = [];
            $matchedLeanSupport = [];
            $matchedStrongOppose = [];
            $matchedLeanOppose = [];
            
            foreach ($decisionDimensions['policy_tags'] as $tag) {
                if (in_array($tag, $strongSupport)) {
                    $matchedStrongSupport[] = $tag;
                }
                if (in_array($tag, $leanSupport)) {
                    $matchedLeanSupport[] = $tag;
                }
                if (in_array($tag, $strongOppose)) {
                    $matchedStrongOppose[] = $tag;
                }
                if (in_array($tag, $leanOppose)) {
                    $matchedLeanOppose[] = $tag;
                }
            }
            
            foreach ($matchedStrongSupport as $tag) {
                $weight = $tagWeights[$tag] ?? 1.0;
                $totalBonus += (10 * $weight) + ($weight * 2.5);
            }
            
            foreach ($matchedLeanSupport as $tag) {
                $weight = $tagWeights[$tag] ?? 1.0;
                $totalBonus += (4 * $weight) + ($weight * 2.5);
            }
            
            foreach ($matchedStrongOppose as $tag) {
                $weight = $tagWeights[$tag] ?? 1.0;
                $totalPenalty += (15 * $weight) + ($weight * 2.5);
            }
            
            foreach ($matchedLeanOppose as $tag) {
                $weight = $tagWeights[$tag] ?? 1.0;
                $totalPenalty += (6 * $weight) + ($weight * 2.5);
            }
            
            $totalBonus = min($totalBonus, 25);
            $totalPenalty = min($totalPenalty, 25);
            
            $score += $totalBonus;
            $score -= $totalPenalty;
            
            $score += ($stabilityChange * 0.15);
            
            if ($presidentParty === 'democrat' && $color === 'blue') {
                $score += 3;
            } elseif ($presidentParty === 'republican' && $color === 'red') {
                $score += 3;
            } elseif ($presidentParty === 'democrat' && $color === 'red') {
                $score -= 3;
            } elseif ($presidentParty === 'republican' && $color === 'blue') {
                $score -= 3;
            } elseif ($color === 'swing') {
                $score += 1;
            }
            
            if ($decisionDimensions['is_mixed']) {
                $score -= 2;
            }
            
            $score += (rand(-5, 5) / 10);
            
            if (!empty($matchedStrongOppose)) {
                $identityClash = 15;
                $conflictCount = count($matchedStrongOppose);
                $stackPenalty = 10 + (5 * ($conflictCount - 1));
                $score -= ($identityClash + $stackPenalty);
            }
            
            if ($decisionDimensions['is_mixed']) {
                $score -= 1;
            }
            
            $score += (rand(-3, 3) / 10);
            
            if ($color === 'swing') {
                $totalSupportTags = count($matchedStrongSupport) + count($matchedLeanSupport);
                $totalOpposeTags = count($matchedStrongOppose) + count($matchedLeanOppose);
                $conflict = $totalSupportTags * $totalOpposeTags;
                
                if ($conflict > 0) {
                    $score -= ($conflict * 2);
                }
            }
            
            $score = round(max(20, min(85, $score)), 2);
            
            $reactions[$fips] = $score;
        }
        
        return $reactions;
    }

    protected function getStateBand(float $score): string
    {
        if ($score >= 75) return 'strongly_supports';
        if ($score >= 65) return 'supports';
        if ($score >= 55) return 'leans_support';
        if ($score >= 45) return 'neutral';
        if ($score >= 35) return 'leans_oppose';
        if ($score >= 25) return 'opposes';
        return 'strongly_opposes';
    }

    protected function getSwingStates(): array
    {
        return ['PA', 'MI', 'AZ', 'GA', 'WI', 'NV', 'NC', 'NH'];
    }
    
    protected function analyzeDecisionDimensions(array $decisionTags): array
    {
        $dimensions = [
            'issues' => [],
            'ideology' => [],
            'direction_penalties' => [],
            'policy_tags' => [],
            'is_mixed' => false,
        ];
        
        $tagMap = [
            'fossil_energy' => [
                'issues' => ['energy' => 1, 'economy' => 1],
                'policy_tags' => ['fossil_expansion'],
            ],
            'clean_energy' => [
                'issues' => ['energy' => 1, 'economy' => 1],
                'policy_tags' => ['climate_action', 'renewable_investment'],
            ],
            'renewables' => [
                'issues' => ['energy' => 1],
                'policy_tags' => ['renewable_investment', 'climate_action'],
            ],
            'climate' => [
                'issues' => ['energy' => 1],
                'policy_tags' => ['climate_action', 'renewable_investment'],
            ],
            'nuclear' => [
                'issues' => ['energy' => 1],
                'policy_tags' => ['nuclear_energy'],
            ],
            'border' => [
                'issues' => ['border' => 1, 'immigration' => 1],
                'policy_tags' => ['border_security'],
            ],
            'immigration' => [
                'issues' => ['immigration' => 1],
                'policy_tags' => [],
            ],
            'enforcement' => [
                'issues' => ['border' => 1],
                'policy_tags' => ['border_security', 'border_enforcement'],
            ],
            'security' => [
                'issues' => ['military' => 1, 'border' => 1],
                'policy_tags' => ['border_security', 'border_enforcement', 'military_strength'],
            ],
            'border_enforcement' => [
                'issues' => ['border' => 1],
                'policy_tags' => ['border_enforcement'],
            ],
            'tech' => [
                'issues' => ['tech' => 1, 'jobs' => 1, 'economy' => 1],
                'policy_tags' => ['tech_growth'],
            ],
            'finance' => [
                'issues' => ['finance' => 1, 'economy' => 1],
                'policy_tags' => ['economic_stability'],
            ],
            'market' => [
                'issues' => ['finance' => 1, 'economy' => 1],
                'policy_tags' => ['economic_stability'],
            ],
            'market_stabilization' => [
                'issues' => ['finance' => 1, 'economy' => 1],
                'policy_tags' => ['economic_stability'],
            ],
            'market_intervention' => [
                'issues' => ['finance' => 1, 'economy' => 1],
                'policy_tags' => ['economic_stability'],
            ],
            'healthcare' => [
                'issues' => ['healthcare' => 1, 'economy' => 1],
                'policy_tags' => ['healthcare_expansion', 'public_services'],
            ],
            'military' => [
                'issues' => ['military' => 1],
                'policy_tags' => ['military_strength'],
            ],
            'show_of_force' => [
                'issues' => ['military' => 1],
                'policy_tags' => ['military_strength'],
            ],
            'military_deployment' => [
                'issues' => ['military' => 1, 'border' => 1],
                'policy_tags' => ['military_strength', 'border_security'],
            ],
            'strength' => [
                'issues' => ['military' => 1],
                'policy_tags' => ['military_strength'],
            ],
            'deterrence' => [
                'issues' => ['military' => 1],
                'policy_tags' => ['military_strength'],
            ],
            'diplomacy' => [
                'issues' => ['economy' => 1],
                'policy_tags' => [],
            ],
            'diplomatic_first' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'alliance_building' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'housing' => [
                'issues' => ['economy' => 1],
                'policy_tags' => [],
            ],
            'labor' => [
                'issues' => ['jobs' => 1, 'economy' => 1],
                'policy_tags' => ['worker_protection'],
            ],
            'workers' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['worker_protection'],
            ],
            'worker_protection' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['worker_protection'],
            ],
            'agriculture' => [
                'issues' => ['agriculture' => 1, 'economy' => 1],
                'policy_tags' => ['agriculture_support'],
            ],
            'jobs' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['job_growth'],
            ],
            'job_growth' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['job_growth'],
            ],
            'workforce_development' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['job_growth'],
            ],
            'retraining' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['job_growth', 'education_funding'],
            ],
            'regulation' => [
                'issues' => ['economy' => 0],
                'policy_tags' => ['regulation'],
            ],
            'deregulation' => [
                'issues' => ['economy' => 0],
                'policy_tags' => ['deregulation'],
            ],
            'subsidy' => [
                'issues' => ['economy' => 1, 'jobs' => 1],
                'policy_tags' => ['job_growth'],
            ],
            'industry_subsidy' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['manufacturing_growth'],
            ],
            'consumer_relief' => [
                'issues' => ['economy' => 1],
                'policy_tags' => [],
            ],
            'unemployment' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['worker_protection'],
            ],
            'market_approach' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['tax_cuts', 'deregulation'],
            ],
            'fiscal_conservatism' => [
                'issues' => ['economy' => 0],
                'policy_tags' => ['tax_cuts', 'deregulation'],
            ],
            'no_action' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'emergency_spending' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['public_services'],
            ],
            'emergency_funding' => [
                'issues' => ['healthcare' => 1, 'economy' => 1],
                'policy_tags' => ['healthcare_expansion', 'public_services'],
            ],
            'tax_breaks' => [
                'issues' => ['economy' => 1, 'jobs' => 1],
                'policy_tags' => ['tax_cuts'],
            ],
            'tax_cuts' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['tax_cuts'],
            ],
            'humane_reforms' => [
                'issues' => ['immigration' => 1],
                'policy_tags' => [],
            ],
            'citizenship' => [
                'issues' => ['immigration' => 1],
                'policy_tags' => [],
            ],
            'asylum' => [
                'issues' => ['immigration' => 1],
                'policy_tags' => [],
            ],
            'reform' => [
                'issues' => ['immigration' => 1],
                'policy_tags' => [],
            ],
            'trade' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['trade'],
            ],
            'sanctions' => [
                'issues' => ['economy' => 1],
                'policy_tags' => [],
            ],
            'negotiation' => [
                'issues' => ['economy' => 1],
                'policy_tags' => [],
            ],
            'peace' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'alliances' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'international' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'multilateralism' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'long_term' => [
                'issues' => ['economy' => 1],
                'policy_tags' => [],
            ],
            'infrastructure' => [
                'issues' => ['jobs' => 1, 'economy' => 1],
                'policy_tags' => ['infrastructure', 'job_growth'],
            ],
            'states_rights' => [
                'issues' => [],
                'policy_tags' => ['federalism'],
            ],
            'federalism' => [
                'issues' => [],
                'policy_tags' => ['federalism'],
            ],
            'education' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['education_funding'],
            ],
            'corporate_tax' => [
                'issues' => ['economy' => 1],
                'policy_tags' => [],
            ],
            'business_subsidy' => [
                'issues' => ['economy' => 1, 'jobs' => 1],
                'policy_tags' => ['business_incentives', 'job_growth'],
            ],
            'business_incentives' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['business_incentives'],
            ],
            'low_cost' => [
                'issues' => ['economy' => 0],
                'policy_tags' => [],
            ],
            'civic_engagement' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'community' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'volunteer' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'volunteer_program' => [
                'issues' => [],
                'policy_tags' => [],
            ],
            'intervention' => [
                'issues' => ['finance' => 1, 'economy' => 1],
                'policy_tags' => ['economic_stability'],
            ],
            'consumer_protection' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['worker_protection'],
            ],
            'public_sector' => [
                'issues' => [],
                'policy_tags' => ['public_services'],
            ],
            'energy_independence' => [
                'issues' => ['energy' => 1],
                'policy_tags' => ['energy_independence'],
            ],
            'manufacturing_growth' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['manufacturing_growth'],
            ],
            'minimum_wage' => [
                'issues' => ['jobs' => 1],
                'policy_tags' => ['minimum_wage', 'worker_protection'],
            ],
            'trade_tariffs' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['trade_tariffs'],
            ],
            'infrastructure' => [
                'issues' => ['jobs' => 1, 'economy' => 1],
                'policy_tags' => ['infrastructure_spending', 'job_growth'],
            ],
            'social_security' => [
                'issues' => [],
                'policy_tags' => ['social_security'],
            ],
            'housing' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['housing_affordability'],
            ],
            'opioid_crisis' => [
                'issues' => ['healthcare' => 1],
                'policy_tags' => ['opioid_crisis'],
            ],
            'criminal_justice' => [
                'issues' => [],
                'policy_tags' => ['criminal_justice'],
            ],
            'voting_rights' => [
                'issues' => [],
                'policy_tags' => ['voting_rights'],
            ],
            'privacy_rights' => [
                'issues' => [],
                'policy_tags' => ['privacy_rights'],
            ],
            'trade_agreements' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['trade_agreements'],
            ],
            'corporate_taxes' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['corporate_taxes'],
            ],
            'foreign_aid' => [
                'issues' => [],
                'policy_tags' => ['foreign_aid'],
            ],
            'defense_spending' => [
                'issues' => ['military' => 1],
                'policy_tags' => ['defense_spending', 'military_strength'],
            ],
            'china_policy' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['china_policy'],
            ],
            'pharmaceutical_pricing' => [
                'issues' => ['healthcare' => 1],
                'policy_tags' => ['pharmaceutical_pricing', 'healthcare_expansion'],
            ],
            'antitrust' => [
                'issues' => ['economy' => 1],
                'policy_tags' => ['antitrust'],
            ],
        ];
        
        $hasPositiveGovt = false;
        $hasNegativeGovt = false;
        $tagCount = 0;
        
        foreach ($decisionTags as $tag) {
            if (isset($tagMap[$tag])) {
                $tagCount++;
                
                foreach ($tagMap[$tag]['policy_tags'] as $policyTag) {
                    if (!in_array($policyTag, $dimensions['policy_tags'])) {
                        $dimensions['policy_tags'][] = $policyTag;
                    }
                }
                
                $govtSpending = 0;
                if (in_array('public_services', $tagMap[$tag]['policy_tags'])) {
                    $govtSpending = 1;
                }
                if (in_array('deregulation', $tagMap[$tag]['policy_tags']) || in_array('tax_cuts', $tagMap[$tag]['policy_tags'])) {
                    $govtSpending = -1;
                }
                
                if ($govtSpending > 0) $hasPositiveGovt = true;
                if ($govtSpending < 0) $hasNegativeGovt = true;
            } else {
                $dimensions['policy_tags'][] = $tag;
            }
        }
        
        $dimensions['is_mixed'] = ($hasPositiveGovt && $hasNegativeGovt) || ($tagCount > 2);
        
        return $dimensions;
    }

    protected function analyzeResponseForTags(string $label, string $response): array
    {
        $text = strtolower($label . ' ' . $response);
        $tags = [];
        
        $tagPatterns = [
            'fossil_expansion' => ['oil', 'gas', 'petroleum', 'coal', 'fracking', 'drilling', 'fossil', 'energy production', 'domestic energy', 'expand energy'],
            'climate_action' => ['climate', 'emissions', 'carbon', 'environment'],
            'renewable_investment' => ['solar', 'wind', 'renewable', 'green energy', 'clean energy'],
            'nuclear_energy' => ['nuclear', 'nuclear power', 'nuclear energy', 'atom', 'reactor'],
            'border_security' => ['border', 'immigration', 'deportation', 'wall', 'migrant', 'illegal'],
            'border_enforcement' => ['deploy', 'detention', 'swift enforcement', 'illegal entry', 'order at the border', 'secure the border', 'enforcement', 'sovereignty'],
            'tech_growth' => ['tech', 'technology', 'software', 'silicon', 'innovation'],
            'financial_regulation' => ['bank', 'financial', 'wall street', 'investment', 'regulation'],
            'economic_stability' => ['market', 'stocks', 'trading', 'stabilize', 'economy'],
            'healthcare_expansion' => ['healthcare', 'hospital', 'medical', 'insurance', 'coverage'],
            'military_strength' => ['military', 'troops', 'navy', 'army', 'defense', 'warfighter'],
            'worker_protection' => ['labor', 'workers', 'jobs', 'union', 'worker', 'employment'],
            'agriculture_support' => ['farm', 'agriculture', 'farming', 'rural'],
            'job_growth' => ['jobs', 'employment', 'workforce', 'hiring', 'careers', 'job creation'],
            'deregulation' => ['deregulation', 'deregulate', 'less regulation', 'cut rules', 'cut unnecessary regulations', 'unnecessary regulations', 'regulations that hold'],
            'tax_cuts' => ['tax cut', 'tax break', 'lower tax', 'reduce tax', 'tax relief'],
            'public_services' => ['public', 'government service', 'social', 'welfare'],
            'environmental_regulation' => ['epa', 'environmental rules'],
            'climate_restrictions' => ['climate restrictions', 'climate limits', 'carbon tax', 'emission limits'],
            'federal_expansion' => ['federal', 'federal government', 'washington'],
            'manufacturing_growth' => ['manufacturing', 'factory', 'industrial', 'production'],
            'education_funding' => ['education', 'school', 'college', 'university', 'student'],
            'energy_independence' => ['energy independence', 'american energy'],
            'business_incentives' => ['business', 'incentive', 'enterprise'],
            'minimum_wage' => ['minimum wage', 'wage increase', 'raise wages', 'wage hike', 'living wage', 'pay workers more'],
            'trade_tariffs' => ['tariff', 'tariffs', 'import duty', 'trade war', 'retaliatory tariffs'],
            'infrastructure_spending' => ['infrastructure', 'roads', 'bridges', 'construction', 'highways', 'build'],
            'social_security' => ['social security', 'medicare', 'medicaid', 'retirement', 'entitlements'],
            'housing_affordability' => ['housing', 'zoning', 'affordable housing', 'real estate'],
            'opioid_crisis' => ['opioid', 'drug', 'pharmaceutical', 'addiction', 'fentanyl', 'overdose'],
            'criminal_justice' => ['criminal justice', 'policing', 'prison', 'sentencing', 'reform justice', 'law enforcement'],
            'voting_rights' => ['voting', 'voter', 'election', 'mail voting', 'ballot', 'vote suppression'],
            'privacy_rights' => ['privacy', 'surveillance', 'data', 'personal data', 'data protection'],
            'trade_agreements' => ['trade deal', 'nafta', 'china trade', 'free trade', 'trade agreement'],
            'corporate_taxes' => ['corporate tax', 'business tax', 'company tax', 'corporate profit'],
            'foreign_aid' => ['foreign aid', 'international aid', 'humanitarian aid', 'aid to other countries'],
            'defense_spending' => ['defense budget', 'military budget', 'pentagon spending', 'army funding'],
            'china_policy' => ['china', 'chinese', 'beijing', 'taiwan', 'trade war with china'],
            'pharmaceutical_pricing' => ['drug prices', 'pharmaceutical prices', 'prescription costs', 'medicine costs'],
            'antitrust' => ['antitrust', 'break up', 'monopoly', 'big tech', 'corporate competition', 'trust busting'],
        ];
        
        foreach ($tagPatterns as $tag => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($text, $pattern)) {
                    $tags[] = $tag;
                    break;
                }
            }
        }
        
        if (str_contains($text, 'border')) {
            $tags[] = 'border_security';
            if (str_contains($text, 'enforcement') || str_contains($text, 'deploy') || str_contains($text, 'illegal')) {
                $tags[] = 'border_enforcement';
            }
        }
        
        return array_unique($tags);
    }
}
