<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carwash Manager</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    {{-- <script src="{{ asset('js/app.js') }}" defer></script> --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>
    @include('partials.header')

    @if (session('success'))
        <div class="alert" style="background: rgba(16, 185, 129, 0.2); border-color: #10b981;">
            {!! session('success') !!}
        </div>
    @endif
    @if (session('error'))
        <div class="alert">
            {!! session('error') !!}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{!! $errors->first() !!}</div>
    @endif


    <div class="container">
        @yield('content')

    </div>
    <script src="{{ asset('js/script.js') }}"></script>
    @yield('scripts')
</body>

</html>
