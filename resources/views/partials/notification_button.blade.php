<button
    type="button"
    class="se-notification-trigger {{ $notificationButtonClass ?? '' }}"
    data-notification-trigger
    aria-label="{{ __('Notifications') }}"
    aria-expanded="false"
    aria-controls="notificationCenter"
>
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9">
        <path stroke-linecap="round" stroke-linejoin="round" d="M14.9 18H5.1c1.2-1.4 1.9-3.2 1.9-5V10a5 5 0 0 1 10 0v3c0 1.8.7 3.6 1.9 5h-4M14 20a2 2 0 0 1-4 0"/>
    </svg>
    <span class="se-notification-count" data-notification-count hidden>0</span>
</button>
