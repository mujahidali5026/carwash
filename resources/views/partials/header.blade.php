<header>
    <div class="header-content">
        <div class="logo-section">
            <div class="logo">💧</div>
            <div class="logo-text">
                <h1>Carwash Manager</h1>
                <p>Professional Wash System</p>
            </div>
        </div>
        <div class="hamburger" onclick="toggleMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav id="mainNav">
            {{-- <button class="nav-btn active" onclick="showView('driver')">🚗 Record Wash</button> --}}
            {{-- <button class="nav-btn" onclick="showView('dashboard')">📊 Dashboard</button> --}}
            {{-- <button class="nav-btn" onclick="showView('vehicles')">👥 Vehicles</button> --}}
            {{-- <button class="nav-btn" onclick="showView('companies')">🏢 Companies</button> --}}
            {{-- <button class="nav-btn" onclick="showView('reports')">📅 Reports</button> --}}
            {{-- <button class="nav-btn active" onclick="window.location.href='{{ route('washes') }}'">🚗 Record Wash</button>
                <button class="nav-btn" onclick="window.location.href='{{ route('dashboard') }}'">📊 Dashboard</button>
                <button class="nav-btn" onclick="window.location.href='{{ route('vehicles') }}'">👥 Vehicles</button>
                <button class="nav-btn" onclick="window.location.href='{{ route('companies') }}'">🏢 Companies</button>
                <button class="nav-btn" onclick="window.location.href='{{ route('reports.index') }}'">📅 Reports</button> --}}
            @php
                function activeRoute($route)
                {
                    return request()->routeIs($route) ? 'active' : '';
                }
            @endphp

            <button class="nav-btn {{ activeRoute('washes') }}" onclick="window.location.href='{{ route('washes') }}'">
                🚗 Record Wash
            </button>

            <button class="nav-btn {{ activeRoute('dashboard') }}"
                onclick="window.location.href='{{ route('dashboard') }}'">
                📊 Dashboard
            </button>

            <button class="nav-btn {{ activeRoute('vehicles') }}"
                onclick="window.location.href='{{ route('vehicles') }}'">
                👥 Vehicles
            </button>

            <button class="nav-btn {{ activeRoute('companies') }}"
                onclick="window.location.href='{{ route('companies') }}'">
                🏢 Companies
            </button>

            <button class="nav-btn {{ activeRoute('reports.index') }}"
                onclick="window.location.href='{{ route('reports.index') }}'">
                📅 Reports
            </button>

            <button class="nav-btn" onclick="window.location.href='{{ route('import.form') }}'">
                📥 CSV Import
            </button>
            <button class="nav-btn" onclick="window.location.href='{{ route('logout') }}'">
                🔐 Logout
            </button>



        </nav>
    </div>
</header>
