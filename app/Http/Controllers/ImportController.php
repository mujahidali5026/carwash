<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        $header = null;
        $inserted = 0;
        $duplicates = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
 
                if (!$header) {
                    $header = $row;
                    continue;
                }

                $data = array_combine($header, $row);

                $registration = strtoupper(trim($data['registration']));
                $companyName = trim($data['company_name']);
 
                if (Vehicle::where('registration', $registration)->exists()) {
                    $duplicates++;
                    continue;
                }
 
                $company = Company::firstOrCreate(
                    ['name' => $companyName],
                    ['name' => $companyName]
                );
 
                Vehicle::create([
                    'registration' => $registration,
                    'driver_name'  => $data['driver_name'] ?? null,
                    'company_id'   => $company->id,
                    'price'        => $data['price'] ?? 0,
                    'limit'        => $data['limit'] ?? 1,
                ]);

                $inserted++;
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', "Import failed: " . $e->getMessage());
        }

        return back()->with('success',
            "CSV imported successfully.<br>
             <strong>$inserted</strong> records added.<br>
             <strong>$duplicates</strong> duplicate registrations were skipped."
        );
    }
}
