<?php
$page_title = "Katalog Produk Pasar Tradisional";
require_once __DIR__ . '/includes/header.php';

// Filter Parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_slug = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'newest';

// Bangun Query Pencarian & Filter
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.name AS seller_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN users u ON p.seller_id = u.id
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($category_slug)) {
    $sql .= " AND c.slug = ?";
    $params[] = $category_slug;
    $types .= "s";
}

// Sorting
switch ($sort) {
    case 'price_asc':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $sql .= " ORDER BY p.name ASC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$products_result = mysqli_stmt_get_result($stmt);
$total_products = mysqli_num_rows($products_result);

// Ambil semua kategori untuk bar filter
$cats_q = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");
?>

<!-- Hero Banner -->
<div class="hero-banner p-4 p-md-5 mb-5 shadow-sm">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill mb-3">
                <i class="bi bi-patch-check-fill me-1"></i> Langsung dari Pedagang & Petani Lokal
            </span>
            <h1 class="display-6 fw-bold mb-3">Pasar Tradisional Nusantara dalam Genggaman Anda</h1>
            <p class="lead opacity-90 mb-4" style="font-size: 1.05rem;">
                Dapatkan bahan pokok, sayur mayur petik segar, bumbu rempah pilihan, hingga kerajinan khas daerah dengan harga jujur dan kualitas terjamin.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="#katalog" class="btn btn-warning rounded-pill px-4 py-2 fw-bold text-dark">
                    <i class="bi bi-bag-plus me-1"></i> Mulai Belanja
                </a>
                <?php if (is_seller()): ?>
                    <a href="<?php echo base_url('admin/product_add.php'); ?>" class="btn btn-outline-light rounded-pill px-4 py-2 fw-medium">
                        <i class="bi bi-plus-circle me-1"></i> Jual Produk Baru
                    </a>
                <?php else: ?>
                    <a href="<?php echo base_url('auth/login.php'); ?>" class="btn btn-outline-light rounded-pill px-4 py-2 fw-medium">
                        <i class="bi bi-shop me-1"></i> Buka Kios / Login Penjual
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4 d-none d-lg-block text-center">
            <div class="display-1">🧺</div>
            <div class="fw-bold fs-4 mt-2">Segar • Murah • Berkah</div>
        </div>
    </div>
</div>

<!-- Kategori Pills Bar -->
<div class="mb-4" id="katalog">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="fw-bold text-dark mb-0">
            <i class="bi bi-tags text-success me-2"></i>Kategori Pasar
        </h4>
        <?php if (!empty($category_slug) || !empty($search)): ?>
            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
            </a>
        <?php endif; ?>
    </div>

    <div class="d-flex gap-2 overflow-x-auto pb-2" style="scrollbar-width: thin;">
        <a href="<?php echo base_url('index.php' . (!empty($search) ? '?search=' . urlencode($search) : '')); ?>" 
           class="category-pill <?php echo empty($category_slug) ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill"></i>
            <span>Semua Produk</span>
        </a>
        <?php while ($cat = mysqli_fetch_assoc($cats_q)): ?>
            <a href="<?php echo base_url('index.php?category=' . $cat['slug'] . (!empty($search) ? '&search=' . urlencode($search) : '')); ?>" 
               class="category-pill <?php echo $category_slug === $cat['slug'] ? 'active' : ''; ?>">
                <i class="bi <?php echo htmlspecialchars($cat['icon']); ?>"></i>
                <span><?php echo htmlspecialchars($cat['name']); ?></span>
            </a>
        <?php endwhile; ?>
    </div>
</div>

<!-- Bar Informasi & Sorting -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body py-3 px-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="text-secondary small">
            Menampilkan <strong class="text-dark"><?php echo $total_products; ?></strong> produk 
            <?php if (!empty($category_slug)): ?>
                dalam kategori <span class="badge bg-success-subtle text-success"><?php echo htmlspecialchars($category_slug); ?></span>
            <?php endif; ?>
            <?php if (!empty($search)): ?>
                dengan kata kunci "<span class="text-dark fw-bold"><?php echo htmlspecialchars($search); ?></span>"
            <?php endif; ?>
        </div>

        <!-- Sorting Form -->
        <form method="GET" action="<?php echo base_url('index.php'); ?>" class="d-flex align-items-center gap-2">
            <?php if (!empty($search)): ?>
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <?php endif; ?>
            <?php if (!empty($category_slug)): ?>
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category_slug); ?>">
            <?php endif; ?>
            <label class="small text-muted text-nowrap">Urutkan:</label>
            <select name="sort" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()" style="min-width: 170px;">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Harga Terendah</option>
                <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Nama (A - Z)</option>
            </select>
        </form>
    </div>
