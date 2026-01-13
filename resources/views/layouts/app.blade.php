<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/@simplewebauthn/browser/dist/bundle/index.umd.min.js"></script>
    <script>
        const { startRegistration, startAuthentication } = SimpleWebAuthnBrowser;
    </script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen bg-pl-gradient text-white">
        @include('layouts.navigation')

        @auth
            @if(auth()->user()->passkeys()->exists())
                <script>
                    localStorage.setItem('has_passkey', 'true');
                    localStorage.setItem('last_email', '{{ auth()->user()->email }}');
                </script>
            @endif
        @endauth

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-pl-purple/50 backdrop-blur-md shadow-lg shadow-pl-pink/10 border-b border-white/10">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
</body>

</html>