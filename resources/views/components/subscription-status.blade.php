@props(['status'])

@php
$styles = [
    'active' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
    'trialing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
    'cancelled' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
    'expired' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
    'payment_failed' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
][$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-300';
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $styles }}">
    {{ ucfirst($status) }}
</span>
