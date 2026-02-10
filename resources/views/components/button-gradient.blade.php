{{--
    Composant : Button Gradient
    Usage:
    <x-button-gradient type="primary" size="md" href="#">
        Cliquez ici
    </x-button-gradient>
--}}

@props([
    'type' => 'primary',
    'size' => 'md',
    'href' => null,
    'icon' => null
])

@php
$classes = "btn-gradient-{$type}";
if ($size === 'sm') $classes .= ' btn-sm';
if ($size === 'lg') $classes .= ' btn-lg';

$tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }} 
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}>
    
    @if($icon)
        <i class="{{ $icon }} me-2"></i>
    @endif
    
    {{ $slot }}
</{{ $tag }}>
