<?php
/**
 * Helper Functions untuk PasarKu Marketplace Tradisional
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Format angka ke format Rupiah (contoh: Rp 35.000)
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

/**
 * Pembersih input data untuk mencegah XSS & spasi berlebih
 */
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Deteksi Base URL aplikasi secara dinamis
 */
function base_url($path = '') {
    // Ambil skema http/https
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Cari root script folder
    $script_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    $script_dir = str_replace('\\', '/', $script_dir);
    // Tangani jika script berada di dalam subfolder seperti /admin atau /auth
    $script_dir = str_replace(['/admin', '/auth'], '', $script_dir);
    $script_dir = ($script_dir === '/' || $script_dir === '.') ? '' : $script_dir;
    
    $base = rtrim($protocol . $host . $script_dir, '/');
    $path = ltrim($path, '/');
    return $path ? $base . '/' . $path : $base;
}

/**
 * Cek apakah user sudah login
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Mengambil data user yang sedang login
 */
function current_user() {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? '',
        'name'     => $_SESSION['user_name'] ?? '',
        'role'     => $_SESSION['user_role'] ?? 'buyer',
        'email'    => $_SESSION['user_email'] ?? '',
        'phone'    => $_SESSION['user_phone'] ?? '',
        'address'  => $_SESSION['user_address'] ?? ''
    ];
}

/**
 * Cek apakah user memiliki hak akses Penjual atau Admin
 */
function is_seller() {
    $role = $_SESSION['user_role'] ?? '';
    return $role === 'seller' || $role === 'admin';
}

/**
 * Proteksi halaman hanya untuk user yang login
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('warning', 'Silakan login terlebih dahulu untuk melanjutkan.');
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

/**
 * Proteksi halaman hanya untuk Penjual / Admin
 */
function require_seller() {
    require_login();
    if (!is_seller()) {
        set_flash('danger', 'Akses ditolak. Halaman ini hanya diperuntukkan bagi Penjual.');
        header('Location: ' . base_url('index.php'));
        exit;
    }
}

/**
 * Set pesan notifikasi flash (success, danger, warning, info)
 */
function set_flash($type, $message) {
    $_SESSION['flash'] = [
        'type'    => $type,
        'message' => $message
    ];
}

/**
 * Menampilkan dan menghapus pesan notifikasi flash
 */
