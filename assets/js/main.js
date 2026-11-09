/**
 * PasarKu - Interactive JS Utilities
 */
document.addEventListener('DOMContentLoaded', function () {
    // Auto dismiss flash alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) {
                bsAlert.close();
            }
        }, 5000);
    });

    // Quantity spinner controls
    document.querySelectorAll('.btn-qty-minus').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.input-group').querySelector('.input-qty');
            if (input) {
                let val = parseInt(input.value) || 1;
                if (val > 1) {
                    input.value = val - 1;
                    if (this.dataset.autoSubmit === 'true') {
                        this.closest('form').submit();
                    }
                }
            }
        });
    });

    document.querySelectorAll('.btn-qty-plus').forEach(btn => {
        btn.addEventListener('click', function () {
            const input = this.closest('.input-group').querySelector('.input-qty');
            if (input) {
                let max = parseInt(input.getAttribute('max')) || 999;
                let val = parseInt(input.value) || 1;
                if (val < max) {
                    input.value = val + 1;
                    if (this.dataset.autoSubmit === 'true') {
                        this.closest('form').submit();
                    }
                } else {
                    alert('Kuantitas telah mencapai batas maksimal stok yang tersedia (' + max + ').');
                }
            }
        });
    });

    // Confirm action modals or prompts
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'Apakah Anda yakin ingin melakukan tindakan ini?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});
