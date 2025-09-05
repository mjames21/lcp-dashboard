<div class="p-6 relative">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show"
             class="fixed top-5 right-5 bg-green-600 text-white text-sm px-4 py-2 rounded shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    {{-- Summary --}}
    @php
        $collection   = collect($recent);
        $cntFinance   = $collection->where('type','Finance')->count();
        $cntIndicator = $collection->where('type','Indicator')->count();
        $cntProject   = $collection->where('type','Project')->count();
        $selected     = optional(($councils instanceof \Illuminate\Support\Collection ? $councils : collect($councils))->firstWhere('id', $councilId));
    @endphp
    <div class="mb-3 flex flex-wrap items-center gap-3 text-sm">
        <span class="inline-flex items-center gap-2 border rounded px-3 py-1 bg-white"><strong>Finance:</strong> {{ $cntFinance }}</span>
        <span class="inline-flex items-center gap-2 border rounded px-3 py-1 bg-white"><strong>Indicators:</strong> {{ $cntIndicator }}</span>
        <span class="inline-flex items-center gap-2 border rounded px-3 py-1 bg-white"><strong>Projects:</strong> {{ $cntProject }}</span>
        <span class="inline-flex items-center gap-2 border rounded px-3 py-1 bg-white"><strong>Period:</strong> {{ $periodStart }} – {{ $periodEnd }}</span>
        <span class="inline-flex items-center gap-2 border rounded px-3 py-1 bg-white"><strong>Council:</strong> {{ $selected->name ?? $selected->councilname ?? 'All/Unset' }}</span>
    </div>

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold">Data Collector</h2>
        <button type="button" wire:click="create"
                class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Entry
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-4 items-end mb-4 text-sm">
        <div>
            <label class="block text-gray-700 mb-1">Council</label>
            <select wire:model.live="councilId" class="border rounded px-2 py-1">
                <option value="">-- Select Council --</option>
                @foreach($councils as $c)
                    <option value="{{ $c->id }}">{{ $c->name ?? $c->councilname }}</option>
                @endforeach
            </select>
            @error('councilId')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </div>

        <div>
            <label class="block text-gray-700 mb-1">From</label>
            <input type="date" wire:model.live="periodStart" class="border rounded px-2 py-1" />
            @error('periodStart')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="block text-gray-700 mb-1">To</label>
            <input type="date" wire:model.live="periodEnd" class="border rounded px-2 py-1" />
            @error('periodEnd')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Recent Entries --}}
   <div class="bg-white border rounded mb-6 shadow-sm">
    <h3 class="text-md font-semibold px-4 pt-4">Recent Entries</h3>
    <div class="overflow-auto">
        <table class="min-w-full text-sm table-auto mt-2 border-collapse">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="px-3 py-2">Type</th>
                    <th class="px-3 py-2">Council</th>
                    <th class="px-3 py-2">Details</th>
                    <th class="px-3 py-2">Period / Date</th>
                    <th class="px-3 py-2">Amount/Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $row)
                    @php
                        $type = $row['type'] ?? '-';
                        $badge = match($type) {
                            'Finance'    => 'bg-emerald-100 text-emerald-800',
                            'Indicator'  => 'bg-indigo-100 text-indigo-800',
                            'Project'    => 'bg-amber-100 text-amber-800',
                            'Governance' => 'bg-sky-100 text-sky-800',
                            default      => 'bg-gray-100 text-gray-700',
                        };
                        $value = $row['amount'] ?? '-';
                        // If backend starts adding a 'unit' key for indicator entries, append it.
                        if (($row['type'] ?? null) === 'Indicator' && !empty($row['unit'] ?? null)) {
                            $value = trim(($row['amount'] ?? '-') . ' ' . $row['unit']);
                        }
                    @endphp
                    <tr class="border-t hover:bg-gray-50" wire:key="recent-{{ $loop->index }}">
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $badge }}">
                                {{ $type }}
                            </span>
                        </td>
                        <td class="px-3 py-2">{{ $row['council'] ?? '-' }}</td>
                        <td class="px-3 py-2">{!! $row['details'] ?? '-' !!}</td>
                        <td class="px-3 py-2">{{ $row['when'] ?? '-' }}</td>
                        <td class="px-3 py-2">{{ $value }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-3 text-center text-gray-500 italic">No entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3 text-xs text-gray-500">Showing up to 20 latest items.</div>
</div>

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-xl p-6 text-sm">
        <div class="flex justify-between items-center mb-4">
                <h2 class="text-base font-semibold">Add Data</h2>
                <button type="button" wire:click="close" class="text-xl text-gray-400 hover:text-gray-600" aria-label="Close">&times;</button>
            </div>

            {{-- Tabs --}}
            <div class="px-6 pt-4">
                <div class="flex gap-2 text-sm mb-4">
                    <button type="button" wire:click="$set('activeTab','finance')"   class="px-3 py-1.5 rounded {{ $activeTab==='finance' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700' }}">Finance</button>
                    <button type="button" wire:click="$set('activeTab','indicator')" class="px-3 py-1.5 rounded {{ $activeTab==='indicator' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700' }}">Indicator</button>
                    <button type="button" wire:click="$set('activeTab','project')"   class="px-3 py-1.5 rounded {{ $activeTab==='project' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700' }}">Project</button>
                    <button type="button" wire:click="$set('activeTab','issue')"class="px-3 py-1.5 rounded {{ $activeTab==='issue' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700' }}">Issue</button>

                </div>
            </div>


        @if ($activeTab==='finance')
        <div class="px-6 pb-2">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <label class="block md:col-span-2">
                    <span class="text-xs text-gray-600">Category</span>
                    <select wire:model.live="financeCategory"  {{-- live so the UI toggles instantly --}}
                            class="w-full border px-2 py-1.5 rounded">
                        <option value="revenue_own">Own-source revenue</option>
                        <option value="grant_central">Central grant</option>
                        <option value="expenditure_sector">Expenditure (by sector)</option>
                    </select>
                </label>

                {{-- Sub-category switches between dropdown (sectors) and a simple optional text --}}
                <label class="block">
                    <span class="text-xs text-gray-600">Sub-category</span>

                    @if ($financeCategory === 'expenditure_sector')
                        <select wire:model.defer="financeSubCategory" class="w-full border px-2 py-1.5 rounded">
                            <option value="">-- Select sector --</option>
                            @foreach($sectors as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                        <div class="text-[11px] text-gray-500 mt-1">Pick a valid sector code.</div>
                        @error('financeSubCategory')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                    @else
                        <input wire:model.defer="financeSubCategory"
                               class="w-full border px-2 py-1.5 rounded"
                               placeholder="Optional tag (e.g., other)" />
                        @error('financeSubCategory')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                    @endif
                </label>

                <label class="block">
                    <span class="text-xs text-gray-600">Amount</span>
                    <input type="number" step="0.01" wire:model.defer="financeAmount" class="w-full border px-2 py-1.5 rounded" />
                    @error('financeAmount')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                </label>
            </div>

            <div class="flex justify-end gap-2 mt-4 border-t pt-4">
                <button type="button" wire:click="close" class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">Cancel</button>
                <button type="button" wire:click="saveFinance" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">Save Finance</button>
            </div>
        </div>
        @endif


            @if ($activeTab==='indicator')
            @php
                /** @var \Illuminate\Support\Collection $indicators */
                $selectedInd = $indicators instanceof \Illuminate\Support\Collection
                    ? $indicators->firstWhere('id', $indicatorId)
                    : collect($indicators)->firstWhere('id', $indicatorId);
                $unit = $selectedInd->unit ?? null;
                // Gentle step based on unit; fine-tune if you later store min/max per indicator
                $step = in_array(strtolower((string) $unit), ['%','percent','ratio']) ? '0.01' : '0.0001';
            @endphp
            <div class="px-6 pb-2">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <label class="block md:col-span-2">
                        <span class="text-xs text-gray-600">Indicator</span>
                        <select wire:model.live="indicatorId" class="w-full border px-2 py-1.5 rounded">
                            <option value="">-- Select Indicator --</option>
                            @foreach($indicators as $ind)
                                <option value="{{ $ind->id }}">{{ $ind->name }} ({{ $ind->unit }})</option>
                            @endforeach
                        </select>
                        @error('indicatorId')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror

                        {{-- Live hint about the selected indicator --}}
                        @if($selectedInd)
                            <div class="mt-1 text-[11px] text-gray-500">
                                <span class="font-medium">Unit:</span> {{ $unit ?: 'n/a' }}
                                {{-- If later you add min/max columns to indicators table, show them: --}}
                                @if(isset($selectedInd->min) || isset($selectedInd->max))
                                    · <span class="font-medium">Range:</span>
                                    {{ $selectedInd->min ?? '—' }} … {{ $selectedInd->max ?? '—' }}
                                @endif
                            </div>
                        @endif
                    </label>

                    <label class="block">
                        <span class="text-xs text-gray-600">Value</span>
                        <input
                            type="number"
                            step="{{ $step }}"
                            wire:model.defer="metricValue"
                            class="w-full border px-2 py-1.5 rounded"
                            @if($unit) placeholder="Enter value ({{ $unit }})" @endif
                        />
                        @error('metricValue')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                    </label>
                </div>

                <div class="flex justify-end gap-2 mt-4 border-t pt-4">
                    <button type="button" wire:click="close" class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">Cancel</button>
                    <button type="button" wire:click="saveMetric" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">Save Indicator</button>
                </div>
            </div>
            @endif

            {{-- Project form --}}
            @if ($activeTab==='project')
            <div class="px-6 pb-2">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                    <label class="block md:col-span-2">
                        <span class="text-xs text-gray-600">Title</span>
                        <input wire:model.defer="projectTitle" class="w-full border px-2 py-1.5 rounded" placeholder="Project title" />
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-600">Status</span>
                        <select wire:model.defer="projectStatus" class="w-full border px-2 py-1.5 rounded">
                            <option value="planned">Planned</option>
                            <option value="ongoing">Ongoing</option>
                            <option value="completed">Completed</option>
                            <option value="stalled">Stalled</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="text-xs text-gray-600">Budget</span>
                        <input type="number" step="0.01" wire:model.defer="projectBudget" class="w-full border px-2 py-1.5 rounded" />
                    </label>
                    <div class="hidden md:block"></div>
                </div>
                <div class="flex justify-end gap-2 mt-4 border-t pt-4">
                    <button type="button" wire:click="close" class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">Cancel</button>
                    <button type="button" wire:click="saveProject" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">Create Project</button>
                </div>
            </div>
            @endif
            {{-- Issue form --}}
@if ($activeTab==='issue')
<div class="px-6 pb-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <label class="block">
            <span class="text-xs text-gray-600">Title</span>
            <input wire:model.defer="issueTitle" class="w-full border px-2 py-1.5 rounded" placeholder="Short issue title" />
            @error('issueTitle')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </label>

        <label class="block">
            <span class="text-xs text-gray-600">Owner</span>
            <input wire:model.defer="issueOwner" class="w-full border px-2 py-1.5 rounded" placeholder="e.g., MoF (FDD)" />
        </label>

        <label class="block">
            <span class="text-xs text-gray-600">Priority</span>
            <select wire:model.defer="issuePriority" class="w-full border px-2 py-1.5 rounded">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
            </select>
        </label>

        <label class="block">
            <span class="text-xs text-gray-600">Status</span>
            <select wire:model.defer="issueStatus" class="w-full border px-2 py-1.5 rounded">
                <option value="open">Open</option>
                <option value="in_progress">In progress</option>
                <option value="blocked">Blocked</option>
                <option value="resolved">Resolved</option>
                <option value="closed">Closed</option>
            </select>
        </label>

        <label class="block">
            <span class="text-xs text-gray-600">Due date (optional)</span>
            <input type="date" wire:model.defer="issueDueAt" class="w-full border px-2 py-1.5 rounded" />
        </label>

        <label class="block md:col-span-2">
            <span class="text-xs text-gray-600">Notes</span>
            <textarea wire:model.defer="issueNotes" rows="3" class="w-full border px-2 py-1.5 rounded"
                      placeholder="Optional details..."></textarea>
        </label>
    </div>

    <div class="flex justify-end gap-2 mt-4 border-t pt-4">
        <button type="button" wire:click="close" class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">Cancel</button>
        <button type="button" wire:click="saveIssue" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">Save Issue</button>
    </div>
</div>
@endif

            <div class="h-3"></div>
        </div>
    </div>
    @endif
</div>
