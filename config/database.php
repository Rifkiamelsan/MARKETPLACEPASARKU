<?php
/**
 * Konfigurasi Koneksi Database MySQLi
 * Mendukung deteksi otomatis:
 * 1. File config kustom (config/db_config.php) - ideal untuk InfinityFree/Hosting
 * 2. Environment variables (DB_HOST, DB_USER, DB_PASS, DB_NAME)
 * 3. Default Localhost XAMPP (localhost, root, '', marketplace_db)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Cek file konfigurasi khusus jika ada (misal untuk InfinityFree)
$configFile = __DIR__ . '/db_config.php';
if (file_exists($configFile)) {
    $customConfig = include $configFile;
    $db_host = $customConfig['DB_HOST'] ?? 'localhost';
    $db_user = $customConfig['DB_USER'] ?? 'root';
    $db_pass = $customConfig['DB_PASS'] ?? '';
    $db_name = $customConfig['DB_NAME'] ?? 'marketplace_db';
    $db_port = $customConfig['DB_PORT'] ?? 3306;
} else {
    // 2. Default XAMPP / Environment
    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $db_name = getenv('DB_NAME') ?: 'marketplace_db';
    $db_port = getenv('DB_PORT') ?: 3306;
}

// Buat koneksi MySQLi
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, (int)$db_port);

if (!$conn) {
    // Tampilan pesan error yang ramah dan solutif
    $error_message = mysqli_connect_error();
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Koneksi Database Gagal - PasarKu</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
    <body class="bg-light py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-danger mb-3">
                                <i class="bi bi-database-exclamation display-3"></i>
                            </div>
                            <h3 class="fw-bold text-danger">Koneksi Database Gagal!</h3>
                            <p class="text-secondary">Aplikasi tidak dapat terhubung ke server database MySQL.</p>
                            
                            <div class="alert alert-danger font-monospace small">
                                <strong>Detail Error:</strong> <?php echo htmlspecialchars($error_message); ?>
                            </div>

                            <div class="card bg-body-tertiary border-0 rounded-3 p-3 mb-4">
                                <h6 class="fw-bold mb-2"><i class="bi bi-lightbulb text-warning me-1"></i> Panduan Penyelesaian:</h6>
                                <ol class="small text-secondary mb-0 ps-3">
                                    <li class="mb-1"><strong>Jika menjalankan di XAMPP Lokal:</strong> Pastikan Apache dan MySQL sudah diaktifkan di XAMPP Control Panel. Buka <code>http://localhost/phpmyadmin</code>, buat database bernama <code>marketplace_db</code>, lalu import file <code>database/marketplace_db.sql</code>.</li>
                                    <li class="mb-1"><strong>Jika menjalankan di InfinityFree:</strong> Salin file <code>config/db_config.example.php</code> menjadi <code>config/db_config.php</code>, lalu isi host, user, password, dan nama database yang tertera di Control Panel InfinityFree.</li>
                                </ol>
                            </div>

                            <a href="javascript:location.reload();" class="btn btn-success rounded-pill px-4">
                                <i class="bi bi-arrow-clockwise me-1"></i> Coba Muat Ulang
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Set charset utf8mb4 agar mendukung karakter luas & emoji
mysqli_set_charset($conn, 'utf8mb4');
