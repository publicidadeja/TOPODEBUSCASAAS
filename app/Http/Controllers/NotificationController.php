<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Notification;
use App\Models\NotificationSetting;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Business $business)
    {
        $notifications = $business->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $settings = $business->notificationSettings;

        return view('notifications.index', compact('business', 'notifications', 'settings'));
    }

    public function settings(Business $business)
    {
        $settings = $business->notificationSettings;
        return view('notifications.settings', compact('business', 'settings'));
    }

    public function updateSettings(Request $request, Business $business)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.metric_type' => 'required|in:views,clicks,conversion',
            'settings.*.condition' => 'required|in:above,below',
            'settings.*.threshold' => 'required|numeric',
            'settings.*.email_enabled' => 'boolean',
            'settings.*.app_enabled' => 'boolean',
        ]);

        // Remove configurações antigas
        $business->notificationSettings()->delete();

        // Adiciona novas configurações
        foreach ($validated['settings'] as $setting) {
            $business->notificationSettings()->create($setting);
        }

        return redirect()
            ->route('notifications.settings', $business)
            ->with('success', 'Configurações de notificação atualizadas com sucesso!');
    }

    public function markAsRead(Business $business, Notification $notification)
    {
        $notification->markAsRead();
        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Business $business)
    {
        $business->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}