@auth
@if (config('webpush.vapid.public_key'))
<script>
    window.MVGX_WEBPUSH = {
        vapidPublicKey: @json(config('webpush.vapid.public_key')),
        subscribeUrl: @json(route('push.subscribe')),
        unsubscribeUrl: @json(route('push.unsubscribe')),
        statusUrl: @json(route('push.status')),
        csrfToken: @json(csrf_token()),
    };
</script>
<script src="{{ asset('js/webpush.js') }}?v=1"></script>
@endif
@endauth
