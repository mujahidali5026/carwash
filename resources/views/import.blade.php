@extends('layouts.app')

@section('content')
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <h2 style="margin-bottom: 10px;">📥 Bulk CSV Import</h2>
        <p style="color: #64748b; margin-bottom: 20px;">
            Upload a CSV file to import vehicles into the system.
        </p>

        @if (session('success'))
            <div class="alert" style="background: rgba(16, 185, 129, 0.2); border-color: #10b981;">{!! session('success') !!}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{!! session('error') !!}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">{!! $errors->first() !!}</div>
        @endif

        <form action="{{ route('import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group" style="margin-bottom: 15px;">
                <label style="font-weight: 600;">Choose CSV File</label>
                <input type="file" name="csv_file" class="form-control" required>
            </div>

            <button class="btn btn-primary" type="submit">Import CSV</button>

            <p style="margin-top: 15px; font-size: 14px; color: #94a3b8;">
                CSV must contain headers: <b>registration, driver_name, company_name, price, limit</b>
            </p>

            <a href="{{ route('import.sample') }}" class="btn btn-secondary" style="margin-top: 10px;text-decoration: none;">
                Download Sample CSV
            </a>
        </form>
    </div>
@endsection
