    </div> <!-- End Container -->
</main>

<!-- Footer -->
<footer class="footer-pasarku py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="brand-logo mb-3">
                    <span class="fs-3">🧺</span>
                    <span>Pasar<span class="text-warning">Ku</span></span>
                </div>
                <p class="small text-muted mb-3">
                    Platform Marketplace Tradisional Modern yang menghubungkan para pedagang pasar tradisional, petani lokal, dan perajin nusantara langsung dengan para pembeli di seluruh Indonesia.
                </p>
                <div class="d-flex gap-2">
                    <span class="badge bg-light text-success border border-success-subtle p-2">
                        <i class="bi bi-shield-check me-1"></i> Transaksi Aman
                    </span>
                    <span class="badge bg-light text-primary border border-primary-subtle p-2">
                        <i class="bi bi-truck me-1"></i> Kurir Pasar
                    </span>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="fw-bold text-dark mb-3">Kategori Pilihan</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?php echo base_url('index.php?category=sayur-buah'); ?>" class="text-decoration-none text-muted">Sayur & Buah Segar</a></li>
                    <li><a href="<?php echo base_url('index.php?category=bumbu-rempah'); ?>" class="text-decoration-none text-muted">Bumbu & Rempah</a></li>
                    <li><a href="<?php echo base_url('index.php?category=sembako'); ?>" class="text-decoration-none text-muted">Sembako Nusantara</a></li>
                    <li><a href="<?php echo base_url('index.php?category=kuliner-tradisional'); ?>" class="text-decoration-none text-muted">Kuliner Pasar</a></li>
                    <li><a href="<?php echo base_url('index.php?category=kerajinan-tangan'); ?>" class="text-decoration-none text-muted">Kerajinan Anyaman</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="fw-bold text-dark mb-3">Akses Cepat</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2 mb-0">
                    <li><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted">Daftar Produk</a></li>
                    <li><a href="<?php echo base_url('cart.php'); ?>" class="text-decoration-none text-muted">Keranjang Belanja</a></li>
                    <li><a href="<?php echo base_url('my_orders.php'); ?>" class="text-decoration-none text-muted">Status Pesanan</a></li>
                    <li><a href="<?php echo base_url('admin/products.php'); ?>" class="text-decoration-none text-muted">Area Penjual (CRUD)</a></li>
                    <li><a href="<?php echo base_url('auth/login.php'); ?>" class="text-decoration-none text-muted">Demo Akun Cepat</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6">
                <h6 class="fw-bold text-dark mb-3">Informasi Sistem</h6>
                <div class="bg-light p-3 rounded-3 small">
                    <div class="text-dark fw-semibold mb-1">Tugas Marketplace Tradisional</div>
                    <div class="text-muted mb-2">Implementasi PHP Native + MySQLi + Bootstrap 5</div>
                    <div class="d-flex align-items-center gap-1 text-success fw-medium">
                        <i class="bi bi-circle-fill" style="font-size: 8px;"></i> Status: Ready for InfinityFree
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4 text-muted opacity-25">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-muted">
            <div>&copy; <?php echo date('Y'); ?> PasarKu Tradisional. Seluruh hak cipta dilindungi.</div>
            <div class="mt-2 mt-md-0">
                <span>Dibuat dengan <i class="bi bi-heart-fill text-danger"></i> untuk UMKM Indonesia</span>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo base_url('assets/js/main.js'); ?>"></script>
</body>
</html>

