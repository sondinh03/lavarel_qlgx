{{-- Bridge Chart.js + Livewire 2: đọc payload JSON trong DOM sau mỗi lần render --}}
@once
@push('scripts')
<script>
window.qlgxChartBridge = window.qlgxChartBridge || (function () {
    const registry = {};

    function destroyCanvas(id) {
        const el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        const existing = Chart.getChart(el);
        if (existing) existing.destroy();
    }

    function refresh(key) {
        const cfg = registry[key];
        if (!cfg) return;

        cfg.canvasIds.forEach(destroyCanvas);

        const payloadEl = document.getElementById(cfg.payloadId);
        if (!payloadEl) return;

        let payload;
        try {
            payload = JSON.parse(payloadEl.textContent);
        } catch (e) {
            return;
        }

        cfg.render(payload);
    }

    function refreshAll() {
        Object.keys(registry).forEach(refresh);
    }

    let listenersBound = false;

    function bindListeners() {
        if (listenersBound) return;
        listenersBound = true;

        document.addEventListener('livewire:load', () => setTimeout(refreshAll, 50));
        document.addEventListener('livewire:update', () => setTimeout(refreshAll, 50));
    }

    return {
        register(key, config) {
            registry[key] = config;
            bindListeners();
            setTimeout(() => refresh(key), 50);
        },
    };
})();
</script>
@endpush
@endonce
