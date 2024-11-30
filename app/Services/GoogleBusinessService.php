<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessAnalytics;
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
                // Aqui vai a lógica de importação
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
                
                // Verifica se é um erro de rate limit
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
        // Implementar a lógica real de importação aqui
        // Fazer as chamadas à API do Google com limites adequados
    }
}