{{-- Khởi tạo lại Alpine trên DOM mới sau mỗi lần Livewire morph (tooltip, dropdown, v.v.) --}}
<script>
    document.addEventListener('livewire:load', () => {
        Livewire.hook('message.processed', (message, component) => {
            if (window.Alpine && component?.el) {
                Alpine.initTree(component.el);
            }
        });
    });
</script>
