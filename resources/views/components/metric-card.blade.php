@props([
    'title',
    'value',
    'growth' => null,
    'color' => 'blue',
    'icon' => null
])

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="flex items-center justify-between">
        <div>
            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">
                {{ $title }}
            </div>
            <div class="mt-1 text-2xl font-semibold text-{{ $color }}-600 dark:text-{{ $color }}-400" data-metric="{{ Str::slug($title) }}">
                {{ $value }}
            </div>
            @if($growth !== null)
                <div class="mt-1 text-sm {{ $growth >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" data-growth="{{ Str::slug($title) }}">
                    <span class="flex items-center">
                        @if($growth >= 0)
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                            +{{ $growth }}%
                        @else
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                            </svg>
                            {{ $growth }}%
                        @endif
                    </span>
                </div>
            @endif
        </div>
        @if($icon)
            <div class="p-3 bg-{{ $color }}-100 dark:bg-{{ $color }}-800 rounded-full">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>