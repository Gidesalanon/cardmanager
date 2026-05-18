<nav class="mobile-menu-nav">

    {{-- Dashboard --}}
    <a href="{{ route('school.dashboard') }}"
       class="{{ request()->routeIs('school.dashboard') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1"/>
            <rect x="14" y="3" width="7" height="7" rx="1"/>
            <rect x="3" y="14" width="7" height="7" rx="1"/>
            <rect x="14" y="14" width="7" height="7" rx="1"/>
        </svg>
        Tableau de bord
    </a>

    {{-- Mon école --}}
    <a href="{{ auth()->user()->ecole ? route('school.ecole.show') : route('school.ecole.create') }}"
       class="{{ request()->routeIs('school.ecole.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Mon école
    </a>

    {{-- Mes élèves --}}
    <a href="{{ route('school.students.index') }}"
       class="{{ request()->routeIs('school.students.*') && !request()->routeIs('school.students.import.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Mes élèves
    </a>

    {{-- Import élèves --}}
    <a href="{{ route('school.students.import.create') }}"
       class="{{ request()->routeIs('school.students.import.*') ? 'active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        Import élèves
    </a>

</nav>