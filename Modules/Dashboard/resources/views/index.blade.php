<x-dashboard::layouts.master>

    <link rel="stylesheet" href="{{ asset('css/modules/dashboard.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <div class="dashboard-wrapper">
        <div class="dashboard-header">
            <h2>Selamat datang di MyHub</h2>
            <p>Pilih portal yang ingin kamu buka</p>
        </div>

        <div class="portal-grid">

            <a href="/todos" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">
                        <div class="w-24 h-24 flex items-center justify-center">
                            <img
                                src="{{ asset('assets/images/todo_logo.png') }}"
                                alt="Logo"
                                class="max-w-full max-h-full object-contain">
                        </div>
                    </span>
                    <p class="portal-title">Todo</p>
                    <p class="portal-desc">Manajemen tugas</p>
                </div>
            </a>

            <a href="/notes" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">📝</span>
                    <p class="portal-title">Notes</p>
                    <p class="portal-desc">Catatan harian</p>
                </div>
            </a>

            <a href="/aquaria" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">🐠</span>
                    <p class="portal-title">Aquarium</p>
                    <p class="portal-desc">Monitor akuarium</p>
                </div>
            </a>

            <a href="/calendars" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">📅</span>
                    <p class="portal-title">Calendar</p>
                    <p class="portal-desc">Jadwal & agenda</p>
                </div>
            </a>

            <a href="/freefires" class="portal-card">
                <div class="portal-card-content">
                    <span class="portal-icon">
                        <div class="w-24 h-24 flex items-center justify-center">
                            <img
                                src="{{ asset('assets/images/ff_logo.webp') }}"
                                alt="Logo"
                                class="max-w-full max-h-full object-contain">
                        </div>
                    </span>
                    <p class=" portal-title">Free Fire</p>
                    <p class="portal-desc">Game tracker</p>
                </div>
            </a>

        </div>
    </div>

    <footer class="bottom-bar" role="contentinfo">
        <div class="bottom-bar-inner">MyHub &middot; © {{ date('Y') }}</div>
    </footer>

</x-dashboard::layouts.master>