<?php
$page_title = "Daftar Akun Baru";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? 'buyer');
    $address = sanitize($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi
    if (empty($name) || empty($username) || empty($email) || empty($phone) || empty($password)) {
        set_flash('danger', 'Mohon isi semua data yang bertanda bintang (*).');
    } elseif ($password !== $confirm_password) {
        set_flash('danger', 'Konfirmasi kata sandi tidak cocok!');
    } elseif (strlen($password) < 6) {
        set_flash('danger', 'Kata sandi minimal 6 karakter.');
    } else {
        // Cek username atau email duplikat
        $stmt_check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
        mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
        mysqli_stmt_execute($stmt_check);
        $res_check = mysqli_stmt_get_result($stmt_check);

        if (mysqli_num_rows($res_check) > 0) {
            set_flash('danger', 'Username atau Email sudah terdaftar. Silakan gunakan yang lain.');
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = ($role === 'seller') ? 'seller' : 'buyer';

            $stmt_ins = mysqli_prepare($conn, "INSERT INTO users (username, password, name, email, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_ins, "sssssss", $username, $hashed, $name, $email, $phone, $address, $role);

            if (mysqli_stmt_execute($stmt_ins)) {
                set_flash('success', 'Pendaftaran akun berhasil! Silakan login menggunakan akun baru Anda.');
                header('Location: ' . base_url('auth/login.php'));
                exit;
            } else {
                set_flash('danger', 'Terjadi kesalahan sistem: ' . mysqli_error($conn));
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center my-4">
    <div class="col-md-7 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <span class="fs-1">🧺</span>
                    <h3 class="fw-bold text-dark mt-2 mb-1">Daftar Akun PasarKu</h3>
                    <p class="text-secondary small">Bergabung sebagai Pembeli atau Pedagang Pasar Tradisional</p>
                </div>

                <form action="<?php echo base_url('auth/register.php'); ?>" method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Daftar Sebagai <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" id="role_buyer" value="buyer" checked>
                                    <label class="form-check-label fw-medium text-dark" for="role_buyer">
                                        <i class="bi bi-basket me-1 text-success"></i> Pembeli
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="role" id="role_seller" value="seller">
                                    <label class="form-check-label fw-medium text-dark" for="role_seller">
                                        <i class="bi bi-shop me-1 text-primary"></i> Pedagang / Penjual Pasar
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Nama Lengkap / Nama Kios <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Ibu Ani / Toko Tani Makmur" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" placeholder="ibu_ani" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                            <input type="tel" name="phone" class="form-control" placeholder="081234567890" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="email@contoh.com" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Alamat Lengkap / Lokasi Kios</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Nama jalan, pasar, nomor kios, atau alamat pengiriman..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Kata Sandi (Password) <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Ketik ulang password" required>
                        </div>

                        <div class="col-12 pt-3">
                            <button type="submit" class="btn btn-success rounded-pill w-100 py-2 fw-bold">
                                Buat Akun Sekarang
                            </button>
                        </div>

                        <div class="col-12 text-center small text-secondary">
                            Sudah punya akun? <a href="<?php echo base_url('auth/login.php'); ?>" class="text-success fw-bold text-decoration-none">Masuk di Sini</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
