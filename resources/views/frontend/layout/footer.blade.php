<footer class="mt-8 border-t border-black/[0.06] bg-white/60 backdrop-blur-sm hidden sm:block">
    <div class="max-w-4xl mx-auto px-4 py-5 space-y-1 text-center">
        @if(config('settings.copyright'))
        <p class="text-xs text-slate-500 mb-0">{{ config('settings.copyright') }}</p>
        @endif
        @if(config('settings.address'))
        <p class="text-xs text-slate-400 mb-0">{{ config('settings.address') }}</p>
        @endif
    </div>
</footer>
