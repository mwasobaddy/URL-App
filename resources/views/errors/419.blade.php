@extends('errors.minimal')

@section('title', __('Page Expired'))
@section('code', '419')
@section('message', __('Page Expired'))
@section('message_description')
    Your session has expired. Please refresh and try again.
@endsection

@section('illustration')
<!-- Custom SVG illustration for 419 page - Token/Security concept -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-2xl">
  <!-- Token/Key Visualization -->
  <g class="animate-float" style="animation-duration: 8s;">
    <!-- Key Body -->
    <path d="M260,240 L340,240 L380,280 L380,320 L340,360 L260,360 L220,320 L220,280 Z" fill="url(#tokenGradient)" />
    
    <!-- Lock Hole -->
    <circle cx="300" cy="300" r="25" fill="#1E293B" />
    <circle cx="300" cy="300" r="15" fill="#475569" />
    
    <!-- Notches -->
    <rect x="350" y="300" width="15" height="20" rx="2" fill="#1E293B" />
    <rect x="365" y="310" width="10" height="10" rx="2" fill="#1E293B" />
  </g>
  
  <!-- Error Highlight -->
  <g class="animate-pulse-slow" style="animation-duration: 2s;">
    <circle cx="300" cy="300" r="100" fill="none" stroke="#F43F5E" stroke-width="4" stroke-dasharray="15,10" />
    <circle cx="300" cy="300" r="120" fill="none" stroke="#F43F5E" stroke-width="2" stroke-dasharray="10,10" opacity="0.6" />
  </g>
  
  <!-- Token Text -->
  <g transform="translate(300, 180)" class="animate-pulse-slow">
    <rect x="-80" y="-25" width="160" height="50" rx="10" fill="#BE185D" />
    <text x="0" y="5" font-family="Poppins, sans-serif" font-size="16" font-weight="bold" text-anchor="middle" fill="white">TOKEN EXPIRED</text>
  </g>
  
  <!-- Binary Code Elements -->
  <g class="animate-float" style="animation-duration: 15s; animation-delay: 1s;" opacity="0.3">
    <text x="200" y="200" font-family="monospace" font-size="12" fill="#A5B4FC">01001011</text>
    <text x="240" y="230" font-family="monospace" font-size="12" fill="#A5B4FC">10101110</text>
    <text x="370" y="210" font-family="monospace" font-size="12" fill="#A5B4FC">11001010</text>
    <text x="400" y="240" font-family="monospace" font-size="12" fill="#A5B4FC">01010101</text>
    <text x="200" y="380" font-family="monospace" font-size="12" fill="#A5B4FC">11010001</text>
    <text x="380" y="370" font-family="monospace" font-size="12" fill="#A5B4FC">00111010</text>
  </g>
  
  <!-- Time Element -->
  <g transform="translate(430, 300)">
    <circle cx="0" cy="0" r="30" fill="#F8FAFC" stroke="#94A3B8" stroke-width="3" />
    <!-- Clock hands -->
    <line x1="0" y1="0" x2="0" y2="-15" stroke="#334155" stroke-width="2" />
    <line x1="0" y1="0" x2="10" y2="10" stroke="#334155" stroke-width="2" />
    <!-- Red X over clock -->
    <path d="M-20,-20 L20,20 M-20,20 L20,-20" stroke="#EF4444" stroke-width="3" />
  </g>
  
  <!-- Decorative Elements -->
  <circle cx="180" cy="250" r="10" fill="#38BDF8" opacity="0.7" class="animate-pulse-slow" />
  <circle cx="160" cy="320" r="8" fill="#22D3EE" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 0.5s;" />
  <circle cx="440" cy="220" r="12" fill="#2DD4BF" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 1s;" />
  
  <!-- Define Gradients -->
  <defs>
    <linearGradient id="tokenGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#64748B" />
      <stop offset="100%" stop-color="#334155" />
    </linearGradient>
  </defs>
</svg>
@endsection
