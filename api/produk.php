<?php
/**
 * ===================================================================
 * ENDPOINT REST API: PRODUK MARKETPLACE ("PasarKu")
 * File: api/produk.php
 * 
 * Metode yang didukung:
 * 1. GET /api/produk.php          -> Menampilkan seluruh katalog produk
 *    Optional query param: ?kategori={id_or_slug}&q={search}&limit={n}
 * 2. GET /api/produk.php?id={id}  -> Menampilkan detail 1 produk spesifik
 * ===================================================================
 */

require_once __DIR__ . '/koneksi.php';

// Validasi Metode Request: Hanya izinkan GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(
        'error', 
        'Metode HTTP ' . $_SERVER['REQUEST_METHOD'] . ' tidak diizinkan pada endpoint ini. Gunakan metode GET.', 
        null, 
        405
    );
}

// -------------------------------------------------------------
// KASUS 1: Detail Produk Berdasarkan ID (?id=...)
// -------------------------------------------------------------
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        sendJsonResponse('fail', 'Parameter ID tidak valid. Harap masukkan angka ID produk yang benar.', null, 400);
    }

    $query = "SELECT 
                p.id,
                p.name,
                p.slug,
                p.description,
                p.price,
                p.stock,
                p.unit,
                p.image,
                p.created_at,
                p.updated_at,
                c.id AS category_id,
                c.name AS category_name,
                c.slug AS category_slug,
                u.id AS seller_id,
                u.name AS seller_name,
                u.phone AS seller_phone,
                u.address AS seller_address
              FROM products p
              JOIN categories c ON p.category_id = c.id
              JOIN users u ON p.seller_id = u.id
              WHERE p.id = ? 
              LIMIT 1";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Format struktur data detail produk
        $productData = [
            'id'          => (int)$row['id'],
            'nama_produk' => $row['name'],
            'slug'        => $row['slug'],
            'deskripsi'   => $row['description'],
            'harga'       => (float)$row['price'],
            'harga_format'=> 'Rp ' . number_format($row['price'], 0, ',', '.'),
            'stok'        => (int)$row['stock'],
            'satuan'      => $row['unit'],
            'status_stok' => ((int)$row['stock'] > 0) ? 'Tersedia' : 'Habis',
            'gambar'      => $row['image'],
            'url_gambar'  => 'assets/images/products/' . $row['image'],
            'kategori'    => [
                'id'   => (int)$row['category_id'],
                'nama' => $row['category_name'],
                'slug' => $row['category_slug']
            ],
            'penjual'     => [
                'id'     => (int)$row['seller_id'],
                'nama'   => $row['seller_name'],
                'telepon'=> $row['seller_phone'],
                'alamat' => $row['seller_address']
            ],
            'created_at'  => $row['created_at'],
            'updated_at'  => $row['updated_at']
        ];

        sendJsonResponse('success', 'Berhasil mendapatkan detail produk.', $productData, 200);
    } else {
        sendJsonResponse('fail', "Produk dengan ID {$id} tidak ditemukan dalam database.", null, 404);
    }
}

// -------------------------------------------------------------
// KASUS 2: Seluruh Daftar Produk (/api/produk.php)
// -------------------------------------------------------------
$whereClauses = [];
$params = [];
$types = "";

// Filter pencarian nama / deskripsi (?q=...)
if (!empty($_GET['q'])) {
    $search = '%' . trim($_GET['q']) . '%';
    $whereClauses[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = $search;
    $params[] = $search;
    $types .= "ss";
}

// Filter berdasarkan kategori (?kategori=1 atau ?kategori=sayuran)
if (!empty($_GET['kategori'])) {
    $cat = trim($_GET['kategori']);
    if (is_numeric($cat)) {
        $whereClauses[] = "p.category_id = ?";
        $params[] = (int)$cat;
        $types .= "i";
    } else {
        $whereClauses[] = "c.slug = ?";
        $params[] = $cat;
        $types .= "s";
    }
}

$whereSql = "";
if (!empty($whereClauses)) {
    $whereSql = "WHERE " . implode(" AND ", $whereClauses);
}

// Limit hasil (default: 50)
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 50;

$query = "SELECT 
            p.id,
            p.name,
            p.slug,
            p.description,
            p.price,
            p.stock,
            p.unit,
            p.image,
            p.created_at,
            c.id AS category_id,
            c.name AS category_name,
            u.name AS seller_name
          FROM products p
          JOIN categories c ON p.category_id = c.id
          JOIN users u ON p.seller_id = u.id
          {$whereSql}
          ORDER BY p.id ASC
          LIMIT ?";

$params[] = $limit;
$types .= "i";

$stmt = mysqli_prepare($conn, $query);
if (!empty($types)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = [
        'id'          => (int)$row['id'],
        'nama_produk' => $row['name'],
        'slug'        => $row['slug'],
        'kategori'    => $row['category_name'],
        'harga'       => (float)$row['price'],
        'harga_format'=> 'Rp ' . number_format($row['price'], 0, ',', '.'),
        'stok'        => (int)$row['stock'],
        'satuan'      => $row['unit'],
        'penjual'     => $row['seller_name'],
        'gambar'      => $row['image'],
        'url_gambar'  => 'assets/images/products/' . $row['image']
    ];
}

$total = count($products);

// Format respons sukses
http_response_code(200);
echo json_encode([
    'status'  => 'success',
    'code'    => 200,
    'message' => "Berhasil memuat {$total} data produk.",
    'total'   => $total,
    'data'    => $products
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;

