<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LocationCouncils;
use App\Models\Indicator;
use App\Models\Project;
use App\Models\FinanceEntry;
use App\Models\KeyIssue;
use Illuminate\Support\Collection;

class LCPDashboard extends Component
{
    public string $periodStart;
    public string $periodEnd;

    public int $issuesLimit = 8;
    public int $sparkMonths = 6; // last N months for sparklines

    public function mount(): void
    {
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd   = now()->endOfMonth()->toDateString();
    }

    public function quickRange(string $key): void
    {
        $now = now();
        switch ($key) {
            case 'this_month':
                $this->periodStart = $now->startOfMonth()->toDateString();
                $this->periodEnd   = $now->endOfMonth()->toDateString();
                break;
            case 'last_3mo':
                $this->periodStart = $now->copy()->subMonths(2)->startOfMonth()->toDateString();
                $this->periodEnd   = $now->endOfMonth()->toDateString();
                break;
            case 'ytd':
                $this->periodStart = $now->startOfYear()->toDateString();
                $this->periodEnd   = $now->endOfYear()->toDateString();
                break;
        }
    }

    private function wherePeriodOverlap($query, string $from, string $to)
    {
        return $query->where(function ($q) use ($from, $to) {
            $q->whereBetween('period_start', [$from, $to])
              ->orWhereBetween('period_end',   [$from, $to])
              ->orWhere(function ($qq) use ($from, $to) {
                  $qq->where('period_start', '<=', $from)
                     ->where('period_end',   '>=', $to);
              });
        });
    }

    private function kpis(): array
    {
        return [
            'councilsCount'   => LocationCouncils::count(),
            'indicatorsCount' => Indicator::count(),
            'projectsCount'   => Project::count(),
            'openIssuesCount' => KeyIssue::whereNotIn('status', ['resolved','closed'])->count(),
        ];
    }

    private function finance(): array
    {
        $from = $this->periodStart;
        $to   = $this->periodEnd;

        $sumRevenueOwn = (float) $this->wherePeriodOverlap(
            FinanceEntry::query()->where('category','revenue_own'),
            $from,$to
        )->sum('amount');

        $sumGrantCentral = (float) $this->wherePeriodOverlap(
            FinanceEntry::query()->where('category','grant_central'),
            $from,$to
        )->sum('amount');

        $sumExpenditureAll = (float) $this->wherePeriodOverlap(
            FinanceEntry::query()->where('category','expenditure_sector'),
            $from,$to
        )->sum('amount');

        return compact('sumRevenueOwn','sumGrantCentral','sumExpenditureAll');
    }

    /** Build sparkline values for a finance category across last N months */
    private function sparkForCategory(string $category, int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $start = now()->copy()->subMonths($i)->startOfMonth();
            $end   = now()->copy()->subMonths($i)->endOfMonth();

            $sum = (float) $this->wherePeriodOverlap(
                FinanceEntry::query()->where('category', $category),
                $start->toDateString(), $end->toDateString()
            )->sum('amount');

            $labels[] = $start->format('M');
            $values[] = $sum;
        }

        $max = collect($values)->max() ?: 0;

        return ['labels' => $labels, 'values' => $values, 'max' => (float)$max];
    }

    private function issues(): Collection
    {
        $from = $this->periodStart.' 00:00:00';
        $to   = $this->periodEnd.' 23:59:59';

        $cMap = LocationCouncils::select('id','councilname')
            ->get()->keyBy('id')
            ->map(fn($c) => $c->councilname ?? ('#'.$c->id));

        return KeyIssue::query()
            ->select(['id','council_id','title','owner','severity','status','opened_at','due_at','closed_at','created_at','description'])
            ->whereNotIn('status', ['resolved','closed'])
            ->whereBetween('created_at', [$from,$to])
            ->orderBy('severity','desc')
            ->orderBy('created_at','desc')
            ->limit($this->issuesLimit)
            ->get()
            ->map(fn($i) => [
                'id'          => (int)$i->id,
                'council'     => $cMap[$i->council_id] ?? '—',
                'title'       => (string)($i->title ?? ''),
                'owner'       => (string)($i->owner ?? ''),
                'severity'    => (string)($i->severity ?? ''),
                'status'      => (string)($i->status ?? ''),
                'opened'      => optional($i->opened_at)->toDateString(),
                'due'         => optional($i->due_at)->toDateString(),
                'closed'      => optional($i->closed_at)->toDateString(),
                'description' => (string)($i->description ?? ''),
            ]);
    }

    public function render()
    {
        $kpis     = $this->kpis();
        $finance  = $this->finance();

        // build sparkline series
        $sparkRevenue    = $this->sparkForCategory('revenue_own', $this->sparkMonths);
        $sparkGrant      = $this->sparkForCategory('grant_central', $this->sparkMonths);
        $sparkSpend      = $this->sparkForCategory('expenditure_sector', $this->sparkMonths);

        return view('livewire.lcp-dashboard', [
            'periodStart'         => $this->periodStart,
            'periodEnd'           => $this->periodEnd,
            'councilsCount'       => $kpis['councilsCount'],
            'indicatorsCount'     => $kpis['indicatorsCount'],
            'projectsCount'       => $kpis['projectsCount'],
            'openIssuesCount'     => $kpis['openIssuesCount'],
            'sumRevenueOwn'       => $finance['sumRevenueOwn'],
            'sumGrantCentral'     => $finance['sumGrantCentral'],
            'sumExpenditureAll'   => $finance['sumExpenditureAll'],
            'issues'              => $this->issues(),
            // sparklines
            'sparkRevenue'        => $sparkRevenue,
            'sparkGrant'          => $sparkGrant,
            'sparkSpend'          => $sparkSpend,
        ])->layout('layouts.app');
    }
}
