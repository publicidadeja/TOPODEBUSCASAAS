<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $businesses = auth()->user()->businesses;
        return view('business.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('business.create');
    }

    /**
     * Store a newly created resource in storage.
     */
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

        $business = new Business($validated);
        $business->user_id = auth()->id();
        $business->save();

        return redirect()
            ->route('business.index')
            ->with('success', 'Negócio cadastrado com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Business $business)
    {
        $this->authorize('update', $business);
        return view('business.edit', compact('business'));
    }

    /**
     * Update the specified resource in storage.
     */
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
        ]);

        $business->update($validated);

        return redirect()
            ->route('business.index')
            ->with('success', 'Negócio atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Business $business)
    {
        $this->authorize('delete', $business);
        
        $business->delete();

        return redirect()
            ->route('business.index')
            ->with('success', 'Negócio removido com sucesso!');
    }
}