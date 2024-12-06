<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'segment',
        'address',
        'phone',
        'google_business_id',
        'description',
        'website',
        'user_id',
        'settings', // Adicione esta linha aos fillable
    ];

    protected $casts = [
        'settings' => 'array', // Adicione esta linha para o cast do settings
    ];

    /**
     * Relacionamento com o usuário proprietário
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com analytics
     */
    public function analytics()
    {
        return $this->hasMany(BusinessAnalytics::class);
    }

    /**
     * Relacionamento com as metas
     */
    public function goals()
    {
        return $this->hasMany(BusinessGoal::class);
    }

    /**
     * Relacionamento com competidores
     */
    public function competitors()
    {
        return $this->belongsToMany(Business::class, 'business_competitors', 
            'business_id', 'competitor_id')
            ->withTimestamps();
    }

    /**
     * Relacionamento inverso (quando o negócio é competidor de outro)
     */
    public function isCompetitorOf()
    {
        return $this->belongsToMany(Business::class, 'business_competitors',
            'competitor_id', 'business_id')
            ->withTimestamps();
    }

    /**
     * Relacionamento com as notificações
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Relacionamento com as configurações de notificação
     */
    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    /**
     * Calcula a taxa de conversão para um período específico
     */
    public function getConversionRate($startDate = null, $endDate = null)
    {
        $analytics = $this->analytics();
        
        if ($startDate && $endDate) {
            $analytics = $analytics->whereBetween('date', [$startDate, $endDate]);
        }

        $totals = $analytics->selectRaw('
            SUM(views) as total_views,
            SUM(clicks) as total_clicks
        ')->first();

        if (!$totals || $totals->total_views == 0) {
            return 0;
        }

        return ($totals->total_clicks / $totals->total_views) * 100;
    }

    /**
     * Retorna as métricas do mês atual
     */
    public function getCurrentMonthMetrics()
    {
        return $this->analytics()
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->selectRaw('
                SUM(views) as views,
                SUM(clicks) as clicks,
                SUM(calls) as calls
            ')
            ->first();
    }

    /**
     * Verifica se o negócio atingiu alguma meta
     */
    public function checkGoals()
    {
        $currentGoal = $this->goals()
            ->where('year', now()->year)
            ->where('month', now()->month)
            ->first();

        if (!$currentGoal) {
            return null;
        }

        $currentMetrics = $this->getCurrentMonthMetrics();
        
        return [
            'views' => [
                'goal' => $currentGoal->monthly_views_goal,
                'current' => $currentMetrics->views ?? 0,
                'achieved' => ($currentMetrics->views ?? 0) >= $currentGoal->monthly_views_goal
            ],
            'clicks' => [
                'goal' => $currentGoal->monthly_clicks_goal,
                'current' => $currentMetrics->clicks ?? 0,
                'achieved' => ($currentMetrics->clicks ?? 0) >= $currentGoal->monthly_clicks_goal
            ],
            'conversion' => [
                'goal' => $currentGoal->conversion_rate_goal,
                'current' => $this->getConversionRate(now()->startOfMonth(), now()),
                'achieved' => $this->getConversionRate(now()->startOfMonth(), now()) >= $currentGoal->conversion_rate_goal
            ]
        ];
    }

    /**
     * Retorna o crescimento percentual em relação ao período anterior
     */
    public function getGrowthRate($metric = 'views', $currentPeriodStart = null, $currentPeriodEnd = null)
    {
        if (!$currentPeriodStart) {
            $currentPeriodStart = now()->startOfMonth();
        }
        if (!$currentPeriodEnd) {
            $currentPeriodEnd = now();
        }

        $previousPeriodStart = clone $currentPeriodStart;
        $previousPeriodEnd = clone $currentPeriodEnd;
        $previousPeriodStart->subMonth();
        $previousPeriodEnd->subMonth();

        $current = $this->analytics()
            ->whereBetween('date', [$currentPeriodStart, $currentPeriodEnd])
            ->sum($metric);

        $previous = $this->analytics()
            ->whereBetween('date', [$previousPeriodStart, $previousPeriodEnd])
            ->sum($metric);

        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    public function smartCalendar()
{
    return $this->hasMany(SmartCalendar::class);
}

public function actions()
{
    return $this->hasMany(Action::class);
}

public function photos()
{
    return $this->hasMany(Photo::class);
}

public function reviews()
{
    return $this->hasMany(Review::class);
}
}