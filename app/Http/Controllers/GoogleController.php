<?php

namespace App\Http\Controllers;

use App\Services\GoogleBusinessService;
use Exception;
use Illuminate\Http\Request;
use Google\Client;

class GoogleController extends Controller
{
    public function auth()
    {
        $client = new Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(route('google.callback'));
        $client->addScope('https://www.googleapis.com/auth/business.manage');
        
        $authUrl = $client->createAuthUrl();
        return redirect($authUrl);
    }

    public function callback(Request $request)
    {
        try {
            $client = new Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(route('google.callback'));
            
            $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
            
            // Salva o token no usuário
            auth()->user()->update([
                'google_token' => json_encode($token)
            ]);

            // Importa os negócios
            $service = app(GoogleBusinessService::class);
            $result = $service->importBusinesses(auth()->user());

            return redirect()
                ->route('business.index')
                ->with('success', 'Negócios importados com sucesso! ' . ($result['imported_count'] ?? 0) . ' negócios encontrados.');
                
        } catch (Exception $e) {
            return redirect()
                ->route('business.index')
                ->with('error', 'Erro ao importar negócios: ' . $e->getMessage());
        }
    }
}