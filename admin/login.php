<?php
require_once __DIR__ . '/../config/auth.php';

if (current_user()) {
    redirect('/admin/index.php');
}

// One form, two staff roles. ?role= only labels the page and scopes who may
// sign in here; it is never trusted as the user's actual role.
$roleParam = $_GET['role'] ?? $_POST['role'] ?? 'admin';
if (!in_array($roleParam, ['admin', 'staff'], true)) {
    $roleParam = 'admin';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // An admin may use the staff door, but not the reverse.
        if ($roleParam === 'admin' && $user['role'] !== 'admin') {
            flash('error', 'This account does not have administrator access.');
            redirect('/admin/login.php?role=' . $roleParam);
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        redirect('/admin/index.php');
    }

    flash('error', 'Invalid email or password.');
    redirect('/admin/login.php?role=' . $roleParam);
}

$portalLabel = $roleParam === 'staff' ? 'Staff Portal' : 'Admin Portal';
$pageTitle = $portalLabel;
require_once __DIR__ . '/../components/icon.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="bg-primary-50/60 min-h-screen flex items-center justify-center px-4">
  <div class="card w-full max-w-sm">
    <div class="text-center mb-6">
      <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-primary-500 text-white"><?= icon('sun', 'h-6 w-6') ?></span>
      <h1 class="mt-3 text-xl font-bold text-gray-900">Hindustan Vidyut Udyog</h1>
      <p class="text-sm text-gray-500"><?= e($portalLabel) ?></p>
    </div>

    <?php require __DIR__ . '/../components/flash-message.php'; ?>

    <form method="post" action="/admin/login.php" class="space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="role" value="<?= e($roleParam) ?>">
      <div>
        <label class="text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" required class="input mt-1" autofocus>
      </div>
      <div>
        <label class="text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" required class="input mt-1">
      </div>
      <button type="submit" class="btn-primary w-full flex flex-1 items-center justify-center">Sign In</button>
    </form>
  </div>
</body>
</html>
