<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$cart_count = get_cart_count();
$user = current_user();

// Ambil kategori untuk navigasi dropdown
$nav_categories_query = "SELECT id, name, slug, icon FROM categories ORDER BY name ASC";
$nav_categories_result = mysqli_query($conn, $nav_categories_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>PasarKu | Marketplace Tradisional Nusantara</title>
    
    <!-- Google Fonts Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
</head>
<body>

<!-- Top Mini Bar -->
<div class="bg-success text-white py-1 small d-none d-md-block" style="background-color: #146c43 !important;">
    <div class="container d-flex justify-content-between align-items-center">
        <div>
            <i class="bi bi-geo-alt-fill me-1"></i> Pasar Beringharjo, Johar & Petani Lokal Nusantara
            <span class="mx-2">•</span>
            <i class="bi bi-shield-check me-1"></i> 100% Produk Segar & Asli Tradisional
        </div>
        <div class="d-flex align-items-center gap-3">
            <span>Dukungan UMKM & Hasil Bumi Indonesia</span>
            <?php if (!is_logged_in()): ?>
                <a href="<?php echo base_url('auth/login.php'); ?>" class="text-white text-decoration-none fw-semibold">
                    <i class="bi bi-person me-1"></i> Mode Demo
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Main Navbar -->
<nav class="navbar navbar-expand-lg navbar-pasarku sticky-top py-2">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand brand-logo" href="<?php echo base_url('index.php'); ?>">
            <span class="fs-3">🧺</span>
            <span>Pasar<span class="text-warning">Ku</span></span>
            <span class="brand-badge d-none d-sm-inline">Tradisional</span>
        </a>

        <!-- Mobile toggler & cart button -->
        <div class="d-flex align-items-center gap-2 d-lg-none">
            <a href="<?php echo base_url('cart.php'); ?>" class="btn btn-outline-success position-relative rounded-pill px-3 py-1">
                <i class="bi bi-cart3"></i>
                <?php if ($cart_count > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo $cart_count; ?>
                    </span>
                <?php endif; ?>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- Navbar Menu & Search -->
        <div class="collapse navbar-collapse" id="navbarMain">
            <!-- Search Form -->
            <form class="d-flex mx-lg-4 my-2 my-lg-0 flex-grow-1" action="<?php echo base_url('index.php'); ?>" method="GET" style="max-width: 480px;">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted ps-3">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control bg-light border-start-0 rounded-end-pill py-2" 
                           placeholder="Cari cabai, bawang, beras, tempe..." 
                           value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
            </form>

            <!-- Links -->
            <ul class="navbar-nav ms-auto align-items-lg-center gap-1 gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link fw-medium text-dark" href="<?php echo base_url('index.php'); ?>">
                        <i class="bi bi-grid me-1 text-success"></i> Katalog Produk
                    </a>
                </li>

                <!-- Dropdown Kategori -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle fw-medium text-dark" href="#" role="button" data-bs-toggle="dropdown">
                        Kategori
                    </a>
                    <ul class="dropdown-menu border-0 shadow-sm rounded-3 py-2">
                        <li>
                            <a class="dropdown-item py-2" href="<?php echo base_url('index.php'); ?>">
                                <i class="bi bi-list-stars me-2 text-success"></i> Semua Kategori
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <?php 
                        if ($nav_categories_result && mysqli_num_rows($nav_categories_result) > 0) {
                            mysqli_data_seek($nav_categories_result, 0);
                            while ($cat = mysqli_fetch_assoc($nav_categories_result)) {
                                echo '<li>
                                        <a class="dropdown-item py-2" href="' . base_url('index.php?category=' . $cat['slug']) . '">
                                            <i class="bi ' . htmlspecialchars($cat['icon']) . ' me-2 text-success"></i> ' . htmlspecialchars($cat['name']) . '
                                        </a>
                                      </li>';
                            }
                        }
                        ?>
                    </ul>
                </li>

                <!-- Tombol Keranjang Belanja (Desktop) -->
                <li class="nav-item d-none d-lg-block me-2">
                    <a href="<?php echo base_url('cart.php'); ?>" class="btn btn-outline-success position-relative rounded-pill px-3 py-2 fw-medium d-flex align-items-center gap-2">
                        <i class="bi bi-cart3 fs-5"></i>
                        <span>Keranjang</span>
                        <?php if ($cart_count > 0): ?>
                            <span class="badge rounded-pill bg-danger">
                                <?php echo $cart_count; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>

                <!-- Auth Navigation -->
                <?php if (is_logged_in()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-dark fw-medium" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 0.85rem; font-weight: bold;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                            <span class="d-inline-block text-truncate" style="max-width: 130px;"><?php echo htmlspecialchars($user['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3 py-2" style="min-width: 220px;">
                            <li class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark text-truncate"><?php echo htmlspecialchars($user['name']); ?></div>
                                <span class="badge <?php echo $user['role'] === 'seller' ? 'bg-primary' : ($user['role'] === 'admin' ? 'bg-danger' : 'bg-secondary'); ?>">
                                    <?php echo ucfirst($user['role'] === 'seller' ? 'Penjual Pasar' : ($user['role'] === 'admin' ? 'Administrator' : 'Pembeli')); ?>
                                </span>
                            </li>

                            <?php if (is_seller()): ?>
                                <li>
                                    <a class="dropdown-item py-2 text-success fw-semibold" href="<?php echo base_url('admin/products.php'); ?>">
                                        <i class="bi bi-box-seam me-2"></i> Kelola Produk (CRUD)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?php echo base_url('admin/product_add.php'); ?>">
                                        <i class="bi bi-plus-circle me-2"></i> Tambah Produk Baru
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="<?php echo base_url('admin/orders.php'); ?>">
                                        <i class="bi bi-receipt me-2"></i> Pesanan Masuk Penjual
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>

                            <li>
                                <a class="dropdown-item py-2" href="<?php echo base_url('my_orders.php'); ?>">
                                    <i class="bi bi-bag-check me-2 text-primary"></i> Riwayat Pesanan Saya
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="<?php echo base_url('auth/logout.php'); ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-light rounded-pill px-3 py-2 text-dark fw-medium" href="<?php echo base_url('auth/login.php'); ?>">
                            Masuk
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-success rounded-pill px-3 py-2 fw-medium text-white" href="<?php echo base_url('auth/register.php'); ?>">
                            Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Container -->
<main class="py-4">
    <div class="container">
        <?php display_flash(); ?>
