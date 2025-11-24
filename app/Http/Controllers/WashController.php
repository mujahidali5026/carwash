<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Wash;
use App\Models\Company;
use Carbon\Carbon;

class WashController extends Controller
{
    public function index()
    {
        return view('washes.index');
    }

    // Lookup Vehicle
    public function lookupVehicle(Request $request)
    {
        $request->validate([
            'registration' => 'required|string|max:255'
        ]);

        $reg = strtoupper($request->registration);
        $vehicle = Vehicle::with('company')->where('registration', $reg)->first();

        if($vehicle){
            // Calculate today's washes
            $todayWashes = Wash::where('vehicle_id', $vehicle->id)
                                ->whereDate('created_at', Carbon::today())
                                ->count();

            return response()->json([
                'exists' => true,
                'vehicle' => [
                    'id' => $vehicle->id,
                    'registration' => $vehicle->registration,
                    'driver_name' => $vehicle->driver_name,
                    'company_name' => $vehicle->company ? $vehicle->company->name : '-',
                    'company_id' => $vehicle->company_id,
                    'today' => $todayWashes,
                    'limit' => $vehicle->override_limit ?? ($vehicle->company->daily_limit ?? 1),
                    'price' => $vehicle->custom_price ?? ($vehicle->company->price_per_wash ?? 6.00),
                    'banned' => $vehicle->banned
                ]
            ]);
        } else {
            return response()->json(['exists' => false]);
        }
    }

    // Record Wash for registered vehicle
    public function recordWash(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'signature' => 'nullable|string'
        ]);

        $vehicle = Vehicle::find($request->vehicle_id);

        if($vehicle->banned){
            return response()->json(['status'=>'error','message'=>'Vehicle is banned']);
        }

        // Get today's washes
        $todayWashes = Wash::where('vehicle_id', $vehicle->id)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $limit = $vehicle->override_limit ?? ($vehicle->company->daily_limit ?? 1);

        if($todayWashes >= $limit){
            return response()->json(['status'=>'limit_reached','message'=>'Daily limit reached']);
        }

        $wash = Wash::create([
            'vehicle_id' => $vehicle->id,
            'company_id' => $vehicle->company_id,
            'amount' => $vehicle->custom_price ?? ($vehicle->company->price_per_wash ?? 6.00),
            'is_cash' => false,
            'signature' => $request->signature,
        ]);

        return response()->json(['status'=>'success','wash'=>$wash]);
    }

    // Record Cash Wash for unregistered vehicle
    // public function recordCashWash(Request $request)
    // {
    //     $request->validate([
    //         'registration' => 'required|string|max:255',
    //         'amount' => 'required|numeric',
    //         'signature' => 'nullable|string'
    //     ]);

    //     $wash = Wash::create([
    //         'vehicle_id' => null,
    //         'company_id' => null,
    //         'amount' => $request->amount,
    //         'is_cash' => true,
    //         'signature' => $request->signature,
    //     ]);

    //     return response()->json(['status'=>'success','wash'=>$wash]);
    // }
    public function recordCashWash(Request $request)
{
    $request->validate([
        'registration' => 'required|string|max:255',
        'amount' => 'required|numeric',
        'signature' => 'nullable|string'
    ]);

    $wash = Wash::create([
        'vehicle_id' => null,
        'company_id' => null,
        'registration' => strtoupper($request->registration), // store registration
        'amount' => $request->amount,
        'is_cash' => true,
        'signature' => $request->signature,
    ]);

    return response()->json(['status'=>'success','wash'=>$wash]);
}

}
