<?php

namespace App\Exports;

use App\Models\BusinessAnalytics;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AnalyticsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $businessId;
    protected $startDate;
    protected $endDate;

    public function __construct($businessId, $startDate, $endDate)
    {
        $this->businessId = $businessId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return BusinessAnalytics::where('business_id', $this->businessId)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Data',
            'Visualizações',
            'Cliques',
            'Chamadas',
            'Desktop',
            'Mobile',
            'Tablet',
            'Principais Localizações',
            'Palavras-chave Principais'
        ];
    }

    public function map($analytics): array
    {
        return [
            $analytics->date->format('d/m/Y'),
            $analytics->views,
            $analytics->clicks,
            $analytics->calls,
            $analytics->devices['desktop'] ?? 0,
            $analytics->devices['mobile'] ?? 0,
            $analytics->devices['tablet'] ?? 0,
            $this->formatLocations($analytics->user_locations),
            $this->formatKeywords($analytics->search_keywords)
        ];
    }

    private function formatLocations($locations)
    {
        if (empty($locations)) return '';
        
        arsort($locations);
        $top3 = array_slice($locations, 0, 3, true);
        return collect($top3)->map(function ($count, $city) {
            return "$city ($count)";
        })->implode(', ');
    }

    private function formatKeywords($keywords)
    {
        if (empty($keywords)) return '';
        
        arsort($keywords);
        $top3 = array_slice($keywords, 0, 3, true);
        return collect($top3)->map(function ($count, $keyword) {
            return "$keyword ($count)";
        })->implode(', ');
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Analytics';
    }
}