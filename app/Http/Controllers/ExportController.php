<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\FinanceEntry;
use App\Models\MetricValue;
use App\Models\Indicator;
use App\Models\Project;
use App\Models\KeyIssue;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function index(Request $request)
    {
        return view('exports.index', [
            'from' => $request->input('from', now()->startOfMonth()->toDateString()),
            'to'   => $request->input('to',   now()->endOfMonth()->toDateString()),
        ])->layout('layouts.app');
    }

    // ---------------------- FINANCE ----------------------
    public function financeCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $councils = $this->csvToArray($request->input('councils'));   // e.g. ?councils=17,18
        $category = $request->input('category');                      // revenue_own|grant_central|expenditure_sector
        $sector   = $request->input('sector');                        // only for expenditure_sector

        $filename = 'finance_'.($from ?: 'all').'_to_'.($to ?: 'all').'.csv';

        return response()->streamDownload(function () use ($from, $to, $councils, $category, $sector) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['council_id','category','sub_category','period_start','period_end','amount']);

            $q = FinanceEntry::query()
                ->select(['council_id','category','sub_category','period_start','period_end','amount'])
                ->when(!empty($councils), fn($qq) => $qq->whereIn('council_id', $councils))
                ->when($category, fn($qq) => $qq->where('category', $category))
                ->when($category === 'expenditure_sector' && $sector, fn($qq) => $qq->where('sub_category', $sector))
                ->when($from && $to, function ($qq) use ($from, $to) {
                    $qq->where(function ($q) use ($from, $to) {
                        $q->whereBetween('period_start', [$from, $to])
                          ->orWhereBetween('period_end',   [$from, $to])
                          ->orWhere(function ($x) use ($from, $to) {
                              $x->where('period_start', '<=', $from)
                                ->where('period_end',   '>=', $to);
                          });
                    });
                })
                ->orderBy('council_id')->orderBy('category')->orderBy('sub_category');

            foreach ($q->cursor() as $row) {
                fputcsv($out, [
                    $row->council_id,
                    $row->category,
                    $row->sub_category,
                    $row->period_start,
                    $row->period_end,
                    (string) $row->amount,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate',
            'X-Accel-Buffering'   => 'no',
        ]);
    }

    // ---------------------- INDICATORS ----------------------
    public function indicatorsCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $councils   = $this->csvToArray($request->input('councils'));
        $indicators = $this->csvToArray($request->input('indicators')); // e.g. ?indicators=12,34

        $filename = 'indicators_'.($from ?: 'all').'_to_'.($to ?: 'all').'.csv';

        return response()->streamDownload(function () use ($from, $to, $councils, $indicators) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['council_id','indicator_id','indicator','unit','period_start','period_end','value']);

            $indicatorMap = Indicator::query()->select('id','name','unit')->get()->keyBy('id');

            $q = MetricValue::query()
                ->select(['council_id','indicator_id','period_start','period_end','value'])
                ->when(!empty($councils),  fn($qq) => $qq->whereIn('council_id', $councils))
                ->when(!empty($indicators),fn($qq) => $qq->whereIn('indicator_id', $indicators))
                ->when($from && $to, function ($qq) use ($from, $to) {
                    $qq->where(function ($q) use ($from, $to) {
                        $q->whereBetween('period_start', [$from, $to])
                          ->orWhereBetween('period_end',   [$from, $to])
                          ->orWhere(function ($x) use ($from, $to) {
                              $x->where('period_start', '<=', $from)
                                ->where('period_end',   '>=', $to);
                          });
                    });
                })
                ->orderBy('indicator_id')->orderBy('council_id')->orderBy('period_end');

            foreach ($q->cursor() as $row) {
                $meta = $indicatorMap[$row->indicator_id] ?? null;
                fputcsv($out, [
                    $row->council_id,
                    $row->indicator_id,
                    $meta->name ?? ('Indicator #'.$row->indicator_id),
                    $meta->unit ?? '',
                    $row->period_start,
                    $row->period_end,
                    (string) $row->value,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type'      => 'text/csv; charset=UTF-8',
            'Cache-Control'     => 'no-store, no-cache, must-revalidate',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    // ---------------------- PROJECTS ----------------------
    public function projectsCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $councils    = $this->csvToArray($request->input('councils'));

        $filename = 'projects_'.($from ?: 'all').'_to_'.($to ?: 'all').'.csv';

        return response()->streamDownload(function () use ($from, $to, $councils) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['council_id','title','status','budget','created_at']);

            $q = Project::query()
                ->select(['council_id','title','status','budget','created_at'])
                ->when(!empty($councils), fn($qq) => $qq->whereIn('council_id', $councils))
                ->when($from && $to,    fn($qq) => $qq->whereBetween('created_at', [$from.' 00:00:00',$to.' 23:59:59']))
                ->orderBy('council_id')->orderBy('created_at');

            foreach ($q->cursor() as $row) {
                fputcsv($out, [
                    $row->council_id,
                    $row->title,
                    $row->status,
                    (string) $row->budget,
                    $row->created_at,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ---------------------- ISSUES ----------------------
    public function issuesCsv(Request $request): StreamedResponse
    {
        [$from, $to] = $this->dateRange($request);
        $councils    = $this->csvToArray($request->input('councils'));
        $status      = $request->input('status');   // open|in_progress|blocked|resolved|closed
        $severity    = $request->input('severity'); // low|medium|high|critical

        $filename = 'issues_'.($from ?: 'all').'_to_'.($to ?: 'all').'.csv';

        return response()->streamDownload(function () use ($from, $to, $councils, $status, $severity) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['council_id','title','owner','status','severity','opened_at','due_at','closed_at','source','tags']);

            $q = KeyIssue::query()
                ->select(['council_id','title','owner','status','severity','opened_at','due_at','closed_at','source','tags','created_at'])
                ->when(!empty($councils), fn($qq) => $qq->whereIn('council_id', $councils))
                ->when($status,         fn($qq) => $qq->where('status', $status))
                ->when($severity,       fn($qq) => $qq->where('severity', $severity))
                ->when($from && $to,    fn($qq) => $qq->whereBetween('created_at', [$from.' 00:00:00',$to.' 23:59:59']))
                ->orderBy('created_at','desc');

            foreach ($q->cursor() as $row) {
                fputcsv($out, [
                    $row->council_id,
                    $row->title,
                    $row->owner,
                    $row->status,
                    $row->severity,
                    $row->opened_at,
                    $row->due_at,
                    $row->closed_at,
                    $row->source,
                    $row->tags,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // ---------------------- helpers ----------------------
    private function dateRange(Request $r): array
    {
        $from = $r->input('from');
        $to   = $r->input('to');
        if (!$from && !$to) {
            $from = now()->startOfMonth()->toDateString();
            $to   = now()->endOfMonth()->toDateString();
        }
        return [$from, $to];
    }

    private function csvToArray(?string $csv): array
    {
        if (!$csv) return [];
        return collect(explode(',', $csv))
            ->map(fn($x) => (int) trim($x))
            ->filter()
            ->values()
            ->all();
    }
}
