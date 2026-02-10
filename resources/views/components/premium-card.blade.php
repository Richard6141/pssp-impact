{{-- 
    Composant : Premium Card
    Usage:
    <x-premium-card title="Titre" :gradient="false">
        Contenu ici
    </x-premium-card>
--}}

@props([
    'title' => null,
    'gradient' => false,
    'glass' => false,
    'hover' => true
])

@php
$classes = 'premium-card';
if ($gradient) $classes .= ' premium-card-gradient';
if ($glass) $classes .= ' premium-card-glass';
if (!$hover) $classes = str_replace('premium-card:hover', '', $classes);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if($title)
    <div class="premium-card-header">
        <h5 class="premium-card-title">{{ $title }}</h5>
        @isset($actions)
            <div class="premium-card-actions">
                {{ $actions }}
            </div>
        @endisset
    </div>
    @endif
    
    <div class="premium-card-body">
        {{ $slot }}
    </div>
    
    @isset($footer)
    <div class="premium-card-footer">
        {{ $footer }}
    </div>
    @endisset
</div>
