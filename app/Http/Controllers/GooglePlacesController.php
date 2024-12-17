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

        // Log da resposta da API
        \Log::info('Resposta da API Places:', ['response' => $results]);

        // Verifica se a resposta está no formato esperado
        if (!isset($results['results']) || !is_array($results['results'])) {
            return response()->json([
                'success' => false,
                'message' => 'Formato de dados inválido da API',
                'debug_info' => [
                    'received_data' => $results
                ]
            ], 500);
        }

        // Formata os dados para o padrão esperado
        $formattedResults = array_map(function($place) {
            return [
                'place_id' => $place['place_id'] ?? null,
                'name' => $place['name'] ?? '',
                'vicinity' => $place['vicinity'] ?? '',
                'rating' => $place['rating'] ?? null,
                'user_ratings_total' => $place['user_ratings_total'] ?? 0,
                'geometry' => [
                    'location' => [
                        'lat' => $place['geometry']['location']['lat'] ?? null,
                        'lng' => $place['geometry']['location']['lng'] ?? null
                    ]
                ],
                'opening_hours' => [
                    'open_now' => $place['opening_hours']['open_now'] ?? null
                ],
                'photos' => isset($place['photos']) ? array_map(function($photo) {
                    return $photo['photo_reference'] ?? null;
                }, $place['photos']) : []
            ];
        }, $results['results']);

        return response()->json([
            'success' => true,
            'results' => $formattedResults,
            'total_results' => count($formattedResults)
        ]);

    } catch (\Exception $e) {
        \Log::error('Erro ao buscar concorrentes:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Erro ao buscar locais próximos: ' . $e->getMessage()
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