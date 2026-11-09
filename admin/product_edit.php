<?php
$page_title = "Ubah Data Produk";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_seller();
$user = current_user();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('danger', 'ID produk tidak valid.');
    header('Location: ' . base_url('admin/products.php'));
    exit;
}

// Ambil data produk yang ada
$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($res);

if (!$product) {
    set_flash('danger', 'Produk tidak ditemukan.');
    header('Location: ' . base_url('admin/products.php'));
    exit;
}

// Hak akses: jika bukan admin dan bukan pemilik produk, tolak akses
if ($user['role'] !== 'admin' && $product['seller_id'] != $user['id']) {
    set_flash('danger', 'Anda tidak memiliki hak untuk mengubah produk milik pedagang lain.');
    header('Location: ' . base_url('admin/products.php'));
    exit;
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $unit = sanitize($_POST['unit'] ?? 'Pcs');
    $description = sanitize($_POST['description'] ?? '');

    if (empty($name) || $category_id <= 0 || $price <= 0 || $stock < 0 || empty($description)) {
        set_flash('danger', 'Mohon isi semua data produk dengan benar.');
    } else {
        $image_name = $product['image']; // Gambar lama

        // Cek jika ada unggahan gambar baru
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_orig = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $ext = strtolower(pathinfo($file_orig, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            if (!in_array($ext, $allowed_exts)) {
                set_flash('danger', 'Format gambar tidak didukung!');
                header('Location: ' . base_url('admin/product_edit.php?id=' . $id));
                exit;
            }

            if ($file_size > 2 * 1024 * 1024) {
                set_flash('danger', 'Ukuran file gambar maksimal 2MB.');
                header('Location: ' . base_url('admin/product_edit.php?id=' . $id));
                exit;
            }

            $new_image_name = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
            $target_dir = __DIR__ . '/../assets/uploads/products/';
            if (move_uploaded_file($file_tmp, $target_dir . $new_image_name)) {
                // Hapus file lama jika bukan default dan ada di server
                if (!empty($product['image']) && $product['image'] !== 'default.svg' && file_exists($target_dir . $product['image'])) {
                    @unlink($target_dir . $product['image']);
                }
                $image_name = $new_image_name;
            }
        }

        // Update database
        $stmt_update = mysqli_prepare($conn, "UPDATE products 
                                              SET category_id = ?, name = ?, description = ?, price = ?, stock = ?, unit = ?, image = ? 
                                              WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "issdissi", $category_id, $name, $description, $price, $stock, $unit, $image_name, $id);

        if (mysqli_stmt_execute($stmt_update)) {
            set_flash('success', 'Produk "' . htmlspecialchars($name) . '" berhasil diperbarui!');
            header('Location: ' . base_url('admin/products.php'));
            exit;
        } else {
            set_flash('danger', 'Gagal memperbarui data: ' . mysqli_error($conn));
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center mb-5">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">
                    <i class="bi bi-pencil-square text-success me-2"></i>Ubah Data Produk
                </h2>
                <p class="text-secondary small mb-0">Perbarui informasi harga, persediaan stok, atau foto produk.</p>
            </div>
            <a href="<?php echo base_url('admin/products.php'); ?>" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Batal / Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="<?php echo base_url('admin/product_edit.php?id=' . $id); ?>" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Nama Produk -->
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">Nama Produk Pasar <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                        </div>

                        <!-- Kategori -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Harga -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Harga (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">Rp</span>
                                <input type="number" name="price" class="form-control" value="<?php echo (int)$product['price']; ?>" min="500" step="100" required>
                            </div>
                        </div>

                        <!-- Stok -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Jumlah Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" value="<?php echo (int)$product['stock']; ?>" min="0" required>
                        </div>

                        <!-- Satuan -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Satuan Jual <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" list="unitList" value="<?php echo htmlspecialchars($product['unit']); ?>" required>
                            <datalist id="unitList">
                                <option value="Kg">
                                <option value="Gram">
                                <option value="Ikat">
                                <option value="Pcs">
                                <option value="Porsi">
                                <option value="Bungkus">
                                <option value="Karung (5 Kg)">
                                <option value="Set">
                            </datalist>
                        </div>

                        <!-- Foto Produk -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Foto Produk</label>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <img src="<?php echo get_product_image_url($product['image']); ?>" 
                                     alt="Preview" 
                                     class="rounded-3 border" 
                                     style="width: 70px; height: 70px; object-fit: cover;"
                                     onerror="this.src='<?php echo base_url('assets/uploads/default.svg'); ?>';">
                                <div class="flex-grow-1">
                                    <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg">
                                    <div class="form-text small">Pilih file baru jika ingin mengganti gambar produk. Biarkan kosong jika tidak ingin mengubah.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Deskripsi Produk <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 fw-bold">
                                <i class="bi bi-save me-1"></i> Perbarui Produk
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

