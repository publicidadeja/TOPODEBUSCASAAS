<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Configurações de Notificações - {{ $business->name }}
            </h2>
            <a href="{{ route('notifications.index', $business) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Voltar para Notificações
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('notifications.settings.update', $business) }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Notificações de Visualizações -->
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Alertas de Visualizações
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Condição
                                        </label>
                                        <select name="settings[0][condition]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="below" {{ optional($settings->where('metric_type', 'views')->first())->condition === 'below' ? 'selected' : '' }}>
                                                Abaixo de
                                            </option>
                                            <option value="above" {{ optional($settings->where('metric_type', 'views')->first())->condition === 'above' ? 'selected' : '' }}>
                                                Acima de
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Limite
                                        </label>
                                        <input type="number" name="settings[0][threshold]" 
                                            value="{{ optional($settings->where('metric_type', 'views')->first())->threshold }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <input type="hidden" name="settings[0][metric_type]" value="views">
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="settings[0][email_enabled]" id="views_email"
                                            {{ optional($settings->where('metric_type', 'views')->first())->email_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="views_email" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Receber notificações por e-mail
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="settings[0][app_enabled]" id="views_app"
                                            {{ optional($settings->where('metric_type', 'views')->first())->app_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="views_app" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Receber notificações no aplicativo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Notificações de Cliques -->
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Alertas de Cliques
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Condição
                                        </label>
                                        <select name="settings[1][condition]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="below" {{ optional($settings->where('metric_type', 'clicks')->first())->condition === 'below' ? 'selected' : '' }}>
                                                Abaixo de
                                            </option>
                                            <option value="above" {{ optional($settings->where('metric_type', 'clicks')->first())->condition === 'above' ? 'selected' : '' }}>
                                                Acima de
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Limite
                                        </label>
                                        <input type="number" name="settings[1][threshold]" 
                                            value="{{ optional($settings->where('metric_type', 'clicks')->first())->threshold }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <input type="hidden" name="settings[1][metric_type]" value="clicks">
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="settings[1][email_enabled]" id="clicks_email"
                                            {{ optional($settings->where('metric_type', 'clicks')->first())->email_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="clicks_email" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Receber notificações por e-mail
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="settings[1][app_enabled]" id="clicks_app"
                                            {{ optional($settings->where('metric_type', 'clicks')->first())->app_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="clicks_app" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Receber notificações no aplicativo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Notificações de Taxa de Conversão -->
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Alertas de Taxa de Conversão
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Condição
                                        </label>
                                        <select name="settings[2][condition]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="below" {{ optional($settings->where('metric_type', 'conversion')->first())->condition === 'below' ? 'selected' : '' }}>
                                                Abaixo de
                                            </option>
                                            <option value="above" {{ optional($settings->where('metric_type', 'conversion')->first())->condition === 'above' ? 'selected' : '' }}>
                                                Acima de
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Limite (%)
                                        </label>
                                        <input type="number" step="0.01" name="settings[2][threshold]" 
                                            value="{{ optional($settings->where('metric_type', 'conversion')->first())->threshold }}"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                                <input type="hidden" name="settings[2][metric_type]" value="conversion">
                                <div class="mt-4 space-y-2">
                                    <div class="flex items-center">
                                        <input type="checkbox" name="settings[2][email_enabled]" id="conversion_email"
                                            {{ optional($settings->where('metric_type', 'conversion')->first())->email_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="conversion_email" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Receber notificações por e-mail
                                        </label>
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" name="settings[2][app_enabled]" id="conversion_app"
                                            {{ optional($settings->where('metric_type', 'conversion')->first())->app_enabled ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <label for="conversion_app" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                            Receber notificações no aplicativo
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-t border-gray-200 dark:border-gray-700 text-right">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>