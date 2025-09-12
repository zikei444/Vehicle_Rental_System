<!-- 
STUDENT NAME: LIEW ZI KEI 
STUDENT ID: 23WMR14570
-->

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

<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;"></div>
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

    <!-- Toast container -->
<div aria-live="polite" aria-atomic="true" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="toast-container-global"></div>
</div>

    {{-- Include Footer --}}
    @include('layouts.footer')

    @stack('scripts') 
</body>

@auth
<script>
function showToast(message, url = '#', type = 'primary') {
    const toastContainer = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-bg-${type} border-0 show`;
    toast.role = 'alert';
    toast.ariaLive = 'assertive';
    toast.ariaAtomic = 'true';
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"></button>
        </div>
    `;
    toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
    toast.addEventListener('click', () => {
        if (url) window.location.href = url;
    });

    toastContainer.appendChild(toast);

    setTimeout(() => toast.remove(), 5000);
}

// Poll notifications every 5 seconds
setInterval(() => {
    fetch('{{ route(auth()->user()->role === "admin" ? "admin.notifications.unread" : "notifications.unread") }}')
        .then(res => res.json())
        .then(data => {
            data.forEach(n => showToast(n.message, n.url, 'success'));
        })
        .catch(err => console.error(err));
}, 5000);
</script>
@endauth
</html>
