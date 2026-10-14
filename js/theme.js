// Toggle dark/light mode — preferensi disimpan di localStorage
(function () {
    function updateIcon() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        document.querySelectorAll('#theme-toggle').forEach(function (btn) {
            btn.textContent = isDark ? '☀️' : '🌙';
            btn.setAttribute('aria-label', isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateIcon();
        document.querySelectorAll('#theme-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-theme');
                var next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                try { localStorage.setItem('bsz-theme', next); } catch (e) {}
                updateIcon();
            });
        });
    });
})();
