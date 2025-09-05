<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LocationCouncils;
use App\Models\Indicator;
use App\Models\MetricValue;
use App\Models\FinanceEntry;
use App\Models\Project;
use App\Models\Sector;
use App\Models\KeyIssue;                 // 👈 add

class CompareCouncils extends Component
{
    /** UI mode */
    public string $mode = 'indicator'; // indicator | finance | project | issue

    /** Common filters */
    public array $councilIds = [];
    public string $periodStart;
    public string $periodEnd;

    /** Indicator mode */
    public array $indicatorIds = [];
    public string $stat = 'latest'; // latest|avg|sum

    /** Finance mode */
    public string $financeCategory = 'revenue_own'; // revenue_own|grant_central|expenditure_sector
    public ?string $financeSubCategory = null;

    /** Project mode */
    public string $projectAgg = 'count';            // count|sum_budget

    /** Issue mode */
    public string $issueStatus = 'open_any';        // open_any|closed_any|all
    public ?string $issueSeverity = null;           // null=All | low|medium|high|critical

    /** Option lists */
    public array $allCouncils = [];
    public array $allIndicators = [];
    public array $sectors = [];

    /** Labels */
    private array $financeLabels = [
        'revenue_own'        => 'Own-source revenue',
        'grant_central'      => 'Central grant',
        'expenditure_sector' => 'Expenditure (by sector)',
    ];

    protected function rules(): array
    {
        return [
            'mode'         => 'required|in:indicator,finance,project,issue',
            'councilIds'   => 'required|array|min:1',
            'councilIds.*' => 'integer|exists:location_councils,id',
            'periodStart'  => 'required|date',
            'periodEnd'    => 'required|date|after_or_equal:periodStart',

            // indicator
            'indicatorIds'   => 'exclude_unless:mode,indicator|array|min:1',
            'indicatorIds.*' => 'integer|exists:indicators,id',
            'stat'           => 'exclude_if:mode,project|in:latest,avg,sum',

            // finance
            'financeCategory'    => 'exclude_unless:mode,finance|in:revenue_own,grant_central,expenditure_sector',
            'financeSubCategory' => 'nullable|string',

            // project
            'projectAgg' => 'exclude_unless:mode,project|in:count,sum_budget',

            // issue
            'issueStatus'   => 'exclude_unless:mode,issue|in:open_any,closed_any,all',
            'issueSeverity' => 'nullable|in:low,medium,high,critical',
        ];
    }

    public function mount(): void
    {
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd   = now()->endOfMonth()->toDateString();

        $this->allCouncils = LocationCouncils::query()
            ->select('id','councilname')
            ->orderByRaw('COALESCE(councilname)')
            ->get()
            ->map(fn($c) => [
                'id'   => (int) $c->id,
                'name' => $c->councilname ?? $c->name ?? ('#'.$c->id),
            ])->all();

        $this->allIndicators = Indicator::query()
            ->select('id','name','unit')
            ->orderBy('name')
            ->get()
            ->map(fn($i) => [
                'id'   => (int) $i->id,
                'name' => (string) $i->name,
                'unit' => (string) ($i->unit ?? ''),
            ])->all();

        $this->sectors = Sector::query()
            ->pluck('sector')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // defaults
        $this->councilIds   = array_slice(array_column($this->allCouncils, 'id'), 0, 2);
        $this->indicatorIds = array_slice(array_column($this->allIndicators, 'id'), 0, 5);
    }

    public function resetFilters(): void
    {
        $this->mount();
        $this->mode = 'indicator';
        $this->stat = 'latest';
        $this->financeCategory    = 'revenue_own';
        $this->financeSubCategory = null;
        $this->projectAgg         = 'count';
        $this->issueStatus        = 'open_any';
        $this->issueSeverity      = null;
    }

    public function updatedMode(string $value): void
    {
        if ($value === 'indicator') {
            $this->stat = 'latest';
            if (empty($this->indicatorIds)) {
                $this->indicatorIds = array_slice(array_column($this->allIndicators, 'id'), 0, 5);
            }
        } elseif ($value === 'finance') {
            $this->stat = 'sum';
            $this->financeCategory = 'revenue_own';
            $this->financeSubCategory = null;
        } elseif ($value === 'project') {
            $this->projectAgg = 'count';
        } elseif ($value === 'issue') {
            $this->issueStatus   = 'open_any';
            $this->issueSeverity = null;
        }
    }

