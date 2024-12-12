<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
            try {
                // Gera prompt contextualizado
                $prompt = $this->buildKeywordPrompt($business);
                
                // Obtém sugestões do Gemini
                $suggestions = $this->geminiService->generateContent($prompt);
                
                if (empty($suggestions)) {
                    throw new \Exception('Não foi possível gerar sugestões de palavras-chave');
                }
                
                return $this->processKeywordSuggestions($suggestions);
                
            } catch (\Exception $e) {
                \Log::error('Erro ao buscar palavras-chave: ' . $e->getMessage());
                return $this->getFallbackKeywords($business);
            }
        });
    }
    
    protected function buildKeywordPrompt(Business $business)
    {
        return "Gere 10 palavras-chave relevantes para uma empresa com as seguintes características:
                Nome: {$business->name}
                Segmento: {$business->segment}
                Cidade: {$business->city}
                Estado: {$business->state}
                Descrição: {$business->description}
                
                Considere:
                - Termos de busca local
                - Intenção de compra
                - Variações comuns
                - Termos específicos do segmento
                
                Formato: Retorne apenas as palavras-chave, uma por linha.";
    }

    protected function processKeywordSuggestions($suggestions)
    {
        // Verifica se $suggestions é um array
        if (is_array($suggestions)) {
            $keywords = $suggestions;
        } else {
            // Se for string, converte em array
            $keywords = array_map('trim', explode("\n", (string)$suggestions));
        }
    
        // Filtra valores vazios
        $keywords = array_filter($keywords);
    
        // Gera volumes de busca simulados
        $processedKeywords = [];
        foreach ($keywords as $keyword) {
            $processedKeywords[$keyword] = rand(100, 1000);
        }
    
        // Ordena por volume de busca
        arsort($processedKeywords);
    
        return $processedKeywords;
    }

    protected function getFallbackKeywords(Business $business)
    {
        // Palavras-chave genéricas baseadas no segmento
        $baseKeywords = [
            $business->segment . ' ' . $business->city,
            'melhor ' . $business->segment,
            $business->segment . ' próximo',
            $business->segment . ' recomendado',
            $business->segment . ' profissional'
        ];
        
        $keywords = [];
        foreach ($baseKeywords as $keyword) {
            $keywords[$keyword] = rand(50, 500);
        }
        
        return $keywords;
    }
}