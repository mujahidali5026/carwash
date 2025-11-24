<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
     
    public function index()
    {
        return view('companies.index');
    }

     
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'daily_limit' => 'required|integer|min:1',
            'price_per_wash' => 'required|numeric|min:0',
        ]);

        $company = Company::create([
            'name' => $request->name,
            'daily_limit' => $request->daily_limit,
            'price_per_wash' => $request->price_per_wash,
            'status' => 'active',  
        ]);

        return response()->json(['success' => true, 'company' => $company]);
    }
 
    // public function list()
    // {
    //     $companies = Company::all();
    //     return response()->json($companies);
    // }
    public function list()
{
    $companies = Company::withCount('vehicles')->get();
    return response()->json($companies);
}

 
    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'daily_limit' => 'required|integer|min:1',
            'price_per_wash' => 'required|numeric|min:0',
        ]);

        $company->update([
            'name' => $request->name,
            'daily_limit' => $request->daily_limit,
            'price_per_wash' => $request->price_per_wash,
        ]);

        return response()->json(['success' => true, 'company' => $company]);
    }
 
    public function destroy(Company $company)
    {
        $company->delete();
        return response()->json(['success' => true]);
    }

}
