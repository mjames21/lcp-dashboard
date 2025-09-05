{{-- resources/views/livewire/compare-councils.blade.php --}}
<div class="p-6">
    {{-- Header + Period controls (top-right) --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
        <h2 class="text-xl font-semibold text-gray-900">Compare Councils</h2>

        <div class="flex flex-wrap items-end gap-2">
            <label class="block">
                <span class="text-[11px] text-gray-500">From</span>
                <input type="date" wire:model.live="periodStart"
                       class="mt-0.5 border rounded-lg px-2 py-1.5 text-sm" />
            </label>

            <span class="pb-2 text-gray-400">—</span>

            <label class="block">
                <span class="text-[11px] text-gray-500">To</span>
                <input type="date" wire:model.live="periodEnd"
                       class="mt-0.5 border rounded-lg px-2 py-1.5 text-sm" />
            </label>
        </div>
    </div>

    {{-- Filters / Controls --}}
    <div class="bg-white border rounded-xl shadow-sm p-5 mb-6">
        {{-- Tabs (active: gray-800) --}}
        <div class="flex gap-2 mb-5">
            <button type="button"
                    wire:click="$set('mode','indicator')"
                    class="px-3 py-1.5 text-sm rounded-lg border transition
                           {{ $mode==='indicator' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200' }}">
                Indicators
            </button>
            <button type="button"
                    wire:click="$set('mode','finance')"
                    class="px-3 py-1.5 text-sm rounded-lg border transition
                           {{ $mode==='finance' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200' }}">
                Finance
            </button>
            <button type="button"
                    wire:click="$set('mode','project')"
                    class="px-3 py-1.5 text-sm rounded-lg border transition
                           {{ $mode==='project' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200' }}">
                Projects
            </button>
            <button type="button"
                    wire:click="$set('mode','issue')"
                    class="px-3 py-1.5 text-sm rounded-lg border transition
                           {{ $mode==='issue' ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-200' }}">
                Issues
            </button>
        </div>

        {{-- Row 1: side-by-side lists / controls --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Councils (compact width) --}}
            <label class="block">
                <span class="text-xs text-gray-600">Councils</span>
                <select wire:model.live="councilIds" multiple size="10"
                        class="mt-1 w-full max-w-[18rem] border rounded-lg px-2 py-1.5">
                    @foreach($councils as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
                <div class="text-[11px] text-gray-500 mt-1">
                    Selected: {{ count($councilIds) }} · Hold Ctrl/Cmd to select multiple.
                </div>
            </label>

            {{-- Right column varies by mode --}}
            <div class="grid grid-cols-1 gap-6">
                {{-- Indicators picker --}}
                @if($mode === 'indicator')
                    <label class="block">
                        <span class="text-xs text-gray-600">Indicators</span>
                        <select wire:model.live="indicatorIds" multiple size="10"
                                class="mt-1 w-full max-w-[18rem] border rounded-lg px-2 py-1.5">
                            @foreach($indicators as $i)
                                <option value="{{ $i['id'] }}">{{ $i['name'] }}@if($i['unit']) ({{ $i['unit'] }})@endif</option>
                            @endforeach
                        </select>
                        <div class="text-[11px] text-gray-500 mt-1">
                            Selected: {{ count($indicatorIds) }} · Hold Ctrl/Cmd to select multiple.
                        </div>
                    </label>
                @endif

                {{-- Finance controls --}}
                @if($mode === 'finance')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-[36rem]">
                        <label class="block">
                            <span class="text-xs text-gray-600">Finance category</span>
                            <select wire:model.live="financeCategory"
                                    class="mt-1 w-full border rounded-lg px-2 py-1.5">
                                <option value="revenue_own">Own-source revenue</option>
                                <option value="grant_central">Central grant</option>
                                <option value="expenditure_sector">Expenditure (by sector)</option>
                            </select>
                        </label>

                        @if($financeCategory === 'expenditure_sector')
                            <label class="block">
                                <span class="text-xs text-gray-600">Sector (sub-category)</span>
                                <select wire:model.live="financeSubCategory"
                                        class="mt-1 w-full border rounded-lg px-2 py-1.5">
                                    <option value="">— Any sector —</option>
                                    @foreach($sectors as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                    </div>
                @endif

                {{-- Project controls --}}
                @if($mode === 'project')
                    <label class="block max-w-[18rem]">
                        <span class="text-xs text-gray-600">Project aggregation</span>
                        <select wire:model.live="projectAgg"
                                class="mt-1 w-full border rounded-lg px-2 py-1.5">
                            <option value="count">Count projects</option>
                            <option value="sum_budget">Sum of budget</option>
                        </select>
                    </label>
                @endif

                {{-- Issue controls --}}
                @if($mode === 'issue')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-[36rem]">
                        <label class="block">
                            <span class="text-xs text-gray-600">Status</span>
                            <select wire:model.live="issueStatus"
                                    class="mt-1 w-full border rounded-lg px-2 py-1.5">
                                <option value="open_any">Open (any)</option>
                                <option value="closed_any">Closed (any)</option>
                                <option value="all">All</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-xs text-gray-600">Severity</span>
                            <select wire:model.live="issueSeverity"
                                    class="mt-1 w-full border rounded-lg px-2 py-1.5">
                                <option value="">All</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </label>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 2: Stat (when applicable) + Reset --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 items-end">
            @if($mode !== 'project' && $mode !== 'issue')
                <label class="block max-w-[18rem]">
                    <span class="text-xs text-gray-600">Statistic</span>
                    <select wire:model.live="stat"
                            class="mt-1 w-full border rounded-lg px-2 py-1.5">
                        <option value="latest">Latest in period</option>
                        <option value="avg">Average over period</option>
                        <option value="sum">Sum over period</option>
                    </select>
                </label>
            @endif

            <div class="sm:col-span-2">
                <button wire:click="resetFilters" type="button"
                        class="mt-2 inline-flex items-center gap-2 border px-3 py-1.5 rounded-lg text-sm hover:bg-gray-50">
                    Reset all
                </button>
            </div>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white border rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">{{ $this->leadHeading }}</th>
                    @foreach($headers as $h)
                        <th class="px-3 py-2 text-left">{{ $h['name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-t">
                        <td class="px-3 py-2 align-top">
                            <div class="font-medium text-gray-900">{{ $row['indicator'] }}</div>
                            @if(!empty($row['unit']))
                                <div class="text-[11px] text-gray-500">Unit: {{ $row['unit'] }}</div>
                            @endif
                        </td>
                        @foreach($headers as $h)
                            @php
                                $val = $row['values'][$h['id']] ?? null;
                                $max = max(1, (float) ($row['max'] ?? 0));
                                $pct = $val === null ? 0 : min(100, round(($val / $max) * 100));
                            @endphp
                            <td class="px-3 py-2 align-middle">
                                @if($val === null)
                                    <span class="text-gray-400">—</span>
                                @else
                                    <div class="mb-1 font-mono text-xs text-gray-900">
                                        {{ rtrim(rtrim(number_format($val, 4, '.', ''), '0'), '.') }}
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded">
                                        <div class="h-2 rounded bg-gray-800/70" style="width: {{ $pct }}%"></div>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + count($headers) }}" class="px-3 py-6 text-center text-gray-500 italic">
                            No matching data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
    </div>
    {{-- Issue details (title / owner / description) --}}
@if($mode === 'issue')
    <div class="bg-white border rounded-xl shadow-sm overflow-x-auto mt-6">
        <div class="px-4 pt-4 pb-2 font-semibold text-gray-900">Issue details</div>
        <table class="min-w-full text-sm table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left">Council</th>
                    <th class="px-3 py-2 text-left">Title</th>
                    <th class="px-3 py-2 text-left">Owner</th>
                    <th class="px-3 py-2 text-left">Severity</th>
                    <th class="px-3 py-2 text-left">Status</th>
                    <th class="px-3 py-2 text-left">Opened</th>
                    <th class="px-3 py-2 text-left">Due</th>
                    <th class="px-3 py-2 text-left">Closed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($issueDetails as $it)
                    <tr class="border-t align-top">
                        <td class="px-3 py-2">{{ $it['council'] }}</td>
                        <td class="px-3 py-2">
                            <div class="font-medium text-gray-900">{{ $it['title'] }}</div>
                            @if(!empty($it['description']))
                                <div class="text-[11px] text-gray-500">{{ $it['description'] }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $it['owner'] ?: '—' }}</td>
                        <td class="px-3 py-2 capitalize">{{ $it['severity'] ?: '—' }}</td>
                        <td class="px-3 py-2 capitalize">{{ str_replace('_',' ', $it['status']) ?: '—' }}</td>
                        <td class="px-3 py-2">{{ $it['opened'] ?: '—' }}</td>
                        <td class="px-3 py-2">{{ $it['due'] ?: '—' }}</td>
                        <td class="px-3 py-2">{{ $it['closed'] ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-3 py-6 text-center text-gray-500 italic">
                            No issues match the filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif


    {{-- Footer --}}
    <div class="mt-2 text-[11px] text-gray-500">
        Mode: {{ ucfirst($mode) }} · Showing {{ count($rows) }} row(s) across {{ count($headers) }} council(s).
    </div>
</div>
