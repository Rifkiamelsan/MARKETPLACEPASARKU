<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    set_flash('danger', 'ID Produk tidak valid.');
    header('Location: ' . base_url('index.php'));
    exit;
}

// Ambil data produk
$stmt = mysqli_prepare($conn, "SELECT p.*, c.name AS category_name, c.slug AS category_slug, 
                               u.name AS seller_name, u.phone AS seller_phone, u.address AS seller_address 
                               FROM products p
                               JOIN categories c ON p.category_id = c.id
                               JOIN users u ON p.seller_id = u.id
                               WHERE p.id = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);

if (!$product) {
    set_flash('danger', 'Produk yang Anda cari tidak ditemukan atau telah dihapus.');
    header('Location: ' . base_url('index.php'));
    exit;
}

$page_title = $product['name'];
require_once __DIR__ . '/includes/header.php';

// Ambil Produk Serupa dalam Kategori yang sama
$stmt_related = mysqli_prepare($conn, "SELECT p.*, c.name AS category_name, u.name AS seller_name 
                                       FROM products p 
                                       JOIN categories c ON p.category_id = c.id
                                       JOIN users u ON p.seller_id = u.id
                                       WHERE p.category_id = ? AND p.id != ? 
                                       LIMIT 4");
mysqli_stmt_bind_param($stmt_related, "ii", $product['category_id'], $product['id']);
mysqli_stmt_execute($stmt_related);
$related_result = mysqli_stmt_get_result($stmt_related);
?>

<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?php echo base_url('index.php'); ?>" class="text-decoration-none text-muted">Beranda</a></li>
        <li class="breadcrumb-item">
            <a href="<?php echo base_url('index.php?category=' . $product['category_slug']); ?>" class="text-decoration-none text-muted">
                <?php echo htmlspecialchars($product['category_name']); ?>
            </a>
        </li>
        <li class="breadcrumb-item active text-dark fw-medium" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
    </ol>
</nav>

<!-- Main Detail Card -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="card-body p-4 p-lg-5">
        <div class="row g-4 g-lg-5">
            <!-- Kolom Gambar -->
            <div class="col-lg-5">
                <div class="position-relative rounded-4 overflow-hidden border bg-light shadow-sm" style="max-height: 440px;">
                    <img src="<?php echo get_product_image_url($product['image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>" 
                         class="img-fluid w-100 object-fit-cover"
                         style="min-height: 340px; max-height: 440px;"
                         onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                    
                    <span class="badge bg-success position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm">
                        <?php echo htmlspecialchars($product['category_name']); ?>
                    </span>
                </div>

                <!-- Info Kios / Penjual Box -->
                <div class="card bg-body-tertiary border-0 rounded-4 mt-4 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.3rem;">
                            <i class="bi bi-shop"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($product['seller_name']); ?></div>
                            <div class="small text-muted text-truncate" style="max-width: 250px;">
                                <i class="bi bi-geo-alt me-1"></i><?php echo htmlspecialchars($product['seller_address'] ?: 'Pasar Tradisional'); ?>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                            <i class="bi bi-patch-check-fill me-1"></i> Terverifikasi
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kolom Informasi Produk & Form Pemesanan -->
            <div class="col-lg-7">
                <h1 class="h2 fw-bold text-dark mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <span class="text-warning">
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-fill"></i>
                        <i class="bi bi-star-half"></i>
                        <strong class="text-dark ms-1">4.9</strong>
                    </span>
                    <span class="text-muted">•</span>
                    <span class="text-muted small">Kategori: <strong><?php echo htmlspecialchars($product['category_name']); ?></strong></span>
                    <span class="text-muted">•</span>
                    <span class="text-muted small">Satuan: <strong><?php echo htmlspecialchars($product['unit']); ?></strong></span>
                </div>

                <!-- Harga -->
                <div class="bg-light p-3 rounded-4 mb-4 d-flex align-items-baseline gap-2">
                    <span class="display-6 fw-bold text-success"><?php echo format_rupiah($product['price']); ?></span>
                    <span class="text-secondary fw-medium">/ <?php echo htmlspecialchars($product['unit']); ?></span>
                </div>

                <!-- Deskripsi Produk -->
                <div class="mb-4">
                    <h5 class="fw-bold text-dark mb-2">Deskripsi Produk</h5>
                    <div class="text-secondary lh-lg" style="white-space: pre-line;">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                </div>

                <!-- Ketersediaan Stok -->
                <div class="mb-4 d-flex align-items-center gap-2">
                    <span class="text-muted small fw-medium">Ketersediaan Stok:</span>
                    <?php if ($product['stock'] > 10): ?>
                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-check-circle me-1"></i> Tersedia (<?php echo $product['stock'] . ' ' . htmlspecialchars($product['unit']); ?>)
                        </span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-exclamation-circle me-1"></i> Stok Terbatas (Sisa <?php echo $product['stock'] . ' ' . htmlspecialchars($product['unit']); ?>)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-x-circle me-1"></i> Stok Habis
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Form Tambah ke Keranjang -->
                <?php if ($product['stock'] > 0): ?>
                    <form action="<?php echo base_url('cart.php'); ?>" method="POST" class="p-3 bg-light rounded-4 border">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                        <div class="row align-items-center g-3">
                            <div class="col-sm-4">
                                <label class="form-label small fw-bold text-dark mb-1">Jumlah Pesanan:</label>
                                <div class="input-group">
                                    <button class="btn btn-outline-secondary btn-qty-minus" type="button">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" name="qty" class="form-control text-center input-qty fw-bold" 
                                           value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button class="btn btn-outline-secondary btn-qty-plus" type="button">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-sm-8 d-flex gap-2 align-items-end">
                                <button type="submit" class="btn btn-success rounded-pill px-4 py-2 w-100 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-cart-plus fs-5"></i>
                                    <span>Masukkan Keranjang</span>
                                </button>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="alert alert-secondary rounded-4 text-center">
                        <i class="bi bi-info-circle me-1"></i> Maaf, produk ini saat ini sedang habis. Silakan periksa kembali nanti atau hubungi penjual.
                    </div>
                <?php endif; ?>

                <!-- Nilai Tambah Belanja Pasar -->
                <div class="row g-3 mt-3 pt-3 border-top text-muted small">
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <span>Produk Segar & Asli</span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-success fs-5"></i>
                        <span>Pengiriman Cepat</span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-heart text-success fs-5"></i>
                        <span>Dukung Petani Lokal</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Produk Serupa -->
<?php if (mysqli_num_rows($related_result) > 0): ?>
    <div class="mb-5">
        <h4 class="fw-bold text-dark mb-4">
            <i class="bi bi-bag-heart text-success me-2"></i>Produk Serupa Lainnya
        </h4>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
            <?php while ($rel = mysqli_fetch_assoc($related_result)): ?>
                <div class="col">
                    <div class="product-card shadow-sm">
                        <div class="product-card-img-wrapper">
                            <img src="<?php echo get_product_image_url($rel['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($rel['name']); ?>"
                                 onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                            <span class="product-category-tag"><?php echo htmlspecialchars($rel['category_name']); ?></span>
                        </div>
                        <div class="product-card-body">
                            <h3 class="product-title">
                                <a href="<?php echo base_url('product_detail.php?id=' . $rel['id']); ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($rel['name']); ?>
                                </a>
                            </h3>
                            <div class="mt-auto">
                                <div class="product-price mb-2"><?php echo format_rupiah($rel['price']); ?> <span class="product-unit">/ <?php echo htmlspecialchars($rel['unit']); ?></span></div>
                                <a href="<?php echo base_url('product_detail.php?id=' . $rel['id']); ?>" class="btn btn-outline-success btn-sm rounded-pill w-100">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
