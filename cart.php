<?php
$page_title = "Keranjang Belanja";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Menangani Aksi Keranjang
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 1);

        $result = add_to_cart($product_id, $qty, $conn);
        if ($result['status']) {
            set_flash('success', $result['message']);
        } else {
            set_flash('danger', $result['message']);
        }

        // Redirect kembali ke keranjang atau halaman sebelumnya
        $redirect = $_POST['redirect'] ?? base_url('cart.php');
        header('Location: ' . $redirect);
        exit;
    }

    if ($action === 'update') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['qty'] ?? 1);

        $result = update_cart_qty($product_id, $qty, $conn);
        if ($result['status']) {
            set_flash('success', $result['message']);
        } else {
            set_flash('warning', $result['message']);
        }
        header('Location: ' . base_url('cart.php'));
        exit;
    }
}

// Aksi GET (Remove / Clear)
if ($action === 'remove') {
    $product_id = (int)($_GET['id'] ?? 0);
    remove_from_cart($product_id);
    set_flash('success', 'Item berhasil dihapus dari keranjang belanja.');
    header('Location: ' . base_url('cart.php'));
    exit;
}

if ($action === 'clear') {
    clear_cart();
    set_flash('info', 'Seluruh isi keranjang belanja telah dikosongkan.');
    header('Location: ' . base_url('cart.php'));
    exit;
}

$cart = get_cart();
$total_amount = get_cart_total();
$total_items = get_cart_count();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h2 class="fw-bold text-dark mb-1">
            <i class="bi bi-cart3 text-success me-2"></i>Keranjang Belanja
        </h2>
        <p class="text-secondary small mb-0">Periksa daftar pesanan bahan pangan dan produk tradisional Anda.</p>
    </div>
    <?php if (!empty($cart)): ?>
        <a href="<?php echo base_url('cart.php?action=clear'); ?>" 
           class="btn btn-outline-danger btn-sm rounded-pill px-3"
           data-confirm="Apakah Anda yakin ingin mengosongkan seluruh isi keranjang belanja?">
            <i class="bi bi-trash me-1"></i> Kosongkan Keranjang
        </a>
    <?php endif; ?>
</div>

<?php if (empty($cart)): ?>
    <!-- Tampilan Keranjang Kosong -->
    <div class="card border-0 shadow-sm rounded-4 text-center p-5 my-4">
        <div class="display-1 text-muted mb-3">🛒</div>
        <h4 class="fw-bold text-dark">Keranjang Belanja Anda Masih Kosong</h4>
        <p class="text-secondary mx-auto mb-4" style="max-width: 480px;">
            Yuk isi keranjang Anda dengan sayuran segar, bumbu dapur, bahan pokok, atau cemilan lezat langsung dari pasar tradisional!
        </p>
        <div>
            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-success rounded-pill px-4 py-2 fw-semibold">
                <i class="bi bi-bag-plus me-1"></i> Jelajahi Produk Sekarang
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- Tampilan Ada Item Keranjang -->
    <div class="row g-4">
        <!-- Kolom Tabel Item -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold" style="min-width: 250px;">Produk</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold text-center" style="min-width: 120px;">Harga Satuan</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold text-center" style="min-width: 140px;">Jumlah</th>
                                <th class="py-3 text-secondary text-uppercase small fw-bold text-end" style="min-width: 120px;">Subtotal</th>
                                <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold text-center" style="width: 60px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart as $id => $item): 
                                $subtotal = $item['price'] * $item['qty'];
                            ?>
                                <tr>
                                    <!-- Kolom Produk -->
                                    <td class="ps-4 py-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?php echo get_product_image_url($item['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="rounded-3 border" 
                                                 style="width: 64px; height: 64px; object-fit: cover;"
                                                 onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                                            <div>
                                                <a href="<?php echo base_url('product_detail.php?id=' . $id); ?>" class="fw-bold text-dark text-decoration-none d-block mb-1">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </a>
                                                <div class="small text-muted">
                                                    <i class="bi bi-shop me-1 text-success"></i><?php echo htmlspecialchars($item['seller_name']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom Harga -->
                                    <td class="py-3 text-center">
                                        <span class="fw-semibold text-dark"><?php echo format_rupiah($item['price']); ?></span>
                                        <span class="d-block small text-muted">/ <?php echo htmlspecialchars($item['unit']); ?></span>
                                    </td>

                                    <!-- Kolom Kuantitas Spinner Form -->
                                    <td class="py-3">
                                        <form action="<?php echo base_url('cart.php'); ?>" method="POST" class="d-flex justify-content-center">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                            <div class="input-group input-group-sm" style="max-width: 120px;">
                                                <button class="btn btn-outline-secondary btn-qty-minus" type="button" data-auto-submit="true">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" name="qty" class="form-control text-center input-qty fw-bold" 
                                                       value="<?php echo $item['qty']; ?>" min="1" max="<?php echo $item['stock']; ?>" 
                                                       onchange="this.form.submit()">
                                                <button class="btn btn-outline-secondary btn-qty-plus" type="button" data-auto-submit="true">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </td>

                                    <!-- Kolom Subtotal -->
                                    <td class="py-3 text-end fw-bold text-success">
                                        <?php echo format_rupiah($subtotal); ?>
                                    </td>

                                    <!-- Kolom Hapus -->
                                    <td class="pe-4 py-3 text-center">
                                        <a href="<?php echo base_url('cart.php?action=remove&id=' . $id); ?>" 
                                           class="btn btn-outline-danger btn-sm rounded-circle" 
                                           title="Hapus Item"
                                           data-confirm="Hapus '<?php echo htmlspecialchars($item['name']); ?>' dari keranjang?">
                                            <i class="bi bi-trash3"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                    <a href="<?php echo base_url('index.php'); ?>" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                        <i class="bi bi-arrow-left me-1"></i> Lanjut Belanja
                    </a>
                    <span class="text-muted small">Total jenis produk: <strong><?php echo count($cart); ?></strong></span>
                </div>
            </div>
        </div>

        <!-- Kolom Ringkasan Belanja (Checkout Box) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 90px;">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom">Ringkasan Pesanan</h5>

                    <div class="d-flex justify-content-between text-secondary mb-2 small">
                        <span>Total Kuantitas Barang</span>
                        <span class="fw-semibold text-dark"><?php echo $total_items; ?> item</span>
                    </div>

                    <div class="d-flex justify-content-between text-secondary mb-3 small">
                        <span>Estimasi Ongkos Kirim</span>
                        <span class="badge bg-success-subtle text-success">Dihitung saat Checkout</span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-baseline mb-4">
                        <span class="fw-bold text-dark fs-6">Total Harga</span>
                        <span class="display-6 fw-bold text-success fs-3"><?php echo format_rupiah($total_amount); ?></span>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <a href="<?php echo base_url('checkout.php'); ?>" class="btn btn-success btn-lg rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                            <span>Lanjut ke Checkout</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>

                    <div class="bg-light p-3 rounded-3 small text-muted">
                        <div class="d-flex align-items-center gap-2 mb-1 text-dark fw-semibold">
                            <i class="bi bi-shield-check text-success"></i> Garansi Belanja Aman
                        </div>
                        Pesanan langsung diteruskan ke pedagang pasar tradisional. Pembayaran dapat melalui Transfer Bank atau Bayar di Tempat (COD).
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
