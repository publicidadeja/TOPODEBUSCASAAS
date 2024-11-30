<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessAnalytics;
use Google\Client;
use Google\Service\MyBusinessBusinessInformation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class GoogleBusinessService
{
    protected $retryAttempts = 3;
    protected $retryDelay = 60; // segundos

    public function importBusinesses($user)
    {
        try {
            return $this->executeWithRetry(function () use ($user) {
                return $this->doImportBusinesses($user);
            });
        } catch (Exception $e) {
            Log::error('Erro na importação de negócios do Google:', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw $e;
        }
    }

    protected function executeWithRetry($callback)
    {
        $attempts = 0;
        
        while ($attempts < $this->retryAttempts) {
            try {
                return $callback();
            } catch (Exception $e) {
                $attempts++;
                
                if ($this->isRateLimitError($e)) {
                    if ($attempts < $this->retryAttempts) {
                        Log::warning('Rate limit atingido, aguardando antes de tentar novamente...', [
                            'attempt' => $attempts,
                            'delay' => $this->retryDelay
                        ]);
                        
                        sleep($this->retryDelay);
                        continue;
                    }
                }
                
                throw $e;
            }
        }
    }

    protected function isRateLimitError($exception)
    {
        if (method_exists($exception, 'getCode')) {
            return $exception->getCode() === 429;
        }
        return false;
    }

    protected function doImportBusinesses($user)
    {
        Log::info('Iniciando importação de negócios', ['user_id' => $user->id]);

        // Configura o cliente Google
        $client = new Client();
        $client->setApplicationName(config('services.google.application_name'));
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setAccessToken(json_decode($user->google_token, true));

        // Verifica se o token precisa ser atualizado
        if ($client->isAccessTokenExpired()) {
            Log::info('Token expirado, renovando...', ['user_id' => $user->id]);
            
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                // Atualiza o token do usuário no banco
                $user->update(['google_token' => json_encode($client->getAccessToken())]);
            } else {
                throw new Exception('Refresh token não disponível. Necessário reautenticar.');
            }
        }

        // Cria o serviço do Google My Business
        $mybusinessService = new MyBusinessBusinessInformation($client);
        
        try {
            // Lista as contas do usuário
            $accounts = $mybusinessService->accounts->listAccounts();
            $importedCount = 0;

            foreach ($accounts->getAccounts() as $account) {
                // Lista os locais/negócios para cada conta
                $locations = $mybusinessService->accounts_locations->listAccountsLocations(
                    $account->name
                );

                foreach ($locations->getLocations() as $location) {
                    // Cria ou atualiza o negócio
                    $business = Business::updateOrCreate(
                        ['google_business_id' => $location->name],
                        [
                            'user_id' => $user->id,
                            'name' => $location->locationName,
                            'address' => $location->address->addressLines[0] ?? '',
                            'city' => $location->address->locality ?? '',
                            'state' => $location->address->administrativeArea ?? '',
                            'postal_code' => $location->address->postalCode ?? '',
                            'phone' => $location->phoneNumbers->primaryPhone ?? '',
                            'website' => $location->websiteUri ?? '',
                            'status' => 'active',
                            'last_sync' => now(),
                        ]
                    );

                    $importedCount++;
                    
                    Log::info('Negócio importado/atualizado', [
                        'business_id' => $business->id,
                        'name' => $business->name
                    ]);
                }
            }

            Log::info('Importação concluída com sucesso', [
                'user_id' => $user->id,
                'total_imported' => $importedCount
            ]);

            return [
                'success' => true,
                'imported_count' => $importedCount,
                'message' => "Importação concluída. {$importedCount} negócios importados."
            ];

        } catch (Exception $e) {
            Log::error('Erro ao importar negócios do Google', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            throw new Exception('Erro ao importar negócios: ' . $e->getMessage());
        }
    }

    public function updateAnalytics(Business $business)
    {
        // Implementação do método de atualização de analytics
        // Este método seria responsável por atualizar as métricas do negócio
        try {
            // Aqui você implementaria a lógica real de busca de analytics
            // Por enquanto, vamos criar dados simulados
            $analytics = new BusinessAnalytics([
                'business_id' => $business->id,
                'views' => rand(100, 1000),
                'clicks' => rand(50, 500),
                'calls' => rand(10, 100),
                'date' => Carbon::now()->format('Y-m-d'),
            ]);

            $analytics->save();

            return $analytics;
        } catch (Exception $e) {
            Log::error('Erro ao atualizar analytics:', [
                'business_id' => $business->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}