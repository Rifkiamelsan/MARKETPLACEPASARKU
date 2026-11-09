<?php
/**
 * Contoh Konfigurasi Database Kustom (Khusus Hosting InfinityFree / Server Eksternal)
 * 
 * CARA MENGGUNAKAN UNTUK DEPLOYMENT INFINITYFREE:
 * 1. Duplikat/ganti nama file ini menjadi `db_config.php` di dalam folder `config/`.
 * 2. Sesuaikan nilai kredensial di bawah dengan informasi MySQL dari InfinityFree cPanel.
 * 3. File `db_config.php` sudah masuk dalam .gitignore agar kredensial Anda tidak bocor ke GitHub.
 */

return [
    // Contoh Host MySQL InfinityFree: sql123.infinityfree.com atau sql200.epizy.com
    'DB_HOST' => 'sqlXXX.infinityfree.com', 
    
    // Contoh Username MySQL InfinityFree: if0_12345678
    'DB_USER' => 'if0_XXXXXXXX',
    
    // Password akun vPanel / Hosting InfinityFree Anda
    'DB_PASS' => 'KATA_SANDI_ANDA',
    
    // Nama Database yang dibuat di vPanel InfinityFree: if0_XXXXXXXX_marketplace_db
    'DB_NAME' => 'if0_XXXXXXXX_marketplace_db',
    
    // Port standar MySQL
    'DB_PORT' => 3306,
];
