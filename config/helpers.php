<?php
require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(): void
{
    $expected = $_SESSION['csrf'] ?? '';
    $token = $_POST['csrf'] ?? '';

    // A request with no session carries no expected token, and hash_equals('','')
    // is true — so an empty pair must be rejected explicitly or a cookie-less
    // POST sails straight through every form on the site.
    if ($expected === '' || $token === '' || !hash_equals($expected, $token)) {
        http_response_code(419);
        exit('Invalid or expired form submission. Please go back and try again.');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function setting(string $key, string $default = ''): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (db()->query('SELECT `key`, `value` FROM site_settings') as $row) {
            $cache[$row['key']] = $row['value'];
        }
    }
    return $cache[$key] ?? $default;
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

// The single lead-capture handler for the whole site. Every public form posts through
// components/lead-form.php, which is backed by this. Call it before any output on any page
// that renders the form; it redirects back on both success and error.
//
// $opts:
//   source   — leads.source value ('contact', 'calculator', 'financing', 'career', …)
//   back     — where to redirect; defaults to the current URL + #enquiry
//   prepend  — text folded onto the top of the message (product name, role, estimate…),
//              because leads has no column for those. Kept out of $_POST so a crafted
//              request cannot forge it.
//   success  — flash message on success
//   extra    — ['system_kw' => float|null, 'monthly_bill' => int|null]
function lead_handle(array $opts = []): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    // Forms that post something other than a lead (newsletter, admin CRUD) set their own
    // hidden marker, so a shared handler on the same page must not swallow their POST.
    if (($_POST['form'] ?? 'lead') !== 'lead') {
        return;
    }

    csrf_verify();

    $back = $opts['back'] ?? ($_SERVER['REQUEST_URI'] . '#enquiry');
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Hand the typed values back to components/lead-form.php so a bounce never wipes the form.
    $fail = static function (string $msg) use ($back, $name, $phone, $email, $pincode, $message): never {
        $_SESSION['lead_old'] = compact('name', 'phone', 'email', 'pincode', 'message');
        flash('error', $msg);
        redirect($back);
    };

    // Name and phone are the only fields we genuinely need to call someone back.
    if ($name === '' || $phone === '') {
        $fail('Please enter your name and phone number.');
    }

    // Reject an address we could never dial rather than storing a dead lead.
    if (preg_match_all('/\d/', $phone) < 10) {
        $fail('Please enter a valid phone number.');
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fail('Please enter a valid email address.');
    }

    if ($pincode !== '' && !preg_match('/^\d{6}$/', $pincode)) {
        $fail('Please enter a valid 6-digit PIN code.');
    }

    // leads has no pincode column; it has always ridden along in address.
    $address = $pincode !== '' ? 'PIN ' . $pincode : null;

    $prepend = trim($opts['prepend'] ?? '');
    if ($prepend !== '') {
        $message = $message !== '' ? $prepend . "\n\n" . $message : $prepend;
    }

    // VARCHAR limits — truncate rather than let a long paste throw.
    $stmt = db()->prepare(
        'INSERT INTO leads (name, phone, email, address, message, source, system_kw, monthly_bill)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        mb_substr($name, 0, 100),
        mb_substr($phone, 0, 20),
        $email !== '' ? mb_substr($email, 0, 150) : null,
        $address,
        $message !== '' ? $message : null,
        $opts['source'] ?? 'contact',
        $opts['extra']['system_kw'] ?? null,
        $opts['extra']['monthly_bill'] ?? null,
    ]);

    flash('success', $opts['success'] ?? 'Thanks! We received your request and will contact you shortly.');
    redirect($back);
}

// Loaded last, and after the helpers it depends on, so any page that requires
// helpers.php can build $jsonLd before components/header.php renders it.
require_once __DIR__ . '/seo.php';
