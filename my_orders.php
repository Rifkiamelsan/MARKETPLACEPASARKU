<?php
$page_title = "Riwayat Pesanan Saya";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$user = current_user();
$orders = [];

// Jika user login, ambil pesanan berdasarkan user_id
if (is_logged_in()) {
    $stmt = mysqli_prepare($conn, "SELECT o.*, 
                                   (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) AS total_items 
                                   FROM orders o 
                                   WHERE o.buyer_id = ? 
                                   ORDER BY o.created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $user['id']);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $orders[] = $row;
    }
} else {
    // Mode pencarian manual nomor order untuk tamu
    $search_code = sanitize($_GET['track_code'] ?? '');
    if (!empty($search_code)) {
        $stmt = mysqli_prepare($conn, "SELECT o.*, 
                                       (SELECT COUNT(*) FROM order_details WHERE order_id = o.id) AS total_items 
                                       FROM orders o 
                                       WHERE o.order_code = ? 
                                       ORDER BY o.created_at DESC");
        mysqli_stmt_bind_param($stmt, "s", $search_code);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($res)) {
            $orders[] = $row;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-bag-check text-success me-2"></i>Riwayat & Status Pesanan
        </h2>
        <p class="text-secondary small mb-0">Pantau perkembangan status belanja produk pasar tradisional Anda.</p>
    </div>

    <?php if (!is_logged_in()): ?>
        <!-- Form Lacak Pesanan untuk Tamu -->
        <form method="GET" action="<?php echo base_url('my_orders.php'); ?>" class="d-flex gap-2">
            <input type="text" name="track_code" class="form-control form-control-sm rounded-pill" 
                   placeholder="Masukkan Kode Pesanan (TRAD-...)" 
                   value="<?php echo htmlspecialchars($_GET['track_code'] ?? ''); ?>" required>
            <button type="submit" class="btn btn-sm btn-success rounded-pill px-3">
                <i class="bi bi-search"></i> Lacak
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if (!is_logged_in() && empty($_GET['track_code'])): ?>
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-light">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="fw-bold text-dark mb-1">Belum Masuk ke Akun?</h5>
                <p class="small text-muted mb-md-0">
                    Masuk untuk melihat seluruh riwayat belanjaan Anda secara otomatis, atau gunakan kolom pelacakan kode di atas untuk pesanan sebagai tamu.
                </p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?php echo base_url('auth/login.php?redirect=' . urlencode(base_url('my_orders.php'))); ?>" class="btn btn-success rounded-pill px-4">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login Sekarang
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="card border-0 shadow-sm rounded-4 text-center p-5 my-4">
        <div class="display-3 text-muted mb-3">📦</div>
        <h4 class="fw-bold text-dark">Belum Ada Riwayat Pesanan</h4>
        <p class="text-secondary mx-auto mb-4" style="max-width: 480px;">
            Anda belum pernah membuat transaksi pesanan atau kode pesanan yang dicari tidak ditemukan.
        </p>
        <div>
            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                <i class="bi bi-cart3 me-1"></i> Mulai Belanja Sekarang
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold">Kode Pesanan</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Tanggal</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Penerima</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold text-center">Jumlah Item</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold text-end">Total Bayar</th>
                        <th class="py-3 text-secondary text-uppercase small fw-bold text-center">Status</th>
                        <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <span class="fw-bold font-monospace text-dark"><?php echo htmlspecialchars($ord['order_code']); ?></span>
                                <div class="small text-muted"><?php echo htmlspecialchars($ord['payment_method']); ?></div>
                            </td>
                            <td class="py-3 small text-muted">
                                <?php echo date('d M Y, H:i', strtotime($ord['created_at'])); ?>
                            </td>
                            <td class="py-3">
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($ord['recipient_name']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($ord['recipient_phone']); ?></div>
                            </td>
                            <td class="py-3 text-center fw-semibold">
                                <?php echo $ord['total_items']; ?> item
                            </td>
                            <td class="py-3 text-end fw-bold text-success">
                                <?php echo format_rupiah($ord['total_amount']); ?>
                            </td>
                            <td class="py-3 text-center">
                                <span class="badge badge-status-<?php echo $ord['status']; ?> px-3 py-2 rounded-pill text-uppercase small fw-bold">
                                    <?php echo ucfirst($ord['status']); ?>
                                </span>
                            </td>
                            <td class="pe-4 py-3 text-center">
                                <a href="<?php echo base_url('order_success.php?code=' . urlencode($ord['order_code'])); ?>" 
                                   class="btn btn-outline-success btn-sm rounded-pill px-3 fw-medium">
                                    <i class="bi bi-receipt me-1"></i> Lihat Nota
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

