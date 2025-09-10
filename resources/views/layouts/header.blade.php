<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" width="40" height="40" class="me-2">
            Fantasy Rentals
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav" aria-controls="navbarNav" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                {{-- Guest only --}}
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('vehicles.index') }}">Vehicles</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                @endguest

                {{-- Customer only --}}
                @auth
                    @if(Auth::user()->role === 'customer')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('home') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vehicles.index') }}">Vehicles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('reservation.my') }}">My Reservations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile.edit') }}">Profile</a>
                        </li>
                    @endif

                    {{-- Admin only --}}
                    @if(Auth::user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.vehicles.index') }}">Vehicles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.reservations.index') }}">Reservations</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('maintenance.index') }}">Maintenance</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('ratings_admin.index') }}">Ratings</a>
                        </li>
                    @endif

                    {{-- Common for any logged-in user --}}
                    <li class="nav-item">
                        <span class="nav-link">Welcome, {{ Auth::user()->name }}</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Logout
                        </a>
                    </li>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endauth
            </ul>
        </div>
    </div>
</nav>
