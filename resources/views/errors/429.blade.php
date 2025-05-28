@extends('errors.minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Too Many Requests'))
@section('message_description', __('You have made too many requests recently. Please wait before trying again.'))

@section('illustration')
<!-- Custom SVG illustration for 429 page - Rate limit/traffic concept -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
  <!-- Traffic Light -->
  <g class="animate-float" style="animation-duration: 6s;">
    <!-- Light Housing -->
    <rect x="280" y="150" width="40" height="130" rx="5" fill="#334155" />
    <rect x="275" y="280" width="50" height="15" rx="3" fill="#1E293B" />
    <rect x="290" y="295" width="20" height="50" rx="3" fill="#1E293B" />
    
    <!-- Lights -->
    <circle cx="300" cy="180" r="10" fill="#22C55E" opacity="0.3" /> <!-- Green -->
    <circle cx="300" cy="215" r="10" fill="#FBBF24" opacity="0.3" /> <!-- Yellow -->
    <circle cx="300" cy="250" r="10" fill="#EF4444" class="animate-pulse-slow" /> <!-- Red - Active -->
  </g>
  
  <!-- Traffic Queue Visualization -->
  <g>
    <!-- Request packets -->
    <g transform="translate(210, 320)" class="animate-bounce-slow" style="animation-duration: 3s;">
      <rect x="-15" y="-15" width="30" height="30" rx="5" fill="#3B82F6" opacity="0.8" />
      <text x="0" y="5" font-family="monospace" font-size="16" text-anchor="middle" fill="white">R</text>
    </g>
    
    <g transform="translate(250, 320)" class="animate-bounce-slow" style="animation-duration: 3s; animation-delay: 0.2s;">
      <rect x="-15" y="-15" width="30" height="30" rx="5" fill="#3B82F6" opacity="0.8" />
      <text x="0" y="5" font-family="monospace" font-size="16" text-anchor="middle" fill="white">E</text>
    </g>
    
    <g transform="translate(290, 320)" class="animate-bounce-slow" style="animation-duration: 3s; animation-delay: 0.4s;">
      <rect x="-15" y="-15" width="30" height="30" rx="5" fill="#3B82F6" opacity="0.8" />
      <text x="0" y="5" font-family="monospace" font-size="16" text-anchor="middle" fill="white">Q</text>
    </g>
    
    <!-- Barrier Element -->
    <rect x="340" y="305" width="80" height="10" rx="2" fill="#DC2626" />
    <rect x="345" y="260" width="10" height="45" fill="#DC2626" />
    <rect x="405" y="260" width="10" height="45" fill="#DC2626" />
    <path d="M345,260 L355,250 L405,250 L415,260" fill="#DC2626" />
    
    <!-- Stopwatch / Timer -->
    <g transform="translate(375, 200)">
      <circle cx="0" cy="0" r="35" fill="#F8FAFC" stroke="#94A3B8" stroke-width="3" />
      <!-- Clock markings -->
      <line x1="0" y1="-25" x2="0" y2="-30" stroke="#475569" stroke-width="2" />
      <line x1="0" y1="25" x2="0" y2="30" stroke="#475569" stroke-width="2" />
      <line x1="-25" y1="0" x2="-30" y2="0" stroke="#475569" stroke-width="2" />
      <line x1="25" y1="0" x2="30" y2="0" stroke="#475569" stroke-width="2" />
      <!-- Clock hands -->
      <line x1="0" y1="0" x2="0" y2="-20" stroke="#DC2626" stroke-width="3" />
      <line x1="0" y1="0" x2="15" y2="15" stroke="#334155" stroke-width="2" />
      <!-- Center dot -->
      <circle cx="0" cy="0" r="3" fill="#334155" />
    </g>
    
    <!-- Wait message -->
    <g transform="translate(450, 320)">
      <rect x="-60" y="-20" width="120" height="40" rx="8" fill="#FEF2F2" />
      <text x="0" y="5" font-family="Poppins, sans-serif" font-size="12" font-weight="bold" text-anchor="middle" fill="#DC2626">PLEASE WAIT</text>
    </g>
  </g>
  
  <!-- Rate Limit Counter -->
  <g transform="translate(450, 200)">
    <rect x="-40" y="-25" width="80" height="50" rx="8" fill="#F1F5F9" />
    <text x="0" y="0" font-family="monospace" font-size="18" font-weight="bold" text-anchor="middle" fill="#DC2626">25/30</text>
    <text x="0" y="20" font-family="Poppins, sans-serif" font-size="10" text-anchor="middle" fill="#64748B">Requests</text>
  </g>
  
  <!-- Decorative Elements -->
  <path d="M150,300 C200,250 250,280 300,280 S400,250 450,300" stroke="#94A3B8" stroke-width="1" stroke-dasharray="5,5" fill="none" />
  <path d="M150,325 C200,275 250,305 300,305 S400,275 450,325" stroke="#94A3B8" stroke-width="1" stroke-dasharray="5,5" fill="none" />
  <path d="M150,350 C200,300 250,330 300,330 S400,300 450,350" stroke="#94A3B8" stroke-width="1" stroke-dasharray="5,5" fill="none" />
</svg>
@endsection
