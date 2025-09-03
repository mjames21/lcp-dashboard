<?php


namespace App\Livewire;

use Livewire\Component;
use App\Models\LocationCouncils;
use App\Models\Indicator;
use App\Models\MetricValue;
use App\Models\FinanceEntry;
use App\Models\Project;
use App\Models\Sector; // table: sectors

class CompareCouncils extends Component
{
    /** UI mode */
    public string $mode = 'indicator'; // indicator | finance | project

    /** Common filters */
    public array $councilIds = [];
    public string $periodStart;
    public string $periodEnd;

    /** Indicator mode */
    public array $indicatorIds = [];
    public string $stat = 'latest'; // latest|avg|sum

    /** Finance mode */
    public string $financeCategory = 'revenue_own'; // revenue_own|grant_central|expenditure_sector
    public ?string $financeSubCategory = null;      // sector (when expenditure_sector)

    /** Project mode */
    public string $projectAgg = 'count';            // count|sum_budget

    /** Option lists (sent to blade) */
    public array $allCouncils = [];
    public array $allIndicators = [];
    public array $sectors = [];                     // ['HLTH','EDU',...]

    /** Labels */
    private array $financeLabels = [
        'revenue_own'       => 'Own-source revenue',
        'grant_central'     => 'Central grant',
        'expenditure_sector'=> 'Expenditure (by sector)',
    ];
    public array $tabs = [
    'indicator' => 'Indicators',
    'finance'   => 'Finance',
    'project'   => 'Projects',
];

    protected function rules(): array
    {
        return [
            'mode'           => 'required|in:indicator,finance,project',
            'councilIds'     => 'required|array|min:1',
            'councilIds.*'   => 'integer|exists:location_councils,id',
            'periodStart'    => 'required|date',
            'periodEnd'      => 'required|date|after_or_equal:periodStart',

            // indicator
            'indicatorIds'   => 'exclude_unless:mode,indicator|array|min:1',
            'indicatorIds.*' => 'integer|exists:indicators,id',
            'stat'           => 'exclude_if:mode,project|in:latest,avg,sum',

            // finance
            'financeCategory'    => 'exclude_unless:mode,finance|in:revenue_own,grant_central,expenditure_sector',
            'financeSubCategory' => 'nullable|string',

            // project
            'projectAgg'     => 'exclude_unless:mode,project|in:count,sum_budget',
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

    /** Reset to defaults */
    public function resetFilters(): void
    {
        $this->mount();
        $this->mode = 'indicator';
        $this->stat = 'latest';
        $this->financeCategory = 'revenue_own';
        $this->financeSubCategory = null;
        $this->projectAgg = 'count';
    }

    /** When mode changes, clear mode-specific inputs (keeps common filters) */
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
        }
    }

    /** Computed heading for first column */
    public function getLeadHeadingProperty(): string
    {
        return match ($this->mode) {
            'finance' => 'Finance',
            'project' => 'Project',
            default   => 'Indicator',
        };
    }

    public function render()
    {
        $headers = $this->headers();
        $rows    = $this->buildRows($headers);

        return view('livewire.compare-councils', [
            'councils'    => $this->allCouncils,
            'indicators'  => $this->allIndicators,
            'headers'     => $headers,
            'rows'        => $rows,
            'periodStart' => $this->periodStart,
            'periodEnd'   => $this->periodEnd,
            'sectors'     => $this->sectors,
        ])->layout('layouts.app');
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
                        default => (float) optional($group->last())->value, // latest
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
        // end indicator
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
        // end finance
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
        // end projects
    }
}
