{{-- Di chuyển giữa các ô điểm bằng Enter và các phím mũi tên --}}
<script>
    document.addEventListener('keydown', function(e) {
        if (!e.target.classList.contains('score-input')) return;

        const key = e.key;
        if (!['Enter', 'ArrowDown', 'ArrowUp', 'ArrowLeft', 'ArrowRight'].includes(key)) return;

        e.preventDefault();

        const row = parseInt(e.target.dataset.row);
        const col = parseInt(e.target.dataset.col);
        let nextRow = row, nextCol = col;

        switch (key) {
            case 'Enter':
            case 'ArrowDown':  nextRow = row + 1; break;
            case 'ArrowUp':    nextRow = row - 1; break;
            case 'Tab': e.shiftKey ? nextCol = col - 1 : nextCol = col + 1; break;
        }

        const next = document.querySelector(
            `.score-input[data-row="${nextRow}"][data-col="${nextCol}"]`
        );
        if (next) { next.focus(); next.select(); }
    });
</script>
