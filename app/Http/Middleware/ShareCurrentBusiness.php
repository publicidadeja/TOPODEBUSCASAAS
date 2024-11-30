<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShareCurrentBusiness
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $currentBusiness = null;
            
            // Tenta pegar o ID do negócio da rota ou query string
            $businessId = $request->route('businessId') ?? $request->query('businessId');
            
            if ($businessId) {
                $currentBusiness = auth()->user()->businesses()->find($businessId);
            }
            
            // Se não encontrou, pega o primeiro negócio do usuário
            if (!$currentBusiness) {
                $currentBusiness = auth()->user()->businesses()->first();
            }
            
            // Compartilha com todas as views
            view()->share('currentBusiness', $currentBusiness);
        }

        return $next($request);
    }
}