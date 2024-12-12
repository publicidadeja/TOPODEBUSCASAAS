<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class KeywordService
{
    protected $geminiService;
    
    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    public function getPopularKeywords(Business $business)
    {
        $cacheKey = "business_{$business->id}_keywords";
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($business) {
            // Busca palavras-chave usando informações do negócio
            $keywords = $this->searchKeywords($business);
            
            // Organiza e retorna os resultados
            return collect($keywords)
                ->sortByDesc('relevance')
                ->take(10)
                ->mapWithKeys(function ($item) {
                    return [$item['keyword'] => $item['searchVolume']];
                })
                ->toArray();
        });
    }

    protected function searchKeywords(Business $business)
    {
        try {
            // Usa o GeminiService para gerar palavras-chave relevantes
            $prompt = $this->buildKeywordPrompt($business);
            $suggestions = $this->geminiService->generateContent($prompt);
            
            // Processa e valida as sugestões
            return $this->processKeywordSuggestions($suggestions, $business);
        } catch (\Exception $e) {
            \Log::error('Erro ao buscar palavras-chave: ' . $e->getMessage());
            return [];
        }
    }

    protected function buildKeywordPrompt(Business $business)
    {
        return "Gere palavras-chave relevantes para uma empresa com as seguintes características:
                Segmento: {$business->segment}
                Cidade: {$business->city}
                Descrição: {$business->description}
                Considere termos de busca que potenciais clientes usariam para encontrar este tipo de negócio.";
    }

    protected function processKeywordSuggestions($suggestions, Business $business)
    {
        // Processa e formata as sugestões
        $keywords = [];
        
        foreach ($suggestions as $suggestion) {
            $keywords[] = [
                'keyword' => $suggestion,
                'searchVolume' => rand(100, 1000), // Exemplo - idealmente usar dados reais
                'relevance' => rand(1, 100) // Exemplo - idealmente calcular baseado em dados reais
            ];
        }
        
        return $keywords;
    }
}