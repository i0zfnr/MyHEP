@php($footerClass = $footerClass ?? 'app-footer')
<footer class="{{ $footerClass }}">
    &copy; {{ date('Y') }} MyHEP POLIBESUT. {{ __('home.copyright') }}
</footer>
