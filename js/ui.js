// Animasi angka naik di stat card (0 -> nilai asli)
function bszAnimateStatNumbers() {
    document.querySelectorAll('.stat-number').forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-target') || '0');
        var format = el.getAttribute('data-format') || 'int';
        var duration = 900;
        var start = null;

        function formatValue(v) {
            if (format === 'currency') {
                return 'Rp ' + Math.round(v).toLocaleString('id-ID');
            }
            if (format === 'decimal') {
                return v.toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
            }
            return Math.round(v).toLocaleString('id-ID');
        }

        function step(ts) {
            if (!start) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3); // ease-out
            el.textContent = formatValue(target * eased);
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });
}

// Ubah semua .alert jadi toast yang muncul dari pojok kanan atas & ilang sendiri
function bszInitToasts() {
    var alerts = document.querySelectorAll('.alert');
    if (alerts.length === 0) return;

    var container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);

    function removeToast(toast) {
        if (!toast.parentNode) return;
        toast.classList.add('toast-out');
        setTimeout(function () { toast.remove(); }, 250);
    }

    alerts.forEach(function (alertEl) {
        var isError = alertEl.classList.contains('alert-error');
        var toast = document.createElement('div');
        toast.className = 'toast ' + (isError ? 'toast-error' : 'toast-success');
        toast.textContent = alertEl.textContent.trim();

        var closeBtn = document.createElement('button');
        closeBtn.type = 'button';
        closeBtn.className = 'toast-close';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', function () { removeToast(toast); });
        toast.appendChild(closeBtn);

        container.appendChild(toast);
        alertEl.remove();

        setTimeout(function () { removeToast(toast); }, 4500);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    bszAnimateStatNumbers();
    bszInitToasts();
});
