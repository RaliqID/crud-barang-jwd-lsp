    </div>
</main>

<footer>
    <div class="container text-center">
        <span>Sistem Inventaris Data Barang &copy; <?= date('Y') ?> - Uji Kompetensi Junior Web Developer</span>
    </div>
</footer>

<!-- Bootstrap 5 Bundle JS CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Penanganan konfirmasi hapus via data attribute bersih
    document.querySelectorAll('.form-hapus').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            var nama = this.getAttribute('data-nama') || 'barang ini';
            var kode = this.getAttribute('data-kode') || '';
            var label = kode ? nama + ' (' + kode + ')' : nama;
            if (!confirm('Apakah Anda yakin ingin menghapus ' + label + '?')) {
                e.preventDefault();
                return false;
            }
        });
    });

    // Pencegah double submit dan visual loading spinner
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (e.defaultPrevented) return;

            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }

            var submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                form.dataset.submitting = 'true';
                submitBtn.classList.add('btn-loading');
                var spinner = document.createElement('span');
                spinner.className = 'spinner-border spinner-border-sm me-1';
                spinner.setAttribute('role', 'status');
                spinner.setAttribute('aria-hidden', 'true');
                submitBtn.prepend(spinner);
                setTimeout(function () {
                    submitBtn.disabled = true;
                }, 0);
            }
        });
    });
});
</script>
</body>
</html>
