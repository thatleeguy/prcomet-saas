<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\MatchRecord;
use App\Models\Source;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Brief')]
class Dashboard extends Component
{
    /**
     * Top 3 unreviewed opportunities by score — the brief.
     */
    #[Computed]
    public function topMatches()
    {
        $team = auth()->user()->currentTeam;

        return MatchRecord::query()
            ->with(['company', 'publicationItem.source', 'author'])
            ->whereHas('company', fn ($q) => $q->where('team_id', $team->id))
            ->where('status', MatchRecord::STATUS_NEW)
            ->orderByDesc('score')
            ->limit(3)
            ->get();
    }

    /**
     * Older / lower-scoring / already-actioned matches that didn't make
     * the top of today's brief but are still in the queue.
     */
    #[Computed]
    public function queueMatches()
    {
        $team = auth()->user()->currentTeam;
        $topIds = $this->topMatches->pluck('id');

        return MatchRecord::query()
            ->with(['company', 'publicationItem.source', 'author'])
            ->whereHas('company', fn ($q) => $q->where('team_id', $team->id))
            ->whereNotIn('id', $topIds)
            ->whereNotIn('status', [MatchRecord::STATUS_DISMISSED])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function stats(): array
    {
        $team = auth()->user()->currentTeam;
        $teamCompanyIds = Company::where('team_id', $team->id)->pluck('id');
        $base = MatchRecord::whereIn('company_id', $teamCompanyIds);

        $newThisWeek = (clone $base)->where('status', MatchRecord::STATUS_NEW)
            ->where('created_at', '>=', now()->subDays(7))->count();
        $newPrevWeek = (clone $base)->where('status', MatchRecord::STATUS_NEW)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])->count();

        return [
            'companies' => $teamCompanyIds->count(),
            'new_this_week' => $newThisWeek,
            'in_outreach' => (clone $base)->whereIn('status', [MatchRecord::STATUS_SAVED, MatchRecord::STATUS_CONTACTED])->count(),
            'placed' => (clone $base)->where('status', MatchRecord::STATUS_PLACED)->count(),
            'sources_scanned' => Source::query()
                ->where(fn ($q) => $q->where('scope', Source::SCOPE_GLOBAL)
                    ->orWhere(fn ($q2) => $q2->where('scope', Source::SCOPE_TEAM)->where('team_id', $team->id)))
                ->where('is_active', true)->count(),
            'trend_pct' => $newPrevWeek > 0
                ? (int) round((($newThisWeek - $newPrevWeek) / $newPrevWeek) * 100)
                : ($newThisWeek > 0 ? 100 : null),
        ];
    }

    /**
     * 14-day sparkline series for the inline status strip.
     *
     * @return array<int, array{date:string, count:int}>
     */
    #[Computed]
    public function sparkline(): array
    {
        $team = auth()->user()->currentTeam;
        $teamCompanyIds = Company::where('team_id', $team->id)->pluck('id');
        $start = now()->subDays(13)->startOfDay();

        $counts = MatchRecord::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
            ->whereIn('company_id', $teamCompanyIds)
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->pluck('c', 'day');

        $series = [];
        $cursor = $start->copy();
        for ($i = 0; $i < 14; $i++) {
            $series[] = ['date' => $cursor->format('M j'), 'count' => (int) ($counts[$cursor->toDateString()] ?? 0)];
            $cursor->addDay();
        }
        return $series;
    }

    /** Build sparkline SVG points. */
    #[Computed]
    public function sparklinePath(): string
    {
        $series = $this->sparkline;
        $width = 100;
        $height = 24;
        $max = max(1, max(array_column($series, 'count')));
        $stepX = $width / max(1, count($series) - 1);

        $parts = [];
        foreach ($series as $i => $p) {
            $x = round($i * $stepX, 2);
            $y = round($height - ($p['count'] / $max) * $height, 2);
            $parts[] = ($i === 0 ? 'M' : 'L')."$x $y";
        }
        return implode(' ', $parts);
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
