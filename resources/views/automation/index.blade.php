<x-app-layout>
    @push('styles')
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />
    <link href='https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.css' rel='stylesheet' />

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Painel de Controle Principal -->
            <div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-google-sans mb-4">Painel de Controle de Automação</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Controle de Posts Automáticos -->
                    <div class="automation-control-card">
                        <h4 class="font-semibold mb-2">Posts Automáticos</h4>
                        <label class="switch">
                            <input type="checkbox" id="autoPostToggle" onchange="toggleAutomation('posts')">
                            <span class="slider round"></span>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Permite que a IA crie e agende posts automaticamente</p>
                    </div>

                    <!-- Controle de Análise de Tendências -->
                    <div class="automation-control-card">
                        <h4 class="font-semibold mb-2">Análise de Tendências</h4>
                        <label class="switch">
                            <input type="checkbox" id="trendAnalysisToggle" onchange="toggleAutomation('trends')">
                            <span class="slider round"></span>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Monitora tendências do seu segmento</p>
                    </div>

                    <!-- Controle de Proteção Automática -->
                    <div class="automation-control-card">
                        <h4 class="font-semibold mb-2">Proteção Automática</h4>
                        <label class="switch">
                            <input type="checkbox" id="autoProtectionToggle" onchange="toggleAutomation('protection')">
                            <span class="slider round"></span>
                        </label>
                        <p class="text-sm text-gray-600 mt-2">Monitora e protege sua presença online</p>
                    </div>
                </div>
            </div>

            <!-- Grid Principal -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Coluna Esquerda -->
                <div class="space-y-6">
                    <!-- Calendário -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-google-sans mb-4">Calendário de Publicações</h3>
                            <div id="calendar-container"></div>
                        </div>
                    </div>

                    <!-- Análise Competitiva -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-google-sans mb-4">Análise Competitiva</h3>
                            <button onclick="getCompetitiveAnalysis()" 
                                    class="btn-primary mb-4">
                                Atualizar Análise
                            </button>
                            <div id="competitive-analysis-container" class="space-y-4">
                                <!-- Conteúdo será carregado via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Coluna Direita -->
                <div class="space-y-6">
                    <!-- Sugestões de IA -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-google-sans">Sugestões da IA</h3>
                                <button onclick="refreshAISuggestions()" 
                                        class="btn-secondary">
                                    Atualizar Sugestões
                                </button>
                            </div>
                            <div id="ai-suggestions-container" class="space-y-4">
                                <!-- Sugestões serão carregadas via AJAX -->
                            </div>
                        </div>
                    </div>

                    <!-- Eventos Sazonais -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-google-sans mb-4">Eventos Sazonais</h3>
                            <button id="checkSeasonalEvents" 
                                    class="btn-primary w-full">
                                Verificar Eventos Sazonais
                            </button>
                            <div id="seasonal-events-container" class="mt-4 space-y-4">
                                <!-- Eventos sazonais serão carregados via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Eventos -->
    @include('automation.modals.event-modal')

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/core@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/timegrid@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/@fullcalendar/interaction@4.4.0/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/tippy.js@6/dist/tippy.umd.min.js'></script>

    <script>

        
        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            initializeCalendar();
            loadInitialData();
            setupEventListeners();
        });

        // Funções principais
        function initializeCalendar() {
            const calendarEl = document.getElementById('calendar-container');
            calendar = new FullCalendar.Calendar(calendarEl, {
                plugins: ['dayGrid', 'timeGrid', 'interaction'],
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '/automation/calendar-events',
                editable: true,
                selectable: true,
                selectMirror: true,
                dayMaxEvents: true,
                select: openEventModal,
                eventClick: handleEventClick,
                eventDrop: handleEventDrop,
                eventDidMount: function(info) {
                    if (info.event.extendedProps.type === 'seasonal') {
                        tippy(info.el, {
                            content: `${info.event.title}<br>${info.event.extendedProps.description}`,
                            allowHTML: true
                        });
                    }
                }
            });
            calendar.render();
        }

        function loadInitialData() {
            loadAISuggestions();
            loadCompetitiveAnalysis();
            checkAutomationStatus();
        }

        // Funções de automação
        async function toggleAutomation(type) {
            try {
                const response = await fetch(`/automation/toggle/${type}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                showNotification(data.message, data.success ? 'success' : 'error');
            } catch (error) {
                showNotification('Erro ao alterar automação', 'error');
            }
        }

        // Funções de IA e análise
        async function refreshAISuggestions() {
            const container = document.getElementById('ai-suggestions-container');
            container.innerHTML = '<div class="loading-skeleton"></div>';
            
            try {
                const response = await fetch('/automation/ai-suggestions');
                const data = await response.json();
                
                container.innerHTML = data.suggestions.map(suggestion => `
                    <div class="suggestion-card">
                        <h4 class="font-semibold">${suggestion.title}</h4>
                        <p class="text-gray-600">${suggestion.description}</p>
                        <div class="mt-2 space-x-2">
                            <button onclick="handleAISuggestion('${suggestion.id}', 'apply')" 
                                    class="btn-primary">Aplicar</button>
                            <button onclick="handleAISuggestion('${suggestion.id}', 'dismiss')" 
                                    class="btn-secondary">Ignorar</button>
                        </div>
                    </div>
                `).join('');
            } catch (error) {
                showNotification('Erro ao carregar sugestões', 'error');
            }
        }

        // Funções auxiliares
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 p-4 rounded-lg text-white ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            } animate-fade-in z-50`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('animate-fade-out');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Event Listeners
        function setupEventListeners() {
            document.querySelectorAll('.modal-close').forEach(button => {
                button.addEventListener('click', () => {
                    button.closest('.modal').classList.add('hidden');
                });
            });

            document.querySelectorAll('.feedback-button').forEach(button => {
                button.addEventListener('click', () => {
                    provideFeedback(button.dataset.suggestionId, button.dataset.feedback);
                });
            });
        }
    </script>
    @endpush
</x-app-layout>