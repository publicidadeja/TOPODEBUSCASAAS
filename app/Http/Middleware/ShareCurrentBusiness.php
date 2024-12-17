<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShareCurrentBusiness
{
    public function handle(Request $request, Closure $next)
{
    // Inicialize as variáveis com null por padrão
    view()->share('currentBusiness', null);
    view()->share('currentBusinessId', null);

    if (auth()->check()) {
        $currentBusiness = null;
        
        // Try to get business ID from route or query string
        $businessId = $request->route('businessId') ?? $request->query('businessId');
        
        if ($businessId) {
            $currentBusiness = auth()->user()->businesses()->find($businessId);
        }
        
        // If not found, get user's first business
        if (!$currentBusiness) {
            $currentBusiness = auth()->user()->businesses()->first();
        }
        
        if ($currentBusiness) {
            // Store the current business ID in session
            session(['current_business_id' => $currentBusiness->id]);
            
            // Share both the business object and ID with views
            view()->share('currentBusiness', $currentBusiness);
            view()->share('currentBusinessId', $currentBusiness->id);
        }
    }

    return $next($request);
}
}