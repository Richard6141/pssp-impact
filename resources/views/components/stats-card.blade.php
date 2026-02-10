{{--
    Composant : Stats Card
    Usage:
    <x-stats-card 
        icon="bi-truck" 
        value="145" 
        label="Collectes"
        :trend="12"
        color="primary">
    </x-stats-card>
--}}

@props([
    'icon' => 'bi-circle',
    'value' => '0',
    'label' => '',
    'trend' => null,
    'color' => 'primary',
    'animate' => true
])

@php
$iconBgClass = "bg-gradient-{$color}";
$animationClass = $animate ? 'animate-fade-in-up' : '';
@endphp

<div class="stats-card {{ $animationClass }}" {{ $attributes }}>
    <div class="stats-card-icon {{ $iconBgClass }} text-white">
        <i class="{{ $icon }}"></i>
    </div>
    
    <h3 class="stats-card-value">{{ $value }}</h3>
    <p class="stats-card-label">{{ $label }}</p>
    
    @if($trend !== null)
        <span class="stats-card-trend {{ $trend >= 0 ? 'trend-up' : 'trend-down' }}">
            <i class="bi bi-{{ $trend >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
            {{ abs($trend) }}%
        </span>
    @endif
    
    {{ $slot }}
</div>
