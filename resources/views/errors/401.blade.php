@extends('errors.minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Unauthorized'))
@section('message_description')
    Sorry, you are not authorized to access this page. Please log in or contact the administrator.
@endsection

@section('illustration')
<!-- Custom SVG illustration for 401 page - Authentication concept -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-2xl">
  <!-- Locked Padlock -->
  <g class="animate-float" style="animation-duration: 8s;">
    <!-- Lock Body -->
    <rect x="260" y="250" width="80" height="85" rx="10" fill="url(#lockGradient)" />
    
    <!-- Lock Shackle -->
    <path d="M320,250 C320,220 280,220 280,250" 
          fill="none" stroke="#475569" stroke-width="15" stroke-linecap="round" />
          
    <!-- Keyhole -->
    <circle cx="300" cy="285" r="12" fill="#1E293B" />
    <rect x="297" y="285" width="6" height="18" rx="3" fill="#1E293B" />
  </g>
  
  <!-- Login Form visualization -->
  <g transform="translate(410, 240)">
    <!-- Form background -->
    <rect x="-60" y="-50" width="120" height="100" rx="8" fill="#FFFFFF" stroke="#CBD5E1" stroke-width="2" />
    
    <!-- Username field -->
    <rect x="-45" y="-30" width="90" height="25" rx="5" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="1" />
    <text x="-35" y="-13" font-family="Poppins, sans-serif" font-size="10" fill="#64748B">Username</text>
    
    <!-- Password field -->
    <rect x="-45" y="5" width="90" height="25" rx="5" fill="#F1F5F9" stroke="#CBD5E1" stroke-width="1" />
    <text x="-35" y="22" font-family="Poppins, sans-serif" font-size="10" fill="#64748B">• • • • • •</text>
    
    <!-- Login button -->
    <rect x="-30" y="40" width="60" height="20" rx="5" fill="#EF4444" />
    <text x="0" y="53" font-family="Poppins, sans-serif" font-size="10" text-anchor="middle" fill="white">LOGIN</text>
  </g>
  
  <!-- Authentication Failed Message -->
  <g transform="translate(300, 180)" class="animate-pulse-slow">
    <rect x="-90" y="-25" width="180" height="50" rx="10" fill="#FEF2F2" stroke="#FECACA" stroke-width="2" />
    <text x="0" y="5" font-family="Poppins, sans-serif" font-size="16" font-weight="bold" text-anchor="middle" fill="#DC2626">NOT AUTHORIZED</text>
  </g>
  
  <!-- Circular Authentication Visualization -->
  <g transform="translate(200, 240)">
    <circle cx="0" cy="0" r="40" fill="none" stroke="#94A3B8" stroke-width="3" stroke-dasharray="10,5" class="animate-spin" style="animation-duration: 20s;" />
    
    <!-- User Icon -->
    <circle cx="0" cy="-10" r="12" fill="#94A3B8" />
    <path d="M-15,15 C-15,0 15,0 15,15" fill="#94A3B8" />
    
    <!-- Red X -->
    <path d="M-25,-25 L25,25 M-25,25 L25,-25" stroke="#EF4444" stroke-width="4" />
  </g>
  
  <!-- Connection Line -->
  <path d="M235,240 L265,250" stroke="#94A3B8" stroke-width="2" stroke-dasharray="5,5" />
  
  <!-- Decorative Elements -->
  <circle cx="180" cy="180" r="10" fill="#A855F7" opacity="0.7" class="animate-pulse-slow" />
  <circle cx="460" cy="180" r="8" fill="#EC4899" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 0.5s;" />
  <circle cx="210" cy="320" r="12" fill="#14B8A6" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 1s;" />
  
  <!-- Define Gradients -->
  <defs>
    <linearGradient id="lockGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#64748B" />
      <stop offset="100%" stop-color="#334155" />
    </linearGradient>
  </defs>
</svg>
@endsection
