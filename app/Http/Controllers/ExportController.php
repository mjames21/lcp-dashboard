<?php
// app/Http/Controllers/ExportController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /** Small helper for consistent date params */
    private function period(Request $request): array
    {
        $from = $request->query('from') ?: '1900-01-01';
        $to   = $request->query('to')   ?: '2100-12-31';
        return [$from, $to];
    }

    /** Stream a CSV with UTF-8 BOM so Excel opens it nicely */
    private function streamCsv(string $filename, array $headers, \Closure $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writeRows) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, $headers);
            $writeRows($out);
            fclose($out);
        }, $filename, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /** -------------------- Finance CSV -------------------- */
    public function finance(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);

        $category = $request->query('category');         // revenue_own | grant_central | expenditure_sector
        $sector   = $request->query('sector');           // when category = expenditure_sector
        $council  = $request->query('council_id');       // optional numeric

        $headers = ['Council','Category','Sub-category','Period start','Period end','Amount'];

        return $this->streamCsv('finance.csv', $headers, function ($out) use ($from, $to, $category, $sector, $council) {
            DB::table('finance_entries AS fe')
                ->leftJoin('location_councils AS lc', 'lc.id', '=', 'fe.council_id')
                ->selectRaw('COALESCE(lc.name, lc.councilname, CONCAT("#", fe.council_id)) AS council')
                ->addSelect('fe.category','fe.sub_category','fe.period_start','fe.period_end','fe.amount')
                ->when($category, fn($q) => $q->where('fe.category', $category))
                ->when($category === 'expenditure_sector' && $sector, fn($q) => $q->where('fe.sub_category', $sector))
                ->when($council,  fn($q) => $q->where('fe.council_id', $council))
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('fe.period_start', [$from, $to])
                      ->orWhereBetween('fe.period_end',   [$from, $to])
                      ->orWhere(fn($qq) => $qq->where('fe.period_start','<=',$from)->where('fe.period_end','>=',$to));
                })
                ->orderBy('fe.id')
                ->chunk(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [$r->council, $r->category, $r->sub_category, $r->period_start, $r->period_end, $r->amount]);
                    }
                });
        });
    }

    /** -------------------- Indicators CSV -------------------- */
    public function indicators(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);

        $indicatorId = $request->query('indicator_id');  // optional filter
        $council     = $request->query('council_id');    // optional

        $headers = ['Council','Indicator','Unit','Period start','Period end','Value'];

        return $this->streamCsv('indicators.csv', $headers, function ($out) use ($from, $to, $indicatorId, $council) {
            DB::table('metric_values AS mv')
                ->leftJoin('indicators AS i', 'i.id', '=', 'mv.indicator_id')
                ->leftJoin('location_councils AS lc', 'lc.id', '=', 'mv.council_id')
                ->selectRaw('COALESCE(lc.name, lc.councilname, CONCAT("#", mv.council_id)) AS council')
                ->addSelect('i.name AS indicator','i.unit','mv.period_start','mv.period_end','mv.value')
                ->when($indicatorId, fn($q) => $q->where('mv.indicator_id', $indicatorId))
                ->when($council,     fn($q) => $q->where('mv.council_id', $council))
                ->where(function ($q) use ($from, $to) {
                    $q->whereBetween('mv.period_start', [$from, $to])
                      ->orWhereBetween('mv.period_end',   [$from, $to])
                      ->orWhere(fn($qq) => $qq->where('mv.period_start','<=',$from)->where('mv.period_end','>=',$to));
                })
                ->orderBy('mv.id')
                ->chunk(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [$r->council, $r->indicator, $r->unit, $r->period_start, $r->period_end, $r->value]);
                    }
                });
        });
    }

    /** -------------------- Projects CSV -------------------- */
    public function projects(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);
        $council     = $request->query('council_id'); // optional
        $status      = $request->query('status');     // planned|ongoing|completed|stalled (optional)

        $headers = ['Council','Title','Status','Budget','Created at'];

        return $this->streamCsv('projects.csv', $headers, function ($out) use ($from, $to, $council, $status) {
            DB::table('projects AS p')
                ->leftJoin('location_councils AS lc', 'lc.id', '=', 'p.council_id')
                ->selectRaw('COALESCE(lc.name, lc.councilname, CONCAT("#", p.council_id)) AS council')
                ->addSelect('p.title','p.status','p.budget','p.created_at')
                ->when($council, fn($q) => $q->where('p.council_id', $council))
                ->when($status,  fn($q) => $q->where('p.status', $status))
                ->whereBetween('p.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->orderBy('p.id')
                ->chunk(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [$r->council, $r->title, $r->status, $r->budget, $r->created_at]);
                    }
                });
        });
    }

    /** -------------------- Issues CSV -------------------- */
    public function issues(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);
        $council     = $request->query('council_id');       // optional
        $status      = $request->query('status');           // open|in_progress|blocked|resolved|closed (or leave)
        $severity    = $request->query('severity');         // low|medium|high|critical (optional)

        $headers = ['Council','Title','Owner','Severity','Status','Opened','Due','Closed','Tags','Source'];

        return $this->streamCsv('issues.csv', $headers, function ($out) use ($from, $to, $council, $status, $severity) {
            DB::table('key_issues AS ki')
                ->leftJoin('location_councils AS lc', 'lc.id', '=', 'ki.council_id')
                ->selectRaw('COALESCE(lc.name, lc.councilname, CONCAT("#", ki.council_id)) AS council')
                ->addSelect('ki.title','ki.owner','ki.severity','ki.status','ki.opened_at','ki.due_at','ki.closed_at','ki.tags','ki.source','ki.created_at')
                ->when($council,  fn($q) => $q->where('ki.council_id', $council))
                ->when($status,   fn($q) => $q->where('ki.status', $status))
                ->when($severity, fn($q) => $q->where('ki.severity', $severity))
                ->whereBetween('ki.created_at', [$from.' 00:00:00', $to.' 23:59:59'])
                ->orderBy('ki.created_at','desc')
                ->chunk(1000, function ($rows) use ($out) {
                    foreach ($rows as $r) {
                        fputcsv($out, [
                            $r->council, $r->title, $r->owner, $r->severity, $r->status,
                            $r->opened_at, $r->due_at, $r->closed_at, $r->tags, $r->source
                        ]);
                    }
                });
        });
    }
}
