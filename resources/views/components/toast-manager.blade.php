<div role="status" aria-live="polite">
    @foreach(['message' => 'success', 'error' => 'error', 'warning' => 'warning', 'info' => 'info'] as $key => $type)
        @if(session()->has($key))
            <x-toast-notification :type="$type" :duration="$type === 'success' ? 3500 : 4000">
                {{ session($key) }}
            </x-toast-notification>
        @endif
    @endforeach
</div>