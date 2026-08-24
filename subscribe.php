<?php
/**
 * Newsletter subscribe endpoint. Posted to by the footer form on every page,
 * so it redirects back to wherever the visitor was rather than rendering.
 */
require_once __DIR__ . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

csrf_verify();

// Only same-origin referers, so the redirect can't be pointed off-site.
$back = '/index.php';
$referer = $_POST['back'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
if ($referer !== '') {
    $path = parse_url($referer, PHP_URL_PATH);
    $host = parse_url($referer, PHP_URL_HOST);
    if ($path && (!$host || $host === parse_url(APP_URL, PHP_URL_HOST) || $host === ($_SERVER['HTTP_HOST'] ?? ''))) {
        $back = $path;
    }
}
$back .= '#subscribe';

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('subscribe_error', 'Please enter a valid email address.');
    redirect($back);
}

// Re-subscribing is not an error from the visitor's point of view.
db()->prepare('INSERT INTO newsletter_subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE email = email')
    ->execute([$email]);

flash('subscribe_success', 'Subscribed. We send occasional updates on solar subsidy and tariffs — no spam.');
redirect($back);
