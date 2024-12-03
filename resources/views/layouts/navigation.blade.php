<nav x-data="{ open: false, sidebarOpen: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <!-- Main Navigation Container -->
    <div class="max-w-full mx-auto">
        <div class="flex justify-between h-16">
            <!-- Left Side Navigation -->
            <div class="flex items-center">
                <!-- Hamburger for Sidebar (Mobile) -->
                <button @click="sidebarOpen = !sidebarOpen" class="p-4 focus:outline-none lg:hidden">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center px-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <x-application-logo class="block h-8 w-auto" />
                        <span class="ml-2 text-lg font-google-sans text-gray-800">TopoSaaS</span>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden lg:flex lg:space-x-1 lg:ml-6">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" 
                        class="px-4 py-2 rounded-full text-sm font-google-sans transition-colors duration-200">
                        <i class="material-icons-outlined text-xl mr-1">dashboard</i>
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <x-nav-link :href="route('business.index')" :active="request()->routeIs('business.*')"
                        class="px-4 py-2 rounded-full text-sm font-google-sans transition-colors duration-200">
                        <i class="material-icons-outlined text-xl mr-1">business</i>
                        {{ __('Negócios') }}
                    </x-nav-link>

                    <x-nav-link :href="route('automation.index')" :active="request()->routeIs('automation.*')"
                        class="px-4 py-2 rounded-full text-sm font-google-sans transition-colors duration-200">
                        <i class="material-icons-outlined text-xl mr-1">auto_fix_high</i>
                        {{ __('Automação') }}
                    </x-nav-link>

                    @php
                        $currentBusinessId = auth()->user()->businesses->first()->id ?? null;
                    @endphp

                    @if($currentBusinessId)
                        <x-nav-link :href="route('analytics.index', ['business' => $currentBusinessId])" 
                            :active="request()->routeIs('analytics.*')"
                            class="px-4 py-2 rounded-full text-sm font-google-sans transition-colors duration-200">
                            <i class="material-icons-outlined text-xl mr-1">analytics</i>
                            {{ __('Analytics') }}
                        </x-nav-link>

                        <x-nav-link :href="route('goals.index', ['business' => $currentBusinessId])" 
                            :active="request()->routeIs('goals.index')"
                            class="px-4 py-2 rounded-full text-sm font-google-sans transition-colors duration-200">
                            <i class="material-icons-outlined text-xl mr-1">flag</i>
                            {{ __('Metas') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Right Side Navigation -->
            <div class="flex items-center">
                <!-- Notifications -->
                <div class="relative mr-4">
                    <button class="p-2 rounded-full hover:bg-gray-100 transition-colors duration-200">
                        <i class="material-icons-outlined text-xl text-gray-600">notifications</i>
                    </button>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center px-3 py-2 rounded-full hover:bg-gray-100 transition-colors duration-200">
                                <img class="h-8 w-8 rounded-full object-cover" 
                                     src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}" 
                                     alt="{{ Auth::user()->name }}">
                                <span class="ml-2 text-sm font-google-sans text-gray-700">
                                    {{ Auth::user()->name }}
                                </span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="py-1">
                                <x-dropdown-link :href="route('profile.edit')" class="flex items-center">
                                    <i class="material-icons-outlined text-lg mr-2">person</i>
                                    {{ __('Profile') }}
                                </x-dropdown-link>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();"
                                        class="flex items-center">
                                        <i class="material-icons-outlined text-lg mr-2">logout</i>
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-0 z-40 lg:hidden">
        
        <!-- Sidebar Backdrop -->
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50" 
             @click="sidebarOpen = false"></div>

        <!-- Sidebar Content -->
        <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg">
            <div class="flex flex-col h-full">
                <div class="p-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <x-application-logo class="block h-8 w-auto" />
                        <span class="ml-2 text-lg font-google-sans text-gray-800">TopoSaaS</span>
                    </a>
                </div>

                <div class="flex-1 px-2 py-4 space-y-1">
                    <!-- Mobile Navigation Links -->
                    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"
                        class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50">
                        <i class="material-icons-outlined text-xl mr-2">dashboard</i>
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('business.index')" :active="request()->routeIs('business.*')"
                        class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50">
                        <i class="material-icons-outlined text-xl mr-2">business</i>
                        {{ __('Negócios') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('automation.index')" :active="request()->routeIs('automation.*')"
                        class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50">
                        <i class="material-icons-outlined text-xl mr-2">auto_fix_high</i>
                        {{ __('Automação') }}
                    </x-responsive-nav-link>

                    @if($currentBusinessId)
                        <x-responsive-nav-link :href="route('analytics.index', ['business' => $currentBusinessId])" 
                            :active="request()->routeIs('analytics.*')"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50">
                            <i class="material-icons-outlined text-xl mr-2">analytics</i>
                            {{ __('Analytics') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('goals.index', ['business' => $currentBusinessId])" 
                            :active="request()->routeIs('goals.*')"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50">
                            <i class="material-icons-outlined text-xl mr-2">flag</i>
                            {{ __('Metas') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('notifications.index', ['business' => $currentBusinessId])" 
                            :active="request()->routeIs('notifications.*')"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50">
                            <i class="material-icons-outlined text-xl mr-2">notifications</i>
                            {{ __('Notificações') }}
                        </x-responsive-nav-link>

                        <x-responsive-nav-link :href="route('automation.protection', ['business' => $currentBusinessId])" 
                            :active="request()->routeIs('automation.protection')"
                            class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50">
                            <i class="material-icons-outlined text-xl mr-2">security</i>
                            {{ __('Proteção') }}
                        </x-responsive-nav-link>
                    @endif
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&display=swap');
    @import url('https://fonts.googleapis.com/icon?family=Material+Icons+Outlined');

    .font-google-sans {
        font-family: 'Google Sans', sans-serif;
    }

    /* Active state for navigation links */
    .nav-link-active {
        @apply bg-blue-50 text-blue-700;
    }

    /* Hover state for navigation links */
    .nav-link:hover {
        @apply bg-gray-50;
    }
</style>