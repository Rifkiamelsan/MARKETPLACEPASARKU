<?php
$page_title = "Bukti Pemesanan Pasar";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$order_code = sanitize($_GET['code'] ?? '');

if (empty($order_code)) {
    set_flash('danger', 'Kode pesanan tidak ditemukan.');
    header('Location: ' . base_url('index.php'));
    exit;
}

// Ambil data order
$stmt = mysqli_prepare($conn, "SELECT o.*, u.name AS buyer_username, u.email AS buyer_email 
                               FROM orders o 
                               JOIN users u ON o.buyer_id = u.id 
                               WHERE o.order_code = ?");
mysqli_stmt_bind_param($stmt, "s", $order_code);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($res);

if (!$order) {
    set_flash('danger', 'Pesanan dengan kode "' . htmlspecialchars($order_code) . '" tidak ditemukan.');
    header('Location: ' . base_url('index.php'));
    exit;
}

// Ambil detail item pesanan
$stmt_items = mysqli_prepare($conn, "SELECT od.*, p.name AS product_name, p.unit, p.image, 
                                     c.name AS category_name, s.name AS seller_name 
                                     FROM order_details od
                                     JOIN products p ON od.product_id = p.id
                                     JOIN categories c ON p.category_id = c.id
                                     JOIN users s ON p.seller_id = s.id
                                     WHERE od.order_id = ?");
mysqli_stmt_bind_param($stmt_items, "i", $order['id']);
mysqli_stmt_execute($stmt_items);
$items_res = mysqli_stmt_get_result($stmt_items);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Banner Sukses -->
<div class="card border-0 shadow-sm rounded-4 text-center p-4 mb-4 bg-success text-white no-print" style="background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;">
    <div class="display-4 mb-2">🎉</div>
    <h3 class="fw-bold mb-1">Terima Kasih, Pesanan Anda Berhasil Dibuat!</h3>
    <p class="lead small opacity-90 mb-0">
        Kode Transaksi Anda: <strong class="badge bg-warning text-dark fs-6 px-3 py-2 rounded-pill"><?php echo htmlspecialchars($order['order_code']); ?></strong>
    </p>
</div>

<!-- Lembar Nota / Invoice -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="invoiceCard">
    <div class="card-body p-4 p-md-5">
        <!-- Header Nota -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start pb-4 border-bottom gap-3">
            <div>
                <div class="brand-logo mb-1">
                    <span class="fs-2">🧺</span>
                    <span class="fs-3 fw-bold">Pasar<span class="text-warning">Ku</span></span>
                    <span class="brand-badge">Tradisional</span>
                </div>
                <p class="text-muted small mb-0">Platform Marketplace Hasil Bumi & UMKM Lokal Indonesia</p>
                <div class="small text-muted">Tanggal: <?php echo date('d F Y, H:i', strtotime($order['created_at'])); ?> WIB</div>
            </div>

            <div class="text-md-end">
                <span class="badge bg-light text-dark border px-3 py-2 fs-6 rounded-pill mb-2 d-inline-block">
                    INVOICE: <strong><?php echo htmlspecialchars($order['order_code']); ?></strong>
                </span>
                <div>
                    <span class="badge badge-status-<?php echo $order['status']; ?> px-3 py-2 rounded-pill text-uppercase fw-bold">
                        <i class="bi bi-clock-history me-1"></i> Status: <?php echo ucfirst($order['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Info Penerima & Pembayaran -->
        <div class="row g-4 my-3">
            <div class="col-md-6">
                <h6 class="fw-bold text-dark text-uppercase small mb-2"><i class="bi bi-person-badge text-success me-1"></i> Data Penerima:</h6>
                <div class="fw-bold text-dark fs-5"><?php echo htmlspecialchars($order['recipient_name']); ?></div>
                <div class="text-secondary small mb-1"><i class="bi bi-telephone me-1"></i> <?php echo htmlspecialchars($order['recipient_phone']); ?></div>
                <div class="text-secondary small"><i class="bi bi-geo-alt me-1"></i> <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></div>
            </div>

            <div class="col-md-6">
                <h6 class="fw-bold text-dark text-uppercase small mb-2"><i class="bi bi-credit-card text-success me-1"></i> Rincian Pembayaran:</h6>
                <div class="small text-secondary mb-1">Metode: <strong class="text-dark"><?php echo htmlspecialchars($order['payment_method']); ?></strong></div>
                <?php if (!empty($order['notes'])): ?>
                    <div class="small text-secondary mt-2 p-2 bg-light rounded-3">
                        <strong>Catatan Pembeli:</strong> "<?php echo htmlspecialchars($order['notes']); ?>"
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabel Barang -->
        <h6 class="fw-bold text-dark text-uppercase small mb-3"><i class="bi bi-bag-check text-success me-1"></i> Daftar Barang yang Dipesan:</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th>Produk Pasar</th>
                        <th class="text-center">Penjual</th>
                        <th class="text-end">Harga Satuan</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($item = mysqli_fetch_assoc($items_res)): 
                    ?>
                        <tr>
                            <td class="text-center"><?php echo $no++; ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?php echo get_product_image_url($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                         class="rounded border d-none d-sm-inline" 
                                         style="width: 36px; height: 36px; object-fit: cover;"
                                         onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($item['category_name']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center small text-muted">
                                <i class="bi bi-shop me-1 text-success"></i><?php echo htmlspecialchars($item['seller_name']); ?>
                            </td>
                            <td class="text-end"><?php echo format_rupiah($item['unit_price']); ?> / <?php echo htmlspecialchars($item['unit']); ?></td>
                            <td class="text-center fw-bold"><?php echo $item['quantity']; ?></td>
                            <td class="text-end fw-bold text-dark"><?php echo format_rupiah($item['subtotal']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Biaya Pengiriman:</td>
                        <td class="text-end text-success fw-bold">GRATIS</td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="5" class="text-end fs-5 fw-bold text-dark">Total Pembayaran:</td>
                        <td class="text-end fs-5 fw-bold text-success"><?php echo format_rupiah($order['total_amount']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Instruksi Pembayaran -->
        <div class="p-3 bg-light rounded-4 border mb-4">
            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-primary me-1"></i> Informasi Pembayaran & Pengiriman:</h6>
            <p class="small text-secondary mb-0">
                Pedagang pasar tradisional kami akan segera menyiapkan pesanan segar Anda. Jika Anda memilih metode <strong>Bayar di Tempat (COD)</strong>, siapkan uang pas saat kurir tiba. Jika memilih <strong>Transfer Bank</strong>, silakan lakukan transfer sejumlah nominal di atas ke rekening resmi PasarKu (BCA: <code>8890-1234-5678</code> a/n Pasar Tradisional Nusantara).
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center no-print">
            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Kembali Belanja
            </a>
            
            <div class="d-flex gap-2">
                <a href="<?php echo base_url('my_orders.php'); ?>" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="bi bi-bag-check me-1"></i> Riwayat Pesanan
                </a>
                <button type="button" onclick="window.print();" class="btn btn-success rounded-pill px-4 fw-bold">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Bukti Nota (Print)
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