    /** First-column heading */
    public function getLeadHeadingProperty(): string
    {
        return match ($this->mode) {
            'finance' => 'Finance',
            'project' => 'Project',
            'issue'   => 'Issue',
            default   => 'Indicator',
        };
    }

  

    private function headers(): array
    {
        $byId = collect($this->allCouncils)->keyBy('id');
        return array_values(array_filter(
            array_map(fn ($cid) => $byId[$cid] ?? null, $this->councilIds)
        ));
    }

    private function buildRows(array $headers): array
    {
        if (empty($headers)) return [];

        return match ($this->mode) {
            'finance' => $this->buildFinanceRows($headers),
            'project' => $this->buildProjectRows($headers),
            'issue'   => $this->buildIssueRows($headers),   // 👈 add
            default   => $this->buildIndicatorRows($headers),
        };
    }

    /** ---------------- INDICATORS ---------------- */
    private function buildIndicatorRows(array $headers): array
    {
        if (empty($this->indicatorIds)) return [];

        $from = $this->periodStart; $to = $this->periodEnd;

        $records = MetricValue::query()
            ->select(['council_id','indicator_id','period_start','period_end','value'])
            ->whereIn('council_id', array_column($headers, 'id'))
            ->whereIn('indicator_id', $this->indicatorIds)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('period_start', [$from, $to])
                  ->orWhereBetween('period_end', [$from, $to])
                  ->orWhere(function ($qq) use ($from, $to) {
                      $qq->where('period_start', '<=', $from)
                         ->where('period_end',   '>=', $to);
                  });
            })
            ->orderBy('indicator_id')
            ->orderBy('council_id')
            ->orderBy('period_end')
            ->get()
            ->groupBy(['indicator_id','council_id']);

        $indMeta = collect($this->allIndicators)->keyBy('id');

        $rows = [];
        foreach ($this->indicatorIds as $indId) {
            $meta = $indMeta[$indId] ?? ['name' => 'Indicator #'.$indId, 'unit' => ''];
            $values = [];
            foreach ($headers as $h) {
                $cid = $h['id'];
                $group = $records[$indId][$cid] ?? collect();
                $val = null;
                if ($group->isNotEmpty()) {
                    $val = match ($this->stat) {
                        'avg' => (float) $group->avg('value'),
                        'sum' => (float) $group->sum('value'),
                        default => (float) optional($group->last())->value,
                    };
                }
                $values[$cid] = $val;
            }
            $max = collect($values)->filter(fn($v)=>$v!==null)->max() ?? 0;

            $rows[] = [
                'indicator' => $meta['name'],
                'unit'      => $meta['unit'] ?? '',
                'values'    => $values,
                'max'       => (float) $max,
            ];
        }

        return $rows;
    }

    /** ---------------- FINANCE ---------------- */
    private function buildFinanceRows(array $headers): array
    {
        $from = $this->periodStart; $to = $this->periodEnd;

        $base = FinanceEntry::query()
            ->select(['council_id','category','sub_category','period_start','period_end','amount'])
            ->whereIn('council_id', array_column($headers, 'id'))
            ->where('category', $this->financeCategory)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('period_start', [$from, $to])
                  ->orWhereBetween('period_end', [$from, $to])
                  ->orWhere(function ($qq) use ($from, $to) {
                      $qq->where('period_start', '<=', $from)
                         ->where('period_end',   '>=', $to);
                  });
            });

        if ($this->financeCategory === 'expenditure_sector') {
            if ($this->financeSubCategory) {
                $base->where('sub_category', $this->financeSubCategory);
            }
            $grouped = $base->orderBy('sub_category')->orderBy('council_id')->get()
                ->groupBy(['sub_category','council_id']);
            $sectorKeys = $this->financeSubCategory
                ? [$this->financeSubCategory]
                : array_values(array_unique(array_filter($grouped->keys()->all())));

            $rows = [];
            foreach ($sectorKeys as $sector) {
                $values = [];
                foreach ($headers as $h) {
                    $cid = $h['id'];
                    $g = $grouped[$sector][$cid] ?? collect();
                    $val = null;
                    if ($g->isNotEmpty()) {
                        $val = match ($this->stat) {
                            'avg' => (float) $g->avg('amount'),
                            'sum' => (float) $g->sum('amount'),
                            default => (float) optional($g->sortBy(['period_end'])->last())->amount,
                        };
                    }
                    $values[$cid] = $val;
                }
                $max = collect($values)->filter(fn($v)=>$v!==null)->max() ?? 0;
                $rows[] = [
                    'indicator' => 'Expenditure – '.($sector ?: 'N/A'),
                    'unit'      => 'SLE',
                    'values'    => $values,
                    'max'       => (float) $max,
                ];
            }
            return $rows;
        }

        // Non-sector categories => single row
        $list = $base->orderBy('council_id')->get()->groupBy('council_id');
        $values = [];
        foreach ($headers as $h) {
            $cid = $h['id'];
            $g = $list[$cid] ?? collect();
            $val = null;
            if ($g->isNotEmpty()) {
                $val = match ($this->stat) {
                    'avg' => (float) $g->avg('amount'),
                    'sum' => (float) $g->sum('amount'),
                    default => (float) optional($g->sortBy(['period_end'])->last())->amount,
                };
            }
            $values[$cid] = $val;
        }
        $max = collect($values)->filter(fn($v)=>$v!==null)->max() ?? 0;

        return [[
            'indicator' => $this->financeLabels[$this->financeCategory] ?? ucfirst(str_replace('_',' ',$this->financeCategory)),
            'unit'      => 'SLE',
            'values'    => $values,
            'max'       => (float) $max,
        ]];
    }

    /** ---------------- PROJECTS ---------------- */
    private function buildProjectRows(array $headers): array
    {
        $from = $this->periodStart; $to = $this->periodEnd;

        $list = Project::query()
            ->select(['council_id','budget','status','created_at'])
            ->whereIn('council_id', array_column($headers, 'id'))
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->get()
            ->groupBy('council_id');

        $values = [];
        foreach ($headers as $h) {
            $cid = $h['id'];
            $g = $list[$cid] ?? collect();
            $values[$cid] = match ($this->projectAgg) {
                'sum_budget' => (float) $g->sum('budget'),
                default      => (int) $g->count(),
            };
        }
        $max = collect($values)->filter(fn($v)=>$v!==null)->max() ?? 0;

        $label = $this->projectAgg === 'sum_budget' ? 'Project budget (sum)' : 'Projects (count)';
        $unit  = $this->projectAgg === 'sum_budget' ? 'SLE' : 'count';

        return [[
            'indicator' => $label,
            'unit'      => $unit,
            'values'    => $values,
            'max'       => (float) $max,
        ]];
    }

    /** ---------------- ISSUES ---------------- */
  // In App\Livewire\CompareCouncils.php
