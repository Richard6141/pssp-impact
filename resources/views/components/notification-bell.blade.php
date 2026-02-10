{{--
    Composant : Notification Bell
    Usage:
    <x-notification-bell :count="5" />
--}}

@props([
    'count' => 0
])

<div class="notification-bell" title="Notifications">
    <i class="bi bi-bell"></i>
    @if($count > 0)
        <span class="notification-badge">{{ $count > 99 ? '99+' : $count }}</span>
    @endif
</div>
