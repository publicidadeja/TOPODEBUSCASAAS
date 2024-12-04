@component('mail::message')
# Novo Insight Importante

**{{ $notification->title }}**

{{ $notification->message }}

@if($notification->action_type)
@component('mail::button', ['url' => route('automation.handle-insight', [
    'business' => $notification->business_id,
    'notification' => $notification->id
])])
Ver Detalhes
@endcomponent
@endif

Obrigado,<br>
{{ config('app.name') }}
@endcomponent