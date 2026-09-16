<?php
require_once __DIR__ . '/../config/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/admin/login.php');
}

csrf_verify();
unset($_SESSION['user_id']);
flash('success', 'You have been logged out successfully.');
redirect('/admin/login.php');
