<div class="max-w-5xl mx-auto">
    <h1 class="text-xl font-semibold text-gray-900 mb-4">Exports</h1>

    <form class="bg-white border rounded-lg p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <label class="block">
            <span class="text-xs text-gray-600">From</span>
            <input type="date" name="from" value="{{ $from }}" class="w-full border rounded px-2 py-1.5">
        </label>
        <label class="block">
            <span class="text-xs text-gray-600">To</span>
            <input type="date" name="to" value="{{ $to }}" class="w-full border rounded px-2 py-1.5">
        </label>
        <label class="block md:col-span-2">
            <span class="text-xs text-gray-600">Councils (IDs, comma-sep)</span>
            <input type="text" name="councils" placeholder="e.g. 17,18" class="w-full border rounded px-2 py-1.5">
        </label>

        <div class="md:col-span-4 flex flex-wrap gap-2 pt-2">
            <a class="border px-3 py-1.5 rounded hover:bg-gray-50"
               href="{{ route('exports.finance', request()->only('from','to','councils')) }}">Download Finance CSV</a>

            <a class="border px-3 py-1.5 rounded hover:bg-gray-50"
               href="{{ route('exports.indicators', request()->only('from','to','councils')) }}">Download Indicators CSV</a>

            <a class="border px-3 py-1.5 rounded hover:bg-gray-50"
               href="{{ route('exports.projects', request()->only('from','to','councils')) }}">Download Projects CSV</a>

            <a class="border px-3 py-1.5 rounded hover:bg-gray-50"
               href="{{ route('exports.issues', request()->only('from','to','councils')) }}">Download Issues CSV</a>
        </div>
    </form>

    <p class="mt-3 text-xs text-gray-500">
        Tip: You can also call the CSV endpoints directly, e.g.
        <code class="px-1 rounded bg-gray-50 border">/exports/finance.csv?from=2025-08-01&to=2025-08-31&councils=17,18</code>
    </p>
</div>
