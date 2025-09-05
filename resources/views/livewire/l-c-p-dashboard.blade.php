{{-- resources/views/livewire/dashboard.blade.php --}}
<div class="p-6">
    {{-- Header / Period --}}
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-900">Dashboard</h2>

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

    {{-- KPI tiles --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Councils</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($kpis['councils']) }}</div>
        </div>
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Indicators</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($kpis['indicators']) }}</div>
        </div>
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Projects</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($kpis['projects']) }}</div>
        </div>
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Open issues</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($kpis['issuesOpen']) }}</div>
        </div>
    </div>

    {{-- Finance snapshot --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-6">
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Own-source revenue</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($finance['own_revenue'], 2) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Category: revenue_own</div>
        </div>
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Central grant</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($finance['grants'], 2) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Category: grant_central</div>
        </div>
        <div class="bg-white border rounded p-4">
            <div class="text-xs text-gray-500">Expenditure (all sectors)</div>
            <div class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($finance['expend'], 2) }}</div>
            <div class="text-[11px] text-gray-500 mt-1">Category: expenditure_sector</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">
        {{-- Open issues --}}
        <div class="bg-white border rounded overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold text-gray-900">Open Issues</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Title</th>
                            <th class="px-3 py-2 text-left">Owner</th>
                            <th class="px-3 py-2 text-left">Council</th>
                            <th class="px-3 py-2 text-left">Severity</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Opened</th>
                            <th class="px-3 py-2 text-left">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($issues as $it)
                            <tr class="border-t">
                                <td class="px-3 py-2 text-gray-900">{{ $it['title'] }}</td>
                                <td class="px-3 py-2">{{ $it['owner'] ?: '—' }}</td>
                                <td class="px-3 py-2">{{ $it['council'] }}</td>
                                <td class="px-3 py-2 capitalize">{{ $it['severity'] ?: '—' }}</td>
                                <td class="px-3 py-2 capitalize">{{ str_replace('_',' ', $it['status']) ?: '—' }}</td>
                                <td class="px-3 py-2">{{ $it['opened'] ?: '—' }}</td>
                                <td class="px-3 py-2">{{ $it['due'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-3 py-4 text-center text-gray-500 italic">No issues found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-2 text-xs text-gray-500">Showing up to 8 latest issues.</div>
        </div>

        {{-- Recent activity --}}
        <div class="bg-white border rounded overflow-hidden">
            <div class="px-4 py-3 border-b font-semibold text-gray-900">Recent Activity</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">Type</th>
                            <th class="px-3 py-2 text-left">Council</th>
                            <th class="px-3 py-2 text-left">Details</th>
                            <th class="px-3 py-2 text-left">Period / Date</th>
                            <th class="px-3 py-2 text-left">Amount/Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $row)
                            <tr class="border-t">
                                <td class="px-3 py-2">{{ $row['type'] }}</td>
                                <td class="px-3 py-2">{{ $row['council'] }}</td>
                                <td class="px-3 py-2">{{ $row['details'] }}</td>
                                <td class="px-3 py-2">{{ $row['when'] }}</td>
                                <td class="px-3 py-2">{{ $row['amount'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-4 text-center text-gray-500 italic">No activity yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-2 text-xs text-gray-500">Last 15 items across modules.</div>
        </div>
    </div>
</div>
