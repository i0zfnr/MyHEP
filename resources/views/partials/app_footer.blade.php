@php($footerClass = $footerClass ?? 'app-footer')
<footer class="{{ $footerClass }}">
    <div class="app-footer-inner">
        &copy; {{ date('Y') }} MyHEP. {{ __('home.copyright') }}
    </div>
</footer>
