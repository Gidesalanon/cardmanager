<nav class="top-nav">
    <div class="nav-container">

        <div class="nav-left">
            <a href="{{ route('school.dashboard') }}" class="logo">
                <div class="logo-icon">
                    <img 
                        src="{{ asset('assets/web/images/logo-3.png') }}" 
                        alt="Logo" 
                        style="width: 24px; height: 24px; object-fit: contain;"
                    >
                </div>
                {{ config('app.name') }}
            </a>

            <div class="nav-menu">

                {{-- Dashboard --}}
                <a href="{{ route('school.dashboard') }}"
                   class="nav-link {{ request()->routeIs('school.dashboard') ? 'active' : '' }}">
                    Tableau de bord
                </a>

                {{-- Mon école --}}
                <a href="{{ auth()->user()->ecole
                        ? route('school.ecole.show')
                        : route('school.ecole.create') }}"
                class="nav-link {{ request()->routeIs('school.ecole.*') ? 'active' : '' }}">
                    Mon école
                </a>

                {{-- Élèves --}}
                <a href="{{ route('school.students.index') }}"
                   class="nav-link {{ request()->routeIs('school.students.*') ? 'active' : '' }}">
                    Mes élèves
                </a>

                {{-- televersement et analyse de doc eleves --}}
                <a href="{{ route('school.students.import.create') }}"
                   class="nav-link">
                    Import élèves
                </a>

            </div>
        </div>

        <div class="nav-right">
                    <div class="theme-toggle">
                        <button class="theme-btn theme-btn-snow active" onclick="setTheme('snow')" title="Snow Edition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="5"/>
                                <line x1="12" y1="1" x2="12" y2="3"/>
                                <line x1="12" y1="21" x2="12" y2="23"/>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                                <line x1="1" y1="12" x2="3" y2="12"/>
                                <line x1="21" y1="12" x2="23" y2="12"/>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                            </svg>
                        </button>
                        <button class="theme-btn theme-btn-carbon" onclick="setTheme('carbon')" title="Carbon Edition">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                            </svg>
                        </button>
                    </div>
                                            <a href="{{ route('profile.edit') }}" class="user-menu" style="text-decoration: none; display: flex; align-items: center; gap: 10px;">
                            <div class="user-avatar">
                                @if(auth()->user()->avatar)
                                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" 
                                        style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                                @else
                                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                                @endif
                            </div>
                            <span class="user-name" style="color: inherit;">{{ auth()->user()->name ?? 'User' }}</span>
                        </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                    <button class="btn-logout" title="Déconnexion">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                    </button>
                    </form>

                    <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                </div>

    </div>
</nav>
