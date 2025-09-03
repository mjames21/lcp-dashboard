<?php
// app/Livewire/DataCollector.php
namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use App\Models\LocationCouncils;
use App\Models\Indicator;
use App\Models\FinanceEntry;
use App\Models\MetricValue;
use App\Models\Project;
use App\Models\Sector;
use App\Models\GovernanceEvent;

class DataCollector extends Component
{
    public bool $showModal = false;
    public string $activeTab = 'finance';

    public ?int $councilId = null;
    public string $periodStart;
    public string $periodEnd;

    public string $financeCategory = 'revenue_own';
    public ?string $financeSubCategory = null; // sector code when category = expenditure_sector
    public ?float $financeAmount = null;

    public ?int $indicatorId = null;
    public ?float $metricValue = null;

    public string $projectTitle = '';
    public ?string $projectStatus = 'ongoing';
    public ?float $projectBudget = null;

    // Governance
    public string $governanceTitle = '';
    public ?string $governanceType = 'council_meeting';
    public ?string $governanceStatus = 'planned';
    public ?string $governanceDate = null;
    public ?string $governanceLocation = null;
    public ?string $governanceNotes = null;

    /** Lookups */
    public array $sectors = []; // only sector codes
    public array $govTypes = [
        'council_meeting' => 'Council meeting',
        'audit'           => 'Audit',
        'policy'          => 'Policy',
        'training'        => 'Training',
        'procurement'     => 'Procurement',
    ];
    public array $govStatuses = ['planned','completed','canceled','postponed'];

    /** Friendly messages/labels */
    protected array $messages = [
        'councilId.required'           => 'Please choose a council.',
        'councilId.exists'             => 'Selected council is invalid.',
        'periodStart.required'         => 'Provide the start date.',
        'periodEnd.required'           => 'Provide the end date.',
        'periodEnd.after_or_equal'     => 'End date cannot be before the start date.',
        'financeSubCategory.required'  => 'Please select a sector.',
        'financeSubCategory.in'        => 'Choose a valid sector from the list.',
        'financeAmount.numeric'        => 'Amount must be a number.',
        'metricValue.required'         => 'Enter a value for the indicator.',
        'indicatorId.required'         => 'Select an indicator.',
        'indicatorId.exists'           => 'Selected indicator is invalid.',
        'projectTitle.required'        => 'Project title is required.',
        'projectStatus.in'             => 'Project status must be one of planned/ongoing/completed/stalled.',
        'governanceTitle.required'     => 'Governance title is required.',
        'governanceType.in'            => 'Choose a valid governance type.',
        'governanceStatus.in'          => 'Choose a valid governance status.',
        'governanceDate.required'      => 'Please select a date.',
        'governanceDate.date'          => 'Please enter a valid date.',
    ];

    protected array $validationAttributes = [
        'councilId'          => 'council',
        'periodStart'        => 'from date',
        'periodEnd'          => 'to date',
        'financeCategory'    => 'category',
        'financeSubCategory' => 'sector',
        'financeAmount'      => 'amount',
        'indicatorId'        => 'indicator',
        'metricValue'        => 'value',
        'projectTitle'       => 'title',
        'projectStatus'      => 'status',
        'projectBudget'      => 'budget',
        'governanceTitle'    => 'title',
        'governanceType'     => 'type',
        'governanceStatus'   => 'status',
        'governanceDate'     => 'date',
        'governanceLocation' => 'location',
        'governanceNotes'    => 'notes',
    ];

    protected function rules(): array
    {
        return [
            'councilId'       => 'required|exists:location_councils,id',
            'periodStart'     => 'required|date',
            'periodEnd'       => 'required|date|after_or_equal:periodStart',
            'financeCategory' => 'nullable|string',
            'financeAmount'   => 'nullable|numeric|min:0',
            'indicatorId'     => 'nullable|exists:indicators,id',
            'metricValue'     => 'nullable|numeric',
            'projectTitle'    => 'nullable|string|max:255',
            'projectStatus'   => 'nullable|in:planned,ongoing,completed,stalled',
            'projectBudget'   => 'nullable|numeric|min:0',
        ];
    }

