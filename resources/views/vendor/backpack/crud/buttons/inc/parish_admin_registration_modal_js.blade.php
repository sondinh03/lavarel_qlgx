<script>
    if (typeof window.parishAdminRegOpenModal !== 'function') {
        window.parishAdminRegOpenModal = function (modalId) {
            var modal = document.getElementById(modalId);
            if (!modal) {
                return;
            }

            // Modal trong ô DataTables bị backdrop (body) che — gắn vào body trước khi mở
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            $(modal).modal('show');
        };
    }
</script>
