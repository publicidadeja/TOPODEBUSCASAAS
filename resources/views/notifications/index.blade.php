<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Notificações - {{ $business->name }}
            </h2>
            <a href="{{ route('notifications.settings', $business) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Configurações
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                @if($notifications->isEmpty())
                    <div class="p-6 text-gray-900 dark:text-gray-100 text-center">
                        Nenhuma notificação encontrada.
                    </div>
                @else
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($notifications as $notification)
                            <div class="p-6 {{ $notification->isUnread() ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($notification->type === 'alert')
                                            <svg class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        @elseif($notification->type === 'warning')
                                            <svg class="h-6 w-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @else
                                            <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                {{ $notification->title }}
                                            </h3>
                                            <span class="text-sm text-gray-500">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-gray-600 dark:text-gray-400">
                                            {{ $notification->message }}
                                        </p>
                                        @if($notification->action_url)
                                            <div class="mt-3">
                                                <a href="{{ $notification->action_url }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-500">
                                                    Ver detalhes
                                                    <svg class="ml-1 w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    @if($notification->isUnread())
                                        <button onclick="markAsRead('{{ $notification->id }}')" 
                                                class="flex-shrink-0 text-gray-400 hover:text-gray-500">
                                            <span class="sr-only">Marcar como lida</span>
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function markAsRead(notificationId) {
            fetch(`{{ route('notifications.read', ['business' => $business->id, 'notification' => ':id']) }}`.replace(':id', notificationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }

        function markAllAsRead() {
            fetch(`{{ route('notifications.read.all', ['business' => $business->id]) }}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>