</div>

<!-- Grid Produk -->
<?php if ($total_products > 0): ?>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mb-5">
        <?php while ($prod = mysqli_fetch_assoc($products_result)): ?>
            <div class="col">
                <div class="product-card shadow-sm">
                    <!-- Wrapper Gambar -->
                    <div class="product-card-img-wrapper">
                        <img src="<?php echo get_product_image_url($prod['image']); ?>" 
                             alt="<?php echo htmlspecialchars($prod['name']); ?>"
                             loading="lazy"
                             onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                        
                        <span class="product-category-tag">
                            <?php echo htmlspecialchars($prod['category_name']); ?>
                        </span>

                        <?php if ($prod['stock'] > 5): ?>
                            <span class="badge bg-success product-stock-tag">
                                Stok: <?php echo $prod['stock']; ?>
                            </span>
                        <?php elseif ($prod['stock'] > 0): ?>
                            <span class="badge bg-warning text-dark product-stock-tag">
                                Sisa <?php echo $prod['stock']; ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger product-stock-tag">
                                Habis
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Body Card -->
                    <div class="product-card-body">
                        <h3 class="product-title" title="<?php echo htmlspecialchars($prod['name']); ?>">
                            <a href="<?php echo base_url('product_detail.php?id=' . $prod['id']); ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($prod['name']); ?>
                            </a>
                        </h3>

                        <div class="product-seller-meta mb-2">
                            <i class="bi bi-shop text-success"></i>
                            <span class="text-truncate"><?php echo htmlspecialchars($prod['seller_name']); ?></span>
                        </div>

                        <div class="mt-auto">
                            <div class="d-flex align-items-baseline gap-1 mb-3">
                                <span class="product-price"><?php echo format_rupiah($prod['price']); ?></span>
                                <span class="product-unit">/ <?php echo htmlspecialchars($prod['unit']); ?></span>
                            </div>

                            <div class="d-grid gap-2">
                                <?php if ($prod['stock'] > 0): ?>
                                    <form action="<?php echo base_url('cart.php'); ?>" method="POST">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                                        <input type="hidden" name="qty" value="1">
                                        <button type="submit" class="btn btn-outline-success btn-sm rounded-pill w-100 fw-semibold d-flex align-items-center justify-content-center gap-1">
                                            <i class="bi bi-cart-plus"></i> + Keranjang
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-light btn-sm rounded-pill w-100 text-muted fw-semibold" disabled>
                                        Stok Habis
                                    </button>
                                <?php endif; ?>

                                <a href="<?php echo base_url('product_detail.php?id=' . $prod['id']); ?>" class="btn btn-soft-primary btn-sm rounded-pill fw-semibold text-center">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <!-- State Kosong jika tidak ada hasil -->
    <div class="card border-0 shadow-sm rounded-4 p-5 text-center my-4">
        <div class="display-3 text-muted mb-3">🔍</div>
        <h4 class="fw-bold text-dark">Produk Tidak Ditemukan</h4>
        <p class="text-secondary mx-auto" style="max-width: 480px;">
            Maaf, kami tidak dapat menemukan produk yang sesuai dengan pencarian atau filter yang Anda pilih. Silakan gunakan kata kunci lain.
        </p>
        <div class="mt-3">
            <a href="<?php echo base_url('index.php'); ?>" class="btn btn-success rounded-pill px-4 py-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Semua Produk
            </a>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

