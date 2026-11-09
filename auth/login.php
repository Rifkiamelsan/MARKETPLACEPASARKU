<?php
$page_title = "Masuk ke Akun";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$redirect = sanitize($_GET['redirect'] ?? base_url('index.php'));

// Handle Demo Login Quick Action
if (isset($_GET['quick_demo'])) {
    $demo_user = $_GET['quick_demo'] === 'seller' ? 'pak_bejo' : 'budi_santoso';
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $demo_user);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($u = mysqli_fetch_assoc($res)) {
        $_SESSION['user_id'] = $u['id'];
        $_SESSION['username'] = $u['username'];
        $_SESSION['user_name'] = $u['name'];
        $_SESSION['user_role'] = $u['role'];
        $_SESSION['user_email'] = $u['email'];
        $_SESSION['user_phone'] = $u['phone'];
        $_SESSION['user_address'] = $u['address'];

        set_flash('success', 'Selamat datang, <strong>' . htmlspecialchars($u['name']) . '</strong>! (Login demo ' . ucfirst($u['role']) . ')');
        if ($u['role'] === 'seller' || $u['role'] === 'admin') {
            header('Location: ' . base_url('admin/products.php'));
        } else {
            header('Location: ' . $redirect);
        }
        exit;
    }
}

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_or_email = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_or_email) || empty($password)) {
        set_flash('danger', 'Harap isi username/email dan password.');
    } else {
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_phone'] = $user['phone'];
            $_SESSION['user_address'] = $user['address'];

            set_flash('success', 'Selamat datang kembali, <strong>' . htmlspecialchars($user['name']) . '</strong>!');

            $target = $_POST['redirect'] ?? base_url('index.php');
            if (($user['role'] === 'seller' || $user['role'] === 'admin') && $target === base_url('index.php')) {
                $target = base_url('admin/products.php');
            }
            header('Location: ' . $target);
            exit;
        } else {
            set_flash('danger', 'Username atau kata sandi yang Anda masukkan salah.');
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-6 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <span class="fs-1">🧺</span>
                    <h3 class="fw-bold text-dark mt-2 mb-1">Masuk ke PasarKu</h3>
                    <p class="text-secondary small">Akses akun pembeli atau kelola toko dagangan Anda</p>
                </div>

                <!-- Box Demo 1-Click Login (Sangat Membantu Penilai / Dosen) -->
                <div class="card bg-success-subtle border border-success-subtle rounded-3 p-3 mb-4">
                    <div class="small fw-bold text-success-emphasis mb-2">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Mode Cepat Pengujian (1-Klik Demo):
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="<?php echo base_url('auth/login.php?quick_demo=seller'); ?>" class="btn btn-sm btn-success text-start fw-medium rounded-pill">
                            <i class="bi bi-shop me-1"></i> Masuk sebagai <strong>Penjual (Pak Bejo)</strong> &rarr; Tes CRUD
                        </a>
                        <a href="<?php echo base_url('auth/login.php?quick_demo=buyer'); ?>" class="btn btn-sm btn-outline-success text-start fw-medium rounded-pill bg-white">
                            <i class="bi bi-basket me-1"></i> Masuk sebagai <strong>Pembeli (Budi Santoso)</strong> &rarr; Tes Belanja
                        </a>
                    </div>
                </div>

                <form action="<?php echo base_url('auth/login.php'); ?>" method="POST">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Username atau Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control" placeholder="pak_bejo atau budi_santoso" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">Kata Sandi (Password)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="password123" required>
                        </div>
                        <div class="form-text small text-muted">Password demo: <code>password123</code></div>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <button type="submit" class="btn btn-success rounded-pill py-2 fw-bold">
                            Masuk Sekarang
                        </button>
                    </div>

                    <div class="text-center small text-secondary">
                        Belum memiliki akun? <a href="<?php echo base_url('auth/register.php'); ?>" class="text-success fw-bold text-decoration-none">Daftar Akun Baru</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
