<?php

namespace App\Http\Controllers;

use App\Services\GoogleAuthService;
use App\Services\GoogleBusinessService;
use Illuminate\Http\Request;
use App\Jobs\ImportGoogleBusinesses;

class GoogleAuthController extends Controller
{
    protected $googleAuth;
    protected $googleBusiness;

    public function __construct(GoogleAuthService $googleAuth, GoogleBusinessService $googleBusiness)
    {
        $this->googleAuth = $googleAuth;
        $this->googleBusiness = $googleBusiness;
    }

    public function redirect()
    {
        try {
            return redirect($this->googleAuth->getAuthUrl());
        } catch (\Exception $e) {
            return redirect()
                ->route('business.index')
                ->with('error', 'Erro ao conectar com o Google: ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
{
    try {
        if ($request->has('error')) {
            return redirect()->route('business.index')
                ->with('error', 'Autorização do Google negada.');
        }

        $token = $this->googleAuth->handleCallback();
        $business = Business::where('user_id', auth()->id())->first();
        
        if ($business) {
            $business->update([
                'is_connected' => true,
                'google_token' => json_encode($token)
            ]);
        }

        return redirect()->route('automation.index')
            ->with('success', 'Conectado com sucesso ao Google Meu Negócio.');
    } catch (\Exception $e) {
        return redirect()->route('business.index')
            ->with('error', 'Erro ao conectar com o Google Meu Negócio.');
    }
}
}