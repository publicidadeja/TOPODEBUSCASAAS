<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Services\FakeGoogleBusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BusinessController extends Controller
{
    public function create()
    {
        return view('business.create');
    }

    public function update(Request $request, Business $business)
    {
        $this->authorize('update', $business);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'segment' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
            'cover_photo' => 'nullable|image|max:2048', // Máximo 2MB
        ]);
        Log::info('Negócio atualizado', ['business_id' => $business->id, 'user_id' => auth()->id()]);

        if ($request->hasFile('cover_photo')) {
            // Remove a imagem antiga se existir
            if ($business->cover_photo_url) {
                Storage::disk('public')->delete($business->cover_photo_url);
            }

            // Armazena a nova imagem
            $path = $request->file('cover_photo')->store('business-covers', 'public');
            $validated['cover_photo_url'] = $path;
        }

        $business->update($validated);

        return redirect()
            ->route('business.index')
            ->with('success', 'Negócio atualizado com sucesso!');
    }

    protected $googleService;

    public function __construct(FakeGoogleBusinessService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function index()
    {
        $businesses = auth()->user()->businesses()->latest()->get();
        foreach ($businesses as $business) {
            // Adiciona dados simulados do Google para cada negócio
            $business->googleData = $this->googleService->getBusinessData($business->id);
            $business->insights = $this->googleService->getInsights($business->id);
        }
        
        return view('business.index', compact('businesses'));
    }

    public function show(Business $business)
    {
        $googleData = $this->googleService->getBusinessData($business->id);
        $insights = $this->googleService->getInsights($business->id);
        
        return view('business.show', compact('business', 'googleData', 'insights'));
    }

    // API Endpoints para dados simulados
    public function getGoogleData(Business $business)
    {
        return response()->json([
            'success' => true,
            'data' => $this->googleService->getBusinessData($business->id)
        ]);
    }

    public function getInsights(Business $business, Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        return response()->json([
            'success' => true,
            'data' => $this->googleService->getInsights($business->id, $startDate, $endDate)
        ]);
    }

    public function getMetrics(Business $business)
    {
        $googleData = $this->googleService->getBusinessData($business->id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'views' => $googleData['metrics']['views'],
                'clicks' => $googleData['metrics']['clicks'],
                'calls' => $googleData['metrics']['calls'],
                'rating' => $googleData['rating'] ?? 0,
                'reviews_count' => count($googleData['reviews'] ?? []),
            ]
        ]);
    }

    public function getAutomationData(Business $business)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'suggestions' => [
                    [
                        'type' => 'post',
                        'title' => 'Sugestão de Post',
                        'message' => 'Que tal compartilhar fotos dos produtos mais vendidos esta semana?',
                    ],
                    [
                        'type' => 'promotion',
                        'title' => 'Sugestão de Promoção',
                        'message' => 'Os clientes estão procurando muito por café expresso. Considere criar uma promoção.',
                    ],
                ],
                'trends' => [
                    [
                        'keyword' => 'café artesanal',
                        'growth' => '+15%',
                        'period' => 'última semana',
                    ],
                    [
                        'keyword' => 'café gourmet',
                        'growth' => '+8%',
                        'period' => 'último mês',
                    ],
                ],
            ]
        ]);
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'segment' => 'required|string|max:255',
        'address' => 'required|string|max:255',
        'phone' => 'required|string|max:255',
        'website' => 'nullable|url|max:255',
        'description' => 'nullable|string|max:1000',
    ]);
    Log::info('Negócio criado', ['business_id' => $business->id, 'user_id' => auth()->id()]);


    $business = auth()->user()->businesses()->create($validated);

    return redirect()->route('business.index')
        ->with('success', 'Negócio cadastrado com sucesso!');
}

/**
 * Remove the specified business from storage.
 *
 * @param  \App\Models\Business  $business
 * @return \Illuminate\Http\RedirectResponse
 */
public function destroy(Business $business)
{
    // Autoriza a ação de exclusão
    $this->authorize('delete', $business);

    // Remove a foto de capa se existir
    if ($business->cover_photo) {
        Storage::delete($business->cover_photo);
    }
    Log::info('Negócio excluído', ['business_id' => $business->id, 'user_id' => auth()->id()]);
    // Deleta o negócio
    $business->delete();

    // Redireciona com mensagem de sucesso
    return redirect()->route('business.index')
        ->with('success', 'Negócio excluído com sucesso.');
}

public function edit(Business $business)
{
    return view('business.edit', compact('business'));
}
}