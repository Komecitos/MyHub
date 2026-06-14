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

    {{-- Page specific --}}
    @stack('styles')
</head>

<body>

    {{-- Global Header --}}
    <header class="global-header">
        <a href="/" class="app-name">MyHub</a>
        <a href="/" class="back-btn">← Back to Hub</a>
    </header>

    {{-- Top Bar --}}
    <div class="top-bar">
        @yield('topbar')
    </div>

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