<?php

namespace App\Livewire\Companies;

use App\Models\Company;
use App\Models\NewsroomSubscriber;
use App\Models\NewsroomSubscription;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Customer-facing audience dashboard.
 *
 * Shows the slice of the PrComet network that follows this specific
 * company: aggregate stats, a breakdown by cadence, and a recent-
 * subscriber list. The export action streams a CSV of the full set
 * so customers can take their list with them when they want it
 * (it's their audience, even though PrComet owns the identity
 * graph).
 *
 * Numbers framed to make the network angle obvious — every metric
 * pairs the customer's slice with the network total so they can
 * see the multiplier they're plugged into.
 */
#[Layout('layouts.app')]
#[Title('Audience')]
class Audience extends Component
{
    use WithPagination;

    public Company $company;

    /** all | instant | daily | weekly | unconfirmed */
    #[Url(as: 'filter', except: 'all')]
    public string $filter = 'all';

    #[Url(as: 'q')]
    public string $search = '';

    public function mount(Company $company): void
    {
        abort_unless($company->team_id === auth()->user()->currentTeam?->id, 403);
        $this->company = $company;

        if (auth()->user()->current_company_id !== $company->id) {
            auth()->user()->switchCompany($company);
        }
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilter(): void { $this->resetPage(); }

    public function setFilter(string $value): void
    {
        $this->filter = $value;
        $this->resetPage();
    }

    /**
     * Active subscribers to THIS company. Columns are qualified so
     * the render() pipeline can join the pivot for per-company
     * subscribed_at ordering without ambiguity — both tables carry
     * an unsubscribed_at column.
     */
    protected function baseQuery(): Builder
    {
        return NewsroomSubscriber::query()
            ->whereHas('subscriptions', fn ($q) => $q
                ->where('company_id', $this->company->id)
                ->whereNull('newsroom_subscriptions.unsubscribed_at'))
            ->whereNull('newsroom_subscribers.unsubscribed_at');
    }

    #[Computed]
    public function stats(): array
    {
        $base = $this->baseQuery();

        $totalActive = (clone $base)->whereNotNull('confirmed_at')->count();
        $unconfirmed = (clone $base)->whereNull('confirmed_at')->count();
        $instant = (clone $base)->whereNotNull('confirmed_at')->where('cadence', NewsroomSubscriber::CADENCE_INSTANT)->count();
        $daily = (clone $base)->whereNotNull('confirmed_at')->where('cadence', NewsroomSubscriber::CADENCE_DAILY)->count();
        $weekly = (clone $base)->whereNotNull('confirmed_at')->where('cadence', NewsroomSubscriber::CADENCE_WEEKLY)->count();

        // Network total — every confirmed, non-unsubscribed identity
        // on the install. The framing pairs "your followers" with
        // "the audience you're plugged into" so the customer sees the
        // multiplier even when their own count is small.
        $networkTotal = NewsroomSubscriber::query()
            ->whereNotNull('confirmed_at')
            ->whereNull('newsroom_subscribers.unsubscribed_at')
            ->count();

        $thisMonth = (clone $base)
            ->whereHas('subscriptions', fn ($q) => $q
                ->where('company_id', $this->company->id)
                ->whereNull('newsroom_subscriptions.unsubscribed_at')
                ->where('subscribed_at', '>=', now()->startOfMonth()))
            ->whereNotNull('confirmed_at')
            ->count();

        return [
            'total_active' => $totalActive,
            'unconfirmed' => $unconfirmed,
            'this_month' => $thisMonth,
            'instant' => $instant,
            'daily' => $daily,
            'weekly' => $weekly,
            'network_total' => $networkTotal,
        ];
    }

    /**
     * Stream a CSV of every active subscription on this company.
     * Headers: email, name, cadence, confirmed, subscribed_at.
     * Exports the audience but stops short of revealing what other
     * companies the subscriber follows — privacy boundary across the
     * network.
     */
    public function exportCsv(): StreamedResponse
    {
        $companyId = $this->company->id;
        $filename = 'audience-'.$this->company->slug.'-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($companyId) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['email', 'name', 'cadence', 'confirmed', 'subscribed_at']);

            NewsroomSubscriber::query()
                ->select(['newsroom_subscribers.*', 'newsroom_subscriptions.subscribed_at'])
                ->join('newsroom_subscriptions', 'newsroom_subscriptions.newsroom_subscriber_id', '=', 'newsroom_subscribers.id')
                ->where('newsroom_subscriptions.company_id', $companyId)
                ->whereNull('newsroom_subscriptions.unsubscribed_at')
                ->whereNull('newsroom_subscribers.unsubscribed_at')
                ->orderByDesc('newsroom_subscriptions.subscribed_at')
                ->cursor()
                ->each(function ($r) use ($handle) {
                    fputcsv($handle, [
                        $r->email,
                        $r->name,
                        NewsroomSubscriber::CADENCES[$r->cadence] ?? $r->cadence,
                        $r->confirmed_at ? 'yes' : 'pending',
                        $r->subscribed_at,
                    ]);
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $query = $this->baseQuery();

        $query->when($this->filter === 'instant',     fn ($q) => $q->whereNotNull('confirmed_at')->where('cadence', NewsroomSubscriber::CADENCE_INSTANT));
        $query->when($this->filter === 'daily',       fn ($q) => $q->whereNotNull('confirmed_at')->where('cadence', NewsroomSubscriber::CADENCE_DAILY));
        $query->when($this->filter === 'weekly',      fn ($q) => $q->whereNotNull('confirmed_at')->where('cadence', NewsroomSubscriber::CADENCE_WEEKLY));
        $query->when($this->filter === 'unconfirmed', fn ($q) => $q->whereNull('confirmed_at'));

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(fn ($q) => $q->where('email', 'like', $term)->orWhere('name', 'like', $term));
        }

        // Pull the per-company subscribed_at by joining the pivot —
        // we want subscribers ordered by when THIS company gained
        // them, not when their identity was first created.
        $query
            ->select(['newsroom_subscribers.*', 'newsroom_subscriptions.subscribed_at as pivot_subscribed_at'])
            ->join('newsroom_subscriptions', function ($join) {
                $join->on('newsroom_subscriptions.newsroom_subscriber_id', '=', 'newsroom_subscribers.id')
                    ->where('newsroom_subscriptions.company_id', $this->company->id);
            })
            ->orderByDesc('newsroom_subscriptions.subscribed_at');

        $subscribers = $query->paginate(20);

        return view('livewire.companies.audience', [
            'subscribers' => $subscribers,
        ]);
    }
}
