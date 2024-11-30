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
    protected $client;
    protected $service;
    protected $retryAttempts = 3;
    protected $retryDelay = 60; // segundos
    protected $rateLimitDelay = 61; // segundos (slightly over 1 minute for safety)

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('services.google.application_name'));
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
    }

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
                    Log::warning('Rate limit atingido, aguardando antes de tentar novamente...', [
                        'attempt' => $attempts,
                        'delay' => $this->rateLimitDelay
                    ]);
                    
                    if ($attempts < $this->retryAttempts) {
                        sleep($this->rateLimitDelay);
                        continue;
                    }
                }
                
                throw $e;
            }
        }
    }

    protected function isRateLimitError($exception)
    {
        // Check for rate limit error (HTTP 429)
        if (method_exists($exception, 'getCode')) {
            return $exception->getCode() === 429;
        }
        
        // Check error message content
        if (method_exists($exception, 'getMessage')) {
            $message = $exception->getMessage();
            return strpos($message, 'RATE_LIMIT_EXCEEDED') !== false 
                || strpos($message, 'Quota exceeded') !== false;
        }
        
        return false;
    }

    protected function doImportBusinesses($user)
    {
        Log::info('Iniciando importação de negócios', ['user_id' => $user->id]);

        try {
            // Configura o token de acesso
            $accessToken = json_decode($user->google_token, true);
            $this->client->setAccessToken($accessToken);

            // Verifica se o token precisa ser atualizado
            if ($this->client->isAccessTokenExpired()) {
                Log::info('Token expirado, renovando...', ['user_id' => $user->id]);
                
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $user->update(['google_token' => json_encode($this->client->getAccessToken())]);
                } else {
                    throw new Exception('Refresh token não disponível. Necessário reautenticar.');
                }
            }

            // Inicializa o serviço
            $this->service = new MyBusinessBusinessInformation($this->client);
            
            // Lista as contas do usuário com tratamento de rate limit
            $accounts = $this->executeWithRetry(function() {
                return $this->service->accounts->listAccounts();
            });

            $importedCount = 0;

            foreach ($accounts->getAccounts() as $account) {
                // Adiciona delay entre requisições para evitar rate limit
                sleep(1);

                // Lista os locais/negócios para cada conta com tratamento de rate limit
                $locations = $this->executeWithRetry(function() use ($account) {
                    return $this->service->accounts_locations->listAccountsLocations($account->name);
                });

                if ($locations && $locations->getLocations()) {
                    foreach ($locations->getLocations() as $location) {
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
            }

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
}