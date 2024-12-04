<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function createInsightNotification(
        Business $business,
        string $title,
        string $message,
        ?string $actionType = null,
        ?array $actionData = null
    ) {
        // Criar notificação no banco
        $notification = Notification::create([
            'business_id' => $business->id,
            'type' => 'insight',
            'title' => $title,
            'message' => $message,
            'action_type' => $actionType,
            'action_data' => $actionData ? json_encode($actionData) : null,
            'read_at' => null
        ]);

        // Verificar preferências de notificação do usuário
        if ($business->user->notification_preferences['email_insights'] ?? false) {
            $this->sendEmailNotification($business->user->email, $notification);
        }

        return $notification;
    }

    protected function sendEmailNotification($email, Notification $notification)
    {
        Mail::send('emails.insight-notification', [
            'notification' => $notification
        ], function ($message) use ($email, $notification) {
            $message->to($email)
                ->subject('Novo insight importante: ' . $notification->title);
        });
    }
}