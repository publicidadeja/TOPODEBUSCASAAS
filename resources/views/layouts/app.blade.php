<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Sistema de análise e gestão para empresas no Google">
        <meta name="theme-color" content="#4285F4">

        <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

        <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        
        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            :root {
                /* Google Colors */
                --google-blue: #4285F4;
                --google-red: #EA4335;
                --google-yellow: #FBBC05;
                --google-green: #34A853;
                
                /* UI Colors */
                --surface-color: #FFFFFF;
                --background-color: #F8F9FA;
                --border-color: #DADCE0;
                --text-primary: #202124;
                --text-secondary: #5F6368;
                
                /* Shadows */
                --shadow-sm: 0 1px 2px 0 rgba(60,64,67,0.3);
                --shadow-md: 0 1px 3px 0 rgba(60,64,67,0.3);
                --shadow-lg: 0 2px 6px 0 rgba(60,64,67,0.3);
            }

            body {
                font-family: 'Roboto', sans-serif;
                background-color: var(--background-color);
                color: var(--text-primary);
                line-height: 1.5;
                -webkit-font-smoothing: antialiased;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: 'Google Sans', sans-serif;
                color: var(--text-primary);
            }

            /* Card Styles */
            .card {
                background: var(--surface-color);
                border-radius: 8px;
                border: 1px solid var(--border-color);
                transition: box-shadow 0.2s ease-in-out;
            }

            .card:hover {
                box-shadow: var(--shadow-md);
            }

            /* Button Styles */
            .btn {
                font-family: 'Google Sans', sans-serif;
                padding: 8px 24px;
                border-radius: 4px;
                font-weight: 500;
                transition: all 0.2s ease;
                cursor: pointer;
            }

            .btn-primary {
                background-color: var(--google-blue);
                color: white;
                border: none;
            }

            .btn-primary:hover {
                background-color: #1a73e8;
                box-shadow: var(--shadow-sm);
            }

            /* Navigation */
            .nav-header {
                background: var(--surface-color);
                border-bottom: 1px solid var(--border-color);
                padding: 12px 0;
            }

            /* Container */
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 24px;
            }

            /* Grid System */
            .grid {
                display: grid;
                gap: 24px;
            }

            @media (min-width: 768px) {
                .grid-cols-2 {
                    grid-template-columns: repeat(2, 1fr);
                }
            }

            @media (min-width: 1024px) {
                .grid-cols-3 {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            /* Form Elements */
            .input {
                border: 1px solid var(--border-color);
                border-radius: 4px;
                padding: 8px 16px;
                width: 100%;
                transition: border 0.2s ease;
            }

            .input:focus {
                border-color: var(--google-blue);
                outline: none;
            }

            /* Utility Classes */
            .elevation-1 {
                box-shadow: var(--shadow-sm);
            }

            .elevation-2 {
                box-shadow: var(--shadow-md);
            }

            [x-cloak] { display: none !important; }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white border-b border-gray-200">
                    <div class="container py-4">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Main Content -->
            <main class="flex-grow py-6">
                <div class="container">
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white border-t border-gray-200 mt-auto">
                <div class="container py-4">
                    <div class="text-center text-sm text-gray-500">
                        © {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
                    </div>
                </div>
            </footer>
        </div>

        @stack('scripts')
    </body>
</html>