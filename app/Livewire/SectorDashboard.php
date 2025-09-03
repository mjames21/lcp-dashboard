<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sector;
use App\Models\Indicator;
use App\Models\LocationCouncils;
use App\Models\MetricValue;
use App\Models\FinanceEntry;

class SectorDashboard extends Component
{
    /** Route param (sector code like HLTH/EDU/INFR/SANI) */
    public string $code;

    /** Resolved sector row */
    public ?Sector $sector = null;

    /** Filters */
    public string $periodStart;
    public string $periodEnd;
    public array  $councilIds = [];

    /** Option lists */
    public array $councils = [];
    public array $indicators = [];

    /** Sector nav pills */
    public array $sectorTabs = []; // [['code'=>'HLTH','label'=>'Health'], ...]

    /** Data */
    public array $rows = [];
    public float $financeTotal = 0.0;

    public function mount(string $code): void
    {
        $this->code   = strtoupper($code);
        $this->sector = Sector::where('sector', $this->code)->first();
        abort_unless($this->sector, 404, 'Sector not found.');

        // Pill list (ordered, nice labels)
       $this->sectorTabs = Sector::query()
    ->orderBy('sector')
    ->get(['sector'])
    ->map(fn ($s) => [
        'code'  => (string) $s->sector,
        'label' => (string) $s->sector, // show the sector code on the pill
    ])
    ->all();

        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd   = now()->endOfMonth()->toDateString();

        $this->councils = LocationCouncils::query()
            ->select('id','councilname')
            ->orderByRaw('COALESCE(councilname)')
            ->get()
            ->map(fn($c)=>['id'=>(int)$c->id, 'name'=>$c->councilname ?? $c->name ?? '#'.$c->id])
            ->all();

        $this->indicators = Indicator::query()
            ->select('id','name','unit')
            ->where('sector_id', $this->sector->id)
            ->orderBy('name')
            ->get()
            ->map(fn($i)=>['id'=>(int)$i->id,'name'=>$i->name,'unit'=>$i->unit ?? ''])
            ->all();

        // default councils: first 2
        $this->councilIds = array_slice(array_column($this->councils, 'id'), 0, 2);

        $this->refreshData();
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['periodStart','periodEnd','councilIds'], true)) {
            $this->refreshData();
        }
    }

    public function quickRange(string $preset): void
    {
        $today = now();
        match ($preset) {
            'this_month' => [
                $this->periodStart = $today->copy()->startOfMonth()->toDateString(),
                $this->periodEnd   = $today->copy()->endOfMonth()->toDateString(),
            ],
            'last_3mo' => [
                $this->periodStart = $today->copy()->subMonthsNoOverflow(2)->startOfMonth()->toDateString(),
                $this->periodEnd   = $today->copy()->endOfMonth()->toDateString(),
            ],
            'ytd' => [
                $this->periodStart = $today->copy()->startOfYear()->toDateString(),
                $this->periodEnd   = $today->copy()->toDateString(),
            ],
            default => null,
        };

        $this->refreshData();
    }

    private function refreshData(): void
    {
        $this->rows = $this->buildRows();
        $this->financeTotal = $this->calcFinanceTotal();
    }

    private function buildRows(): array
    {
        if (empty($this->councilIds) || empty($this->indicators)) return [];

        $from = $this->periodStart; $to = $this->periodEnd;
        $indicatorIds = array_column($this->indicators, 'id');

        $records = MetricValue::query()
            ->select(['council_id','indicator_id','period_start','period_end','value'])
            ->whereIn('council_id', $this->councilIds)
            ->whereIn('indicator_id', $indicatorIds)
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

        $rows = [];
        foreach ($this->indicators as $ind) {
            $values = [];
            foreach ($this->councilIds as $cid) {
                $g = $records[$ind['id']][$cid] ?? collect();
                $values[$cid] = $g->isEmpty() ? null : (float) optional($g->last())->value;
            }
            $max = collect($values)->filter(fn($v)=>$v!==null)->max() ?? 0;

            $rows[] = [
                'indicator' => $ind['name'],
                'unit'      => $ind['unit'],
                'values'    => $values,
                'max'       => (float) $max,
            ];
        }

        return $rows;
    }

    /** Finance sum for this sector in period */
    private function calcFinanceTotal(): float
    {
        $from = $this->periodStart; $to = $this->periodEnd;

        return (float) FinanceEntry::query()
            ->where('category', 'expenditure_sector')
            ->where('sub_category', $this->code)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('period_start', [$from, $to])
                  ->orWhereBetween('period_end', [$from, $to])
                  ->orWhere(function ($qq) use ($from, $to) {
                      $qq->where('period_start', '<=', $from)
                         ->where('period_end',   '>=', $to);
                  });
            })
            ->sum('amount');
    }

    public function getCouncilHeadersProperty(): array
    {
        $byId = collect($this->councils)->keyBy('id');
        return array_values(array_filter(
            array_map(fn($cid)=>$byId[$cid] ?? null, $this->councilIds)
        ));
    }

    public function render()
    {
        return view('livewire.sector-dashboard', [
            'headers' => $this->councilHeaders,
        ])->layout('layouts.app');
    }
}
