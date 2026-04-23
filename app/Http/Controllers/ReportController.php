<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Wash;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Return a JSON list of all companies (for the filter dropdown).
     */
    public function companies()
    {
        return response()->json(
            Company::orderBy('name')->get(['id', 'name'])
        );
    }

    /**
     * Main data endpoint – supports all filter combinations:
     *
     *  ?month=2025-01               → original month/year mode
     *  ?from=2025-01-01&to=2025-03-31  → custom date range
     *  &type=all|company|cash|vehicle
     *  &company_id=5                → filter to one company (type=company)
     *  &registration=AB12CDE        → filter to one vehicle (type=vehicle)
     */
    public function data(Request $request)
    { 
        if ($request->filled('from') && $request->filled('to')) {
            $start = Carbon::parse($request->from)->startOfDay();
            $end   = Carbon::parse($request->to)->endOfDay();
        } else {
            $month = $request->month ?? now()->format('Y-m');
            $start = Carbon::parse($month . '-01')->startOfMonth();
            $end   = Carbon::parse($month . '-01')->endOfMonth();
        }

        // ── 2. Base query ─────────────────────────────────────────────────
        $type = $request->type ?? 'all';  

        $query = Wash::with('company', 'vehicle')
            ->whereBetween('created_at', [$start, $end]);

        // ── 3. Type filter ────────────────────────────────────────────────
        if ($type === 'company') {
            $query->where('is_cash', false);

            if ($request->filled('company_id')) {
                $query->where('company_id', $request->company_id);
            }
        } elseif ($type === 'cash') {
            $query->where('is_cash', true);
        } elseif ($type === 'vehicle') {
            if ($request->filled('registration')) {
                $reg = strtoupper(str_replace(' ', '', $request->registration));
                $query->where(function ($q) use ($reg) {
                     $q->whereRaw("UPPER(REPLACE(registration, ' ', '')) = ?", [$reg])
                       ->orWhereHas('vehicle', fn($v) =>
                          $v->whereRaw("UPPER(REPLACE(registration, ' ', '')) = ?", [$reg])
                      );
                });
            }
        } 

        $washes = $query->get();

        // ── 4. Summary values ─────────────────────────────────────────────
        $companyTotal = $washes->where('is_cash', false)->sum('amount');
        $cashTotal    = $washes->where('is_cash', true)->sum('amount');
        $totalWashes  = $washes->count();

        // ── 5. Company account summary ────────────────────────────────────
         
        $companySummary = collect();

        if (in_array($type, ['all', 'company'])) {
            $companySummary = $washes
                ->where('is_cash', false)
                ->groupBy('company_id')
                ->map(function ($group) {
                    $company = $group->first()->company;
                    return [
                        'company'      => $company?->name ?? 'Unknown',
                        'total_washes' => $group->count(),
                        'rate'         => $group->first()->amount,
                        'total_amount' => $group->sum('amount'),
                    ];
                })
                ->values();
        }

        // ── 6. All washes ─────────────────────────────────────────────────
        $allWashes = $washes->map(function ($wash) {
            return [
                'datetime'     => $wash->created_at->format('Y-m-d H:i'),
                'registration' => $wash->registration
                    ?? ($wash->vehicle?->registration ?? '-'),
                'driver'       => $wash->vehicle?->driver_name ?? '-',
                'company'      => $wash->is_cash
                    ? 'Cash'
                    : ($wash->company?->name ?? 'Unknown'),
                'amount'       => $wash->amount,
            ];
        });

        return response()->json([
            'company_total'   => $companyTotal,
            'cash_total'      => $cashTotal,
            'total_washes'    => $totalWashes,
            'company_summary' => $companySummary,
            'all_washes'      => $allWashes,
        ]);
    }
}