    public function mount(): void
    {
        $this->periodStart = now()->startOfMonth()->toDateString();
        $this->periodEnd   = now()->endOfMonth()->toDateString();
        $this->governanceDate = now()->toDateString();

        $this->sectors = Sector::query()
            ->pluck('sector')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function create(): void
    {
        $this->resetValidation();
        $this->activeTab = 'finance';
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function saveFinance(): void
    {
        $rules = Arr::only($this->rules(), [
            'councilId', 'periodStart', 'periodEnd', 'financeCategory', 'financeAmount'
        ]);

        if ($this->financeCategory === 'expenditure_sector') {
            $rules['financeSubCategory'] = ['required', 'string', Rule::in($this->sectors)];
        } else {
            $rules['financeSubCategory'] = ['nullable', 'string'];
            $this->financeSubCategory = null;
        }

        $this->validate($rules);

        FinanceEntry::updateOrCreate(
            [
                'council_id'   => $this->councilId,
                'category'     => $this->financeCategory,
                'sub_category' => $this->financeSubCategory,
                'period_start' => $this->periodStart,
                'period_end'   => $this->periodEnd,
            ],
            ['amount' => $this->financeAmount ?? 0]
        );

        $this->financeAmount = null;
        $this->financeSubCategory = null;
        session()->flash('message', 'Finance entry saved.');
        $this->close();
    }

    public function saveMetric(): void
    {
        $this->validate([
            'councilId'   => 'required|exists:location_councils,id',
            'periodStart' => 'required|date',
            'periodEnd'   => 'required|date|after_or_equal:periodStart',
            'indicatorId' => 'required|exists:indicators,id',
            'metricValue' => 'required|numeric',
        ]);

        MetricValue::updateOrCreate(
            [
                'council_id'   => $this->councilId,
                'indicator_id' => $this->indicatorId,
                'period_start' => $this->periodStart,
                'period_end'   => $this->periodEnd,
            ],
            ['value' => $this->metricValue]
        );

        $this->metricValue = null;
        session()->flash('message', 'Indicator value saved.');
        $this->close();
    }

    public function saveProject(): void
    {
        $this->validate([
            'councilId'     => 'required|exists:location_councils,id',
            'projectTitle'  => 'required|string|max:255',
            'projectStatus' => 'required|in:planned,ongoing,completed,stalled',
            'projectBudget' => 'nullable|numeric|min:0',
        ]);

        Project::create([
            'council_id' => $this->councilId,
            'title'      => $this->projectTitle,
            'status'     => $this->projectStatus ?? 'ongoing',
            'budget'     => $this->projectBudget,
        ]);

        $this->projectTitle  = '';
        $this->projectStatus = 'ongoing';
        $this->projectBudget = null;
        session()->flash('message', 'Project created.');
        $this->close();
    }

    public function saveGovernance(): void
    {
        $this->validate([
            'councilId'          => 'required|exists:location_councils,id',
            'governanceTitle'    => 'required|string|max:255',
            'governanceType'     => ['required','string', Rule::in(array_keys($this->govTypes))],
            'governanceStatus'   => ['required','string', Rule::in($this->govStatuses)],
            'governanceDate'     => 'required|date',
            'governanceLocation' => 'nullable|string|max:255',
            'governanceNotes'    => 'nullable|string',
        ]);

        GovernanceEvent::create([
            'council_id'  => $this->councilId,
            'title'       => $this->governanceTitle,
            'type'        => $this->governanceType,
            'status'      => $this->governanceStatus,
            'occurred_at' => $this->governanceDate,
            'location'    => $this->governanceLocation,
            'notes'       => $this->governanceNotes,
        ]);

        $this->governanceTitle = '';
        $this->governanceType = 'council_meeting';
        $this->governanceStatus = 'planned';
        $this->governanceDate = now()->toDateString();
        $this->governanceLocation = null;
        $this->governanceNotes = null;

        session()->flash('message', 'Governance event recorded.');
        $this->close();
    }

    public function render()
    {
        
       
        return view('livewire.data-collector', [
            'councils'   => LocationCouncils::orderBy('councilname')->orderBy('councilname')->get(),
            'indicators' => Indicator::orderBy('name')->get(),
            'recent'     => $this->recent(),
        ])->layout('layouts.app');
    }

// app/Livewire/DataCollector.php
private function recent(): array
{
    try {
        $councilId   = $this->councilId;
        $from        = $this->periodStart ?: null;
        $to          = $this->periodEnd   ?: null;

        // Helper: period overlap
        $periodFilter = function ($q) use ($from, $to) {
            if ($from && $to) {
                // overlap: [period_start, period_end] intersects [from, to]
                $q->where(function ($qq) use ($from, $to) {
                    $qq->where('period_start', '<=', $to)
                       ->where('period_end', '>=', $from);
                });
            }
        };

        // FINANCE
        $finance = \App\Models\FinanceEntry::with('council:id,councilname')
            ->when($councilId, fn($q) => $q->where('council_id', $councilId))
            ->tap($periodFilter)
            ->orderByDesc('id')->limit(10)->get()
            ->map(function ($f) {
                $cname = $f->council->councilname ?? '-';
                return [
                    'created_at' => $f->created_at ?? now(),
                    'type'       => 'Finance',
                    'council'    => $cname,
                    'details'    => sprintf('<span class="font-medium">%s</span>%s',
                                    e($f->category),
                                    $f->sub_category ? ' · '.e($f->sub_category) : ''),
                    'when'       => sprintf('%s – %s', $f->period_start, $f->period_end),
                    'amount'     => number_format((float) $f->amount, 2),
                ];
            });

        // INDICATORS
        $metrics = \App\Models\MetricValue::with(['council:id,councilname','indicator:id,name,unit'])
            ->when($councilId, fn($q) => $q->where('council_id', $councilId))
            ->tap($periodFilter)
            ->orderByDesc('id')->limit(10)->get()
            ->map(function ($m) {
                $cname =  $m->council->councilname ?? '-';
                $ind   = $m->indicator;
                $unit  = $ind->unit ?? null;
                return [
                    'created_at' => $m->created_at ?? now(),
                    'type'       => 'Indicator',
                    'council'    => $cname,
                    'details'    => $ind ? e($ind->name.' ('.$ind->unit.')') : '-',
                    'when'       => sprintf('%s – %s', $m->period_start, $m->period_end),
                    'amount'     => isset($m->value) ? (rtrim(rtrim(number_format((float)$m->value, 4, '.', ''), '0'), '.') . ($unit ? " $unit" : '')) : '-',
                ];
            });

        // PROJECTS
        $projects = \App\Models\Project::with('council:id,councilname')
            ->when($councilId, fn($q) => $q->where('council_id', $councilId))
            ->orderByDesc('id')->limit(10)->get()
            ->map(function ($p) {
                $cname = $p->council->councilname ?? '-';
                return [
                    'created_at' => $p->created_at ?? now(),
                    'type'       => 'Project',
                    'council'    => $cname,
                    'details'    => e($p->title).' · '.e(ucfirst((string) $p->status)),
                    'when'       => optional($p->created_at)->toDateString(),
                    'amount'     => $p->budget !== null ? number_format((float) $p->budget, 2) : '-',
                ];
            });

        // GOVERNANCE
        $governance = \App\Models\GovernanceEvent::with('council:id,councilname')
            ->when($councilId, fn($q) => $q->where('council_id', $councilId))
            ->when($from && $to, fn($q) => $q->whereBetween('occurred_at', [$from, $to]))
            ->orderByDesc('id')->limit(10)->get()
            ->map(function ($g) {
                $cname =  $g->council->councilname ?? '-';
                return [
                    'created_at' => $g->created_at ?? now(),
                    'type'       => 'Governance',
                    'council'    => $cname,
                    'details'    => e($g->title).' · '.e(ucfirst((string) $g->type)).' · '.e((string) $g->status),
                    'when'       => optional($g->occurred_at)->toDateString(),
                    'amount'     => '-',
                ];
            });

        return $finance->merge($metrics)->merge($projects)->merge($governance)
            ->sortByDesc('created_at')->take(20)->values()
            ->map(function ($row) { unset($row['created_at']); return $row; })
            ->all();
    } catch (\Throwable $e) {
        report($e);
        return [];
    }
}

}
