<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Examen de Ascenso') }}</title>

        <link rel="icon" href="/favicon.png?v=2" type="image/png">
        <link rel="icon" href="/favicon.ico?v=2" sizes="any">
        <link rel="apple-touch-icon" href="/favicon.png?v=2">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Carga los scripts y estilos de Vite -->
        @viteReactRefresh
        @vite('resources/js/app.tsx')
    </head>
    <body class="font-sans antialiased">
        {{-- Aquí es donde React montará toda la aplicación --}}
        <div id="root"></div>

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script defer>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>
    </body>
</html>
