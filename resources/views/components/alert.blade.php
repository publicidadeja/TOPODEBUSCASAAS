@props(['type' => 'info', 'message'])

<div class="alert alert-{{ $type }} mb-4">
    {{ $message }}
</div>