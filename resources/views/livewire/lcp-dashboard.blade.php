<div class="p-6">
    {{-- Header / Period --}}
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Local Council Performance — Dashboard</h2>
            <div class="text-xs text-gray-500">Period: {{ $periodStart }} – {{ $periodEnd }}</div>
        </div>

        <div class="flex items-end gap-2">
            <label class="block">
                <span class="text-[11px] text-gray-500">From</span>
                <input type="date" wire:model.live="periodStart" class="mt-0.5 border rounded-lg px-2 py-1.5 text-sm" />
            </label>

            <span class="pb-2 text-gray-400">—</span>

            <label class="block">
                <span class="text-[11px] text-gray-500">To</span>
                <input type="date" wire:model.live="periodEnd" class="mt-0.5 border rounded-lg px-2 py-1.5 text-sm" />
            </label>

            <div class="flex items-center gap-1.5 pl-2">
                <button type="button" wire:click="quickRange('this_month')" class="border rounded-full px-2.5 py-1 text-xs hover:bg-gray-50">This month</button>
                <button type="button" wire:click="quickRange('last_3mo')"   class="border rounded-full px-2.5 py-1 text-xs hover:bg-gray-50">Last 3 mo</button>
                <button type="button" wire:click="quickRange('ytd')"        class="border rounded-full px-2.5 py-1 text-xs hover:bg-gray-50">YTD</button>
            </div>
        </div>
    </div>

    {{-- ===== KPIs: 4-up ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs text-gray-500">Councils</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($councilsCount) }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs text-gray-500">Indicators</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($indicatorsCount) }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs text-gray-500">Projects</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($projectsCount) }}</div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs text-gray-500">Open issues</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($openIssuesCount) }}</div>
        </div>
    </div>

    {{-- ===== Finance summary (4 cards) with Sparklines ===== --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        {{-- Card helper as repeated block --}}
        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs text-gray-500">Own-source revenue</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($sumRevenueOwn, 2) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Category: revenue_own</div>

            @php
                $vals = $sparkRevenue['values'] ?? [];
                $labs = $sparkRevenue['labels'] ?? [];
                $max  = max(1, (float)($sparkRevenue['max'] ?? 0));
                $w = 220; $h = 36; $c = count($vals);
                $step = $c > 1 ? ($w - 4) / ($c - 1) : 0;
                $pts = [];
                for ($i=0; $i<$c; $i++) {
                    $x = 2 + $i * $step;
                    $y = $h - 2 - ((float)$vals[$i] / $max) * ($h - 4);
                    $pts[] = $x . ',' . $y;
                }
            @endphp
            <div class="mt-3 text-gray-400">
                <svg viewBox="0 0 {{ $w }} {{ $h }}" width="{{ $w }}" height="{{ $h }}">
                    <polyline fill="none" stroke="currentColor" stroke-width="2" points="{{ implode(' ', $pts) }}"/>
                </svg>
                <div class="text-[10px] text-gray-400">{{ implode(' · ', $labs) }}</div>
            </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs text-gray-500">Central grant</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($sumGrantCentral, 2) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Category: grant_central</div>

            @php
                $vals = $sparkGrant['values'] ?? [];
                $labs = $sparkGrant['labels'] ?? [];
                $max  = max(1, (float)($sparkGrant['max'] ?? 0));
                $w = 220; $h = 36; $c = count($vals);
                $step = $c > 1 ? ($w - 4) / ($c - 1) : 0;
                $pts = [];
                for ($i=0; $i<$c; $i++) {
                    $x = 2 + $i * $step;
                    $y = $h - 2 - ((float)$vals[$i] / $max) * ($h - 4);
                    $pts[] = $x . ',' . $y;
                }
            @endphp
            <div class="mt-3 text-gray-400">
                <svg viewBox="0 0 {{ $w }} {{ $h }}" width="{{ $w }}" height="{{ $h }}">
                    <polyline fill="none" stroke="currentColor" stroke-width="2" points="{{ implode(' ', $pts) }}"/>
                </svg>
                <div class="text-[10px] text-gray-400">{{ implode(' · ', $labs) }}</div>
            </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            <div class="text-xs text-gray-500">Expenditure (all sectors)</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($sumExpenditureAll, 2) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Category: expenditure_sector</div>

            @php
                $vals = $sparkSpend['values'] ?? [];
                $labs = $sparkSpend['labels'] ?? [];
                $max  = max(1, (float)($sparkSpend['max'] ?? 0));
                $w = 220; $h = 36; $c = count($vals);
                $step = $c > 1 ? ($w - 4) / ($c - 1) : 0;
                $pts = [];
                for ($i=0; $i<$c; $i++) {
                    $x = 2 + $i * $step;
                    $y = $h - 2 - ((float)$vals[$i] / $max) * ($h - 4);
                    $pts[] = $x . ',' . $y;
                }
            @endphp
            <div class="mt-3 text-gray-400">
                <svg viewBox="0 0 {{ $w }} {{ $h }}" width="{{ $w }}" height="{{ $h }}">
                    <polyline fill="none" stroke="currentColor" stroke-width="2" points="{{ implode(' ', $pts) }}"/>
                </svg>
                <div class="text-[10px] text-gray-400">{{ implode(' · ', $labs) }}</div>
            </div>
        </div>

        <div class="bg-white border rounded-xl p-4">
            @php $totalFinance = (float)$sumRevenueOwn + (float)$sumGrantCentral + (float)$sumExpenditureAll; @endphp
            <div class="text-xs text-gray-500">Total (shown)</div>
            <div class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($totalFinance, 2) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Sum of the three cards</div>
        </div>
    </div>

    {{-- ===== Open issues list ===== --}}
    <div class="bg-white border rounded-xl shadow-sm overflow-x-auto">
        <div class="px-4 pt-4 pb-2 font-semibold text-gray-900">Open issues (top {{ $issuesLimit }})</div>
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
            </tr>
            </thead>
            <tbody>
            @forelse($issues as $i)
                <tr class="border-t align-top">
                    <td class="px-3 py-2">{{ $i['council'] }}</td>
                    <td class="px-3 py-2">
                        <div class="font-medium text-gray-900">{{ $i['title'] }}</div>
                        @if(!empty($i['description']))
                            <div class="text-[11px] text-gray-500">{{ $i['description'] }}</div>
                        @endif
                    </td>
                    <td class="px-3 py-2">{{ $i['owner'] ?: '—' }}</td>
                    <td class="px-3 py-2 capitalize">{{ $i['severity'] ?: '—' }}</td>
                    <td class="px-3 py-2 capitalize">{{ str_replace('_',' ', $i['status']) ?: '—' }}</td>
                    <td class="px-3 py-2">{{ $i['opened'] ?: '—' }}</td>
                    <td class="px-3 py-2">{{ $i['due'] ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-3 py-6 text-center text-gray-500 italic">No open issues in the selected period.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
