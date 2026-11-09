<?php
$page_title = "Daftar Pesanan Masuk (Penjual)";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_seller();
$user = current_user();

// Update status pesanan jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = sanitize($_POST['status'] ?? 'pending');

    $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
    if (in_array($new_status, $allowed_statuses) && $order_id > 0) {
        $stmt_status = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_status, "si", $new_status, $order_id);
        if (mysqli_stmt_execute($stmt_status)) {
            set_flash('success', 'Status pesanan berhasil diperbarui menjadi: ' . ucfirst($new_status));
        } else {
            set_flash('danger', 'Gagal memperbarui status pesanan.');
        }
    }
    header('Location: ' . base_url('admin/orders.php'));
    exit;
}

// Query pesanan
if ($user['role'] === 'admin') {
    $sql = "SELECT DISTINCT o.*, u.name AS buyer_username,
            (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) AS total_items
            FROM orders o
            JOIN users u ON o.buyer_id = u.id
            ORDER BY o.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
} else {
    // Seller hanya melihat order yang berisi barang miliknya
    $sql = "SELECT DISTINCT o.*, u.name AS buyer_username,
            (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) AS total_items
            FROM orders o
            JOIN users u ON o.buyer_id = u.id
            JOIN order_details od ON o.id = od.order_id
            JOIN products p ON od.product_id = p.id
            WHERE p.seller_id = ?
            ORDER BY o.created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user['id']);
}

mysqli_stmt_execute($stmt);
$orders_res = mysqli_stmt_get_result($stmt);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-receipt text-success me-2"></i>Daftar Pesanan Masuk
        </h2>
        <p class="text-secondary small mb-0">Kelola dan perbarui status pesanan dari pembeli pasar tradisional.</p>
    </div>
    <a href="<?php echo base_url('admin/products.php'); ?>" class="btn btn-outline-secondary rounded-pill px-3">
        <i class="bi bi-box-seam me-1"></i> Kembali ke Kelola Produk
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Kode & Tanggal</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold">Penerima & Alamat</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold text-center">Item</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold text-end">Total Bayar</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold text-center" style="min-width: 170px;">Status Pesanan</th>
                    <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold text-center" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($orders_res) > 0): ?>
                    <?php while ($ord = mysqli_fetch_assoc($orders_res)): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="fw-bold font-monospace text-dark"><?php echo htmlspecialchars($ord['order_code']); ?></span>
                                <div class="small text-muted"><?php echo date('d M Y, H:i', strtotime($ord['created_at'])); ?> WIB</div>
                                <span class="badge bg-light text-dark border small mt-1"><?php echo htmlspecialchars($ord['payment_method']); ?></span>
                            </td>
                            <td class="py-3">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($ord['recipient_name']); ?></div>
                                <div class="small text-muted"><i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($ord['recipient_phone']); ?></div>
                                <div class="small text-secondary text-truncate" style="max-width: 250px;">
                                    <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($ord['shipping_address']); ?>
                                </div>
                            </td>
                            <td class="py-3 text-center fw-semibold">
                                <?php echo $ord['total_items']; ?> barang
                            </td>
                            <td class="py-3 text-end fw-bold text-success">
                                <?php echo format_rupiah($ord['total_amount']); ?>
                            </td>
                            <td class="py-3 text-center">
                                <form action="<?php echo base_url('admin/orders.php'); ?>" method="POST" class="d-inline-block">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?php echo $ord['id']; ?>">
                                    <select name="status" class="form-select form-select-sm rounded-pill fw-semibold badge-status-<?php echo $ord['status']; ?>" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $ord['status'] === 'pending' ? 'selected' : ''; ?>>Pending (Menunggu)</option>
                                        <option value="processing" <?php echo $ord['status'] === 'processing' ? 'selected' : ''; ?>>Processing (Diproses)</option>
                                        <option value="completed" <?php echo $ord['status'] === 'completed' ? 'selected' : ''; ?>>Completed (Selesai)</option>
                                        <option value="cancelled" <?php echo $ord['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled (Batal)</option>
                                    </select>
                                </form>
                            </td>
                            <td class="pe-4 py-3 text-center">
                                <a href="<?php echo base_url('order_success.php?code=' . urlencode($ord['order_code'])); ?>" 
                                   target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-receipt me-1"></i> Nota
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="display-6 text-muted mb-2">📋</div>
                            <p class="text-secondary mb-0">Belum ada pesanan masuk untuk produk Anda.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
