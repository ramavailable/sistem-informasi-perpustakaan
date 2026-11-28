<?php
/** Halaman login. */
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

if (currentUser()) {
    redirect('/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT u.id, u.name, u.username, u.password, u.role_id, u.anggota_id, r.name AS role_name
         FROM users u JOIN roles r ON r.id = u.role_id
         WHERE u.username = ?'
    );
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        redirect('/dashboard.php');
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login (Sistem Informasi Perpustakaan)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= url('/assets/css/app.css') ?>" rel="stylesheet">
</head>
<body>
<div class="login-page">
    <div class="login-panel">
        <span class="lib-brand"><i class="bi bi-book" aria-hidden="true"></i><span>Perpustakaan<small>Sistem Informasi</small></span></span>
        <hr class="login-rule">
        <p>Kelola buku, anggota, peminjaman, dan pengembalian dalam satu tempat.</p>
        <p>Untuk pustakawan dan anggota Universitas Pamulang.</p>
    </div>
    <div class="login-form-col">
        <div class="card login-card">
            <div class="card-body">
                <h1 class="font-display mb-1" style="font-size: 1.5rem;">Masuk</h1>
                <p class="text-muted">Masuk dengan akun perpustakaan Anda.</p>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="post" action="<?= url('/login.php') ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <hr>
                <div class="demo-box">
                    <p><strong>Akun demo</strong> (lihat README.md)</p>
                    <p>Admin: <code>admin / admin123</code></p>
                    <p>Pustakawan: <code>pustakawan / pustaka123</code></p>
                    <p>Anggota: <code>anggota / anggota123</code></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
