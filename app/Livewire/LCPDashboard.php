<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LocationCouncils;
use App\Models\Indicator;
use App\Models\FinanceEntry;
use App\Models\MetricValue;
use App\Models\Project;
use App\Models\KeyIssue;
use Illuminate\Support\Arr;

class LCPDashboard extends Component
{
    public string $periodStart;
    public string $periodEnd;

    public function mount(): void
    {
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd   = now()->endOfMonth()->toDateString();
    }

    public function quickRange(string $key): void
    {
        $now = now();
        [$from, $to] = match ($key) {
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_3mo'   => [$now->copy()->subMonths(2)->startOfMonth(), $now->copy()->endOfMonth()],
            'ytd'        => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default      => [$this->periodStart, $this->periodEnd],
        };

        $this->periodStart = $from instanceof \Carbon\Carbon ? $from->toDateString() : (string) $from;
        $this->periodEnd   = $to   instanceof \Carbon\Carbon ? $to->toDateString()   : (string) $to;
    }

    public function render()
    {
        $from = $this->periodStart;
        $to   = $this->periodEnd;

        // Top KPIs
        $kpis = [
            'councils'   => LocationCouncils::count(),
            'indicators' => Indicator::count(),
            'projects'   => Project::count(),
            'issuesOpen' => KeyIssue::whereNotIn('status', ['resolved','closed'])->count(),
        ];

        // Finance snapshots (period overlap aware)
        $finance = [
            'own_revenue' => $this->sumFinance('revenue_own', null, $from, $to),
            'grants'      => $this->sumFinance('grant_central', null, $from, $to),
            'expend'      => $this->sumFinance('expenditure_sector', null, $from, $to),
        ];

        // Open issues (details)
        $issues = KeyIssue::query()
            ->select('id','title','owner','status','severity','council_id','opened_at','due_at','created_at')
            ->with(['council:id,councilname'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(fn($i) => [
                'id'       => (int) $i->id,
                'title'    => (string) $i->title,
                'owner'    => (string) ($i->owner ?? ''),
                'status'   => (string) ($i->status ?? ''),
                'severity' => (string) ($i->severity ?? ''),
                'council'  => optional($i->council)->councilname ?? optional($i->council)->name ?? '—',
                'opened'   => optional($i->opened_at)->toDateString() ?? optional($i->created_at)->toDateString(),
                'due'      => optional($i->due_at)->toDateString(),
            ]);

        // Recent activity (last 15 across modules)
        $recent = $this->recent($from, $to);

        return view('livewire.l-c-p-dashboard', compact('kpis','finance','issues','recent'))
            ->layout('layouts.app');
    }

    private function sumFinance(string $category, ?string $sub, string $from, string $to): float
    {
        $q = FinanceEntry::query()
            ->where('category', $category)
            ->when($sub, fn($qq) => $qq->where('sub_category', $sub))
            ->where(function ($w) use ($from, $to) {
                $w->whereBetween('period_start', [$from, $to])
                  ->orWhereBetween('period_end', [$from, $to])
                  ->orWhere(function ($x) use ($from, $to) {
                      $x->where('period_start', '<=', $from)->where('period_end', '>=', $to);
                  });
            });

        return (float) $q->sum('amount');
    }

    private function recent(string $from, string $to): array
    {
        try {
            $finance = FinanceEntry::with('council:id,councilname')
                ->orderByDesc('created_at')->limit(10)->get()->map(function ($f) {
                    return [
                        'created_at' => $f->created_at,
                        'type'       => 'Finance',
                        'council'    => optional($f->council)->councilname ?? optional($f->council)->name ?? '—',
                        'details'    => trim(($f->category ?? '-') . ($f->sub_category ? ' · '.$f->sub_category : '')),
                        'when'       => ($f->period_start ? $f->period_start.' – ' : '').($f->period_end ?? ''),
                        'amount'     => $f->amount !== null ? number_format((float) $f->amount, 2) : '—',
                    ];
                });

            $metrics = MetricValue::with(['council:id,councilname','indicator:id,name,unit'])
                ->orderByDesc('created_at')->limit(10)->get()->map(function ($m) {
                    return [
                        'created_at' => $m->created_at,
                        'type'       => 'Indicator',
                        'council'    => optional($m->council)->councilname ?? optional($m->council)->name ?? '—',
                        'details'    => optional($m->indicator)->name ? ($m->indicator->name.($m->indicator->unit ? ' ('.$m->indicator->unit.')' : '')) : '—',
                        'when'       => ($m->period_start ? $m->period_start.' – ' : '').($m->period_end ?? ''),
                        'amount'     => $m->value !== null ? (string) $m->value : '—',
                    ];
                });

            $projects = Project::with('council:id,councilname')
                ->orderByDesc('created_at')->limit(10)->get()->map(function ($p) {
                    return [
                        'created_at' => $p->created_at,
                        'type'       => 'Project',
                        'council'    => optional($p->council)->councilname ?? optional($p->council)->name ?? '—',
                        'details'    => trim(($p->title ?? '-') . ($p->status ? ' · '.ucfirst((string) $p->status) : '')),
                        'when'       => optional($p->created_at)->toDateString(),
                        'amount'     => $p->budget !== null ? number_format((float) $p->budget, 2) : '—',
                    ];
                });

            $issues = KeyIssue::with('council:id,councilname')
                ->orderByDesc('created_at')->limit(10)->get()->map(function ($i) {
                    return [
                        'created_at' => $i->created_at,
                        'type'       => 'Issue',
                        'council'    => optional($i->council)->councilname ?? optional($i->council)->name ?? '—',
                        'details'    => trim(($i->title ?? '-') . ($i->owner ? ' · '.$i->owner : '')),
                        'when'       => optional($i->created_at)->toDateString(),
                        'amount'     => ucfirst((string) ($i->status ?? 'open')),
                    ];
                });

            return $finance->merge($metrics)->merge($projects)->merge($issues)
                ->sortByDesc('created_at')->take(15)->values()
                ->map(function ($row) {
                    unset($row['created_at']);
                    return $row;
                })
                ->all();
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }
}
