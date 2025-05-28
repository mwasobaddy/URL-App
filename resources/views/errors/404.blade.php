@extends('errors.minimal')

@section('title', __('Page Not Found'))
@section('code', '404')
@section('message', __('Page Not Found'))
@section('message_description', __('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.'))

@section('illustration')
<!-- Custom SVG illustration for 404 page - Person on scooter looking for connection -->
<svg viewBox="0 0 650 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto drop-shadow-2xl">
  <!-- Scooter and Person -->
  <g class="animate-float" style="animation-duration: 6s;">
    <ellipse cx="320" cy="390" rx="80" ry="15" fill="rgba(99, 102, 241, 0.2)"/>
    
    <!-- Scooter Base -->
    <path d="M250,380 C290,380 330,380 370,380 C375,370 375,360 370,350 L290,350 C285,360 285,370 290,380 Z" fill="#FFC53D"/>
    
    <!-- Scooter Details -->
    <circle cx="275" cy="385" r="15" fill="#FF5733"/>
    <circle cx="345" cy="385" r="15" fill="#FF5733"/>
    
    <!-- Person -->
    <ellipse cx="310" cy="310" rx="20" ry="25" fill="#FF7E67"/> <!-- Head -->
    <path d="M310,335 L310,360 L340,380" stroke="#FF7E67" stroke-width="10" stroke-linecap="round"/> <!-- Right Arm -->
    <path d="M310,335 L310,360 L285,375" stroke="#FF7E67" stroke-width="10" stroke-linecap="round"/> <!-- Left Arm -->
    <path d="M310,335 L310,365" stroke="#FF7E67" stroke-width="15" stroke-linecap="round"/> <!-- Body -->
    
    <!-- Person Details - Face looking up -->
    <circle cx="305" cy="305" r="2" fill="white"/> <!-- Left Eye -->
    <circle cx="315" cy="305" r="2" fill="white"/> <!-- Right Eye -->
  </g>
  
  <!-- Floating WiFi Icon -->
  <g transform="translate(390, 200)" class="animate-pulse-slow">
    <circle cx="0" cy="0" r="30" fill="rgba(250, 204, 21, 0.7)"/>
    <!-- WiFi Signal -->
    <path d="M-15,10 C-10,5 10,5 15,10" stroke="white" stroke-width="4" stroke-linecap="round" fill="none"/>
    <path d="M-10,0 C-5,-5 5,-5 10,0" stroke="white" stroke-width="4" stroke-linecap="round" fill="none"/>
    <path d="M-5,-10 C-2,-12 2,-12 5,-10" stroke="white" stroke-width="4" stroke-linecap="round" fill="none"/>
    <circle cx="0" cy="15" r="3" fill="white"/>
  </g>
  
  <!-- Cloud Elements -->
  <ellipse cx="475" cy="150" rx="40" ry="30" fill="#F9FAFB" class="animate-pulse-slow" style="animation-delay: 1s; opacity: 0.7;"/>
  
  <!-- Decorative Elements -->
  <path d="M100,200 C150,100 450,120 500,200" stroke="#6366F1" stroke-width="1" stroke-dasharray="5,5" fill="none"/>
  <path d="M150,300 C200,250 400,270 450,300" stroke="#6366F1" stroke-width="1" stroke-dasharray="5,5" fill="none"/>
</svg>
@endsection
