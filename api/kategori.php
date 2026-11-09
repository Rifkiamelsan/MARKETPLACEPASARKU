<?php
/**
 * ===================================================================
 * ENDPOINT REST API: KATEGORI PRODUK MARKETPLACE ("PasarKu")
 * File: api/kategori.php
 * 
 * Metode yang didukung:
 * 1. GET /api/kategori.php         -> Menampilkan seluruh kategori
 * 2. GET /api/kategori.php?id={id} -> Menampilkan detail 1 kategori
 * ===================================================================
 */

require_once __DIR__ . '/koneksi.php';

// Validasi Metode: Hanya izinkan GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJsonResponse(
        'error', 
        'Metode HTTP ' . $_SERVER['REQUEST_METHOD'] . ' tidak diizinkan. Gunakan metode GET.', 
        null, 
        405
    );
}

// -------------------------------------------------------------
// KASUS 1: Detail Kategori Berdasarkan ID (?id=...)
// -------------------------------------------------------------
if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($id === false || $id <= 0) {
        sendJsonResponse('fail', 'Parameter ID kategori tidak valid.', null, 400);
    }

    $query = "SELECT 
                c.id, 
                c.name, 
                c.slug, 
                c.description, 
                c.icon, 
                c.created_at,
                COUNT(p.id) AS total_produk
              FROM categories c
              LEFT JOIN products p ON c.id = p.category_id
              WHERE c.id = ?
              GROUP BY c.id
              LIMIT 1";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $categoryData = [
            'id'           => (int)$row['id'],
            'nama_kategori'=> $row['name'],
            'slug'         => $row['slug'],
            'deskripsi'    => $row['description'],
            'icon'         => $row['icon'],
            'total_produk' => (int)$row['total_produk'],
            'created_at'   => $row['created_at']
        ];
        sendJsonResponse('success', 'Berhasil mendapatkan detail kategori.', $categoryData, 200);
    } else {
        sendJsonResponse('fail', "Kategori dengan ID {$id} tidak ditemukan.", null, 404);
    }
}

// -------------------------------------------------------------
// KASUS 2: Seluruh Daftar Kategori (/api/kategori.php)
// -------------------------------------------------------------
$query = "SELECT 
            c.id, 
            c.name, 
            c.slug, 
            c.description, 
            c.icon, 
            c.created_at,
            COUNT(p.id) AS total_produk
          FROM categories c
          LEFT JOIN products p ON c.id = p.category_id
          GROUP BY c.id
          ORDER BY c.id ASC";

$result = mysqli_query($conn, $query);

if (!$result) {
    sendJsonResponse('error', 'Gagal mengeksekusi kueri database: ' . mysqli_error($conn), null, 500);
}

$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = [
        'id'           => (int)$row['id'],
        'nama_kategori'=> $row['name'],
        'slug'         => $row['slug'],
        'deskripsi'    => $row['description'],
        'icon'         => $row['icon'],
        'total_produk' => (int)$row['total_produk'],
        'created_at'   => $row['created_at']
    ];
}

$total = count($categories);

http_response_code(200);
echo json_encode([
    'status'  => 'success',
    'code'    => 200,
    'message' => "Berhasil memuat {$total} data kategori.",
    'total'   => $total,
    'data'    => $categories
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
exit;

