<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REST API Documentation - PasarKu Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .method-badge { font-weight: 700; font-size: 0.85rem; padding: 0.35rem 0.65rem; border-radius: 6px; }
        .method-get { background-color: #e0f2fe; color: #0369a1; }
        .method-post { background-color: #dcfce7; color: #15803d; }
        pre code { font-size: 0.85rem; }
        .card-endpoint { border-left: 4px solid #198754; transition: transform 0.15s; }
        .card-endpoint:hover { transform: translateY(-2px); }
    </style>
</head>
<body class="py-4">
    <div class="container">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
            <div>
                <span class="badge bg-success mb-2 px-3 py-2"><i class="bi bi-code-slash me-1"></i> Web Service / REST API</span>
                <h2 class="fw-bold text-dark mb-1">Dokumentasi REST API - PasarKu</h2>
                <p class="text-muted mb-0">Implementasi API Marketplace Tradisional menggunakan PHP Native & MySQLi (Soal 12)</p>
            </div>
            <div>
                <a href="../index.php" class="btn btn-outline-secondary rounded-pill me-2">
                    <i class="bi bi-house me-1"></i> Web Marketplace
                </a>
                <a href="../database/marketplace_db.sql" download class="btn btn-primary rounded-pill">
                    <i class="bi bi-download me-1"></i> Unduh SQL
                </a>
            </div>
        </div>

        <!-- Info Box -->
        <div class="alert alert-info border-0 shadow-sm rounded-4 mb-4">
            <div class="d-flex">
                <i class="bi bi-info-circle-fill fs-4 me-3 text-primary"></i>
                <div>
                    <h6 class="fw-bold mb-1">Informasi Base URL:</h6>
                    <code class="text-primary bg-white px-2 py-1 rounded">http://localhost/MARKETPLACE/api/</code>
                    <p class="small text-muted mb-0 mt-2">Semua response dikembalikan dalam format <strong>JSON</strong> standar dengan HTTP Status Code yang sesuai (200, 201, 400, 404, 405, 500).</p>
                </div>
            </div>
        </div>

        <!-- Daftar Endpoint -->
        <h4 class="fw-bold mb-3"><i class="bi bi-hdd-network me-2 text-success"></i>Daftar Endpoint API</h4>

        <!-- Endpoint 1 -->
        <div class="card card-endpoint border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="method-badge method-get">GET</span>
                        <code class="fs-6 fw-bold text-dark">/api/produk.php</code>
                    </div>
                    <a href="produk.php" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Uji di Browser
                    </a>
                </div>
                <p class="text-secondary mb-2">Mengambil seluruh katalog produk yang tersedia di marketplace (mendukung filter pencarian dan kategori).</p>
                <div class="small text-muted mb-3">
                    <strong>Parameter Query (Opsional):</strong> <code>?q={keyword}</code>, <code>?kategori={id_or_slug}</code>, <code>?limit={angka}</code>
                </div>
                <div class="bg-dark text-light p-3 rounded-3">
                    <div class="small text-secondary mb-1">Contoh Response (HTTP 200 OK):</div>
<pre class="mb-0 text-info"><code>{
  "status": "success",
  "code": 200,
  "message": "Berhasil memuat 12 data produk.",
  "total": 12,
  "data": [
    {
      "id": 1,
      "nama_produk": "Beras Rojolele Organik",
      "slug": "beras-rojolele-organik",
      "kategori": "Beras & Biji-bijian",
      "harga": 65000,
      "harga_format": "Rp 65.000",
      "stok": 50,
      "satuan": "Karung 5kg",
      "penjual": "Pak Bejo (Toko Tani Makmur)",
      "gambar": "beras.png"
    }
  ]
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Endpoint 2 -->
        <div class="card card-endpoint border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="method-badge method-get">GET</span>
                        <code class="fs-6 fw-bold text-dark">/api/produk.php?id={id}</code>
                    </div>
                    <a href="produk.php?id=1" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Uji Detail (ID=1)
                    </a>
                </div>
                <p class="text-secondary mb-2">Mengambil informasi detail satu produk lengkap beserta relasi kategori dan identitas penjual.</p>
                <div class="bg-dark text-light p-3 rounded-3">
                    <div class="small text-secondary mb-1">Contoh Response (HTTP 200 OK):</div>
<pre class="mb-0 text-info"><code>{
  "status": "success",
  "code": 200,
  "message": "Berhasil mendapatkan detail produk.",
  "data": {
    "id": 1,
    "nama_produk": "Beras Rojolele Organik",
    "slug": "beras-rojolele-organik",
    "deskripsi": "Beras rojolele asli Klaten, pulen, wangi dan tanpa pengawet...",
    "harga": 65000,
    "harga_format": "Rp 65.000",
    "stok": 50,
    "satuan": "Karung 5kg",
    "status_stok": "Tersedia",
    "kategori": { "id": 1, "nama": "Beras & Biji-bijian" },
    "penjual": { "nama": "Pak Bejo", "telepon": "081234567890" }
  }
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Endpoint 3 -->
        <div class="card card-endpoint border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="method-badge method-get">GET</span>
                        <code class="fs-6 fw-bold text-dark">/api/kategori.php</code>
                    </div>
                    <a href="kategori.php" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Uji di Browser
                    </a>
                </div>
                <p class="text-secondary mb-2">Mengambil seluruh kategori produk beserta jumlah produk yang tersedia di tiap kategori.</p>
                <div class="bg-dark text-light p-3 rounded-3">
                    <div class="small text-secondary mb-1">Contoh Response (HTTP 200 OK):</div>
<pre class="mb-0 text-info"><code>{
  "status": "success",
  "code": 200,
  "message": "Berhasil memuat 6 data kategori.",
  "total": 6,
  "data": [
    {
      "id": 1,
      "nama_kategori": "Beras & Biji-bijian",
      "slug": "beras-biji-bijian",
      "icon": "bi-basket2",
      "total_produk": 2
    }
  ]
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Endpoint 4 -->
        <div class="card card-endpoint border-0 shadow-sm rounded-4 mb-3" style="border-left-color: #198754;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="method-badge method-post">POST</span>
                        <code class="fs-6 fw-bold text-dark">/api/pesanan.php</code>
                    </div>
                    <span class="badge bg-secondary">Uji via Postman</span>
                </div>
                <p class="text-secondary mb-2">Membuat transaksi pesanan baru. Menerima payload JSON dan secara otomatis memverifikasi ketersediaan stok, menghitung total bayar, mengunci transaksi (ACID), serta mengurangi stok produk.</p>
                
                <div class="mb-2"><strong>Headers:</strong> <code>Content-Type: application/json</code></div>
                
                <div class="bg-dark text-light p-3 rounded-3 mb-3">
                    <div class="small text-secondary mb-1">Contoh Request Body (JSON):</div>
<pre class="mb-0 text-warning"><code>{
  "recipient_name": "Siti Rahma",
  "recipient_phone": "081298765432",
  "shipping_address": "Jl. Kaliurang KM 5 No. 12, Sleman, Yogyakarta",
  "payment_method": "COD (Bayar di Tempat)",
  "notes": "Tolong kirim sore hari",
  "buyer_id": 3,
  "items": [
    {
      "product_id": 1,
      "quantity": 2
    },
    {
      "product_id": 3,
      "quantity": 1
    }
  ]
}</code></pre>
                </div>

                <div class="bg-dark text-light p-3 rounded-3">
                    <div class="small text-secondary mb-1">Contoh Response Sukses (HTTP 201 Created):</div>
<pre class="mb-0 text-success"><code>{
  "status": "success",
  "code": 201,
  "message": "Pesanan baru berhasil dibuat.",
  "data": {
    "order_id": 5,
    "order_code": "TRAD-20261109-A4F2",
    "recipient_name": "Siti Rahma",
    "total_amount": 165000,
    "total_formatted": "Rp 165.000",
    "status": "pending",
    "total_items": 2
  }
}</code></pre>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-muted small py-4 border-top mt-5">
            &copy; 2026 PasarKu Marketplace REST API Service &bull; Soal 12 Integrasi Data Marketplace
        </div>
    </div>
</body>
</html>

