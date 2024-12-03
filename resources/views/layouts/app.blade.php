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
                --primary-color: #4285F4;
                --secondary-color: #34A853;
                --warning-color: #FBBC05;
                --error-color: #EA4335;
                --surface-color: #FFFFFF;
                --background-color: #F8F9FA;
                --text-primary: #202124;
                --text-secondary: #5F6368;
            }

            .dark {
                --surface-color: #202124;
                --background-color: #1A1A1A;
                --text-primary: #FFFFFF;
                --text-secondary: #9AA0A6;
            }

            body {
                font-family: 'Roboto', sans-serif;
            }

            h1, h2, h3, h4, h5, h6 {
                font-family: 'Google Sans', sans-serif;
            }

            [x-cloak] { display: none !important; }

            /* Material Design inspired elevation */
            .elevation-1 {
                box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.3), 0 1px 3px 1px rgba(60, 64, 67, 0.15);
            }

            .elevation-2 {
                box-shadow: 0 1px 2px 0 rgba(60, 64, 67, 0.3), 0 2px 6px 2px rgba(60, 64, 67, 0.15);
            }

            /* Smooth transitions */
            .transition-all {
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>
    <body 
        class="font-sans antialiased min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100"
        x-data="{ showNotification: false, notificationMessage: '', notificationType: 'success' }"
    >
        <div class="min-h-screen flex flex-col">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 elevation-1">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Notification Component -->
            <div
                x-cloak
                x-show="showNotification"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed bottom-4 right-4 z-50"
            >
                <div class="max-w-sm w-full bg-white dark:bg-gray-800 elevation-2 rounded-lg pointer-events-auto">
                    <div class="p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <template x-if="notificationType === 'success'">
                                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </template>
                                <!-- Add other notification type icons here -->
                            </div>
                            <div class="ml-3 w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="notificationMessage"></p>
                            </div>
                            <button @click="showNotification = false" class="ml-4 flex-shrink-0 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-grow py-6">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>

            <!-- Footer -->
            <footer class="bg-white dark:bg-gray-800 elevation-1 mt-auto">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                    <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                        © {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
                    </div>
                </div>
            </footer>
        </div>

        <!-- Scripts -->
        @stack('scripts')
        <script>
            // Notification system
            window.notify = function(message, type = 'success') {
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: { message, type }
                }));
            };

            window.addEventListener('notify', (e) => {
                const notification = document.querySelector('[x-data]').__x.$data;
                notification.notificationMessage = e.detail.message;
                notification.notificationType = e.detail.type;
                notification.showNotification = true;

                setTimeout(() => {
                    notification.showNotification = false;
                }, 5000);
            });
        </script>
    </body>
</html>