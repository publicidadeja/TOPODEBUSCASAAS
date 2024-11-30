<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório Analytics - {{ $business->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .section {
            margin-bottom: 20px;
        }
        .metrics {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .metrics th, .metrics td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .metrics th {
            background-color: #f5f5f5;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        .insights {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório Analytics</h1>
        <h2>{{ $business->name }}</h2>
        <p>Período: {{ $period['start'] }} - {{ $period['end'] }}</p>
    </div>

    <div class="section">
        <h3>Métricas Principais</h3>
        <table class="metrics">
            <tr>
                <th>Métrica</th>
                <th>Total</th>
                <th>Crescimento</th>
            </tr>
            <tr>
                <td>Visualizações</td>
                <td>{{ number_format($currentTotal['views']) }}</td>
                <td>{{ $growth['views'] }}%</td>
            </tr>
            <tr>
                <td>Cliques</td>
                <td>{{ number_format($currentTotal['clicks']) }}</td>
                <td>{{ $growth['clicks'] }}%</td>
            </tr>
            <tr>
                <td>Chamadas</td>
                <td>{{ number_format($currentTotal['calls']) }}</td>
                <td>{{ $growth['calls'] }}%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Dispositivos</h3>
        <table class="metrics">
            @foreach($devices as $device => $percentage)
            <tr>
                <td>{{ ucfirst($device) }}</td>
                <td>{{ $percentage }}%</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h3>Principais Localizações</h3>
        <table class="metrics">
            @foreach($locations as $location => $percentage)
            <tr>
                <td>{{ $location }}</td>
                <td>{{ $percentage }}%</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h3>Palavras-chave Principais</h3>
        <table class="metrics">
            @foreach($keywords as $keyword => $count)
            <tr>
                <td>{{ $keyword }}</td>
                <td>{{ $count }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div class="section">
        <h3>Insights</h3>
        <div class="insights">
            @foreach($insights as $insight)
            <p>• {{ $insight }}</p>
            @endforeach
        </div>
    </div>
</body>
</html>