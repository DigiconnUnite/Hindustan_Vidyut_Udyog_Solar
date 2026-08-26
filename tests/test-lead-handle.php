<?php
/**
 * Tests for lead_handle() — the single lead-capture handler behind
 * components/lead-form.php, used by every public form on the site.
 *
 * Covers the validation gate, the prepend fold (product/role/estimate text that has
 * no column of its own), pincode -> address, source tagging, and the numeric clamps.
 *
 * lead_handle() calls redirect(), which exits, so each case runs in its own subprocess
 * and reports through a temp file. Inserted rows are rolled back by deleting on the
 * marker phone number.
 *
 * Run: php tests/test-lead-handle.php
 */

declare(strict_types=1);

const MARKER = '9000000001';

// ---------------------------------------------------------------- child process
if (($argv[1] ?? '') === '--case') {
    // Payload arrives through a file: escapeshellarg() on Windows mangles inline JSON.
    $payload = json_decode(file_get_contents($argv[2]), true);
    $post = $payload['post'];
    $opts = $payload['opts'];
    $out = $argv[3];

    session_start();
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/test.php';
    $_POST = $post;

    // Satisfy csrf_verify() so these cases exercise lead_handle(), not CSRF
    // (tests/test-csrf.php already covers that).
    $token = str_repeat('a', 64);
    $_SESSION['csrf'] = $token;
    $_POST['csrf'] = $token;

    require_once __DIR__ . '/../config/helpers.php';

    // redirect() exits, so record what the flash said on the way out.
    register_shutdown_function(static function () use ($out) {
        file_put_contents($out, json_encode([
            'error' => $_SESSION['flash']['error'] ?? null,
            'success' => $_SESSION['flash']['success'] ?? null,
        ]));
    });

    lead_handle($opts);
    exit(0);
}

// --------------------------------------------------------------- parent process
require_once __DIR__ . '/../config/helpers.php';

$tmp = sys_get_temp_dir() . '/lead-case-' . getmypid() . '.json';
$passed = 0;
$failed = 0;

function run(array $post, array $opts = []): array
{
    global $tmp;
    @unlink($tmp);
    $in = $tmp . '.in';
    file_put_contents($in, json_encode(['post' => $post, 'opts' => $opts]));

    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' --case '
        . escapeshellarg($in) . ' ' . escapeshellarg($tmp);
    exec($cmd . ' 2>&1', $out, $code);

    if ($code !== 0) {
        echo "  (subprocess exit $code) " . implode(' ', array_slice($out, 0, 2)) . "\n";
    }
    $result = is_file($tmp) ? json_decode(file_get_contents($tmp), true) : [];
    @unlink($in);
    return $result + ['error' => null, 'success' => null];
}

function check(string $label, bool $ok): void
{
    global $passed, $failed;
    if ($ok) {
        $passed++;
        echo "  PASS  $label\n";
    } else {
        $failed++;
        echo "  FAIL  $label\n";
    }
}

/** Most recent lead written by these tests. */
function lastLead(): ?array
{
    $stmt = db()->prepare('SELECT * FROM leads WHERE phone = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([MARKER]);
    return $stmt->fetch() ?: null;
}

$valid = ['name' => 'Test Runner', 'phone' => MARKER];

echo "lead_handle()\n";

// -- validation ------------------------------------------------------------
$r = run(['name' => '', 'phone' => MARKER]);
check('rejects a missing name', $r['error'] !== null && $r['success'] === null);

$r = run(['name' => 'Test Runner', 'phone' => '']);
check('rejects a missing phone', $r['error'] !== null && $r['success'] === null);

$r = run(['name' => 'Test Runner', 'phone' => '12345']);
check('rejects a phone with under 10 digits', $r['error'] !== null);

$r = run($valid + ['email' => 'not-an-email']);
check('rejects a malformed email', $r['error'] !== null);

$r = run($valid + ['pincode' => '12']);
check('rejects a short PIN code', $r['error'] !== null);

// A blank optional field must not trip the optional-field validators.
$r = run($valid + ['email' => '', 'pincode' => '']);
check('accepts blank optional email and PIN', $r['success'] !== null && $r['error'] === null);

// -- the happy path --------------------------------------------------------
$r = run($valid + ['email' => 'lead@example.com', 'pincode' => '226001', 'message' => 'Ten kW please'],
         ['source' => 'product', 'prepend' => 'Product: Panel X']);
check('accepts a complete submission', $r['success'] !== null);

$lead = lastLead();
check('writes a row', $lead !== null);
check('stores the name', ($lead['name'] ?? '') === 'Test Runner');
check('stores the email', ($lead['email'] ?? '') === 'lead@example.com');
check('folds the PIN into address', ($lead['address'] ?? '') === 'PIN 226001');
check('tags the source', ($lead['source'] ?? '') === 'product');
check('prepends the context line', str_starts_with($lead['message'] ?? '', 'Product: Panel X'));
check('keeps the typed message', str_contains($lead['message'] ?? '', 'Ten kW please'));

// -- prepend cannot be forged from the request body ------------------------
$r = run($valid + ['prepend' => 'Product: Forged'], ['source' => 'contact']);
$lead = lastLead();
check('ignores a prepend posted by the client', !str_contains($lead['message'] ?? '', 'Forged'));

// -- source defaults -------------------------------------------------------
$r = run($valid);
$lead = lastLead();
check('defaults source to contact', ($lead['source'] ?? '') === 'contact');

// -- calculator extras -----------------------------------------------------
$r = run($valid, ['source' => 'calculator', 'extra' => ['system_kw' => 3.5, 'monthly_bill' => 4200]]);
$lead = lastLead();
check('stores system_kw', (float) ($lead['system_kw'] ?? 0) === 3.5);
check('stores monthly_bill', (int) ($lead['monthly_bill'] ?? 0) === 4200);

// -- non-lead POSTs are left alone -----------------------------------------
$r = run($valid + ['form' => 'newsletter']);
check('ignores a POST marked as another form', $r['success'] === null && $r['error'] === null);

// -- oversized input is truncated, not fatal -------------------------------
$r = run(['name' => str_repeat('x', 400), 'phone' => MARKER]);
check('accepts an over-long name', $r['success'] !== null);
$lead = lastLead();
check('truncates the name to the column width', mb_strlen($lead['name'] ?? '') <= 100);

// -- cleanup ---------------------------------------------------------------
$deleted = db()->prepare('DELETE FROM leads WHERE phone = ?');
$deleted->execute([MARKER]);
@unlink($tmp);
echo "  ..   removed {$deleted->rowCount()} test row(s)\n";

echo "\n$passed passed, $failed failed\n";
exit($failed === 0 ? 0 : 1);
