<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Análise de Concorrentes - {{ $business->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
            padding-bottom: 20px;
        }
        .section {
            margin-bottom: 25px;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        .metric-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .metric-value {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .metric-label {
            font-size: 14px;
            color: #666;
        }
        .analysis-content {
            background-color: #f9fafb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            white-space: pre-wrap;
            font-size: 14px;
        }
        .recommendations {
            margin-top: 20px;
        }
        .recommendation-item {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            color: #666;
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .gemini-response {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin: 20px 0;
            white-space: pre-wrap;
            font-size: 14px;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Análise de Concorrentes</h1>
        <h2>{{ $business->name }}</h2>
        <p>Período: {{ $period['start'] }} - {{ $period['end'] }}</p>
    </div>

    <div class="section">
        <h3>Métricas Principais</h3>
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-value">{{ $analysis['metrics']['average_position'] ?? 'N/A' }}</div>
                <div class="metric-label">Posição Média</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">{{ $analysis['metrics']['rating'] ?? 'N/A' }}</div>
                <div class="metric-label">Avaliação Média</div>
            </div>
            <div class="metric-card">
                <div class="metric-value">{{ $analysis['metrics']['engagement_rate'] ?? 'N/A' }}%</div>
                <div class="metric-label">Taxa de Engajamento</div>
            </div>
        </div>
    </div>

    <div class="section">
        <h3>Análise de Mercado (IA)</h3>
        <div class="gemini-response">
            {!! nl2br(e($analysis['gemini_response'] ?? 'Análise não disponível')) !!}
        </div>
    </div>

    <div class="section">
        <h3>Análise Detalhada</h3>
        <div class="analysis-content">
            {!! nl2br(e($analysis['content'] ?? 'Conteúdo não disponível')) !!}
        </div>
    </div>

    @if(!empty($analysis['recommendations']))
    <div class="section recommendations">
        <h3>Recomendações Estratégicas</h3>
        @foreach($analysis['recommendations'] as $recommendation)
        <div class="recommendation-item">
            <strong>{{ $recommendation['title'] ?? '' }}</strong>
            <p>{{ $recommendation['description'] ?? '' }}</p>
            <small>Prioridade: {{ ucfirst($recommendation['priority'] ?? 'média') }}</small>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>Relatório gerado em {{ now()->format('d/m/Y H:i') }}</p>
        <p>Última atualização: {{ $analysis['lastUpdate'] ?? now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>