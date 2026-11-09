<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('index.php'));
    exit;
}

$cart = get_cart();
if (empty($cart)) {
    set_flash('danger', 'Keranjang belanja Anda kosong!');
    header('Location: ' . base_url('index.php'));
    exit;
}

// Tangkap & sanitasi input
$recipient_name = sanitize($_POST['recipient_name'] ?? '');
$recipient_phone = sanitize($_POST['recipient_phone'] ?? '');
$shipping_address = sanitize($_POST['shipping_address'] ?? '');
$payment_method = sanitize($_POST['payment_method'] ?? 'COD (Bayar di Tempat)');
$notes = sanitize($_POST['notes'] ?? '');

// Validasi Form
if (empty($recipient_name) || empty($recipient_phone) || empty($shipping_address)) {
    set_flash('danger', 'Mohon lengkapi semua kolom wajib (Nama, No HP, dan Alamat Pengiriman).');
    header('Location: ' . base_url('checkout.php'));
    exit;
}

// Tentukan buyer_id
$buyer_id = null;
if (is_logged_in()) {
    $buyer_id = $_SESSION['user_id'];
} else {
    // Jika tidak login, cek apakah ada user buyer dummy atau buat buyer baru
    $safe_phone = preg_replace('/[^0-9]/', '', $recipient_phone);
    $check_user = mysqli_prepare($conn, "SELECT id FROM users WHERE phone = ? LIMIT 1");
    mysqli_stmt_bind_param($check_user, "s", $safe_phone);
    mysqli_stmt_execute($check_user);
    $user_res = mysqli_stmt_get_result($check_user);
    if ($existing_user = mysqli_fetch_assoc($user_res)) {
        $buyer_id = $existing_user['id'];
    } else {
        // Buat akun pembeli otomatis dengan nomor HP & Nama
        $auto_username = 'pembeli_' . substr(md5($recipient_name . time()), 0, 8);
        $auto_email = $auto_username . '@pasarku.guest';
        $auto_password = password_hash('pembeli123', PASSWORD_DEFAULT);
        $insert_user = mysqli_prepare($conn, "INSERT INTO users (username, password, name, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'buyer')");
        mysqli_stmt_bind_param($insert_user, "ssssss", $auto_username, $auto_password, $recipient_name, $auto_email, $recipient_phone, $shipping_address);
        if (mysqli_stmt_execute($insert_user)) {
            $buyer_id = mysqli_insert_id($conn);
        } else {
            // Fallback ke default buyer ID 3
            $buyer_id = 3;
        }
    }
}

// Mulai Database Transaction untuk integritas data
mysqli_begin_transaction($conn);

try {
    // 1. Validasi ulang stok seluruh produk di dalam keranjang
    foreach ($cart as $product_id => $item) {
        $stmt_check = mysqli_prepare($conn, "SELECT stock, name FROM products WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt_check, "i", $product_id);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);
        $curr_prod = mysqli_fetch_assoc($res_check);

        if (!$curr_prod || $curr_prod['stock'] < $item['qty']) {
            throw new Exception('Maaf, stok untuk produk "' . ($curr_prod['name'] ?? $item['name']) . '" tidak mencukupi (sisa ' . ($curr_prod['stock'] ?? 0) . ').');
        }
    }

    // 2. Buat Nomor Invoice Unik (Format: TRAD-YYYYMMDD-XXXX)
    $today_prefix = 'TRAD-' . date('Ymd') . '-';
    $random_suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
    $order_code = $today_prefix . $random_suffix;

    $total_amount = get_cart_total();
    $status = 'pending';

    // 3. Simpan ke Tabel orders
    $stmt_order = mysqli_prepare($conn, "INSERT INTO orders 
        (order_code, buyer_id, recipient_name, recipient_phone, shipping_address, payment_method, total_amount, status, notes) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt_order, "sissssiss", 
        $order_code, $buyer_id, $recipient_name, $recipient_phone, $shipping_address, $payment_method, $total_amount, $status, $notes
    );

    if (!mysqli_stmt_execute($stmt_order)) {
        throw new Exception('Gagal menyimpan pesanan ke database: ' . mysqli_error($conn));
    }

    $order_id = mysqli_insert_id($conn);

    // 4. Simpan setiap item ke order_details & kurangi stok produk
    $stmt_detail = mysqli_prepare($conn, "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt_update_stock = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ?");

    foreach ($cart as $product_id => $item) {
        $subtotal = $item['price'] * $item['qty'];
        
        // Insert detail
        mysqli_stmt_bind_param($stmt_detail, "iiidd", $order_id, $product_id, $item['qty'], $item['price'], $subtotal);
        if (!mysqli_stmt_execute($stmt_detail)) {
            throw new Exception('Gagal menyimpan rincian item pesanan.');
        }

        // Update stok
        mysqli_stmt_bind_param($stmt_update_stock, "ii", $item['qty'], $product_id);
        if (!mysqli_stmt_execute($stmt_update_stock)) {
            throw new Exception('Gagal memperbarui stok produk.');
        }
    }

    // Komit transaksi
    mysqli_commit($conn);

    // Kosongkan keranjang belanja
    clear_cart();

    // Redirect ke halaman sukses invoice
    set_flash('success', 'Pesanan Anda berhasil dibuat dan telah tersimpan di sistem!');
    header('Location: ' . base_url('order_success.php?code=' . urlencode($order_code)));
    exit;

} catch (Exception $e) {
    // Batalkan seluruh perubahan jika ada error
    mysqli_rollback($conn);
    set_flash('danger', $e->getMessage());
    header('Location: ' . base_url('cart.php'));
    exit;
}