private function buildIssueRows(array $headers): array
{
    $from = $this->periodStart.' 00:00:00';
    $to   = $this->periodEnd.' 23:59:59';

    $base = \App\Models\KeyIssue::query() // <-- use your model class name
        ->select(['council_id','status','severity','created_at','resolved_at'])
        ->whereIn('council_id', array_column($headers, 'id'))
        ->whereBetween('created_at', [$from, $to]);

    if (!empty($this->issueSeverity)) {
        $base->where('severity', $this->issueSeverity);
    }

    if (!empty($this->issueStatus)) {
        // examples of friendly filters; tweak to your UI values if different
        if ($this->issueStatus === 'open_any') {
            $base->whereNotIn('status', ['resolved','closed']);
        } elseif ($this->issueStatus === 'closed_any') {
            $base->whereIn('status', ['resolved','closed']);
        } else {
            $base->where('status', $this->issueStatus);
        }
    }

    $byCouncil = $base->get()->groupBy('council_id');

    // Row 1: count by council
    $valuesCount = [];
    foreach ($headers as $h) {
        $cid = $h['id'];
        $valuesCount[$cid] = (int) (($byCouncil[$cid] ?? collect())->count());
    }
    $maxCount = collect($valuesCount)->max() ?? 0;

    // Row 2: average days open (created_at -> resolved_at or now if not resolved)
    $now = now();
    $valuesAvgDays = [];
    foreach ($headers as $h) {
        $cid = $h['id'];
        $issues = $byCouncil[$cid] ?? collect();
        if ($issues->isEmpty()) {
            $valuesAvgDays[$cid] = null;
            continue;
        }
        $days = $issues->map(function ($i) use ($now) {
            $end = $i->resolved_at ?? $now;                // both Carbon (see model casts)
            return max(0, $i->created_at->diffInDays($end));
        });
        $valuesAvgDays[$cid] = round($days->avg(), 2);
    }
    $maxDays = collect($valuesAvgDays)->filter(fn($v)=>$v!==null)->max() ?? 0;

    return [
        [
            'indicator' => 'Issues (count)',
            'unit'      => 'count',
            'values'    => $valuesCount,
            'max'       => (float) $maxCount,
        ],
        [
            'indicator' => 'Avg days open',
            'unit'      => 'days',
            'values'    => $valuesAvgDays,
            'max'       => (float) $maxDays,
        ],
    ];
}
  /** Detailed list for Issues (title / owner / description + dates) */
    private function buildIssueDetails(array $headers): array
    {
        $from = $this->periodStart.' 00:00:00';
        $to   = $this->periodEnd.' 23:59:59';

        $q = KeyIssue::query()
            ->select([
                'id','council_id','title','owner','description',
                'severity','status','created_at','due_at','resolved_at'
            ])
            ->whereIn('council_id', array_column($headers, 'id'))
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc');

        if ($this->issueSeverity !== '') {
            $q->where('severity', $this->issueSeverity);
        }
        if ($this->issueStatus !== '') {
            if ($this->issueStatus === 'open_any') {
                $q->whereNotIn('status', ['resolved','closed']);
            } elseif ($this->issueStatus === 'closed_any') {
                $q->whereIn('status', ['resolved','closed']);
            } else {
                $q->where('status', $this->issueStatus);
            }
        }

        $byCouncil = collect($this->allCouncils)->keyBy('id');

        return $q->get()->map(function ($r) use ($byCouncil) {
            return [
                'id'          => $r->id,
                'council_id'  => $r->council_id,
                'council'     => $byCouncil[$r->council_id]['name'] ?? ('#'.$r->council_id),
                'title'       => (string) $r->title,
                'owner'       => (string) ($r->owner ?? ''),
                'description' => Str::limit((string) ($r->description ?? ''), 160),
                'severity'    => (string) ($r->severity ?? ''),
                'status'      => (string) ($r->status ?? ''),
                'opened'      => optional($r->created_at)->toDateString(),
                'due'         => optional($r->due_at)->toDateString(),
                'closed'      => optional($r->resolved_at)->toDateString(),
            ];
        })->all();
    }



      public function render()
    {
        $headers = $this->headers();
        $rows    = $this->buildRows($headers);
         $issueDetails = [];
if ($this->mode === 'issue') {
    $from = $this->periodStart . ' 00:00:00';
    $to   = $this->periodEnd   . ' 23:59:59';

    $issueDetails = \App\Models\KeyIssue::query()
        ->with('council:id,councilname')
        ->whereIn('council_id', array_column($headers ?? $this->headers(), 'id'))
        ->when($this->issueSeverity, fn($q) => $q->where('severity', $this->issueSeverity))
        ->when($this->issueStatus === 'open_any',    fn($q) => $q->whereNotIn('status', ['resolved','closed']))
        ->when($this->issueStatus === 'closed_any',  fn($q) => $q->whereIn('status', ['resolved','closed']))
        ->whereBetween('created_at', [$from,$to])
        ->orderByDesc('created_at')
        ->get()
        ->map(fn($r) => [
            'council'     => optional($r->council)->councilname ?? '—',
            'title'       => (string) $r->title,
            'owner'       => (string) ($r->owner ?? ''),
            'description' => (string) ($r->description ?? ''),
            'severity'    => (string) ($r->severity ?? ''),
            'status'      => (string) ($r->status ?? ''),
            'opened'      => optional($r->created_at)->toDateString(),
            'due'         => optional($r->due_at ?? null)->toDateString(),
            'closed'      => optional($r->resolved_at ?? $r->closed_at ?? null)->toDateString(),
        ])
        ->toArray();
}
        return view('livewire.compare-councils', [
            'councils'    => $this->allCouncils,
            'indicators'  => $this->allIndicators,
            'headers'     => $headers,
            'rows'        => $rows,
            'periodStart' => $this->periodStart,
            'periodEnd'   => $this->periodEnd,
            'sectors'     => $this->sectors,
             'issueDetails' => $issueDetails,
        ])->layout('layouts.app');
    }

}
