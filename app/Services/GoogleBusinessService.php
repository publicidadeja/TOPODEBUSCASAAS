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
    protected $retryAttempts = 5;
    protected $retryDelay = 60;
    protected $rateLimitDelay = 61;
    protected $requestDelay = 2;
    protected $maxBackoffDelay = 300; // 5 minutos

    public function __construct()
    {
        $this->client = new Client();
    $this->client->setApplicationName(config('services.google.application_name'));
    $this->client->setClientId(config('services.google.client_id'));
    $this->client->setClientSecret(config('services.google.client_secret'));
    $this->client->addScope('https://www.googleapis.com/auth/business.manage');
    }

    public function importBusinesses($user)
{
    Log::info('Iniciando importação de negócios', [
        'user_id' => $user->id,
        'has_token' => !empty($user->google_token)
    ]);

    try {
        return $this->executeWithRetry(function () use ($user) {
            return $this->doImportBusinesses($user);
        });
    } catch (Exception $e) {
        Log::error('Erro na importação de negócios do Google:', [
            'error' => $e->getMessage(),
            'user_id' => $user->id,
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}
protected function doImportBusinesses($user)
{
    Log::info('Iniciando importação de negócios', ['user_id' => $user->id]);

    try {
        $this->setupClientToken($user);
        $this->service = new MyBusinessBusinessInformation($this->client);
        
        // Obtém as contas com retry
        $accounts = $this->executeWithRetry(function() {
            sleep($this->requestDelay);
            return $this->service->accounts->list(); // Changed from listAccounts() to list()
        });

            if (!$accounts || !$accounts->getAccounts()) {
                Log::warning('Nenhuma conta encontrada', ['user_id' => $user->id]);
                return $this->createResponse(0, "Nenhuma conta encontrada.");
            }

            $importedCount = 0;
            $errors = [];

            foreach ($accounts->getAccounts() as $account) {
                try {
                    $importedCount += $this->processAccount($account, $user);
                } catch (Exception $e) {
                    $errors[] = "Erro na conta {$account->name}: {$e->getMessage()}";
                    Log::error('Erro ao processar conta', [
                        'account' => $account->name,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            $message = $this->createResponseMessage($importedCount, $errors);
            return $this->createResponse($importedCount, $message, !empty($errors));

        } catch (Exception $e) {
            Log::error('Erro fatal na importação', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            throw new Exception('Erro ao importar negócios: ' . $e->getMessage());
        }
    }

    protected function processAccount($account, $user)
    {
        sleep($this->requestDelay);
        
        $locations = $this->executeWithRetry(function() use ($account) {
            return $this->service->accounts_locations->listAccountsLocations($account->name);
        });

        if (!$locations || !$locations->getLocations()) {
            return 0;
        }

        $importedCount = 0;

        foreach ($locations->getLocations() as $location) {
            try {
                if ($this->processLocation($location, $user)) {
                    $importedCount++;
                }
            } catch (Exception $e) {
                Log::error('Erro ao processar location', [
                    'location' => $location->name,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        return $importedCount;
    }

    protected function processLocation($location, $user)
    {
        $business = Business::updateOrCreate(
            ['google_business_id' => $location->name],
            [
                'user_id' => $user->id,
                'name' => $location->locationName,
                'address' => $location->address->addressLines[0] ?? '',
                'city' => $location->address->locality ?? '',
                'state' => $location->address->administrativeArea ?? '',
                'postal_code' => $location->address->postalCode ?? '',
                'phone' => $location->primaryPhone ?? '',
                'website' => $location->websiteUri ?? '',
                'status' => 'active',
                'last_sync' => now(),
            ]
        );

        Log::info('Negócio importado/atualizado', [
            'business_id' => $business->id,
            'name' => $business->name
        ]);

        return true;
    }

    protected function setupClientToken($user)
    {
        $accessToken = json_decode($user->google_token, true);
        $this->client->setAccessToken($accessToken);

        if ($this->client->isAccessTokenExpired()) {
            Log::info('Token expirado, renovando...', ['user_id' => $user->id]);
            
            if (!$this->client->getRefreshToken()) {
                throw new Exception('Refresh token não disponível. Necessário reautenticar.');
            }

            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
            $user->update(['google_token' => json_encode($this->client->getAccessToken())]);
        }
    }

    protected function executeWithRetry($callback)
    {
        $attempts = 0;
        $lastError = null;
        
        while ($attempts < $this->retryAttempts) {
            try {
                return $callback();
            } catch (Exception $e) {
                $attempts++;
                $lastError = $e;
                
                if ($this->isRateLimitError($e)) {
                    $delay = $this->calculateBackoff($attempts);
                    
                    Log::warning('Rate limit atingido, aguardando...', [
                        'attempt' => $attempts,
                        'delay' => $delay
                    ]);
                    
                    if ($attempts < $this->retryAttempts) {
                        sleep($delay);
                        continue;
                    }
                }
                
                if ($attempts >= $this->retryAttempts) {
                    throw $lastError;
                }
            }
        }
    }

    protected function isRateLimitError($e)
    {
        return (method_exists($e, 'getCode') && $e->getCode() === 429) ||
               (method_exists($e, 'getMessage') && (
                   strpos($e->getMessage(), 'RATE_LIMIT_EXCEEDED') !== false ||
                   strpos($e->getMessage(), 'Quota exceeded') !== false
               ));
    }

    protected function calculateBackoff($attempt)
    {
        $baseDelay = $this->rateLimitDelay;
        $delay = min($this->maxBackoffDelay, $baseDelay * pow(2, $attempt - 1));
        $jitter = $delay * 0.3;
        return $delay + rand(-$jitter * 100, $jitter * 100) / 100;
    }

    protected function createResponse($count, $message, $hasErrors = false)
    {
        return [
            'success' => !$hasErrors,
            'imported_count' => $count,
            'message' => $message
        ];
    }

    protected function createResponseMessage($count, $errors = [])
    {
        $message = "Importação concluída. {$count} negócios importados.";
        if (!empty($errors)) {
            $message .= " Alguns erros ocorreram: " . implode("; ", $errors);
        }
        return $message;
    }

    // Add to GoogleBusinessService.php
    public function handleWebhook(Request $request)
    {
        $notification = $request->all();
        Log::info('Received webhook from Google:', $notification);
        
        // Process notification based on type
        switch ($notification['type']) {
            case 'BUSINESS_INFORMATION_CHANGED':
                $this->updateBusinessInformation($notification['location']);
                break;
            case 'NEW_REVIEW':
                $this->processNewReview($notification['review']);
                break;
            // Add other notification types as needed
        }
    }

// Add to GoogleBusinessService.php
private function monitorApiHealth()
{
    try {
        $response = $this->service->accounts->listAccounts();
        Log::info('Google API health check passed');
        return true;
    } catch (Exception $e) {
        Log::error('Google API health check failed: ' . $e->getMessage());
        // Notify administrators
        return false;
    }
}
}