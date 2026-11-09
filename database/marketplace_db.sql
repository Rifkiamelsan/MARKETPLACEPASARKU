-- ===================================================================
-- SQL DDL & SEED DATA UNTUK MARKETPLACE TRADISIONAL ("PasarKu")
-- Basis Data: MySQL / MariaDB (Kompatibel dengan XAMPP & InfinityFree)
-- ===================================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `order_details`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- -------------------------------------------------------------
-- 1. Tabel: users (Pengguna: Penjual, Pembeli, Admin)
-- -------------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(25) NOT NULL,
  `address` TEXT,
  `role` ENUM('seller', 'buyer', 'admin') NOT NULL DEFAULT 'buyer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- 2. Tabel: categories (Kategori Produk Tradisional)
-- -------------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `icon` VARCHAR(50) DEFAULT 'bi-tag',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- 3. Tabel: products (Katalog Produk Pasar Tradisional)
-- -------------------------------------------------------------
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `seller_id` INT NOT NULL,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(150) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `stock` INT NOT NULL DEFAULT 0,
  `unit` VARCHAR(30) NOT NULL DEFAULT 'Pcs',
  `image` VARCHAR(255) DEFAULT 'default.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- 4. Tabel: orders (Transaksi Pemesanan)
-- -------------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_code` VARCHAR(50) NOT NULL UNIQUE,
  `buyer_id` INT NOT NULL,
  `recipient_name` VARCHAR(100) NOT NULL,
  `recipient_phone` VARCHAR(25) NOT NULL,
  `shipping_address` TEXT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Transfer Bank / COD',
  `total_amount` DECIMAL(12,2) NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- 5. Tabel: order_details (Detail Item Transaksi Pemesanan)
-- -------------------------------------------------------------
CREATE TABLE `order_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `subtotal` DECIMAL(12,2) NOT NULL,
  CONSTRAINT `fk_details_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_details_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================
-- DATA AWAL (SEEDERS DUMMY DATA)
-- Password untuk semua akun default: password123
-- =============================================================

INSERT INTO `users` (`id`, `username`, `password`, `name`, `email`, `phone`, `address`, `role`) VALUES
(1, 'pak_bejo', '$2y$10$L8F5OzqoLzccNPnLeIJ4pOxD7l1EBnN4G8g0qAgP/P69/UML7at/i', 'Pak Bejo (Toko Tani Makmur)', 'bejo@pasarku.id', '081234567890', 'Kios Blok A No. 12, Pasar Beringharjo, Yogyakarta', 'seller'),
(2, 'bu_siti', '$2y$10$L8F5OzqoLzccNPnLeIJ4pOxD7l1EBnN4G8g0qAgP/P69/UML7at/i', 'Bu Siti (Dapur Tradisional)', 'siti@pasarku.id', '081298765432', 'Kios Kuliner Blok C No. 05, Pasar Johar, Semarang', 'seller'),
(3, 'budi_santoso', '$2y$10$L8F5OzqoLzccNPnLeIJ4pOxD7l1EBnN4G8g0qAgP/P69/UML7at/i', 'Budi Santoso', 'budi@gmail.com', '085612345678', 'Jl. Malioboro No. 45, Danurejan, Yogyakarta', 'buyer'),
(4, 'ratna_dewi', '$2y$10$L8F5OzqoLzccNPnLeIJ4pOxD7l1EBnN4G8g0qAgP/P69/UML7at/i', 'Ratna Dewi', 'ratna@gmail.com', '087812345678', 'Jl. Pandanaran No. 88, Semarang Selatan', 'buyer');

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Sayur & Buah Segar', 'sayur-buah', 'Hasil panen segar langsung dari petani lokal', 'bi-basket2'),
(2, 'Bumbu & Rempah', 'bumbu-rempah', 'Bumbu dapur pilihan khas nusantara berkualitas', 'bi-flower1'),
(3, 'Sembako & Hasil Bumi', 'sembako', 'Beras, gula, minyak kelapa tradisional, dan palawija', 'bi-shop'),
(4, 'Kuliner Tradisional', 'kuliner-tradisional', 'Jajanan pasar, kue basah, dan olahan makanan khas', 'bi-egg-fried'),
(5, 'Kerajinan Tangan', 'kerajinan-tangan', 'Anyaman bambu, cobek batu kali, dan perlengkapan dapur tradisional', 'bi-brush');

INSERT INTO `products` (`id`, `seller_id`, `category_id`, `name`, `slug`, `description`, `price`, `stock`, `unit`, `image`) VALUES
(1, 1, 1, 'Bawang Merah Brebes Pilihan', 'bawang-merah-brebes-pilihan', 'Bawang merah super asli Brebes, kering sempurna, aroma kuat dan tahan disimpan lama.', 32000.00, 45, 'Kg', 'bawang-merah.jpg'),
(2, 1, 1, 'Cabai Rawit Merah Segar (Petik Pagi)', 'cabai-rawit-merah-segar', 'Cabai rawit merah pedas mantap dipetik pagi hari dari lereng Gunung Merapi. Tanpa bahan pengawet.', 45000.00, 30, 'Kg', 'cabai-rawit.jpg'),
(3, 1, 3, 'Beras Pandan Wangi Cianjur 5 Kg', 'beras-pandan-wangi-cianjur-5kg', 'Beras asli bermutu tinggi dengan aroma pandan alami, pulen, wangi dan bersih tanpa pemutih sintetis.', 78000.00, 25, 'Karung (5 Kg)', 'beras-pandan.jpg'),
(4, 1, 2, 'Gula Aren Asli Organik Cetak Batok', 'gula-aren-asli-organik', 'Gula aren murni dibuat dari nira aren pegunungan, rasa legit alami tanpa campuran gula pasir biasa.', 28000.00, 50, 'Kg', 'gula-aren.jpg'),
(5, 2, 4, 'Tempe Mendoan Mentah Daun Pisang (Isi 10)', 'tempe-mendoan-mentah-daun-pisang', 'Tempe kedelai bungkus daun pisang asli Purwokerto, gurih alami siap digoreng mendoan dengan kecap pedas.', 15000.00, 40, 'Bungkus', 'tempe-daun.jpg'),
(6, 2, 4, 'Lemper Ayam Bakar Gurih Tradisional', 'lemper-ayam-bakar-gurih', 'Ketan pulen dibalut daun pisang dengan isian suwiran daging ayam bumbu gurih melimpah. Isi 5 pcs.', 20000.00, 35, 'Paket (5 Pcs)', 'lemper-ayam.jpg'),
(7, 2, 2, 'Wedang Uwuh Imogiri Asli Komplit', 'wedang-uwuh-imogiri-komplit', 'Minuman herbal tradisional racikan rempah secang, jahe, kayu manis, pala, cengkeh dan gula batu. Hangat dan menyehatkan.', 25000.00, 60, 'Pack (10 Sachet)', 'wedang-uwuh.jpg'),
(8, 1, 5, 'Tenggok / Keranjang Anyaman Bambu', 'tenggok-keranjang-anyaman-bambu', 'Keranjang belanja tradisional ramah lingkungan dari anyaman bambu kokoh pengrajin lokal Bantul.', 35000.00, 20, 'Buah', 'keranjang-bambu.jpg'),
(9, 1, 5, 'Cobek & Ulekan Batu Kali Merapi Asli 20cm', 'cobek-ulekan-batu-kali-20cm', 'Cobek batu kali asli gunung tanpa campuran semen, tidak mudah rontok dan awet puluhan tahun.', 65000.00, 15, 'Set', 'cobek-batu.jpg'),
(10, 1, 1, 'Sayur Bayam & Kangkung Petani Lokal', 'sayur-bayam-kangkung-petani-lokal', 'Sayuran hijau segar bebas pestisida kimia berlebih, dipanen langsung dari kebun rakyat.', 6000.00, 50, '2 Ikat', 'sayur-bayam.jpg');

INSERT INTO `orders` (`id`, `order_code`, `buyer_id`, `recipient_name`, `recipient_phone`, `shipping_address`, `payment_method`, `total_amount`, `status`, `notes`, `created_at`) VALUES
(1, 'TRAD-20261108-001', 3, 'Budi Santoso', '085612345678', 'Jl. Malioboro No. 45 RT 02 RW 01, Kel. Sosromenduran, Kec. Gedongtengen, Kota Yogyakarta 55271', 'Transfer Bank BCA', 142000.00, 'processing', 'Mohon dikirim pagi sebelum jam 10 ya pak.', '2026-11-08 09:15:00'),
(2, 'TRAD-20261108-002', 4, 'Ratna Dewi', '087812345678', 'Jl. Pandanaran No. 88, Mugassari, Kec. Semarang Selatan, Kota Semarang 50249', 'COD (Bayar di Tempat)', 65000.00, 'completed', 'Ditunggu kurirnya.', '2026-11-08 14:30:00');

INSERT INTO `order_details` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `subtotal`) VALUES
(1, 1, 1, 2, 32000.00, 64000.00),
(2, 1, 3, 1, 78000.00, 78000.00),
(3, 2, 9, 1, 65000.00, 65000.00);
