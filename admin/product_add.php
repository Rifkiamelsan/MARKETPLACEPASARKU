<?php
$page_title = "Tambah Produk Baru";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

require_seller();
$user = current_user();

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $unit = sanitize($_POST['unit'] ?? 'Pcs');
    $description = sanitize($_POST['description'] ?? '');

    // Validasi field
    if (empty($name) || $category_id <= 0 || $price <= 0 || $stock < 0 || empty($description)) {
        set_flash('danger', 'Mohon isi semua data produk dengan benar.');
    } else {
        // Buat slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug .= '-' . substr(uniqid(), -4);

        // Upload Gambar
        $image_name = 'default.svg';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['image']['tmp_name'];
            $file_orig = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $ext = strtolower(pathinfo($file_orig, PATHINFO_EXTENSION));

            $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'svg'];
            if (!in_array($ext, $allowed_exts)) {
                set_flash('danger', 'Format gambar tidak didukung! Gunakan JPG, PNG, WEBP, atau SVG.');
                header('Location: ' . base_url('admin/product_add.php'));
                exit;
            }

            if ($file_size > 2 * 1024 * 1024) { // 2MB
                set_flash('danger', 'Ukuran file gambar maksimal 2MB.');
                header('Location: ' . base_url('admin/product_add.php'));
                exit;
            }

            $image_name = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
            $target_dir = __DIR__ . '/../assets/uploads/products/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            move_uploaded_file($file_tmp, $target_dir . $image_name);
        }

        // Simpan ke database
        $seller_id = $user['id'];
        $stmt = mysqli_prepare($conn, "INSERT INTO products (seller_id, category_id, name, slug, description, price, stock, unit, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iisssdiss", $seller_id, $category_id, $name, $slug, $description, $price, $stock, $unit, $image_name);

        if (mysqli_stmt_execute($stmt)) {
            set_flash('success', 'Produk "' . htmlspecialchars($name) . '" berhasil ditambahkan ke katalog pasar!');
            header('Location: ' . base_url('admin/products.php'));
            exit;
        } else {
            set_flash('danger', 'Gagal menambahkan produk: ' . mysqli_error($conn));
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
                    <i class="bi bi-plus-circle text-success me-2"></i>Tambah Produk Baru
                </h2>
                <p class="text-secondary small mb-0">Lengkapi formulir di bawah ini untuk menawarkan barang dagangan Anda.</p>
            </div>
            <a href="<?php echo base_url('admin/products.php'); ?>" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                <i class="bi bi-arrow-left me-1"></i> Batal / Kembali
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="<?php echo base_url('admin/product_add.php'); ?>" method="POST" enctype="multipart/form-data">
                    <div class="row g-3">
                        <!-- Nama Produk -->
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">Nama Produk Pasar <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Bawang Putih Kating Super" required>
                        </div>

                        <!-- Kategori -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Kategori <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?php echo $cat['id']; ?>">
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
                                <input type="number" name="price" class="form-control" placeholder="35000" min="500" step="100" required>
                            </div>
                        </div>

                        <!-- Stok -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Jumlah Stok <span class="text-danger">*</span></label>
                            <input type="number" name="stock" class="form-control" placeholder="50" min="0" required>
                        </div>

                        <!-- Satuan -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Satuan Jual <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" list="unitList" placeholder="Contoh: Kg, Ikat, Pcs" value="Kg" required>
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
                            <label class="form-label small fw-bold text-dark">Foto / Gambar Produk</label>
                            <input type="file" name="image" class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg">
                            <div class="form-text small">Format didukung: JPG, PNG, WEBP, SVG (Maks. 2MB). Jika dikosongkan, ikon bawaan akan otomatis digunakan.</div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Deskripsi Produk <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" 
                                      placeholder="Jelaskan kualitas, kesegaran, asal daerah panen, atau cara penyimpanan produk..." required></textarea>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" class="btn btn-success rounded-pill px-5 py-2 fw-bold">
                                <i class="bi bi-check-lg me-1"></i> Simpan Produk
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
