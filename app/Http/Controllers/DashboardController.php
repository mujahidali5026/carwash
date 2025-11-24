<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wash;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }
    // public function data(Request $request)
    // {
    //     $from = $request->query('from') ? Carbon::parse($request->query('from')) : Carbon::today();
    //     $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : Carbon::today()->endOfDay();

    //     $washes = Wash::with(['vehicle', 'company'])
    //         ->whereBetween('created_at', [$from, $to])
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     $totalWashes = $washes->count();
    //     $totalTakings = $washes->sum('amount');
    //     $accountWashes = $washes->where('is_cash', false)->count();
    //     $cashWashes = $washes->where('is_cash', true)->count();

    //     $washData = $washes->map(function ($wash) {
    //         return [
    //             'time' => $wash->created_at->format('H:i'),
    //             'registration' => $wash->vehicle?->registration ?? 'N/A',
    //             'driver' => $wash->vehicle?->driver_name ?? '-',
    //             'company' => $wash->is_cash ? '<span class="badge badge-cash">Cash</span>' : ($wash->company?->name ?? '-'),
    //             'amount' => '£' . number_format($wash->amount, 2),
    //         ];
    //     });

    //     return response()->json([
    //         'stats' => [
    //             'totalWashes' => $totalWashes,
    //             'totalTakings' => '£' . number_format($totalTakings, 2),
    //             'accountWashes' => $accountWashes,
    //             'cashWashes' => $cashWashes,
    //         ],
    //         'washes' => $washData,
    //         'today' => $from->format('d M Y') . ($from != $to ? ' - ' . $to->format('d M Y') : ''),
    //     ]);
    // }


public function data(Request $request)
{
    $from = $request->from ? Carbon::parse($request->from)->startOfDay() : Carbon::today()->startOfDay();
    $to   = $request->to ? Carbon::parse($request->to)->endOfDay() : Carbon::today()->endOfDay();

    $washes = Wash::whereBetween('created_at', [$from, $to])->get();

    $totalWashes = $washes->count();
    $accountWashes = $washes->where('is_cash', false)->count();
    $cashWashes    = $washes->where('is_cash', true)->count();
    $totalTakings  = $washes->sum('amount');

    return response()->json([
        'stats' => [
            'totalWashes' => $totalWashes,
            'accountWashes' => $accountWashes,
            'cashWashes' => $cashWashes,
            'totalTakings' => number_format($totalTakings, 2),
        ],
        'today' => $from->format('d/m/Y'),
        'washes' => $washes->map(function ($wash) {
            return [
                'time' => $wash->created_at->format('H:i'),
                'registration' => $wash->registration ?? ($wash->vehicle?->registration ?? '-'),
                'driver' => $wash->vehicle?->driver_name ?? '-',
                'company' => $wash->is_cash ? 'Cash' : ($wash->company?->name ?? 'Unknown'),
                'amount' => number_format($wash->amount, 2),
            ];
        }),
    ]);
}


}
