<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vehicle Rental System')</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        /* Make body full height and flex column */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Main content grows to fill space */
        main.content-wrapper {
            flex: 1;
        }

        /* Optional: reduce footer padding to make it thinner */
        footer {
            padding: 0.5rem 0;
        }
    </style>
</head>
<body>
        <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    {{-- Include Header --}}
    @include('layouts.header')

    <main class="content-wrapper container mt-4">
        @if(session('ok'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                {{ session('ok') }}
            </div>

            <script>
                setTimeout(() => {
                    const alert = document.querySelector('.alert');
                    if (alert) {
                        bootstrap.Alert.getOrCreateInstance(alert).close();
                    }
                }, 5000); 
            </script>
        @endif

        @yield('content')
    </main>

    {{-- Include Footer --}}
    @include('layouts.footer')

    @stack('scripts') 
</body>
</html>
