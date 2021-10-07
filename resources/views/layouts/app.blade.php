<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/mdi/css/materialdesignicons.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
{{--        <script src="{{ asset('js/bootstrap.bundle.min.js') }}" defer></script>--}}
        <script src="{{ asset('js/app.js') }}" defer></script>
    </head>
    <body class="font-sans bg-blue-50 antialiased">

        @include('layouts.navigation')

        <div class="px-3">
            <!-- Page Heading -->
            <header class="container max-w-screen-xl mx-auto text-gray-500">

                @if(session('success'))
                    <div class="alert bg-green-200 fade show p-5 rounded-md mt-5">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('warning'))
                    <div class="alert bg-yellow-200 fade show p-5 rounded-md mt-5">
                        {{ session('warning') }}
                    </div>
                @endif

                @if ($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert bg-red-200 fade show p-5 rounded-md mt-5">
                            {{ $error }}
                        </div>
                    @endforeach
                @endif


                {{ $header }}

            </header>

            <!-- Page Content -->
            <main class="container max-w-screen-xl mx-auto">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
