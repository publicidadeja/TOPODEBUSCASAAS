<?php

namespace App\Http\Controllers;

use App\Services\GooglePlacesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GooglePlacesController extends Controller
{
    protected $placesService;

    public function __construct(GooglePlacesService $placesService)
    {
        $this->placesService = $placesService;
    }

    public function nearbySearch(Request $request)
{
    $validator = Validator::make($request->all(), [
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
        'radius' => 'nullable|numeric|max:50000',
        'segment' => 'required|string', // Adicionado segment como obrigatório
        'type' => 'nullable|string'
    ]);

    if ($validator->fails()) {
        \Log::error('Validação falhou na busca de lugares:', [
            'errors' => $validator->errors()->toArray()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos',
            'errors' => $validator->errors()
        ], 422);
    }

    try {
        // Log dos parâmetros recebidos
        \Log::info('Parâmetros da busca:', [
            'lat' => $request->lat,
            'lng' => $request->lng,
            'radius' => $request->radius,
            'segment' => $request->segment,
            'type' => $request->type
        ]);

        // Validação adicional das coordenadas
        if ($request->lat < -90 || $request->lat > 90 || 
            $request->lng < -180 || $request->lng > 180) {
            return response()->json([
                'success' => false,
                'message' => 'Coordenadas geográficas inválidas'
            ], 422);
        }

        $results = $this->placesService->getNearbyCompetitors([
            'location' => [
                'lat' => $request->lat,
                'lng' => $request->lng
            ],
            'radius' => $request->radius ?? 5000,
            'type' => $request->type,
            'segment' => $request->segment, // Adicionado segment aos parâmetros
            'keyword' => $request->segment // Usando segment como keyword também
        ]);

        // Log da resposta completa da API
        \Log::info('Resposta da API Places:', [
            'response' => json_encode($results, JSON_PRETTY_PRINT)
        ]);

        // Verifica se há resultados
        if (empty($results)) {
            return response()->json([
                'success' => true,
                'message' => 'Nenhum concorrente encontrado na região',
                'results' => []
            ]);
        }

        // Processa e formata os resultados
        $formattedResults = array_map(function($result) {
            return [
                'place_id' => $result['place_id'] ?? null,
                'name' => $result['name'] ?? null,
                'address' => $result['address'] ?? null,
                'rating' => $result['rating'] ?? null,
                'total_ratings' => $result['review_count'] ?? 0,
                'distance' => $result['distance'] ?? null,
                'phone' => $result['phone'] ?? null,
                'website' => $result['website'] ?? null,
                'photos' => $result['photos'] ?? [],
                'segment' => $result['segment'] ?? null
            ];
        }, $results);

        // Log dos resultados processados
        \Log::info('Resultados processados:', [
            'count' => count($formattedResults),
            'first_result' => isset($formattedResults[0]) ? $formattedResults[0] : null
        ]);

        return response()->json([
            'success' => true,
            'count' => count($formattedResults),
            'results' => $formattedResults
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro ao buscar concorrentes:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_params' => [
                'lat' => $request->lat,
                'lng' => $request->lng,
                'radius' => $request->radius,
                'segment' => $request->segment,
                'type' => $request->type
            ]
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erro ao buscar locais próximos',
            'debug_info' => [
                'error_type' => get_class($e),
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]
        ], 500);
    }
}

    public function placeDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'place_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $details = $this->placesService->getPlaceDetails($request->place_id);
            return response()->json($details);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar detalhes do local'], 500);
        }
    }

    public function autocomplete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'input' => 'required|string|min:2',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $location = null;
            if ($request->has('lat') && $request->has('lng')) {
                $location = [
                    'lat' => $request->lat,
                    'lng' => $request->lng
                ];
            }

            $results = $this->placesService->autocomplete($request->input, $location);
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro no autocomplete'], 500);
        }
    }
}