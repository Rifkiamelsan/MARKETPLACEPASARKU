<?php
/**
 * ===================================================================
 * ENDPOINT REST API: PESANAN MARKETPLACE ("PasarKu")
 * File: api/pesanan.php
 * 
 * Metode yang didukung:
 * 1. POST /api/pesanan.php -> Membuat transaksi pesanan baru (Menerima JSON)
 * 2. GET  /api/pesanan.php -> Menampilkan riwayat pesanan
 *    Optional query param: ?order_code=TRAD-xxx / ?id={id}
 * ===================================================================
 */

require_once __DIR__ . '/koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

// -------------------------------------------------------------
// METODE 1: POST /api/pesanan.php (Buat Pesanan Baru)
// -------------------------------------------------------------
if ($method === 'POST') {
    $input = getJsonInput();

    // 1. Validasi Input Dasar
    if (empty($input)) {
        sendJsonResponse('fail', 'Payload request body kosong atau format JSON tidak valid.', null, 400);
    }

    $recipient_name    = trim($input['recipient_name'] ?? '');
    $recipient_phone   = trim($input['recipient_phone'] ?? '');
    $shipping_address  = trim($input['shipping_address'] ?? '');
    $payment_method    = trim($input['payment_method'] ?? 'COD (Bayar di Tempat)');
    $notes             = trim($input['notes'] ?? '');
    $items             = $input['items'] ?? [];
    $buyer_id          = isset($input['buyer_id']) ? (int)$input['buyer_id'] : 3; // Default buyer akun pembeli

    if (empty($recipient_name) || empty($recipient_phone) || empty($shipping_address)) {
        sendJsonResponse('fail', 'Harap lengkapi field wajib: recipient_name, recipient_phone, dan shipping_address.', null, 400);
    }

    if (empty($items) || !is_array($items)) {
        sendJsonResponse('fail', 'Daftar item belanja (items) wajib diisi dan berupa array objek [ {product_id, quantity} ].', null, 400);
    }

    // 2. Validasi Pembeli (Buyer)
    $check_buyer = mysqli_prepare($conn, "SELECT id, name FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($check_buyer, "i", $buyer_id);
    mysqli_stmt_execute($check_buyer);
    $res_buyer = mysqli_stmt_get_result($check_buyer);
    if (!mysqli_fetch_assoc($res_buyer)) {
        // Jika buyer_id tidak ditemukan, buat akun pembeli tamu (guest)
        $auto_user = 'guest_' . substr(md5($recipient_name . time()), 0, 6);
        $auto_pass = password_hash('guest123', PASSWORD_DEFAULT);
        $auto_mail = $auto_user . '@pasarku.local';
        $ins_user = mysqli_prepare($conn, "INSERT INTO users (username, password, name, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'buyer')");
        mysqli_stmt_bind_param($ins_user, "ssssss", $auto_user, $auto_pass, $recipient_name, $auto_mail, $recipient_phone, $shipping_address);
        if (mysqli_stmt_execute($ins_user)) {
            $buyer_id = mysqli_insert_id($conn);
        } else {
            $buyer_id = 1; // Fallback darurat
        }
    }

    // 3. Mulai Transaksi Database (ACID Compliance)
    mysqli_begin_transaction($conn);

    try {
        $validatedItems = [];
        $total_amount = 0;

        // Validasi ketersediaan dan stok produk
        foreach ($items as $idx => $item) {
            $productId = isset($item['product_id']) ? (int)$item['product_id'] : 0;
            $qty = isset($item['quantity']) ? (int)$item['quantity'] : 0;

            if ($productId <= 0 || $qty <= 0) {
                throw new Exception("Item index [{$idx}] tidak valid: product_id dan quantity harus berupa angka bulat positif.");
            }

            $stmt_check = mysqli_prepare($conn, "SELECT id, name, price, stock, unit FROM products WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($stmt_check, "i", $productId);
            mysqli_stmt_execute($stmt_check);
            $res_prod = mysqli_stmt_get_result($stmt_check);
            $prod = mysqli_fetch_assoc($res_prod);

            if (!$prod) {
                throw new Exception("Produk dengan ID {$productId} tidak ditemukan.");
            }

            if ($prod['stock'] < $qty) {
                throw new Exception("Stok untuk produk '{$prod['name']}' tidak mencukupi (Tersisa: {$prod['stock']} {$prod['unit']}, diminta: {$qty}).");
            }

            $unit_price = (float)$prod['price'];
            $subtotal   = $unit_price * $qty;
            $total_amount += $subtotal;

            $validatedItems[] = [
                'product_id'   => $prod['id'],
                'product_name' => $prod['name'],
                'unit'         => $prod['unit'],
                'quantity'     => $qty,
                'unit_price'   => $unit_price,
                'subtotal'     => $subtotal
            ];
        }

        // 4. Generate Kode Order Unik (Format: TRAD-YYYYMMDD-XXXX)
        $order_code = 'TRAD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));
        $status = 'pending';

        // 5. Simpan ke Tabel orders
        $stmt_order = mysqli_prepare($conn, "INSERT INTO orders 
            (order_code, buyer_id, recipient_name, recipient_phone, shipping_address, payment_method, total_amount, status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt_order, "sissssiss", 
            $order_code, $buyer_id, $recipient_name, $recipient_phone, $shipping_address, $payment_method, $total_amount, $status, $notes
        );

        if (!mysqli_stmt_execute($stmt_order)) {
            throw new Exception("Gagal menyimpan data pesanan: " . mysqli_error($conn));
        }

        $order_id = mysqli_insert_id($conn);

        // 6. Simpan detail item dan kurangi stok
        $stmt_detail = mysqli_prepare($conn, "INSERT INTO order_details (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmt_stock  = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ?");

        foreach ($validatedItems as $item) {
            mysqli_stmt_bind_param($stmt_detail, "iiidd", $order_id, $item['product_id'], $item['quantity'], $item['unit_price'], $item['subtotal']);
            if (!mysqli_stmt_execute($stmt_detail)) {
                throw new Exception("Gagal menyimpan detail item pesanan: " . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt_stock, "ii", $item['quantity'], $item['product_id']);
            if (!mysqli_stmt_execute($stmt_stock)) {
                throw new Exception("Gagal memperbarui stok produk: " . mysqli_error($conn));
            }
        }

        // Komit Transaksi Database
        mysqli_commit($conn);

        // Kirim Respons Sukses (HTTP 201 Created)
        sendJsonResponse('success', 'Pesanan baru berhasil dibuat.', [
            'order_id'        => $order_id,
            'order_code'      => $order_code,
            'recipient_name'  => $recipient_name,
            'recipient_phone' => $recipient_phone,
            'shipping_address'=> $shipping_address,
            'payment_method'  => $payment_method,
            'total_amount'    => $total_amount,
            'total_formatted' => 'Rp ' . number_format($total_amount, 0, ',', '.'),
            'status'          => $status,
            'notes'           => $notes,
            'total_items'     => count($validatedItems),
            'items'           => $validatedItems,
            'created_at'      => date('Y-m-d H:i:s')
        ], 201);

    } catch (Exception $e) {
        mysqli_rollback($conn);
        sendJsonResponse('fail', $e->getMessage(), null, 400);
    }
}

// -------------------------------------------------------------
// METODE 2: GET /api/pesanan.php (Lihat Riwayat Pesanan)
// -------------------------------------------------------------
if ($method === 'GET') {
    if (isset($_GET['order_code']) || isset($_GET['id'])) {
        $whereSql = isset($_GET['order_code']) ? "o.order_code = ?" : "o.id = ?";
        $paramVal = isset($_GET['order_code']) ? trim($_GET['order_code']) : (int)$_GET['id'];
        $paramType = isset($_GET['order_code']) ? "s" : "i";

        $query = "SELECT o.*, u.name AS buyer_name, u.email AS buyer_email 
                  FROM orders o 
                  JOIN users u ON o.buyer_id = u.id 
                  WHERE {$whereSql} LIMIT 1";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $paramType, $paramVal);
        mysqli_stmt_execute($stmt);
        $order = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$order) {
            sendJsonResponse('fail', 'Data pesanan tidak ditemukan.', null, 404);
        }

        // Ambil Item Detail
        $stmt_items = mysqli_prepare($conn, "SELECT od.*, p.name AS product_name, p.unit, p.image 
                                             FROM order_details od 
                                             JOIN products p ON od.product_id = p.id 
                                             WHERE od.order_id = ?");
        mysqli_stmt_bind_param($stmt_items, "i", $order['id']);
        mysqli_stmt_execute($stmt_items);
        $res_items = mysqli_stmt_get_result($stmt_items);

        $orderItems = [];
        while ($item = mysqli_fetch_assoc($res_items)) {
            $orderItems[] = [
                'product_id'   => (int)$item['product_id'],
                'product_name' => $item['product_name'],
                'unit'         => $item['unit'],
                'quantity'     => (int)$item['quantity'],
                'unit_price'   => (float)$item['unit_price'],
                'subtotal'     => (float)$item['subtotal']
            ];
        }

        $order['total_amount'] = (float)$order['total_amount'];
        $order['total_formatted'] = 'Rp ' . number_format($order['total_amount'], 0, ',', '.');
        $order['items'] = $orderItems;

        sendJsonResponse('success', 'Berhasil mendapatkan detail pesanan.', $order, 200);
    } else {
        // Ambil 20 pesanan terbaru
        $query = "SELECT o.id, o.order_code, o.recipient_name, o.recipient_phone, o.total_amount, o.status, o.created_at,
                         COUNT(od.id) AS total_items
                  FROM orders o
                  LEFT JOIN order_details od ON o.id = od.order_id
                  GROUP BY o.id
                  ORDER BY o.id DESC
                  LIMIT 20";

        $res = mysqli_query($conn, $query);
        $orders = [];
        while ($r = mysqli_fetch_assoc($res)) {
            $orders[] = [
                'id'              => (int)$r['id'],
                'order_code'      => $r['order_code'],
                'recipient_name'  => $r['recipient_name'],
                'recipient_phone' => $r['recipient_phone'],
                'total_amount'    => (float)$r['total_amount'],
                'total_formatted' => 'Rp ' . number_format($r['total_amount'], 0, ',', '.'),
                'total_items'     => (int)$r['total_items'],
                'status'          => $r['status'],
                'created_at'      => $r['created_at']
            ];
        }

        sendJsonResponse('success', 'Berhasil memuat daftar pesanan.', [
            'total'  => count($orders),
            'orders' => $orders
        ], 200);
    }
}

// Jika metode lain
sendJsonResponse('error', "Metode HTTP {$method} tidak diizinkan. Gunakan GET atau POST.", null, 405);

