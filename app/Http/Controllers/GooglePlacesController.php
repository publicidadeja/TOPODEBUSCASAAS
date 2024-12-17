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
            'type' => 'nullable|string',
            'keyword' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $results = $this->placesService->nearbySearch($request->all());
            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao buscar locais próximos'], 500);
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