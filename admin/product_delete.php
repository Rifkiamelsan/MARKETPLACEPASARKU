<?php
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

// Cek produk
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

// Validasi otorisasi
if ($user['role'] !== 'admin' && $product['seller_id'] != $user['id']) {
    set_flash('danger', 'Anda tidak memiliki wewenang untuk menghapus produk ini.');
    header('Location: ' . base_url('admin/products.php'));
    exit;
}

// Cek apakah produk sudah pernah dipesan di order_details
$check_orders = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM order_details WHERE product_id = ?");
mysqli_stmt_bind_param($check_orders, "i", $id);
mysqli_stmt_execute($check_orders);
$res_orders = mysqli_stmt_get_result($check_orders);
$order_count = mysqli_fetch_assoc($res_orders)['count'] ?? 0;

if ($order_count > 0) {
    // Jika sudah ada riwayat transaksi, jangan hapus permanen agar riwayat nota tidak rusak, melainkan kosongkan stok
    $update_stmt = mysqli_prepare($conn, "UPDATE products SET stock = 0 WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "i", $id);
    mysqli_stmt_execute($update_stmt);
    
    set_flash('warning', 'Produk "' . htmlspecialchars($product['name']) . '" sudah memiliki riwayat transaksi (' . $order_count . ' pesanan). Produk tidak dihapus dari riwayat, namun stok telah diubah menjadi 0 (Habis).');
} else {
    // Hapus permanen
    $delete_stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    
    if (mysqli_stmt_execute($delete_stmt)) {
        // Hapus file gambar jika ada dan bukan gambar bawaan
        if (!empty($product['image']) && $product['image'] !== 'default.svg') {
            $img_path = __DIR__ . '/../assets/uploads/products/' . $product['image'];
            if (file_exists($img_path)) {
                @unlink($img_path);
            }
        }
        set_flash('success', 'Produk "' . htmlspecialchars($product['name']) . '" berhasil dihapus secara permanen.');
    } else {
        set_flash('danger', 'Gagal menghapus produk: ' . mysqli_error($conn));
    }
}

header('Location: ' . base_url('admin/products.php'));
exit;

