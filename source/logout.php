<?php
/** Logout: hapus sesi lalu kembali ke halaman login. */
require __DIR__ . '/config/database.php';
require __DIR__ . '/includes/auth.php';

session_unset();
session_destroy();
redirect('/login.php');
