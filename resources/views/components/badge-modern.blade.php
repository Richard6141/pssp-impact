{{--
    Composant : Badge Modern
    Usage:
    <x-badge-modern type="success" :glow="true">Validé</x-badge-modern>
--}}

@props([
    'type' => 'primary',
    'glow' => false
])

@php
$classes = "badge-modern badge-{$type}";
if ($glow) $classes .= ' badge-glow';
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
