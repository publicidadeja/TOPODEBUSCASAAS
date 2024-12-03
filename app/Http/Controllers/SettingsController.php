<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Business $business)
    {
        $this->authorize('update', $business);
        
        return view('business.settings', [
            'business' => $business
        ]);
    }

    public function update(Request $request, Business $business)
    {
        $this->authorize('update', $business);
        
        $validated = $request->validate([
            'notify_views' => 'boolean',
            'notify_clicks' => 'boolean',
            'notify_calls' => 'boolean',
            'notification_frequency' => 'required|in:daily,weekly,monthly',
            'variation_threshold' => 'required|numeric|min:1|max:100',
        ]);

        // Get existing settings or initialize empty array
        $settings = $business->settings ?? [];
        
        // Update settings
        $settings = array_merge($settings, [
            'notify_views' => $validated['notify_views'] ?? false,
            'notify_clicks' => $validated['notify_clicks'] ?? false,
            'notify_calls' => $validated['notify_calls'] ?? false,
            'notification_frequency' => $validated['notification_frequency'],
            'variation_threshold' => $validated['variation_threshold'],
        ]);

        // Update the business settings
        $business->update(['settings' => $settings]);

        return redirect()
            ->route('business.settings', $business)
            ->with('success', 'Configurações atualizadas com sucesso!');
    }
}