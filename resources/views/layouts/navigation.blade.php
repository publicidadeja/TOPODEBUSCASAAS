<nav x-data="{ open: false, sidebarOpen: false }" class="bg-white border-b border-gray-100/50 backdrop-blur-sm sticky top-0 z-50">
    <!-- Container Principal -->
    <div class="max-w-full mx-auto px-4">
        <div class="flex justify-between h-16">
            <!-- Navegação Lado Esquerdo -->
            <div class="flex items-center">
                <!-- Botão do Menu Mobile -->
                <button @click="sidebarOpen = !sidebarOpen" 
                        class="p-2 m-2 rounded-xl hover:bg-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 lg:hidden">
                    <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Logo com Efeito Hover -->
                <div class="flex-shrink-0 flex items-center px-4">
                    <a href="{{ route('dashboard') }}" 
                       class="flex items-center group transition-transform duration-200 hover:scale-105">
                        <x-application-logo class="block h-8 w-auto transition-all duration-200 group-hover:opacity-80" />
                        <span class="ml-2 text-lg font-google-sans bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-transparent">
                            {{ config('app.name') }}
                        </span>
                    </a>
                </div>

                <!-- Links de Navegação Desktop -->
                <div class="hidden lg:flex lg:space-x-1 lg:ml-6">
                    @foreach([
                        ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
                        ['route' => 'business.index', 'icon' => 'business', 'label' => 'Negócios'],
                        ['route' => 'automation.index', 'icon' => 'auto_fix_high', 'label' => 'Automação'],
                        ['route' => 'analytics.index', 'icon' => 'analytics', 'label' => 'Analytics', 'requires_business' => true],
                        ['route' => 'goals.index', 'icon' => 'flag', 'label' => 'Metas', 'requires_business' => true]
                    ] as $link)
                        @if(!($link['requires_business'] ?? false) || $currentBusinessId)
                            <x-nav-link 
                                :href="route($link['route'], isset($link['requires_business']) && $link['requires_business'] ? ['business' => $currentBusinessId] : [])"
                                :active="request()->routeIs($link['route'] . (isset($link['requires_business']) ? '.*' : ''))"
                                class="flex items-center px-4 py-2 rounded-xl text-sm font-google-sans transition-all duration-200 hover:scale-105 hover:bg-gray-50">
                                <i class="material-icons-outlined text-xl mr-1 transition-transform duration-200 group-hover:rotate-12">
                                    {{ $link['icon'] }}
                                </i>
                                {{ __($link['label']) }}
                            </x-nav-link>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Navegação Lado Direito -->
            <div class="flex items-center space-x-4">
                <!-- Centro de Notificações -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="p-2 rounded-xl hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50">
                        <div class="relative">
                            <i class="material-icons-outlined text-xl text-gray-700">notifications</i>
                            <!-- Indicador de Notificações -->
                            <span class="absolute -top-1 -right-1 h-2 w-2 bg-red-500 rounded-full"></span>
                        </div>
                    </button>
                    
                    <!-- Dropdown de Notificações -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="p-4">
                            <h3 class="text-sm font-semibold text-gray-900">Notificações</h3>
                            <div class="mt-2 divide-y divide-gray-100">
                                <!-- Lista de Notificações -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Perfil do Usuário -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" 
                            class="flex items-center px-3 py-2 rounded-xl hover:bg-gray-50 transition-all duration-200 group">
                        <img class="h-8 w-8 rounded-xl object-cover ring-2 ring-gray-100 group-hover:ring-blue-100" 
                             src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name) }}" 
                             alt="{{ Auth::user()->name }}">
                        <span class="ml-2 text-sm font-google-sans text-gray-700">
                            {{ Auth::user()->name }}
                        </span>
                        <svg class="ml-2 h-4 w-4 text-gray-400 group-hover:text-gray-500" 
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <!-- Menu Dropdown do Perfil -->
                    <div x-show="open"
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                        <div class="py-1">
                            <x-dropdown-link :href="route('profile.edit')" 
                                           class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-150">
                                <i class="material-icons-outlined text-lg mr-2 text-gray-500">person</i>
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center px-4 py-2 hover:bg-gray-50 transition-colors duration-150">
                                    <i class="material-icons-outlined text-lg mr-2 text-gray-500">logout</i>
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Mobile -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-0 z-50 lg:hidden">
        
        <!-- Backdrop com Blur -->
        <div class="fixed inset-0 bg-gray-600/20 backdrop-blur-sm" 
             @click="sidebarOpen = false"></div>

        <!-- Conteúdo da Sidebar -->
        <div class="fixed inset-y-0 left-0 w-64 bg-white shadow-xl border-r border-gray-100">
            <div class="flex flex-col h-full">
                <div class="p-4">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <x-application-logo class="block h-8 w-auto" />
                        <span class="ml-2 text-lg font-google-sans text-gray-800">
                            {{ config('app.name') }}
                        </span>
                    </a>
                </div>

                <div class="flex-1 px-2 py-4 space-y-1">
                    <!-- Links Mobile -->
                    @foreach([
                        ['route' => 'dashboard', 'icon' => 'dashboard', 'label' => 'Dashboard'],
                        ['route' => 'business.index', 'icon' => 'business', 'label' => 'Negócios'],
                        ['route' => 'automation.index', 'icon' => 'auto_fix_high', 'label' => 'Automação'],
                        ['route' => 'analytics.index', 'icon' => 'analytics', 'label' => 'Analytics', 'requires_business' => true],
                        ['route' => 'goals.index', 'icon' => 'flag', 'label' => 'Metas', 'requires_business' => true],
                        ['route' => 'notifications.index', 'icon' => 'notifications', 'label' => 'Notificações', 'requires_business' => true],
                        
                    ] as $link)
                        @if(!($link['requires_business'] ?? false) || $currentBusinessId)
                            <x-responsive-nav-link 
                                :href="route($link['route'], isset($link['requires_business']) && $link['requires_business'] ? ['business' => $currentBusinessId] : [])"
                                :active="request()->routeIs($link['route'] . (isset($link['requires_business']) ? '.*' : ''))"
                                class="flex items-center px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors duration-150">
                                <i class="material-icons-outlined text-xl mr-2">{{ $link['icon'] }}</i>
                                {{ __($link['label']) }}
                            </x-responsive-nav-link>
                        @endif
                    @endforeach
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

    .nav-link-active {
        @apply bg-blue-50 text-blue-700;
    }

    .nav-link:hover {
        @apply bg-gray-50;
    }

    [x-cloak] { 
        display: none !important; 
    }
</style>