<x-app-layout>
    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.css' rel='stylesheet' />
    @endpush

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Painel de Controle Principal -->
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold mb-4">Painel de Automação</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Controle de Posts Automáticos -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold mb-2">Posts Automáticos</h4>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="autoPostToggle" class="sr-only peer" 
                                   onchange="toggleAutomation('posts')"
                                   @if($business->automation_settings['auto_posts'] ?? false) checked @endif>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Permite que a IA crie e agende posts automaticamente</p>
                    </div>

                    <!-- Análise de Tendências -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold mb-2">Análise de Tendências</h4>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="trendAnalysisToggle" class="sr-only peer"
                                   onchange="toggleAutomation('trends')"
                                   @if($business->automation_settings['trend_analysis'] ?? false) checked @endif>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Monitora tendências do seu segmento</p>
                    </div>

                    <!-- Sugestões Inteligentes -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold mb-2">Sugestões Inteligentes</h4>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="smartSuggestionsToggle" class="sr-only peer"
                                   onchange="toggleAutomation('suggestions')"
                                   @if($business->automation_settings['smart_suggestions'] ?? false) checked @endif>
                            <div class="w-11 h-6 bg-gray-200 rounded-full peer peer-checked:after:translate-x-full peer-checked:bg-blue-600 after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Receba sugestões personalizadas para seu negócio</p>
                    </div>
                </div>
            </div>

            <!-- Grid Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Calendário e Eventos -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Calendário de Publicações</h3>
                                <div class="flex space-x-2">
                                    <button onclick="refreshCalendarEvents()" 
                                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                        Atualizar
                                    </button>
                                    <button onclick="openEventModal()" 
                                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">
                                        Novo Evento
                                    </button>
                                </div>
                            </div>
                            <div id="calendar"></div>
                        </div>
                    </div>

                    <!-- Eventos Sazonais -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Eventos Sazonais</h3>
                            <div id="seasonal-events" class="space-y-4">
                                <!-- Carregado via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sugestões e Análises -->
                <div class="space-y-6">
                    <!-- Sugestões da IA -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Sugestões Inteligentes</h3>
                                <button onclick="refreshSuggestions()" 
                                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Atualizar
                                </button>
                            </div>
                            <div id="ai-suggestions" class="space-y-4">
                                <!-- Carregado via JavaScript -->
                            </div>
                        </div>
                    </div>

                    <!-- Análise de Tendências -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold">Tendências do Segmento</h3>
                                <button onclick="refreshTrends()" 
                                        class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Atualizar
                                </button>
                            </div>
                            <div id="segment-trends" class="space-y-4">
                                <!-- Carregado via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Eventos -->
    @include('automation.modals.event-modal')

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.umd.min.js'></script>

    <script>
        let calendar;
        const businessId = {{ $business->id }};

        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            loadInitialData();
            setupEventListeners();
        });

        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'timeGrid', 'interaction'],
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: `/automation/calendar-events`,
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                select: handleDateSelect,
                eventClick: handleEventClick,
                eventDrop: handleEventDrop,
                eventResize: handleEventResize,
                loading: function(isLoading) {
                    // Adicionar indicador de carregamento
                }
            });
            calendar.render();
        }

        function loadInitialData() {
            refreshSuggestions();
            refreshTrends();
            loadSeasonalEvents();
        }

        async function refreshSuggestions() {
            try {
                const response = await fetch(`/automation/suggestions/${businessId}`);
                const data = await response.json();
                
                const container = document.getElementById('ai-suggestions');
                container.innerHTML = data.suggestions.map(suggestion => `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold">${suggestion.title}</h4>
                        <p class="text-gray-600 mt-2">${suggestion.description}</p>
                        <div class="mt-4 flex space-x-2">
                            <button onclick="applySuggestion('${suggestion.id}')" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                Aplicar
                            </button>
                            <button onclick="dismissSuggestion('${suggestion.id}')"
                                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400 transition">
                                Ignorar
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                showNotification('Erro ao carregar sugestões', 'error');
            }
        }

        async function refreshTrends() {
            try {
                const response = await fetch(`/automation/segment-events/${businessId}`);
                const data = await response.json();
                
                const container = document.getElementById('segment-trends');
                container.innerHTML = data.trends.map(trend => `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold">${trend.title}</h4>
                        <p class="text-gray-600 mt-2">${trend.description}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            ${trend.tags.map(tag => `
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                    ${tag}
                                </span>
                            `).join('')}
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                showNotification('Erro ao carregar tendências', 'error');
            }
        }

        async function loadSeasonalEvents() {
            try {
                const response = await fetch(`/automation/segment-events/${businessId}`);
                const data = await response.json();
                
                const container = document.getElementById('seasonal-events');
                container.innerHTML = data.events.map(event => `
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h4 class="font-semibold">${event.title}</h4>
                        <p class="text-gray-600 mt-2">${event.description}</p>
                        <div class="mt-4 flex space-x-2">
                            <button onclick="addToCalendar(${JSON.stringify(event)})" 
                                    class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">
                                Adicionar ao Calendário
                            </button>
                            <button onclick="createAutomatedPost(${JSON.stringify(event)})"
                                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                Criar Post
                            </button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                showNotification('Erro ao carregar eventos sazonais', 'error');
            }
        }

        async function toggleAutomation(type) {
            try {
                const response = await fetch(`/automation/toggle/${businessId}/${type}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const data = await response.json();
                showNotification(data.message, data.success ? 'success' : 'error');
                
                if (data.success) {
                    loadInitialData();
                }
            } catch (error) {
                showNotification('Erro ao alterar automação', 'error');
            }
        }

        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } animate-fade-in z-50`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('animate-fade-out');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Funções de manipulação de eventos do calendário
        function handleDateSelect(selectInfo) {
            const modal = document.getElementById('event-modal');
            const form = document.getElementById('event-form');
            
            form.reset();
            document.getElementById('event-start').value = selectInfo.startStr;
            document.getElementById('event-end').value = selectInfo.endStr;
            
            modal.classList.remove('hidden');
        }

        async function handleEventClick(info) {
            // Implementar visualização/edição de evento existente
        }

        async function handleEventDrop(info) {
            // Implementar atualização de data/hora do evento
        }

        async function handleEventResize(info) {
            // Implementar atualização de duração do evento
        }

        // Funções auxiliares
        async function applySuggestion(suggestionId) {
            try {
                const response = await fetch(`/automation/apply-improvement/${businessId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ suggestion_id: suggestionId })
                });
                
                const data = await response.json();
                showNotification(data.message, data.success ? 'success' : 'error');
                
                if (data.success) {
                    refreshSuggestions();
                    refreshCalendarEvents();
                }
            } catch (error) {
                showNotification('Erro ao aplicar sugestão', 'error');
            }
        }

        function setupEventListeners() {
            // Implementar listeners adicionais conforme necessário
        }
    </script>
    @endpush
</x-app-layout>