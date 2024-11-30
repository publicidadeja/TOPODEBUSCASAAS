<?php

namespace App\Http\Controllers;

use App\Services\GoogleAuthService;
use App\Services\GoogleBusinessService;
use Illuminate\Http\Request;

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
            return redirect()
                ->route('business.index')
                ->with('error', 'Autorização do Google negada.');
        }

        $token = $this->googleAuth->handleCallback($request->code);
        
        // Salva o token no usuário
        auth()->user()->update([
            'google_token' => json_encode($token)
        ]);

        // Dispara o job de importação
        ImportGoogleBusinesses::dispatch(auth()->user())
            ->delay(now()->addSeconds(5));

        return redirect()
            ->route('business.index')
            ->with('success', 'Autenticação realizada com sucesso! A importação dos negócios começará em breve.');

    } catch (\Exception $e) {
        return redirect()
            ->route('business.index')
            ->with('error', 'Erro ao importar negócios: ' . $e->getMessage());
    }
}
}