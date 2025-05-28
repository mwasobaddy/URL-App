@extends('errors.minimal')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Server Error'))
@section('message_description', __('Oops! Something went wrong on our servers. We are working to fix the problem. Please try again later.'))

@section('illustration')
<!-- Custom SVG illustration for 500 page - Server maintenance concept -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
  <!-- Server Racks -->
  <g class="animate-float" style="animation-duration: 7s;">
    <!-- Server Stack -->
    <rect x="260" y="220" width="120" height="150" rx="5" fill="#1E293B" />
    
    <!-- Server Units -->
    <rect x="270" y="230" width="100" height="20" rx="2" fill="#475569" />
    <rect x="270" y="260" width="100" height="20" rx="2" fill="#475569" />
    <rect x="270" y="290" width="100" height="20" rx="2" fill="#475569" />
    <rect x="270" y="320" width="100" height="20" rx="2" fill="#475569" />
    <rect x="270" y="350" width="100" height="20" rx="2" fill="#FC8181" /> <!-- Error server unit -->
    
    <!-- Server Lights -->
    <circle cx="280" cy="240" r="3" fill="#10B981" class="animate-pulse-slow" />
    <circle cx="280" cy="270" r="3" fill="#10B981" class="animate-pulse-slow" style="animation-delay: 0.5s;" />
    <circle cx="280" cy="300" r="3" fill="#10B981" class="animate-pulse-slow" style="animation-delay: 1s;" />
    <circle cx="280" cy="330" r="3" fill="#10B981" class="animate-pulse-slow" style="animation-delay: 1.5s;" />
    <circle cx="280" cy="360" r="3" fill="#EF4444" class="animate-pulse-slow" style="animation-delay: 2s;" />
  </g>
  
  <!-- Error Symbol -->
  <g transform="translate(380, 180)" class="animate-pulse-slow" style="animation-duration: 2s;">
    <circle cx="0" cy="0" r="35" fill="rgba(239, 68, 68, 0.7)" />
    <text x="0" y="7" font-family="Poppins, sans-serif" font-size="40" text-anchor="middle" fill="white" font-weight="bold">!</text>
  </g>

  <!-- Connection Lines -->
  <path d="M320,180 C350,160 380,170 380,180" stroke="#6366F1" stroke-width="2" stroke-dasharray="5,5" class="animate-pulse-slow" />
  
  <!-- Binary Floating Elements -->
  <g class="animate-float" style="animation-duration: 10s; animation-delay: 1s;">
    <text x="240" y="150" font-family="monospace" font-size="12" fill="#A5B4FC" opacity="0.7">01001</text>
    <text x="360" y="130" font-family="monospace" font-size="12" fill="#A5B4FC" opacity="0.7">10110</text>
    <text x="420" y="260" font-family="monospace" font-size="12" fill="#A5B4FC" opacity="0.7">11001</text>
    <text x="220" y="190" font-family="monospace" font-size="12" fill="#A5B4FC" opacity="0.7">00101</text>
  </g>
  
  <!-- Decorative Elements - Cloud Services -->
  <ellipse cx="180" cy="150" r="30" fill="#475569" opacity="0.2" />
  <ellipse cx="460" cy="170" r="25" fill="#475569" opacity="0.2" />
  
  <!-- Network Connection Visualization -->
  <g>
    <path d="M180,150 L260,230" stroke="#6366F1" stroke-width="1" stroke-dasharray="5,5" />
    <path d="M460,170 L360,230" stroke="#6366F1" stroke-width="1" stroke-dasharray="5,5" />
    <path d="M180,150 L460,170" stroke="#6366F1" stroke-width="1" stroke-dasharray="5,5" />
  </g>
  
  <!-- Broken Connection -->
  <path d="M320,370 L320,400 L320,410" stroke="#EF4444" stroke-width="3" stroke-linecap="round" stroke-dasharray="1,6" />
  <circle cx="320" cy="420" r="5" fill="#EF4444" class="animate-pulse-slow" />
</svg>
@endsection
