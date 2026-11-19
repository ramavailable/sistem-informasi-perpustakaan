<?php
/** Halaman awal: arahkan ke dashboard jika sudah login, atau ke login. */
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

if (currentUser()) {
    redirect('/dashboard.php');
}
redirect('/login.php');
