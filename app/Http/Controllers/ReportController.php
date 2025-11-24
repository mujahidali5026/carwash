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

    public function data(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = Carbon::parse($month . '-01')->endOfMonth();

        $washes = Wash::with('company')->whereBetween('created_at', [$start, $end])->get();
// ---- SUMMARY VALUES ----
$companyTotal = $washes->where('is_cash', false)->sum('amount');
$cashTotal    = $washes->where('is_cash', true)->sum('amount');
$totalWashes  = $washes->count();

// ---- COMPANY ACCOUNT SUMMARY ----
$companySummary = $washes
    ->where('is_cash', false)
    ->groupBy('company_id')
    ->map(function ($group) {
        $company = Company::find($group->first()->company_id);

        return [
            'company'      => $company?->name ?? 'Unknown',
            'total_washes' => $group->count(),
            'rate'         => $group->first()->amount,
            'total_amount' => $group->sum('amount'),
        ];
    })
    ->values();

// ---- ALL WASHES ----
$allWashes = $washes->map(function ($wash) {
    return [
        'datetime'     => $wash->created_at->format('Y-m-d H:i'),
        'registration' => $wash->registration ?? ($wash->vehicle?->registration ?? '-'),
        'driver'       => $wash->vehicle?->driver_name ?? '-',
        'company'      => $wash->is_cash ? 'Cash' : ($wash->company?->name ?? 'Unknown'),
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

        // $companyTotal = $washes->where('type', 'company')->sum('amount');
        // $cashTotal = $washes->where('type', 'cash')->sum('amount');
        // $totalWashes = $washes->count();

        // // Company summary
        // $companySummary = $washes
        //     ->where('type', 'company')
        //     ->groupBy('company_id')
        //     ->map(function ($group) {
        //         $company = Company::find($group->first()->company_id);
        //         return [
        //             'company' => $company ? $company->name : 'Unknown',
        //             'total_washes' => $group->count(),
        //             'rate' => $group->first()->amount,
        //             'total_amount' => $group->sum('amount'),
        //         ];
        //     })->values();

        // // All washes
        // $allWashes = $washes->map(function ($wash) {
        //     return [
        //         'datetime' => $wash->created_at ? $wash->created_at->format('Y-m-d H:i') : '-',
        //         'registration' => $wash->registration ?? '-',
        //         'driver' => $wash->driver ?? '-',
        //         'company' => $wash->type === 'cash'
        //             ? 'Cash'
        //             : ($wash->company?->name ?? 'Unknown'),
        //         'amount' => $wash->amount ?? 0,
        //     ];
        // });

        // return response()->json([
        //     'company_total' => number_format($companyTotal, 2),
        //     'cash_total' => number_format($cashTotal, 2),
        //     'total_washes' => $totalWashes,
        //     'company_summary' => $companySummary,
        //     'all_washes' => $allWashes,
        //     'month_label' => $month
        // ]);
    }

}
