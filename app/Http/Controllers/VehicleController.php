<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Company;

class VehicleController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('vehicles.index', compact('companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'registration' => 'required|unique:vehicles,registration|max:255',
            'driver_name' => 'required|max:255',
            'company_id' => 'nullable|exists:companies,id',
        ]);

        $vehicle = Vehicle::create($request->all());

        return response()->json([
            'status' => 'success',
            'vehicle' => [
                'id' => $vehicle->id,
                'registration' => $vehicle->registration,
                'driver_name' => $vehicle->driver_name,
                'company_name' => $vehicle->company ? $vehicle->company->name : 'N/A',
            ],
        ]);
    }

    public function list()
    {
        $vehicles = Vehicle::with('company')->latest()->get();

        $data = $vehicles->map(function($v){
            return [
                'id' => $v->id,
                'registration' => $v->registration,
                'driver_name' => $v->driver_name,
                'company_name' => $v->company ? $v->company->name : 'N/A',
            ];
        });

        return response()->json($data);
    }

    public function update(Request $request, Vehicle $vehicle)
{
    $request->validate([
        'driver_name' => 'required|max:255',
        'company_id' => 'nullable|exists:companies,id',
        'custom_price' => 'nullable|numeric',
        'override_limit' => 'nullable|integer',
        // 'banned' => 'nullable|boolean',
    ]);

    $vehicle->update([
        'driver_name' => $request->driver_name,
        'company_id' => $request->company_id,
        'custom_price' => $request->custom_price,
        'override_limit' => $request->override_limit,
        'banned' => $request->banned ?? false,
    ]);

    return response()->json([
        'status' => 'success',
        'vehicle' => $vehicle->load('company')
    ]);
}

}
