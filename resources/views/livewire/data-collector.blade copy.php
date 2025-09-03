{{-- File: resources/views/livewire/data-collector.blade.php --}}
<div class="p-6" x-data="{ tab: @entangle('activeTab').defer || 'finance' }">
    <div class="flex justify-between mb-4">
        <h2 class="text-lg font-semibold">Data Collector</h2>
        <button wire:click="$set('showModal', true)"
            class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded inline-flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Entry
        </button>
    </div>

    <div class="bg-white border rounded shadow-sm p-4 mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
        <label class="block">
            <span class="text-xs text-gray-600">Council</span>
            <select wire:model.live="councilId" class="w-full border px-3 py-1.5 rounded">
                <option value="">-- Select Council --</option>
                @foreach($councils as $c)
                    <option value="{{ $c->id }}">{{ $c->councilname }}</option>
                @endforeach
            </select>
            @error('councilId')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </label>
        <label class="block">
            <span class="text-xs text-gray-600">From</span>
            <input type="date" wire:model.live="periodStart" class="w-full border px-3 py-1.5 rounded" />
            @error('periodStart')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </label>
        <label class="block">
            <span class="text-xs text-gray-600">To</span>
            <input type="date" wire:model.live="periodEnd" class="w-full border px-3 py-1.5 rounded" />
            @error('periodEnd')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
        </label>
    </div>

    <div class="bg-white border rounded shadow-sm mb-6">
        <div class="px-4 py-3 border-b flex items-center justify-between">
            <div class="text-sm font-semibold">Recent Entries</div>
            <div class="text-xs text-gray-500">Showing latest 20</div>
        </div>
        <table class="min-w-full text-sm table-auto">
            <thead class="bg-gray-100 text-xs text-left">
               

            </thead>
            <tbody>
                @forelse($recent as $row)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $row['type'] }}</td>
                        <td class="px-4 py-2">{{ $row['council'] }}</td>
                        <td class="px-4 py-2">{!! $row['details'] !!}</td>
                        <td class="px-4 py-2">{{ $row['when'] }}</td>
                        <td class="px-4 py-2">{{ $row['amount'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-3">No entries yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl p-0 text-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <h2 class="text-base font-semibold">Add Data</h2>
                    <button wire:click="$set('showModal', false)" class="text-xl text-gray-400 hover:text-gray-600" aria-label="Close">&times;</button>
                </div>

                <div class="px-6 pt-4">
                    <div class="flex gap-2 text-sm mb-4">
                        <button @click="tab='finance'"   :class="tab==='finance'   ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700'" class="px-3 py-1.5 rounded">Finance</button>
                        <button @click="tab='indicator'" :class="tab==='indicator' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700'" class="px-3 py-1.5 rounded">Indicator</button>
                        <button @click="tab='project'"   :class="tab==='project'   ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700'" class="px-3 py-1.5 rounded">Project</button>
                    </div>
                </div>

                <div class="px-6 pb-2" x-show="tab==='finance'">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <label class="block md:col-span-2">
                            <span class="text-xs text-gray-600">Category</span>
                            <select wire:model.defer="financeCategory" class="w-full border px-2 py-1.5 rounded">
                                <option value="revenue_own">Own-source revenue</option>
                                <option value="grant_central">Central grant</option>
                                <option value="expenditure_sector">Expenditure (by sector)</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="text-xs text-gray-600">Sub-category</span>
                            <input wire:model.defer="financeSubCategory" class="w-full border px-2 py-1.5 rounded" placeholder="e.g., HLTH / INFR / SANI" />
                        </label>
                        <label class="block">
                            <span class="text-xs text-gray-600">Amount</span>
                            <input type="number" step="0.01" wire:model.defer="financeAmount" class="w-full border px-2 py-1.5 rounded" />
                            @error('financeAmount')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 mt-4 border-t pt-4">
                        <button wire:click="$set('showModal', false)" class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">Cancel</button>
                        <button wire:click="saveFinance" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">Save Finance</button>
                    </div>
                </div>

                <div class="px-6 pb-2" x-show="tab==='indicator'">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="block md:col-span-2">
                            <span class="text-xs text-gray-600">Indicator</span>
                            <select wire:model.defer="indicatorId" class="w-full border px-2 py-1.5 rounded">
                                <option value="">-- Select Indicator --</option>
                                @foreach($indicators as $ind)
                                    <option value="{{ $ind->id }}">{{ $ind->name }} ({{ $ind->unit }})</option>
                                @endforeach
                            </select>
                            @error('indicatorId')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                        </label>
                        <label class="block">
                            <span class="text-xs text-gray-600">Value</span>
                            <input type="number" step="0.0001" wire:model.defer="metricValue" class="w-full border px-2 py-1.5 rounded" />
                            @error('metricValue')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 mt-4 border-t pt-4">
                        <button wire:click="$set('showModal', false)" class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">Cancel</button>
                        <button wire:click="saveMetric" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">Save Indicator</button>
                    </div>
                </div>

                <div class="px-6 pb-2" x-show="tab==='project'">
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
                        <button wire:click="$set('showModal', false)" class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">Cancel</button>
                        <button wire:click="saveProject" class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">Create Project</button>
                    </div>
                </div>

                <div class="h-3"></div>
            </div>
        </div>
    @endif

{{-- Governance form (NEW) --}}
@if ($activeTab === 'governance')
<div class="px-6 pb-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <label class="block">
            <span class="text-xs text-gray-600">Title</span>
            <input wire:model.defer="governanceTitle"
                   class="w-full border px-2 py-1.5 rounded"
                   placeholder="e.g., Quarterly Council Meeting" />
            @error('governanceTitle') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </label>

        <label class="block">
            <span class="text-xs text-gray-600">Type</span>
            <select wire:model.defer="governanceType" class="w-full border px-2 py-1.5 rounded">
                @foreach($govTypes as $code => $label)
                    <option value="{{ $code }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('governanceType') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </label>

        <label class="block">
            <span class="text-xs text-gray-600">Status</span>
            <select wire:model.defer="governanceStatus" class="w-full border px-2 py-1.5 rounded">
                @foreach($govStatuses as $st)
                    <option value="{{ $st }}">{{ ucfirst($st) }}</option>
                @endforeach
            </select>
            @error('governanceStatus') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </label>

        <label class="block">
            <span class="text-xs text-gray-600">Date</span>
            <input type="date" wire:model.defer="governanceDate" class="w-full border px-2 py-1.5 rounded" />
            @error('governanceDate') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </label>

        <label class="block md:col-span-2">
            <span class="text-xs text-gray-600">Location</span>
            <input wire:model.defer="governanceLocation"
                   class="w-full border px-2 py-1.5 rounded"
                   placeholder="e.g., Council Hall" />
            @error('governanceLocation') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </label>

        <label class="block md:col-span-2">
            <span class="text-xs text-gray-600">Notes</span>
            <textarea wire:model.defer="governanceNotes"
                      rows="3"
                      class="w-full border px-2 py-1.5 rounded"
                      placeholder="Optional notes..."></textarea>
            @error('governanceNotes') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
        </label>
    </div>

    <div class="flex justify-end gap-2 mt-4 border-t pt-4">
        <button type="button" wire:click="close"
                class="bg-white border hover:bg-gray-50 text-gray-800 font-semibold px-4 py-1.5 text-sm rounded">
            Cancel
        </button>
        <button type="button" wire:click="saveGovernance"
                class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 py-1.5 text-sm rounded">
            Save Governance
        </button>
    </div>
</div>
@endif

</div>
