<?php
$page_title = "Kelola Produk Pasar (CRUD)";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Pastikan hanya Penjual / Admin yang dapat mengakses
require_seller();

$user = current_user();

// Filter & Pencarian
$search = sanitize($_GET['search'] ?? '');
$filter_cat = sanitize($_GET['category'] ?? '');

$sql = "SELECT p.*, c.name AS category_name, u.name AS seller_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        JOIN users u ON p.seller_id = u.id 
        WHERE 1=1";

$params = [];
$types = "";

// Jika role adalah 'seller', hanya tampilkan produk miliknya sendiri. Jika 'admin', tampilkan semua.
if ($user['role'] === 'seller') {
    $sql .= " AND p.seller_id = ?";
    $params[] = $user['id'];
    $types .= "i";
}

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($filter_cat)) {
    $sql .= " AND c.id = ?";
    $params[] = (int)$filter_cat;
    $types .= "i";
}

$sql .= " ORDER BY p.id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$products_res = mysqli_stmt_get_result($stmt);

// Ambil kategori untuk opsi filter
$categories_res = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Halaman -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h2 class="fw-bold text-dark mb-0">
                <i class="bi bi-box-seam text-success me-2"></i>Kelola Data Produk (CRUD)
            </h2>
            <span class="badge bg-primary rounded-pill">
                <?php echo $user['role'] === 'admin' ? 'Mode Administrator' : 'Kios: ' . htmlspecialchars($user['name']); ?>
            </span>
        </div>
        <p class="text-secondary small mb-0 mt-1">Tambah, perbarui, dan kelola ketersediaan stok dagangan pasar Anda.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="<?php echo base_url('admin/orders.php'); ?>" class="btn btn-outline-primary rounded-pill px-3">
            <i class="bi bi-receipt me-1"></i> Pesanan Masuk
        </a>
        <a href="<?php echo base_url('admin/product_add.php'); ?>" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Produk Baru
        </a>
    </div>
</div>

<!-- Filter & Search Card -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?php echo base_url('admin/products.php'); ?>" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" 
                           placeholder="Cari nama produk dagangan..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category" class="form-select bg-light" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    <?php while ($cat = mysqli_fetch_assoc($categories_res)): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filter_cat == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill w-100">
                    <i class="bi bi-funnel me-1"></i> Filter
                </button>
                <?php if (!empty($search) || !empty($filter_cat)): ?>
                    <a href="<?php echo base_url('admin/products.php'); ?>" class="btn btn-light rounded-pill" title="Reset">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabel CRUD Produk -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 py-3 text-secondary text-uppercase small fw-bold" style="width: 50px;">No</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold" style="width: 80px;">Foto</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold">Nama Produk & Kategori</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold text-end">Harga</th>
                    <th class="py-3 text-secondary text-uppercase small fw-bold text-center">Stok & Satuan</th>
                    <?php if ($user['role'] === 'admin'): ?>
                        <th class="py-3 text-secondary text-uppercase small fw-bold">Penjual</th>
                    <?php endif; ?>
                    <th class="pe-4 py-3 text-secondary text-uppercase small fw-bold text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($products_res) > 0):
                    $no = 1;
                    while ($prod = mysqli_fetch_assoc($products_res)):
                ?>
                    <tr>
                        <td class="ps-4 py-3 text-muted"><?php echo $no++; ?></td>
                        <td class="py-3">
                            <img src="<?php echo get_product_image_url($prod['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($prod['name']); ?>" 
                                 class="rounded-3 border" 
                                 style="width: 54px; height: 54px; object-fit: cover;"
                                 onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                        </td>
                        <td class="py-3">
                            <div class="fw-bold text-dark">
                                <a href="<?php echo base_url('product_detail.php?id=' . $prod['id']); ?>" target="_blank" class="text-decoration-none text-dark" title="Lihat di katalog">
                                    <?php echo htmlspecialchars($prod['name']); ?> <i class="bi bi-box-arrow-up-right small text-muted"></i>
                                </a>
                            </div>
                            <span class="badge bg-success-subtle text-success small">
                                <?php echo htmlspecialchars($prod['category_name']); ?>
                            </span>
                        </td>
                        <td class="py-3 text-end fw-bold text-success">
                            <?php echo format_rupiah($prod['price']); ?>
                        </td>
                        <td class="py-3 text-center">
                            <?php if ($prod['stock'] > 10): ?>
                                <span class="badge bg-success px-2 py-1 rounded-pill">
                                    <?php echo $prod['stock'] . ' ' . htmlspecialchars($prod['unit']); ?>
                                </span>
                            <?php elseif ($prod['stock'] > 0): ?>
                                <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">
                                    Sisa <?php echo $prod['stock'] . ' ' . htmlspecialchars($prod['unit']); ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger px-2 py-1 rounded-pill">
                                    Habis
                                </span>
                            <?php endif; ?>
                        </td>
                        <?php if ($user['role'] === 'admin'): ?>
                            <td class="py-3 small text-muted">
                                <i class="bi bi-shop me-1 text-success"></i><?php echo htmlspecialchars($prod['seller_name']); ?>
                            </td>
                        <?php endif; ?>
                        <td class="pe-4 py-3 text-center">
                            <div class="btn-group btn-group-sm">
                                <a href="<?php echo base_url('admin/product_edit.php?id=' . $prod['id']); ?>" 
                                   class="btn btn-outline-primary" title="Ubah Produk (Edit)">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <a href="<?php echo base_url('admin/product_delete.php?id=' . $prod['id']); ?>" 
                                   class="btn btn-outline-danger" 
                                   title="Hapus Produk (Delete)"
                                   data-confirm="Apakah Anda yakin ingin menghapus produk '<?php echo htmlspecialchars($prod['name']); ?>'?">
                                    <i class="bi bi-trash3"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php 
                    endwhile;
                else: 
                ?>
                    <tr>
                        <td colspan="<?php echo $user['role'] === 'admin' ? '7' : '6'; ?>" class="text-center py-5">
                            <div class="display-6 text-muted mb-2">📦</div>
                            <p class="text-secondary mb-3">Belum ada data produk yang sesuai.</p>
                            <a href="<?php echo base_url('admin/product_add.php'); ?>" class="btn btn-sm btn-success rounded-pill px-3">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Produk Pertama
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
