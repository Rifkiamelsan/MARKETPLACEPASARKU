# 🧺 PasarKu - Aplikasi Web Marketplace Tradisional

[![PHP Version](https://img.shields.io/badge/PHP-8.0%20%7C%208.1%20%7C%208.2-blue?logo=php)](https://www.php.net/)
[![Database](https://img.shields.io/badge/Database-MySQL%20%2F%20MariaDB-orange?logo=mysql)](https://www.mysql.com/)
[![Frontend](https://img.shields.io/badge/CSS-Bootstrap%205.3-purple?logo=bootstrap)](https://getbootstrap.com/)
[![Hosting Ready](https://img.shields.io/badge/Deployment-InfinityFree%20Ready-brightgreen)](https://infinityfree.com/)

> **PasarKu** adalah aplikasi web marketplace tradisional yang menghubungkan para pedagang pasar tradisional, petani lokal, dan perajin nusantara langsung dengan para pembeli. Dibangun menggunakan **PHP Native**, **MySQLi (Prepared Statements)**, dan **Bootstrap 5.3**.

---

## 🌟 Fitur Utama Aplikasi

- 🔍 **Katalog & Jelajah Produk**:
  - Filter interaktif berdasarkan kategori komoditas (*Sayur & Buah Segar, Bumbu & Rempah, Sembako, Kuliner Tradisional, Kerajinan Tangan*).
  - Pencarian real-time berdasarkan nama produk dan deskripsi.
  - Pengurutan (*sorting*) berdasarkan harga terendah, harga tertinggi, dan produk terbaru.
  - Tampilan informasi stok produk (*Tersedia, Sisa Sedikit, Habis*).

- 📦 **Detail Produk Pasar**:
  - Tampilan foto produk berkualitas, deskripsi kesegaran, nama kios penjual terverifikasi, serta estimasi ulasan.
  - Form interaktif penentu kuantitas pembelian dengan validasi batas stok maksimal.
  - Rekomendasi produk serupa dalam kategori yang sama.

- 🛒 **Keranjang Belanja (Shopping Cart)**:
  - Berbasis Session PHP Native (cepat, persisten, dan aman).
  - Update jumlah beli dengan spinner (+/-) otomatis.
  - Penghapusan item individual atau pengosongan seluruh keranjang.
  - Counter badge keranjang interaktif pada navigasi utama.

- 💳 **Pemesanan & Transaksi Database (Checkout)**:
  - Form pengiriman lengkap (Nama Penerima, No WhatsApp/HP, Alamat Lengkap, Catatan Pengiriman).
  - Pilihan metode pembayaran: **Bayar di Tempat (COD)** dan **Transfer Bank**.
  - **Integritas ACID Database Transaction**: Validasi stok ganda sebelum pemesanan disimpan ke tabel `orders` & `order_details`, serta pemotongan kuantitas stok produk otomatis.
  - Pembuatan nomor faktur unik (`TRAD-YYYYMMDD-XXXX`).
  - Halaman Nota / Bukti Pemesanan yang **Print-Ready** (dapat langsung dicetak ke printer atau disimpan sebagai PDF).

- 🛠️ **Manajemen Produk Penjual (CRUD Penuh)**:
  - **Create**: Tambah komoditas baru lengkap dengan upload file gambar (JPG, PNG, WEBP, SVG max 2MB).
  - **Read**: Tabel data produk kios dengan filter pencarian dan badge status stok.
  - **Update**: Edit rincian komoditas, ubah harga, sesuaikan stok, atau ganti foto produk.
  - **Delete**: Hapus produk secara aman (dilengkapi konfirmasi dan pembersihan file gambar fisik dari server).

- 📋 **Pengelolaan Pesanan Masuk Penjual**:
  - Pedagang dapat melihat pesanan masuk khusus untuk barang miliknya.
  - Pembaruan status pesanan: *Pending &rarr; Processing &rarr; Completed &rarr; Cancelled*.

- 🔐 **Autentikasi & Akun Demo 1-Klik**:
  - Registrasi akun baru (*Pembeli* atau *Penjual*).
  - Login dengan enkripsi password Bcrypt (`password_hash`).
  - Tombol **1-Click Quick Demo Login** untuk memudahkan penguji/dosen mengevaluasi aplikasi tanpa harus mengisi formulir manual.

---

## 🏗️ Struktur Basis Data (5 Entitas Relasional)

Aplikasi ini menggunakan 5 tabel relasional:
1. `users` : Mengelola akun pedagang (*seller*), pembeli (*buyer*), dan administrator.
2. `categories` : Pengelompokan komoditas pasar tradisional.
3. `products` : Katalog dagangan pedagang (berelasi ke `users` dan `categories`).
4. `orders` : Transaksi invoice pemesanan (berelasi ke `users`).
5. `order_details` : Item belanja dalam pesanan (berelasi ke `orders` dan `products`).

---

## 🚀 Panduan Instalasi Lokal (XAMPP)

1. Pastikan Anda memiliki **XAMPP** dengan PHP versi 8.0 atau yang lebih baru.
2. Tempatkan folder proyek ini ke dalam direktori `htdocs`, misalnya:
   ```
   D:\XAMPP\htdocs\tugas hary marketplace
   ```
   *(atau `C:\xampp\htdocs\tugas hary marketplace`)*
3. Jalankan **Apache** dan **MySQL** melalui XAMPP Control Panel.
4. Buka phpMyAdmin di browser: [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
5. Buat database baru dengan nama: `marketplace_db`.
6. Klik tab **Import**, pilih file: `database/marketplace_db.sql`, lalu klik **Import / Kirim**.
7. Buka aplikasi melalui web browser:
   ```
   http://localhost/tugas%20hary%20marketplace/
   ```

---

## 🌐 Panduan Deployment ke InfinityFree (Hosting Gratis)

1. **Registrasi Akun Hosting**:
   - Kunjungi [https://infinityfree.com](https://infinityfree.com) dan buat akun gratis.
   - Buat hosting account baru dan pilih subdomain (misalnya: `pasarku.kesug.com`).
2. **Setup Database MySQL**:
   - Di vPanel (cPanel InfinityFree), masuk ke menu **MySQL Databases**.
   - Buat database baru (misal: `if0_XXXXXXXX_marketplace_db`).
   - Catat: **MySQL Host**, **Username**, **Password vPanel**, dan **Database Name**.
3. **Import Database**:
   - Buka **phpMyAdmin** melalui vPanel pada database yang dibuat.
   - Import file `database/marketplace_db.sql`.
4. **Upload File ke Server**:
   - Buka **Online File Manager** atau gunakan FileZilla FTP.
   - Masuk ke folder `/htdocs/`.
   - Unggah seluruh file dan folder aplikasi PasarKu ke dalam `/htdocs/`.
5. **Konfigurasi Database Hosting**:
   - Di folder `config/`, buat file baru bernama `db_config.php` (bisa menduplikasi dari `config/db_config.example.php`).
   - Masukkan kredensial MySQL InfinityFree Anda:
     ```php
     <?php
     return [
         'DB_HOST' => 'sqlXXX.infinityfree.com',
         'DB_USER' => 'if0_XXXXXXXX',
         'DB_PASS' => 'PASSWORD_VPANEL_ANDA',
         'DB_NAME' => 'if0_XXXXXXXX_marketplace_db',
         'DB_PORT' => 3306,
     ];
     ```
6. Buka subdomain yang Anda daftarkan di browser. Aplikasi siap digunakan secara daring!

---

## 🔑 Akun Demo Pengujian

Untuk mempermudah proses evaluasi dan pengujian sistem:

| Peran (Role) | Username | Password | Akses Pengujian |
|---|---|---|---|
| **Penjual (Seller)** | `pak_bejo` | `password123` | Manajemen Produk (CRUD) & Pesanan Masuk |
| **Penjual (Seller 2)** | `bu_siti` | `password123` | Toko Dapur & Jajanan Tradisional |
| **Pembeli (Buyer)** | `budi_santoso` | `password123` | Menjelajah Produk, Keranjang, Checkout Nota |

> **Tips Evaluasi**: Di halaman [Login](auth/login.php) terdapat tombol pintas **Mode Cepat Pengujian (1-Klik Demo)** yang langsung mengautentikasi akun tanpa mengetik kata sandi!

---

## 🔒 Keamanan Sistem

- File `config/db_config.php` telah dimasukkan ke dalam `.gitignore` sehingga informasi rahasia kredensial database hosting tidak akan bocor ke repositori publik GitHub.
- Seluruh query parameter masukan pengguna menggunakan **Prepared Statements (`mysqli_prepare`)** untuk mencegah celah keamanan *SQL Injection*.
- Data output dibersihkan dengan `htmlspecialchars()` untuk melindungi dari *Cross-Site Scripting (XSS)*.

---

## 📄 Lisensi & Kontributor

Aplikasi ini dikembangkan untuk pemenuhan tugas mata kuliah Pemrograman Web dan Basis Data.
Dibuat dengan ❤️ untuk mendukung kemajuan UMKM dan Pedagang Pasar Tradisional Indonesia.

