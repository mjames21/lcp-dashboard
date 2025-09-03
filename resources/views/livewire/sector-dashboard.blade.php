<div class="p-6">
    {{-- Header / Period --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-4">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">
                Sector: {{ $sector->sector }}
            </h2>
            <div class="text-xs text-gray-500">Sector code: {{ $sector->sector }}</div>
        </div>

        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-600">From</label>
            <input type="date" wire:model.live="periodStart" class="border rounded px-2 py-1.5" />
            <label class="text-sm text-gray-600 ml-2">To</label>
            <input type="date" wire:model.live="periodEnd" class="border rounded px-2 py-1.5" />
            <div class="flex gap-1 ml-2">
                <button type="button" wire:click="quickRange('this_month')" class="text-xs border rounded px-2 py-1 hover:bg-gray-50">This month</button>
                <button type="button" wire:click="quickRange('last_3mo')" class="text-xs border rounded px-2 py-1 hover:bg-gray-50">Last 3 mo</button>
                <button type="button" wire:click="quickRange('ytd')" class="text-xs border rounded px-2 py-1 hover:bg-gray-50">YTD</button>
            </div>
        </div>
    </div>

    {{-- Sector Pills --}}
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach($sectorTabs as $tab)
            <a href="{{ route('sector.dashboard', ['code' => $tab['code']]) }}"
               class="px-3 py-1.5 rounded-full border text-sm
                     {{ $tab['code'] === $sector->sector ? 'bg-gray-800 text-white border-gray-800' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                {{ $tab['code'] }}
            </a>
        @endforeach
    </div>

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Finance (expenditure)</div>
            <div class="text-2xl font-semibold text-gray-800 mt-1">
                {{ number_format($financeTotal, 2) }} <span class="text-sm text-gray-500">SLE</span>
            </div>
            <div class="text-[11px] text-gray-500 mt-1">Category: expenditure_sector · Sub: {{ $sector->sector }}</div>
        </div>
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Indicators</div>
            <div class="text-2xl font-semibold text-gray-800 mt-1">{{ count($indicators) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Total indicators in this sector</div>
        </div>
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Councils compared</div>
            <div class="text-2xl font-semibold text-gray-800 mt-1">{{ count($headers) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Change selection below</div>
        </div>
    </div>

    {{-- Pickers --}}
    <div class="bg-white border rounded p-4 mb-5">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <label class="block">
                <span class="text-xs text-gray-600">Councils</span>
                <select wire:model.live="councilIds" multiple size="8" class="border rounded px-2 py-1.5 w-64">
                    @foreach($councils as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
                <div class="text-[11px] text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple.</div>
            </label>

            <label class="block md:col-span-2">
                <span class="text-xs text-gray-600">Indicators ({{ count($indicators) }})</span>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    <div class="border rounded p-2 h-48 overflow-auto">
                        @foreach($indicators as $i)
                            <div class="text-sm text-gray-800">
                                {{ $i['name'] }}
                                @if($i['unit']) <span class="text-gray-500">({{ $i['unit'] }})</span> @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="text-xs text-gray-500 self-center">
                        Table shows the <span class="font-medium text-gray-800">latest</span> value per council inside the selected period.
                    </div>
                </div>
            </label>
        </div>
    </div>

    {{-- Results --}}
    <div class="bg-white border rounded shadow-sm overflow-x-auto">
        <table class="min-w-full text-sm table-auto">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Indicator</th>
                    @foreach($headers as $h)
                        <th class="px-3 py-2 text-left">{{ $h['name'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $row)
                    <tr class="border-t">
                        <td class="px-3 py-2 align-top">
                            <div class="font-medium text-gray-800">{{ $row['indicator'] }}</div>
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
                            <td class="px-3 py-2">
                                @if($val === null)
                                    <span class="text-gray-400">—</span>
                                @else
                                    <div class="mb-1 font-mono text-xs text-gray-800">
                                        {{ rtrim(rtrim(number_format($val, 4, '.', ''), '0'), '.') }}
                                    </div>
                                    <div class="h-2 bg-gray-100 rounded">
                                        <div class="h-2 rounded bg-gray-800" style="width: {{ $pct }}%"></div>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 1 + count($headers) }}" class="px-3 py-4 text-center text-gray-500 italic">
                            No matching data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-2 text-[11px] text-gray-500">
        Period: {{ $periodStart }} – {{ $periodEnd }} · Showing {{ count($rows) }} indicators across {{ count($headers) }} council(s).
    </div>
</div>
