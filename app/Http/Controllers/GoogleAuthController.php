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

            // Importa os negócios
            $this->googleBusiness->importBusinesses(auth()->user());

            return redirect()
                ->route('business.index')
                ->with('success', 'Negócios importados com sucesso!');

        } catch (\Exception $e) {
            return redirect()
                ->route('business.index')
                ->with('error', 'Erro ao importar negócios: ' . $e->getMessage());
        }
    }
}