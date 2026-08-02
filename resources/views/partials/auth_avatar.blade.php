<span class="{{ $class }}" aria-hidden="true">
    <span class="auth-avatar-initials">{{ $initials }}</span>
    @if($url)
        <img src="{{ $url }}" alt="" onerror="this.remove()">
    @endif
</span>
