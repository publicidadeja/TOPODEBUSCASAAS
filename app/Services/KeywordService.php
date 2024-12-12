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
                // Gera prompt contextualizado com informações do negócio
                $prompt = $this->buildKeywordPrompt($business);
                
                // Obtém sugestões do Gemini
                $suggestions = $this->geminiService->generateContent($prompt);
                
                if (empty($suggestions)) {
                    throw new \Exception('Não foi possível gerar sugestões de palavras-chave');
                }
                
                // Processa e formata as sugestões
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
                
                Formato desejado: Retorne apenas as palavras-chave, uma por linha.
                Considere termos que potenciais clientes usariam para encontrar este negócio localmente.";
    }

    protected function processKeywordSuggestions($suggestions)
    {
        // Converte string em array e limpa
        $keywords = array_map('trim', explode("\n", $suggestions));
        $keywords = array_filter($keywords);
        
        // Gera volumes de busca simulados (em produção, usar dados reais)
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