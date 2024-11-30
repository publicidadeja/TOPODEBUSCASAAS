<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessGoal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class GoalController extends Controller
{
    public function index(Business $business)
{
    $currentGoal = $business->goals()
        ->where('year', now()->year)
        ->where('month', now()->month)
        ->first();

    $previousGoals = $business->goals()
        ->where('year', now()->year)
        ->where('month', '<', now()->month)
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();

    // Buscar analytics do mês atual
    $currentMonthAnalytics = $business->analytics()
        ->whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->selectRaw('SUM(views) as views, SUM(clicks) as clicks, SUM(calls) as calls')
        ->first();

    return view('goals.index', compact(
        'business',
        'currentGoal',
        'previousGoals',
        'currentMonthAnalytics'
    ));
}

    public function store(Request $request, Business $business)
    {
        $validated = $request->validate([
            'monthly_views_goal' => 'required|numeric|min:0',
            'monthly_clicks_goal' => 'required|numeric|min:0',
            'conversion_rate_goal' => 'required|numeric|min:0|max:100',
        ]);

        $business->goals()->updateOrCreate(
            [
                'year' => now()->year,
                'month' => now()->month,
            ],
            $validated
        );

        return redirect()
            ->route('goals.index', $business)
            ->with('success', 'Metas atualizadas com sucesso!');
    }
}