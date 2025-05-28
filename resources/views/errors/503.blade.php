@extends('errors.minimal')

@section('title', __('Service Unavailable'))
@section('code', '503')
@section('message', __('Service Unavailable'))
@section('message_description', __('Sorry, we are doing some maintenance. Please check back soon.'))

@section('illustration')
<!-- Custom SVG illustration for 503 page - Maintenance concept -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
  <!-- Maintenance Worker with Tools -->
  <g class="animate-float" style="animation-duration: 7s;">
    <!-- Worker -->
    <ellipse cx="320" cy="310" rx="25" ry="28" fill="#FB923C" /> <!-- Head with helmet -->
    <rect x="310" y="305" width="20" height="10" rx="2" fill="#FBBF24" /> <!-- Helmet Visor -->
    <path d="M320,338 L320,390" stroke="#2563EB" stroke-width="15" stroke-linecap="round" /> <!-- Body -->
    <path d="M320,350 L350,370" stroke="#2563EB" stroke-width="10" stroke-linecap="round" /> <!-- Right arm -->
    <path d="M320,350 L290,400" stroke="#2563EB" stroke-width="10" stroke-linecap="round" /> <!-- Left arm -->
    <circle cx="350" cy="370" r="8" fill="#FB923C" /> <!-- Right hand -->
    
    <!-- Tool/Wrench -->
    <path d="M350,370 L380,350 A10,10 0 0,1 390,360 L370,380" fill="#94A3B8" />
    <circle cx="370" cy="380" r="7" fill="#94A3B8" />
  </g>
  
  <!-- Gear Elements -->
  <g transform="translate(260, 200)" class="animate-spin" style="animation-duration: 20s; transform-origin: center;">
    <path d="M0,-40 L5,-20 L20,-30 L15,-10 L40,-15 L25,0 L40,15 L15,10 L20,30 L5,20 L0,40 L-5,20 L-20,30 L-15,10 L-40,15 L-25,0 L-40,-15 L-15,-10 L-20,-30 L-5,-20 Z" 
          fill="#94A3B8" opacity="0.7" />
    <circle cx="0" cy="0" r="15" fill="#1E293B" />
  </g>
  
  <g transform="translate(420, 220)" class="animate-spin" style="animation-duration: 15s; animation-direction: reverse; transform-origin: center;">
    <path d="M0,-30 L4,-15 L15,-22 L11,-7 L30,-11 L19,0 L30,11 L11,7 L15,22 L4,15 L0,30 L-4,15 L-15,22 L-11,7 L-30,11 L-19,0 L-30,-11 L-11,-7 L-15,-22 L-4,-15 Z" 
          fill="#94A3B8" opacity="0.5" />
    <circle cx="0" cy="0" r="10" fill="#1E293B" />
  </g>
  
  <!-- Status Message -->
  <g transform="translate(340, 180)" class="animate-pulse-slow">
    <rect x="-70" y="-25" width="140" height="50" rx="10" fill="#FBBF24" />
    <text x="0" y="5" font-family="Poppins, sans-serif" font-size="16" font-weight="bold" text-anchor="middle" fill="#1E293B">MAINTENANCE</text>
  </g>
  
  <!-- Progress Bar -->
  <g transform="translate(320, 230)">
    <rect x="-50" y="-10" width="100" height="20" rx="10" fill="#E2E8F0" />
    <rect x="-50" y="-10" width="70" height="20" rx="10" fill="#3B82F6" class="animate-pulse-slow" />
    <text x="0" y="5" font-family="Poppins, sans-serif" font-size="12" text-anchor="middle" fill="white">70%</text>
  </g>
  
  <!-- Decorative Elements -->
  <circle cx="180" cy="150" r="10" fill="#A855F7" opacity="0.7" class="animate-pulse-slow" />
  <circle cx="460" cy="170" r="8" fill="#EC4899" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 1s;" />
  <circle cx="200" cy="350" r="12" fill="#14B8A6" opacity="0.7" class="animate-pulse-slow" style="animation-delay: 0.5s;" />
  
  <!-- Tool Box -->
  <g transform="translate(220, 380)">
    <rect x="-30" y="-20" width="60" height="40" rx="5" fill="#475569" />
    <rect x="-25" y="-25" width="50" height="10" rx="3" fill="#64748B" />
  </g>
  
  <!-- Construction Lines -->
  <path d="M150,320 L520,320" stroke="#F59E0B" stroke-width="2" stroke-dasharray="10,10" />
  <path d="M150,330 L520,330" stroke="#F59E0B" stroke-width="2" stroke-dasharray="10,10" />
</svg>
@endsection