function display_flash() {
    if (isset($_SESSION['flash'])) {
        $type = $_SESSION['flash']['type'];
        $message = $_SESSION['flash']['message'];
        unset($_SESSION['flash']);
        
        $icon = 'bi-info-circle';
        if ($type === 'success') $icon = 'bi-check-circle-fill';
        if ($type === 'danger')  $icon = 'bi-exclamation-triangle-fill';
        if ($type === 'warning') $icon = 'bi-exclamation-circle-fill';

        echo '<div class="alert alert-' . htmlspecialchars($type) . ' alert-dismissible fade show rounded-3 shadow-sm d-flex align-items-center mb-4" role="alert">
                <i class="bi ' . $icon . ' fs-5 me-2"></i>
                <div class="flex-grow-1">' . $message . '</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

/* ==========================================================================
   FUNGSI KERANJANG BELANJA (CART) BERBASIS SESSION PHP
   ========================================================================== */

/**
 * Mengambil array produk dalam keranjang belanja
 */
function get_cart() {
    return $_SESSION['cart'] ?? [];
}

/**
 * Menghitung total jumlah kuantitas produk dalam keranjang
 */
function get_cart_count() {
    $cart = get_cart();
    $total_qty = 0;
    foreach ($cart as $item) {
        $total_qty += (int)($item['qty'] ?? 0);
    }
    return $total_qty;
}

/**
 * Menghitung total harga seluruh belanjaan
 */
function get_cart_total() {
    $cart = get_cart();
    $total = 0;
    foreach ($cart as $item) {
        $total += (float)$item['price'] * (int)$item['qty'];
    }
    return $total;
}

/**
 * Tambah produk ke keranjang belanja
 */
function add_to_cart($product_id, $qty, $conn) {
    $product_id = (int)$product_id;
    $qty = max(1, (int)$qty);

    // Ambil data produk terbaru dari database
    $stmt = mysqli_prepare($conn, "SELECT p.*, c.name AS category_name, u.name AS seller_name 
                                   FROM products p 
                                   JOIN categories c ON p.category_id = c.id 
                                   JOIN users u ON p.seller_id = u.id 
                                   WHERE p.id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($res);

    if (!$product) {
        return ['status' => false, 'message' => 'Produk tidak ditemukan!'];
    }

    if ($product['stock'] < 1) {
        return ['status' => false, 'message' => 'Maaf, stok produk habis!'];
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $current_qty_in_cart = $_SESSION['cart'][$product_id]['qty'] ?? 0;
    $new_qty = $current_qty_in_cart + $qty;

    if ($new_qty > $product['stock']) {
        return [
            'status' => false, 
            'message' => 'Stok tidak mencukupi! Tersedia hanya ' . $product['stock'] . ' ' . $product['unit'] . '.'
        ];
    }

    $_SESSION['cart'][$product_id] = [
        'id'          => $product['id'],
        'name'        => $product['name'],
        'price'       => (float)$product['price'],
        'qty'         => $new_qty,
        'unit'        => $product['unit'],
        'image'       => $product['image'],
        'seller_name' => $product['seller_name'],
        'stock'       => (int)$product['stock']
    ];

    return ['status' => true, 'message' => 'Produk "' . htmlspecialchars($product['name']) . '" berhasil ditambahkan ke keranjang!'];
}

/**
 * Update kuantitas item dalam keranjang
 */
function update_cart_qty($product_id, $qty, $conn) {
    $product_id = (int)$product_id;
    $qty = (int)$qty;

    if (!isset($_SESSION['cart'][$product_id])) {
        return ['status' => false, 'message' => 'Item tidak ada dalam keranjang.'];
    }

    if ($qty <= 0) {
        unset($_SESSION['cart'][$product_id]);
        return ['status' => true, 'message' => 'Item berhasil dihapus dari keranjang.'];
    }

    // Cek ketersediaan stok
    $stmt = mysqli_prepare($conn, "SELECT stock, unit, name FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $prod = mysqli_fetch_assoc($res);

    if (!$prod) {
        unset($_SESSION['cart'][$product_id]);
        return ['status' => false, 'message' => 'Produk sudah tidak tersedia.'];
    }

    if ($qty > $prod['stock']) {
        $_SESSION['cart'][$product_id]['qty'] = $prod['stock'];
        return [
            'status' => false,
            'message' => 'Kuantitas disesuaikan dengan stok maksimal (' . $prod['stock'] . ' ' . $prod['unit'] . ').'
        ];
    }

    $_SESSION['cart'][$product_id]['qty'] = $qty;
    return ['status' => true, 'message' => 'Keranjang berhasil diperbarui.'];
}

/**
 * Hapus satu item dari keranjang
 */
function remove_from_cart($product_id) {
    $product_id = (int)$product_id;
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

/**
 * Kosongkan seluruh keranjang
 */
function clear_cart() {
    $_SESSION['cart'] = [];
}

/**
 * Dapatkan URL gambar produk dengan fallback placeholder
 */
function get_product_image_url($image_filename) {
    if (!empty($image_filename)) {
        $local_path = __DIR__ . '/../assets/uploads/products/' . $image_filename;
        if (file_exists($local_path)) {
            return base_url('assets/uploads/products/' . $image_filename);
        }
    }
    
    // Cek jika default.svg ada
    $default_path = __DIR__ . '/../assets/uploads/default.svg';
    if (file_exists($default_path)) {
        return base_url('assets/uploads/default.svg');
    }
    
    // Fallback data-uri SVG jika file gambar tidak ditemukan
    return 'data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22400%22%20height%3D%22300%22%20viewBox%3D%220%200%20400%20300%22%3E%3Crect%20fill%3D%22%23e9ecef%22%20width%3D%22400%22%20height%3D%22300%22%2F%3E%3Ctext%20fill%3D%22%236c757d%22%20font-family%3D%22sans-serif%22%20font-size%3D%2220%22%20dy%3D%2210.5%22%20font-weight%3D%22bold%22%20x%3D%2250%25%22%20y%3D%2250%25%22%20text-anchor%3D%22middle%22%3E%F0%9F%A7%BA%20PasarKu%20Produk%3C%2Ftext%3E%3C%2Fsvg%3E';
}
