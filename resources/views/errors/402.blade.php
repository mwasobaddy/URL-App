@extends('errors::minimal')

@section('title', __('Payment Required'))
@section('code', '402')
@section('message', __('Payment Required'))
@section('message_description', __('Access to this resource requires payment. Please upgrade your account or complete the payment process.'))

@section('illustration')
<!-- Custom SVG illustration for 402 page - Payment Required concept -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
  <!-- Credit Card -->
  <g class="animate-float" style="animation-duration: 6s;">
    <!-- Card Base -->
    <rect x="240" y="250" width="170" height="110" rx="12" fill="url(#cardGradient)" />
    
    <!-- Chip -->
    <rect x="270" y="280" width="30" height="25" rx="4" fill="#FBBF24" />
    <line x1="275" y1="288" x2="295" y2="288" stroke="#F59E0B" stroke-width="2" />
    <line x1="275" y1="293" x2="295" y2="293" stroke="#F59E0B" stroke-width="2" />
    <line x1="275" y1="298" x2="295" y2="298" stroke="#F59E0B" stroke-width="2" />
    
    <!-- Card Details -->
    <text x="270" y="330" font-family="monospace" font-size="12" fill="rgba(255,255,255,0.8)">**** **** **** 1234</text>
    <text x="270" y="345" font-family="Poppins, sans-serif" font-size="8" fill="rgba(255,255,255,0.6)">CARD HOLDER</text>
    <text x="380" y="345" font-family="Poppins, sans-serif" font-size="8" fill="rgba(255,255,255,0.6)">EXPIRES</text>
    <text x="270" y="355" font-family="Poppins, sans-serif" font-size="10" fill="rgba(255,255,255,0.8)">USER NAME</text>
    <text x="380" y="355" font-family="Poppins, sans-serif" font-size="10" fill="rgba(255,255,255,0.8)">12/25</text>
  </g>
  
  <!-- Dollar Sign -->
  <g transform="translate(380, 180)" class="animate-pulse-slow">
    <circle cx="0" cy="0" r="35" fill="#10B981" />
    <text x="0" y="10" font-family="Poppins, sans-serif" font-size="40" font-weight="bold" text-anchor="middle" fill="white">$</text>
  </g>
  
  <!-- Payment Terminal -->
  <g transform="translate(220, 235)">
    <!-- Terminal Body -->
    <rect x="-40" y="-40" width="80" height="60" rx="5" fill="#334155" />
    <rect x="-35" y="-35" width="70" height="20" rx="2" fill="#1E293B" />
    <text x="0" y="-22" font-family="monospace" font-size="9" text-anchor="middle" fill="#94A3B8">PAYMENT NEEDED</text>
    
    <!-- Buttons -->
    <circle cx="-20" cy="0" r="5" fill="#DC2626" />
    <circle cx="0" cy="0" r="5" fill="#FBBF24" />
    <circle cx="20" cy="0" r="5" fill="#10B981" />
    
    <!-- Base -->
    <path d="M-30,20 L-25,30 L25,30 L30,20" fill="#334155" />
  </g>
  
  <!-- Payment Process Visualization -->
  <g>
    <!-- Arrow -->
    <path d="M260,235 C270,180 370,180 380,180" fill="none" stroke="#94A3B8" stroke-width="2" stroke-dasharray="5,5" />
    <polygon points="385,180 375,175 375,185" fill="#94A3B8" />
    
    <!-- Lock icon on card to indicate locked access -->
    <g transform="translate(325, 225)">
      <circle cx="0" cy="0" r="15" fill="#EF4444" />
      <rect x="-7" y="-3" width="14" height="10" rx="2" fill="white" />
      <path d="M-3,-3 L-3,-7 C-3,-10 3,-10 3,-7 L3,-3" fill="none" stroke="white" stroke-width="2" />
    </g>
  </g>
  
  <!-- Upgrade Message -->
  <g transform="translate(430, 300)">
    <rect x="-60" y="-20" width="120" height="40" rx="8" fill="#ECFDF5" />
    <text x="0" y="5" font-family="Poppins, sans-serif" font-size="12" font-weight="bold" text-anchor="middle" fill="#10B981">UPGRADE NOW</text>
  </g>
  
  <!-- Coins -->
  <g>
    <circle cx="200" cy="330" r="15" fill="#FBBF24" />
    <circle cx="200" cy="330" r="12" fill="#F59E0B" />
    <text x="200" y="335" font-family="monospace" font-size="14" text-anchor="middle" fill="#FFFBEB">$</text>
    
    <circle cx="460" cy="350" r="12" fill="#FBBF24" />
    <circle cx="460" cy="350" r="9" fill="#F59E0B" />
    <text x="460" y="354" font-family="monospace" font-size="12" text-anchor="middle" fill="#FFFBEB">$</text>
    
    <circle cx="480" cy="330" r="10" fill="#FBBF24" />
    <circle cx="480" cy="330" r="7" fill="#F59E0B" />
    <text x="480" y="333" font-family="monospace" font-size="10" text-anchor="middle" fill="#FFFBEB">$</text>
  </g>
  
  <!-- Define Gradients -->
  <defs>
    <linearGradient id="cardGradient" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#3B82F6" />
      <stop offset="100%" stop-color="#1D4ED8" />
    </linearGradient>
  </defs>
</svg>
@endsection
