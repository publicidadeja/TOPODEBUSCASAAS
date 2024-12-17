<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessAnalytics;
use Google\Client;
use Google\Service\MyBusinessBusinessInformation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Exception;

class GoogleBusinessService
{
    protected $client;
    protected $service;
    protected $retryAttempts = 5;
    protected $retryDelay = 60;
    protected $rateLimitDelay = 61;
    protected $requestDelay = 2;
    protected $maxBackoffDelay = 300;
    protected $googleService;
    protected $keywordService;
protected $aiAnalysisService;

    
    public function __construct(
        GoogleBusinessService $googleService,
        KeywordService $keywordService,
        AIAnalysisService $aiAnalysisService
    ) {
        $this->googleService = $googleService;
        $this->keywordService = $keywordService;
        $this->aiAnalysisService = $aiAnalysisService;
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

public function updateBusinessHours($business, $specialHours) 
{
    try {
        $this->setupClientToken($business->user);
        // Implementar lógica de atualização de horários
        return true;
    } catch (\Exception $e) {
        Log::error('Erro ao atualizar horários: ' . $e->getMessage());
        return false;
    }
}

public function getBusinessInsights($businessId, $dateRange = '30daysAgo')
{
    try {
        $this->setupClientToken(auth()->user());
        
        // Make the actual API call to Google My Business API
        // This is a simplified example - you'll need to implement the actual API call
        $response = $this->service->accounts_locations->getMetrics([
            'name' => $businessId,
            'dateRange' => $dateRange
        ]);

        return [
            'views' => [
                'total' => $response->views ?? 0,
                'previous' => $response->previousViews ?? 0,
                'trend' => $response->viewsTrend ?? 0
            ],
            'clicks' => [
                'total' => $response->clicks ?? 0,
                'previous' => $response->previousClicks ?? 0,
                'trend' => $response->clicksTrend ?? 0
            ],
            'calls' => [
                'total' => $response->calls ?? 0,
                'previous' => $response->previousCalls ?? 0,
                'trend' => $response->callsTrend ?? 0
            ],
            'direction_requests' => [
                'total' => $response->directionRequests ?? 0,
                'previous' => $response->previousDirectionRequests ?? 0,
                'trend' => $response->directionRequestsTrend ?? 0
            ]
        ];
    } catch (\Exception $e) {
        \Log::error('Error fetching business insights: ' . $e->getMessage());
        throw $e;
    }
}

public function getSearchKeywords($business, $dateRange = '30daysAgo')
{
    try {
        $this->setupClientToken($business->user);
        
        $locationName = $business->google_business_id;
        
        // Chamada à API do Google My Business para buscar palavras-chave de pesquisa
        $response = $this->service->accounts_locations_searchkeywords->list([
            'name' => $locationName,
            'timeRange' => [
                'startTime' => $dateRange,
                'endTime' => 'today'
            ]
        ]);

        $keywords = [];
        foreach ($response->getSearchKeywords() as $keyword) {
            $keywords[$keyword->getKeyword()] = [
                'count' => $keyword->getSearchCount(),
                'trend' => $keyword->getTrend()
            ];
        }

        return $keywords;

    } catch (Exception $e) {
        Log::error('Erro ao buscar palavras-chave: ' . $e->getMessage());
        return [];
    }
}

public function getNearbyCompetitors($params)
{
    try {
        Log::info('Iniciando getNearbyCompetitors', [
            'params' => $params,
            'api_key_exists' => !empty(config('services.google.places_api_key'))
        ]);

        // Validar parâmetros
        if (empty($params['location']['lat']) || empty($params['location']['lng'])) {
            Log::warning('Coordenadas inválidas para busca de concorrentes', [
                'latitude' => $params['location']['lat'] ?? null,
                'longitude' => $params['location']['lng'] ?? null
            ]);
            return [];
        }

        // Configurar parâmetros da busca
        $searchParams = [
            'location' => [
                'lat' => (float)$params['location']['lat'],
                'lng' => (float)$params['location']['lng']
            ],
            'radius' => $params['radius'] ?? 5000,
            'type' => $params['type'] ?? 'establishment',
            'keyword' => $params['keyword'] ?? '',
            'language' => 'pt-BR'
        ];

        Log::info('Parâmetros de busca configurados', [
            'search_params' => $searchParams
        ]);

        // Fazer a chamada à API
        try {
            $places = $this->service->places->nearbySearch($searchParams);
            
            Log::info('Resposta da API Places recebida', [
                'status' => $places->status ?? 'NO_STATUS',
                'results_count' => isset($places->results) ? count($places->results) : 0
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na chamada à API Places', [
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return [];
        }

        // Processar resultados
        $competitors = [];
        if (!empty($places->results)) {
            foreach ($places->results as $place) {
                $competitors[] = [
                    'name' => $place->name,
                    'rating' => $place->rating ?? 0,
                    'reviews' => $place->user_ratings_total ?? 0,
                    'address' => $place->vicinity ?? '',
                    'distance' => $this->calculateDistance(
                        $params['location']['lat'],
                        $params['location']['lng'],
                        $place->geometry->location->lat,
                        $place->geometry->location->lng
                    ),
                    'place_id' => $place->place_id
                ];
            }
        }

        Log::info('Processamento de concorrentes finalizado', [
            'total_competitors' => count($competitors)
        ]);

        return $competitors;

    } catch (\Exception $e) {
        Log::error('Erro geral em getNearbyCompetitors', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        return [];
    }
}

private function calculateDistance($lat1, $lon1, $lat2, $lon2)
{
    try {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + 
                cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return round($miles * 1.609344, 2); // Conversão para quilômetros
    } catch (\Exception $e) {
        Log::error('Erro ao calcular distância', [
            'error' => $e->getMessage(),
            'coordinates' => [
                'lat1' => $lat1,
                'lon1' => $lon1,
                'lat2' => $lat2,
                'lon2' => $lon2
            ]
        ]);
        return 0;
    }
}

private function getPlacePhotoUrl($photoReference)
{
    try {
        if (!$photoReference) {
            return null;
        }

        $apiKey = config('services.google.places_api_key');
        return "https://maps.googleapis.com/maps/api/place/photo?"
            . "maxwidth=400&photoreference={$photoReference}"
            . "&key={$apiKey}";
    } catch (\Exception $e) {
        Log::error('Erro ao gerar URL da foto', [
            'error' => $e->getMessage()
        ]);
        return null;
    }
}


}