<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->articles() as $data) {
            $daysAgo = $data['days_ago'];
            unset($data['days_ago']);

            // firstOrCreate keyed on the slug: idempotent (no duplicates if this
            // reruns on deploy) and it will NOT overwrite edits made later in the
            // admin — existing rows are returned untouched.
            Article::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'status' => 'published',
                    'author_name' => 'PrComet Team',
                    'published_at' => now()->subDays($daysAgo),
                ]),
            );
        }
    }

    /**
     * Marketing/education articles. Authored to be genuinely useful and
     * publishable — heavy on H2/H3 structure so the on-page index has
     * something to build from.
     */
    private function articles(): array
    {
        return [
            [
                'slug' => 'what-is-pr-discovery',
                'days_ago' => 3,
                'title' => 'What Is PR Discovery (and Why It Beats Spray-and-Pray Outreach)',
                'category' => 'Fundamentals',
                'tags' => ['pr', 'strategy', 'discovery'],
                'excerpt' => 'PR discovery starts with the story and works outward to the few journalists it genuinely fits — the opposite of blasting a media list. Here is how the model works, and why it earns more coverage with far less volume.',
                'meta_description' => 'PR discovery finds the handful of journalists your story genuinely fits, instead of blasting a media list. Here is how the model works and why it wins.',
                'body_md' => <<<'MD'
Most PR advice still begins in the same place: build a media list, write a press release, and send it to as many reporters as you can find. PR discovery starts somewhere else entirely — with the story, and the question of who is *already* writing about things adjacent to it.

It is a small shift in starting point that changes almost everything downstream.

## The media list is the wrong starting point

A media list is a snapshot of contacts that felt relevant whenever it was last built. The problem is that relevance is not a property of a person — it is a property of the match between your story and what that person is working on *right now*.

### Why volume quietly backfires

Sending the same pitch to hundreds of reporters feels productive. It rarely is. The open rates are low, the reply rates are lower, and each irrelevant pitch teaches a journalist to associate your name with noise. The next time you reach out — even with something genuinely on-topic — you are starting from a deficit.

Volume does not just fail to work. It actively spends down the goodwill you will need later.

### The hidden cost of a stale list

Beats change. Reporters move outlets, switch topics, go on leave, or quietly stop covering a sector. A list that was accurate six months ago is now a mix of dead addresses and people who will wonder why a fintech company is pitching them about supply chains. Maintaining that list is real work, and the payoff decays the moment you stop.

## What discovery does differently

Discovery inverts the order of operations. Instead of starting with a list of people and hunting for a reason to contact them, you start with your story and look for the people for whom it is genuinely timely.

### Start with signal, not contacts

The raw material of discovery is published work: the articles, podcast episodes, and newsletters going out in your space this week. Somewhere in that stream are a few writers whose recent work overlaps with your announcement. Those overlaps are the signal. They tell you not just *who* might care, but *why they might care now* — which is exactly the context a good pitch needs.

### Let relevance set the volume

When relevance is high, you need very little volume. Ten well-matched, well-reasoned pitches will almost always outperform a thousand generic ones — not because ten is a magic number, but because each of the ten arrives with a reason to exist. The reporter can see, in the first sentence, why this landed in their inbox specifically.

## The anatomy of a discovery-led pitch

A pitch that comes out of discovery looks different on the page:

- **It names the connection.** "You wrote about X last week; here is a development that extends it."
- **It leads with the angle, not the announcement.** The reporter cares about why this matters, not that you shipped something.
- **It is short, because it can afford to be.** When the relevance is obvious, you do not need three paragraphs of throat-clearing to justify the email.

The difference a reader feels is *respect for their time*. That is the entire game.

## Where to start tomorrow

You do not need a platform to begin practicing discovery. Pick your most recent piece of news and ask: who has written something in the last month that this would be relevant to? Read their actual work. Find the specific sentence your story connects to. Write to that.

Discovery is a habit before it is a tool. Build the habit, and every pitch you send gets sharper — and every tool you adopt later has something real to amplify.
MD,
            ],
            [
                'slug' => 'how-to-find-the-right-journalist',
                'days_ago' => 9,
                'title' => 'How to Find the Right Journalist for Your Story',
                'category' => 'Playbooks',
                'tags' => ['journalists', 'research', 'pitching'],
                'excerpt' => 'Finding the right reporter is not about a bigger database. It is about reading recent work, mapping beats, and matching your angle to what someone is already chasing. A repeatable method you can run for every story.',
                'meta_description' => 'A repeatable method for finding the right journalist for your story: define the angle, build a shortlist from recent work, then qualify for relevance and reach.',
                'body_md' => <<<'MD'
"Who should we pitch?" is the wrong first question. The right one is "what is the story, exactly?" — because the answer determines everyone you should be talking to. Here is a method you can run for any announcement, in roughly an hour.

## Define the story before the search

You cannot match a story to a journalist until you can state the story in one sentence. Vagueness here gets multiplied at every later step.

### What's genuinely new here?

Strip the announcement down to the part a stranger would find new or surprising. A funding round is not a story; *what the funding makes possible* might be. A product launch is not a story; *the problem it solves for a specific group of people* might be. Write the one new thing down before you do anything else.

### Who would care, and why now?

Name the audience that has a reason to care this week, and the reason. "Operations leaders at mid-size logistics firms, because rates just spiked" is a story with a built-in set of relevant beats. "Everyone interested in our company" is not.

## Build a shortlist from recent work

With the angle defined, you are looking for writers whose recent output overlaps it — not whose title sounds related.

### Read the last 30 days, not the masthead

A reporter's job title tells you what they were hired to cover. Their last month of bylines tells you what they are *actually* covering. Read the recent work. You are looking for someone circling your topic, your sector, or your specific angle — ideally all three.

### Look for the follow-up pattern

Some writers cover a topic once and move on. Others return to the same thread repeatedly, building a beat over weeks. The second kind is far more valuable: if your story extends something they have already invested in, you are handing them their next installment rather than asking them to start cold.

## Qualify before you pitch

A name on a shortlist is a hypothesis, not a target. Run two quick tests before you spend a pitch on it.

### The relevance test

Can you point to a specific piece of their recent work and finish the sentence "this matters to them because…"? If the best you can do is "they cover the industry," the match is too loose. Keep it for later or cut it.

### The reachability test

Is there a credible way to reach this person, and is now a sensible time? A reporter who just published a deep piece on your exact topic may be the perfect match *or* completely tapped out on it for a month. Judgment matters here; the signal is necessary but not sufficient.

## Keep the list alive

The output of this process is not a permanent list — it is a list *for this story*. Beats move, so the right people for your next announcement will be partly different. Treat discovery as something you re-run, not something you file away. The companies that consistently land coverage are the ones who keep reading, every cycle, and let the shortlist change with the news.
MD,
            ],
            [
                'slug' => 'press-brief-that-earns-a-reply',
                'days_ago' => 16,
                'title' => 'Writing a Press Brief That Actually Earns a Reply',
                'category' => 'Playbooks',
                'tags' => ['writing', 'briefs', 'pitching'],
                'excerpt' => 'A strong brief does the reporter\'s thinking for them: the angle up front, proof they can verify, and one easy next step. The structure that consistently gets replies — and the habits that kill them.',
                'meta_description' => 'Write a press brief that earns replies: lead with the angle, make every claim checkable, respect the inbox, and close with one easy next step.',
                'body_md' => <<<'MD'
The brief is the product. You can do flawless discovery and still get ignored if the email that lands does not, in its first few lines, make the reporter's job easier. A good brief does their thinking for them.

## Lead with the angle, not the announcement

Reporters do not care that you did a thing. They care why the thing matters, to whom, and why now. Open with that.

### The "so what" test

Read your first sentence and ask "so what?" If a stranger could not tell why this matters from that sentence alone, it is not ready. "We raised $20M" fails the test. "We raised $20M to bring same-day diagnostics to rural clinics that currently wait two weeks" passes — there is a stake, a who, and a change.

## Make every claim checkable

Trust is built on verifiability. A brief full of adjectives reads like marketing; a brief full of specifics reads like a story.

### Numbers beat adjectives

"40% faster than the previous standard" is something a reporter can quote and stand behind. "Blazing fast" is something they have to either ignore or independently verify. Give them the number, the comparison, and the source.

### Quotes worth quoting

Write the quote you would actually want to read in the finished piece — specific, human, and free of corporate hedging. A quote that says something real saves the reporter a step and makes the story easier to write. A quote that says "we are thrilled to announce" gets cut every time.

## Respect the inbox

The best brief in the world still has to survive a triage pass that lasts a few seconds.

### Subject lines that survive a triage

The subject line is a promise about the angle, not a headline for your company. Make it specific and make it true. Curiosity gaps and ALL-CAPS urgency are read as spam signals; a plain, accurate description of the story is read as a professional.

### Length is a courtesy

Every extra paragraph is a withdrawal from the reader's patience. If the relevance is clear, you can be short. Aim for something a busy person can read fully on a phone without scrolling twice.

## Close with one easy next step

End by making the next move obvious and low-effort: an interview window, an embargoed demo, a data set, a named expert available this week. One clear option beats a menu. The easier you make the "yes," the more often you will get one.
MD,
            ],
            [
                'slug' => 'reading-a-reporters-beat',
                'days_ago' => 23,
                'title' => 'Reading a Reporter\'s Beat: The Signals That Predict Coverage',
                'category' => 'Strategy',
                'tags' => ['research', 'beats', 'signals'],
                'excerpt' => 'Beats shift constantly. These are the observable signals — recency, angle, stance, and track record — that tell you whether a writer will actually engage with your story this week, not just whether they cover your industry.',
                'meta_description' => 'Four observable signals — recency, angle, stance, and track record — that predict whether a journalist will actually cover your story, not just whether they cover your industry.',
                'body_md' => <<<'MD'
"They cover our industry" is the weakest possible reason to pitch someone. Industries are enormous; attention is narrow. To predict whether a specific writer will engage with a specific story, you have to read their beat the way they actually work it — through observable signals, not job titles.

## A beat is a moving target

A beat is not a fixed assignment. It is the evolving set of threads a writer is currently pulling on. Two reporters with identical titles can have completely different beats this month, and the same reporter's beat can shift week to week as stories develop. The masthead lies; the recent work tells the truth.

## The four signals that matter

When you read someone's recent output, four signals do most of the predictive work.

### Recency: what they wrote this month

The single strongest signal is what someone published in the last few weeks. A topic they covered yesterday is alive for them in a way that a topic from last year is not. Weight recent work heavily; it is the difference between a warm match and a cold guess.

### Angle: the frame they keep returning to

Writers have frames — the recurring lens through which they approach a topic. One reporter covers AI through the labor-market frame; another through the infrastructure-cost frame; another through the regulation frame. Your story can be a perfect fit for one frame and irrelevant to another. Identify the frame, then ask whether your angle fits it.

### Stance: skeptic, booster, or analyst

Read for posture. Some writers are skeptics who pressure-test claims; some are enthusiasts who amplify what excites them; some are analysts who contextualize. None is "better" — but the stance tells you how to pitch. A skeptic wants evidence and a falsifiable claim. An analyst wants the trend your story fits into. Misreading stance is how a strong story gets a cold reception.

### Track record: do they follow up?

Look at whether the writer returns to stories. A reporter who has historically followed companies they flagged, or revisited predictions they made, is someone for whom your update is a natural continuation. That follow-up habit is one of the most useful and most overlooked signals.

## Putting the signals together

No single signal is decisive. The strongest matches light up on several at once: a recent piece (recency), in a frame your angle fits (angle), from someone whose posture suits your evidence (stance), who tends to follow threads (track record). When several align, you are not guessing — you are reading.

## Signals you can safely ignore

Some things look like signals but predict almost nothing: follower counts, outlet prestige in isolation, and how often someone posts on social media. A large audience is not the same as a relevant audience, and a prestigious outlet does not help if the specific writer's beat is elsewhere. Spend your attention on the four signals that move the needle, and let the vanity metrics go.
MD,
            ],
            [
                'slug' => 'measuring-pr-beyond-impressions',
                'days_ago' => 31,
                'title' => 'Measuring PR That Matters: Beyond Impressions',
                'category' => 'Fundamentals',
                'tags' => ['measurement', 'analytics', 'reporting'],
                'excerpt' => 'Impressions are easy to count and easy to game. These are the metrics that actually tell you whether your PR is moving the narrative — and a simple scorecard for tracking them month over month.',
                'meta_description' => 'Move past impressions. The PR metrics that matter — share of voice, message pull-through, placement quality — and a simple monthly scorecard to track them.',
                'body_md' => <<<'MD'
Impressions make decks look impressive and tell you almost nothing. A number in the millions feels like impact, but it rarely connects to anything you actually care about. Here is what to measure instead, and how to build a loop around it.

## Why impressions mislead

The appeal of impressions is that they are large and easy to report. That is also the problem.

### Reach is not relevance

A mention that reaches a million people who will never become customers, partners, or hires is worth less than a mention that reaches a thousand of exactly the right ones. Raw reach ignores the only thing that matters: whether the *right* people saw the *right* message.

### The vanity-metric trap

Metrics that always go up and never force a decision are vanity metrics. If a number cannot be bad — if there is no version of the report where it tells you to change course — it is not informing your strategy, it is decorating it. Impressions almost always fail this test.

## Metrics worth tracking

Good PR metrics tie coverage to outcomes you can influence and decisions you can actually make.

### Share of voice on your terms

Measure your share of coverage on the specific topics you want to own — not the entire category. "Share of voice in same-day diagnostics" is actionable. "Share of voice in healthcare" is too broad to mean anything. Define the narrow territory you are trying to win, then track your slice of it.

### Message pull-through

Did the coverage carry your actual message, or just your name? Pull-through asks whether the framing you offered survived into the published piece. High name recognition with zero message pull-through means you are visible but not understood — a fixable problem you would never see from impression counts.

### Quality of placement

A thoughtful 800-word piece from a writer your buyers trust outperforms ten syndicated reprints of a wire release. Track the quality and relevance of placements, not just the quantity. One piece in the right place can do more than a hundred in the wrong ones.

## Build the feedback loop

Measurement is only useful if it changes what you do next.

### Tie coverage back to discovery

Close the loop: when a pitch lands, note which signals predicted it and feed that back into how you find the next set of writers. When a pitch misses, ask whether the match was wrong or the brief was. Over a few cycles, this turns measurement from a backward-looking report into a forward-looking advantage.

## A simple monthly scorecard

You do not need a dashboard to start. Once a month, write down four things: your share of voice on your priority topics, your message pull-through rate, the count of high-quality placements, and one lesson about what predicted success or failure. Four numbers and a sentence, reviewed honestly, will outperform any impression report you have ever sent.
MD,
            ],
            [
                'slug' => 'the-follow-up-timing-and-cadence',
                'days_ago' => 40,
                'title' => 'The Follow-Up: Timing, Cadence, and Knowing When to Stop',
                'category' => 'Playbooks',
                'tags' => ['follow-up', 'outreach', 'etiquette'],
                'excerpt' => 'Most pitches die in the follow-up — sent too soon, too often, or never at all. A practical framework for following up in a way reporters actually appreciate, and knowing the difference between persistence and pestering.',
                'meta_description' => 'A practical framework for the PR follow-up: when to send the first nudge, what it should say, how to set cadence without nagging, and when to stop.',
                'body_md' => <<<'MD'
A surprising number of pitches that *should* land never do — not because the story was wrong, but because the follow-up was. It came too soon, too often, repeated the original word for word, or never came at all. The follow-up is a skill of its own.

## The follow-up is where most pitches are won or lost

Reporters are busy and inboxes are deep. A first email that does not get a reply has very often simply not been *seen* yet, not actively rejected. That makes the follow-up not an act of desperation but a normal, expected part of the process — if you do it well.

## Timing the first nudge

Send too soon and you are nagging someone who has not had a chance to read you. Wait too long and the story has gone cold.

### Read the engagement signal

If you can tell that your brief was opened or your linked page was viewed, you have a real signal: there is interest, or at least attention. That is a much stronger moment to follow up than silence. A nudge to someone who already looked is a continuation; a nudge into the void is a guess.

### The 3-to-5 day window

Absent any signal, a few business days is a reasonable default for the first follow-up — long enough to clear a normal triage backlog, short enough that the news is still timely. Adjust for the rhythm of the story: a hard-news angle decays in days, an evergreen trend piece can wait a week.

## What a good follow-up says

The worst follow-up is "just bumping this." It adds nothing and asks the reporter to do all the work again.

### Add, don't repeat

A good follow-up brings something new: a fresh data point, a development since the first email, a different angle, or an offer that lowers the effort of saying yes. Give the reporter a reason this second email exists beyond your own impatience.

## Cadence without nagging

Persistence works; pestering does not. The line between them is mostly about restraint.

### The two-touch rule

For most stories, one well-timed follow-up after the initial pitch is plenty. A second, only if you genuinely have something new to add. Beyond that, you are spending relationship capital you will want for the next story far more than for this one.

## Knowing when to stop

If a story has gone quiet after a thoughtful pitch and a thoughtful follow-up, let it go. A reporter's silence is information: this is not for them, or not now. Respecting that is what makes you welcome the next time.

## Turning a no into a next time

The goal is never a single placement — it is a relationship that produces placements over years. A graceful "no problem, I'll keep you in mind for something more relevant" does more for your long-term coverage than one more aggressive bump ever could. Play for the next story, not just this one.
MD,
            ],
        ];
    }
}
