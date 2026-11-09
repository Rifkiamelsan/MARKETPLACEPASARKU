<?php
/**
 * ===================================================================
 * KONEKSI DAN KONFIGURASI REST API MARKETPLACE ("PasarKu")
 * Basis Data: MySQLi (Kompatibel dengan XAMPP & Server Hosting)
 * ===================================================================
 */

// 1. Set Header HTTP untuk Output JSON & CORS (Cross-Origin Resource Sharing)
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Tangani HTTP Preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Baca Konfigurasi Database
$configFile = dirname(__DIR__) . '/config/db_config.php';
if (file_exists($configFile)) {
    $customConfig = include $configFile;
    $db_host = $customConfig['DB_HOST'] ?? 'localhost';
    $db_user = $customConfig['DB_USER'] ?? 'root';
    $db_pass = $customConfig['DB_PASS'] ?? '';
    $db_name = $customConfig['DB_NAME'] ?? 'marketplace_db';
    $db_port = (int)($customConfig['DB_PORT'] ?? 3306);
} else {
    $db_host = getenv('DB_HOST') ?: 'localhost';
    $db_user = getenv('DB_USER') ?: 'root';
    $db_pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
    $db_name = getenv('DB_NAME') ?: 'marketplace_db';
    $db_port = (int)(getenv('DB_PORT') ?: 3306);
}

// 3. Buat Koneksi MySQLi
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'code'    => 500,
        'message' => 'Gagal terhubung ke database server: ' . mysqli_connect_error()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Set Charset ke UTF-8
mysqli_set_charset($conn, 'utf8mb4');

/**
 * Fungsi pembantu mengirim format respons JSON standar
 *
 * @param string $status    'success' | 'error' | 'fail'
 * @param string $message   Pesan keterangan respons
 * @param mixed  $data      Data payload (array / object / null)
 * @param int    $httpCode  HTTP Status Code (200, 201, 400, 404, 500, dsb)
 */
function sendJsonResponse($status, $message, $data = null, $httpCode = 200) {
    http_response_code($httpCode);
    $response = [
        'status'  => $status,
        'code'    => $httpCode,
        'message' => $message
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Fungsi pembantu membaca payload JSON dari request body
 *
 * @return array
 */
function getJsonInput() {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $decoded = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST ?: [];
}

