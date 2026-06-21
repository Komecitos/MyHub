<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyHub</title>

    {{-- Theme --}}
    <link rel="stylesheet" href="{{ asset('css/theme/color.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme/typography.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme/spacing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/theme/variable.css') }}">

    {{-- Layout --}}
    <link rel="stylesheet" href="{{ asset('css/layouts/app.css') }}">

    {{-- Components --}}
    <link rel="stylesheet" href="{{ asset('css/components/badge_button.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/card.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/table.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components/toast.css') }}">

    {{-- Page specific --}}
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
    @stack('styles')
</head>

{{-- TOAST CONTAINER --}}
<div id="toast-container" class="toast-container"></div>

<script>
    function showToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icon = type === 'success' ? '✓' : '✕';

        toast.innerHTML = `
        <span class="toast-icon">${icon}</span>
        <span class="toast-message">${message}</span>
        <button class="toast-close" onclick="dismissToast(this.parentElement)">✕</button>
    `;

        container.appendChild(toast);

        setTimeout(() => dismissToast(toast), 4000);
    }

    function dismissToast(toast) {
        if (!toast || !toast.parentElement) return;
        toast.style.animation = 'toast-out 0.3s ease forwards';
        setTimeout(() => toast.remove(), 300);
    }

    function openDocsModal() {
        document.getElementById('modal-docs').classList.add('show');
        document.getElementById('modal-docs-overlay').classList.add('show');
    }

    function closeDocsModal() {
        document.getElementById('modal-docs').classList.remove('show');
        document.getElementById('modal-docs-overlay').classList.remove('show');
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // toast dipanggil dari script terpisah di bawah
    });
</script>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("{{ session('success') }}", 'success');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("{{ session('error') }}", 'error');
    });
</script>
@endif

@if($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast("{{ $errors->first() }}", 'error');
    });
</script>
@endif

<body>

    {{-- Global Header --}}
    {{-- Global Header --}}
    <header class="global-header">
        <a href="/" class="app-name">MyHub</a>
        <nav class="portal-nav">
            <a href="{{ route('todo.index') }}" class="portal-nav-btn {{ Request::is('todos*') ? 'active' : '' }}">Todo</a>
            <a href="{{ route('freefire.index') }}" class="portal-nav-btn {{ Request::is('freefires*') ? 'active' : '' }}">Free Fire</a>
        </nav>
    </header>

    @if(!Request::is('/'))
    <div class="top-bar">
        @yield('topbar')
    </div>
    @endif

    {{-- Main Content --}}
    <main class="main-content">
        @yield('content')
    </main>

    {{-- Bottom Bar --}}
    <footer class="bottom-bar">
        <div class="bottom-bar-inner" onclick="openDocsModal()" style="cursor: pointer;">
            MyHub &middot; © {{ date('Y') }}
        </div>
    </footer>

    {{-- MODAL DOKUMENTASI --}}
    <div id="modal-docs-overlay" class="modal-overlay" onclick="closeDocsModal()"></div>
    <div id="modal-docs" class="modal modal-create" aria-hidden="true">
        <div class="modal-header">
            <h3>Tentang MyHub</h3>
            <button onclick="closeDocsModal()" class="modal-close">&times;</button>
        </div>

        <div style="max-height: 65vh; overflow-y: auto;">
            <div class="docs-info-row">
                <span class="task-meta">Nama Project</span>
                <span class="task-title">MyHub</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Tujuan</span>
                <span class="task-title">Personal multi-portal web app untuk manajemen tugas, game tracker, catatan, dan kebutuhan pribadi lainnya dalam satu aplikasi.</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Developer</span>
                <span class="task-title">Irga Prayoga</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Mulai Dibuat</span>
                <span class="task-title">Juni 2026</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Tech Stack</span>
                <span class="task-title">Laravel (PHP 8.3), nwidart/laravel-modules, MySQL</span>
            </div>
            <div class="docs-info-row">
                <span class="task-meta">Repository</span>
                <span class="task-title"><a href="https://github.com/Komecitos/MyHub" target="_blank" style="color: var(--accent-primary);">github.com/Komecitos/MyHub</a></span>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="closeDocsModal()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>
    {{-- Page specific scripts --}}
    @stack('scripts')


</body>

</html>