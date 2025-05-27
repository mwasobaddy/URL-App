@props([
    'title' => '',          // Title for the stats card
    'value' => 0,           // Value to display - Changed default to 0 instead of empty string
    'type' => 'default',    // Visual styling: success, info, warning, danger, default
    'trend' => null,        // Trend indicator: up, down, none
    'trendText' => null,    // Custom trend text to display
    'icon' => null,         // Icon to display: check-circle, clock, users, currency-dollar, chart-bar
    'caption' => null       // Optional custom caption (defaults to type name)
])

@php
    $typeClasses = match ($type ?? 'default') {
        'success' => 'bg-green-50 text-green-700 dark:bg-green-600/20 dark:text-green-300',
        'info' => 'bg-blue-50 text-blue-700 dark:bg-blue-600/20 dark:text-blue-300',
        'warning' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-600/20 dark:text-yellow-300',
        'danger' => 'bg-red-50 text-red-700 dark:bg-red-600/20 dark:text-red-300',
        default => 'bg-gray-50 text-gray-700 dark:bg-gray-600/20 dark:text-gray-300',
    };
    
    // Ensure value is properly formatted as string/number, not array
    $displayValue = is_array($value) ? count($value) : (string) $value;
@endphp

<div class="rounded-md border border-gray-200 bg-white shadow-sm dark:border-gray-600 dark:bg-gray-800">
    <div class="p-4">
        <div class="flex items-center justify-between">
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ $title }}
            </dt>
            <dd class="ml-2 flex items-baseline">
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ $displayValue }}
                </div>
                @if ($trend === 'up')
                    <div class="ml-2 text-sm font-medium text-green-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 flex-shrink-0 text-green-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586l3.293-3.293A1 1 0 0112 7z" clip-rule="evenodd" />
                        </svg>
                        Up
                    </div>
                @elseif ($trend === 'down')
                    <div class="ml-2 text-sm font-medium text-red-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 flex-shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1v-5a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586l-4.293-4.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414l3.293 3.293A1 1 0 0012 13z" clip-rule="evenodd" />
                        </svg>
                        Down
                    </div>
                @elseif(isset($trendText) && $trendText)
                    <div class="ml-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ $trendText }}
                    </div>
                @endif
            </dd>
        </div>
        @if ($icon ?? null)
            <div class="mt-1 flex items-center text-sm {{ $typeClasses }}">
                <svg class="mr-1.5 h-5 w-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    @if ($icon === 'check-circle')
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    @elseif ($icon === 'clock')
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.5l2.529 1.265a1 1 0 00.741-1.575l-3-1.5a1 1 0 10-2 0v3.5l2.529 1.265a1 1 0 00.741-1.575l-3-1.5z" clip-rule="evenodd" />
                    @elseif ($icon === 'users')
                        <path fill-rule="evenodd" d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" clip-rule="evenodd" />
                    @elseif ($icon === 'currency-dollar')
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                    @elseif ($icon === 'chart-bar')
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                    @elseif ($icon === 'document-text')
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.414L15.586 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" />
                    @elseif ($icon === 'x-circle')
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    @else
                        <path d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4z" />
                    @endif
                </svg>
                <span>
                    {{ $caption ?? Str::headline($type) }}
                </span>
            </div>
        @endif
    </div>
</div>