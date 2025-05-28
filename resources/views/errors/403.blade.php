@extends('errors.minimal')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'Forbidden'))

@section('message_description')
    Sorry, you do not have permission to access this page. Please contact the administrator if you believe this is an error.
@endsection

@section('illustration')
<!-- Custom SVG illustration for 403 page - Lock and shield concept -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-2xl">
  <!-- Shield Base with Lock -->
  <g class="animate-float" style="animation-duration: 8s;">
    <!-- Shield -->
    <path d="M320,130 L420,180 C420,280 400,350 320,400 C240,350 220,280 220,180 L320,130" 
          fill="url(#shieldGradient)" stroke="#4F46E5" stroke-width="2" />
          
    <!-- Lock Body -->
    <rect x="280" y="220" width="80" height="70" rx="5" fill="#4F46E5" />
    
    <!-- Lock Shackle -->
    <path d="M340,220 L340,200 C340,180 300,180 300,200 L300,220" 
          fill="none" stroke="#4F46E5" stroke-width="12" stroke-linecap="round" />
          
    <!-- Keyhole -->
    <circle cx="320" cy="245" r="10" fill="#1E293B" />
    <rect x="318" y="245" width="4" height="20" rx="2" fill="#1E293B" />
  </g>
  
  <!-- Access Denied Text -->
  <text x="320" y="310" font-family="Poppins, sans-serif" font-size="14" text-anchor="middle" fill="white" class="animate-pulse-slow">
    ACCESS DENIED
  </text>
  
  <!-- Radiating Warning Elements -->
  <g class="animate-pulse-slow" style="animation-duration: 3s;">
    <circle cx="320" cy="230" r="110" fill="none" stroke="#EF4444" stroke-width="2" stroke-dasharray="10,15" />
    <circle cx="320" cy="230" r="150" fill="none" stroke="#EF4444" stroke-width="2" stroke-dasharray="10,15" opacity="0.7" />
    <circle cx="320" cy="230" r="190" fill="none" stroke="#EF4444" stroke-width="1" stroke-dasharray="5,10" opacity="0.4" />
  </g>

  <!-- Decorative Elements -->
  <circle cx="180" cy="150" r="15" fill="#FBBF24" opacity="0.7" class="animate-pulse-slow" />
  <circle cx="460" cy="170" r="10" fill="#34D399" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 1s;" />
  <circle cx="200" cy="350" r="12" fill="#FB7185" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 0.5s;" />
  
  <!-- Define Gradients -->
  <defs>
    <linearGradient id="shieldGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#818CF8" />
      <stop offset="100%" stop-color="#4F46E5" />
    </linearGradient>
  </defs>
</svg>
@endsection
