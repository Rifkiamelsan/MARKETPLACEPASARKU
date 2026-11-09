<?php
$page_title = "Checkout & Pembayaran";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$cart = get_cart();
if (empty($cart)) {
    set_flash('warning', 'Keranjang belanja Anda masih kosong. Silakan pilih produk terlebih dahulu.');
    header('Location: ' . base_url('index.php'));
    exit;
}

$total_amount = get_cart_total();
$total_items = get_cart_count();
$user = current_user();

require_once __DIR__ . '/includes/header.php';
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">
        <i class="bi bi-credit-card-2-front text-success me-2"></i>Checkout Pesanan
    </h2>
    <p class="text-secondary small mb-0">Lengkapi alamat pengiriman dan pilih metode pembayaran untuk menyelesaikan pesanan pasar.</p>
</div>

<form action="<?php echo base_url('process_order.php'); ?>" method="POST" id="checkoutForm">
    <div class="row g-4">
        <!-- Kolom Form Data Pengiriman -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-success"></i>
                        <span>Informasi Penerima & Pengiriman</span>
                    </h5>

                    <?php if (!is_logged_in()): ?>
                        <div class="alert alert-info rounded-3 small mb-4 d-flex align-items-center justify-content-between">
                            <div>
                                <i class="bi bi-person-circle me-1"></i> Sudah punya akun? <strong>Login</strong> untuk memuat data profil otomatis.
                            </div>
                            <a href="<?php echo base_url('auth/login.php?redirect=' . urlencode(base_url('checkout.php'))); ?>" class="btn btn-sm btn-primary rounded-pill px-3">
                                Login Sekarang
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nama Lengkap Penerima <span class="text-danger">*</span></label>
                            <input type="text" name="recipient_name" class="form-control rounded-3" 
                                   placeholder="Contoh: Budi Santoso" 
                                   value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" name="recipient_phone" class="form-control rounded-3" 
                                   placeholder="Contoh: 081234567890" 
                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                            <div class="form-text small">Kurir pasar akan mengonfirmasi pengiriman via nomor ini.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Alamat Lengkap Pengiriman <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" class="form-control rounded-3" rows="3" 
                                      placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, kecamatan, patokan jalan..." required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Catatan untuk Pedagang / Kurir (Opsional)</label>
                            <textarea name="notes" class="form-control rounded-3" rows="2" 
                                      placeholder="Contoh: Titipkan ke satpam, tolong pilih cabai yang merah segar, dsb."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metode Pembayaran Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="bi bi-wallet2 text-success"></i>
                        <span>Pilihan Metode Pembayaran</span>
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 position-relative">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="COD (Bayar di Tempat)" checked>
                                    <label class="form-check-label fw-bold text-dark" for="pay_cod">
                                        <i class="bi bi-cash-stack text-success me-1"></i> Bayar di Tempat (COD)
                                    </label>
                                </div>
                                <p class="small text-muted mb-0 mt-2 ms-4">
                                    Bayar tunai ke kurir saat barang belanjaan pasar sampai di alamat Anda.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 position-relative">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="pay_transfer" value="Transfer Bank (BCA / Mandiri / BRI)">
                                    <label class="form-check-label fw-bold text-dark" for="pay_transfer">
                                        <i class="bi bi-bank text-primary me-1"></i> Transfer Bank
                                    </label>
                                </div>
                                <p class="small text-muted mb-0 mt-2 ms-4">
                                    Transfer melalui rekening Virtual Account atau rekening resmi pedagang.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Ringkasan Rincian Belanja -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">
                        Rincian Pesanan (<?php echo $total_items; ?> Barang)
                    </h5>

                    <div class="list-group list-group-flush mb-3 overflow-y-auto" style="max-height: 280px;">
                        <?php foreach ($cart as $id => $item): 
                            $subtotal = $item['price'] * $item['qty'];
                        ?>
                            <div class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2" style="max-width: 65%;">
                                    <img src="<?php echo get_product_image_url($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="rounded-2 border" 
                                         style="width: 44px; height: 44px; object-fit: cover;"
                                         onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                                    <div>
                                        <div class="fw-semibold text-dark text-truncate small" style="max-width: 170px;">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo $item['qty']; ?> x <?php echo format_rupiah($item['price']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end fw-bold text-dark small">
                                    <?php echo format_rupiah($subtotal); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between text-secondary mb-2 small">
                            <span>Subtotal Belanja</span>
                            <span class="fw-semibold text-dark"><?php echo format_rupiah($total_amount); ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-secondary mb-3 small">
                            <span>Biaya Pengiriman</span>
                            <span class="text-success fw-semibold">Gratis Ongkir Lokal</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-baseline mb-4">
                            <span class="fw-bold text-dark fs-6">Total Pembayaran</span>
                            <span class="display-6 fw-bold text-success fs-3"><?php echo format_rupiah($total_amount); ?></span>
                        </div>

                        <div class="d-grid gap-2 mb-3">
                            <button type="submit" class="btn btn-success btn-lg rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Konfirmasi & Buat Pesanan</span>
                            </button>
                            <a href="<?php echo base_url('cart.php'); ?>" class="btn btn-link text-muted small text-decoration-none text-center">
                                <i class="bi bi-arrow-left me-1"></i> Kembali ke Keranjang Belanja
                            </a>
                        </div>

                        <div class="bg-success-subtle p-3 rounded-3 small text-success-emphasis text-center">
                            <i class="bi bi-shield-lock-fill me-1"></i> Transaksi Anda diproses aman dan langsung masuk ke database pesanan penjual.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
