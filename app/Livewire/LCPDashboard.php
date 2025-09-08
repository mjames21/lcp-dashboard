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

    // UI (optional): how many rows to show in tables
    public int $issuesLimit = 8;

    public function mount(): void
    {
        // Default to current month
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd   = now()->endOfMonth()->toDateString();
    }

    /** Quick period setter from the UI */
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

    /** Helper: add “period overlap” constraint */
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

    /** KPIs */
    private function kpis(): array
    {
        $councilsCount   = LocationCouncils::count();
        $indicatorsCount = Indicator::count();
        $projectsCount   = Project::count();

        // Open issues = anything not resolved or closed
        $openIssuesCount = KeyIssue::query()
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        return compact('councilsCount', 'indicatorsCount', 'projectsCount', 'openIssuesCount');
    }

    /** Finance rollups */
    private function finance(): array
    {
        $from = $this->periodStart;
        $to   = $this->periodEnd;

        $sumRevenueOwn = (float) $this->wherePeriodOverlap(
            FinanceEntry::query()->where('category', 'revenue_own'),
            $from, $to
        )->sum('amount');

        $sumGrantCentral = (float) $this->wherePeriodOverlap(
            FinanceEntry::query()->where('category', 'grant_central'),
            $from, $to
        )->sum('amount');

        $sumExpenditureAll = (float) $this->wherePeriodOverlap(
            FinanceEntry::query()->where('category', 'expenditure_sector'),
            $from, $to
        )->sum('amount');

        return compact('sumRevenueOwn', 'sumGrantCentral', 'sumExpenditureAll');
    }

    /** Open issues list (top N) */
    private function issues(): Collection
    {
        $from = $this->periodStart . ' 00:00:00';
        $to   = $this->periodEnd   . ' 23:59:59';

        // Map council ids to names
        $cMap = LocationCouncils::select('id', 'councilname')
            ->get()
            ->keyBy('id')
            ->map(fn ($c) => $c->councilname ?? ('#'.$c->id));

        return KeyIssue::query()
            ->select([
                'id','council_id','title','owner','severity','status',
                'opened_at','due_at','closed_at','created_at','description'
            ])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('severity', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($this->issuesLimit)
            ->get()
            ->map(function ($i) use ($cMap) {
                return [
                    'id'          => (int) $i->id,
                    'council'     => $cMap[$i->council_id] ?? '—',
                    'title'       => (string) $i->title,
                    'owner'       => (string) ($i->owner ?? ''),
                    'severity'    => (string) ($i->severity ?? ''),
                    'status'      => (string) ($i->status ?? ''),
                    'opened'      => optional($i->opened_at)->toDateString(),
                    'due'         => optional($i->due_at)->toDateString(),
                    'closed'      => optional($i->closed_at)->toDateString(),
                    'description' => (string) ($i->description ?? ''),
                ];
            });
    }

    public function render()
    {
        $kpis    = $this->kpis();
        $finance = $this->finance();
        $issues  = $this->issues();

        return view('livewire.lcp-dashboard', [
            'periodStart'      => $this->periodStart,
            'periodEnd'        => $this->periodEnd,
            // KPIs
            'councilsCount'    => $kpis['councilsCount'],
            'indicatorsCount'  => $kpis['indicatorsCount'],
            'projectsCount'    => $kpis['projectsCount'],
            'openIssuesCount'  => $kpis['openIssuesCount'],
            // Finance
            'sumRevenueOwn'    => $finance['sumRevenueOwn'],
            'sumGrantCentral'  => $finance['sumGrantCentral'],
            'sumExpenditureAll'=> $finance['sumExpenditureAll'],
            // Lists
            'issues'           => $issues,
        ])->layout('layouts.app');
    }
}
