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
            'type' => $request->type
        ]);

        $results = $this->placesService->getNearbyCompetitors([
            'location' => [
                'lat' => $request->lat,
                'lng' => $request->lng
            ],
            'radius' => $request->radius ?? 5000,
            'type' => $request->type
        ]);

        // Log da resposta completa da API
        \Log::info('Resposta bruta da API Places:', [
            'response' => json_encode($results, JSON_PRETTY_PRINT)
        ]);

        // Verifica se a resposta está no formato esperado
        if (!is_array($results)) {
            \Log::error('Resposta da API não é um array:', [
                'response_type' => gettype($results),
                'response' => $results
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Formato de dados inválido da API',
                'debug_info' => [
                    'response_type' => gettype($results)
                ]
            ], 500);
        }

        if (!isset($results['results'])) {
            \Log::error('Resposta da API não contém o campo "results":', [
                'response_keys' => array_keys($results),
                'response' => $results
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Formato de dados inválido da API',
                'debug_info' => [
                    'available_keys' => array_keys($results)
                ]
            ], 500);
        }

        // Log dos resultados processados
        \Log::info('Resultados processados:', [
            'count' => count($results['results']),
            'first_result' => isset($results['results'][0]) ? $results['results'][0] : null
        ]);

        return response()->json([
            'success' => true,
            'results' => $results['results']
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro ao buscar concorrentes:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_params' => [
                'lat' => $request->lat,
                'lng' => $request->lng,
                'radius' => $request->radius,
                'type' => $request->type
            ]
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erro ao buscar locais próximos: ' . $e->getMessage(),
            'debug_info' => [
                'error_type' => get_class($e),
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