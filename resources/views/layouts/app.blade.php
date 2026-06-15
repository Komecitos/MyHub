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
    <header class="global-header">
        <a href="/" class="app-name">MyHub</a>
        <a href="/" class="back-btn">← Back to Hub</a>
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
        <div class="bottom-bar-inner">MyHub &middot; © {{ date('Y') }}</div>
    </footer>

    {{-- Page specific scripts --}}
    @stack('scripts')


</body>

</html>