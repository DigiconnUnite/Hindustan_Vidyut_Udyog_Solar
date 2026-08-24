<?php
/**
 * Regression test for the CSRF empty-token bypass.
 *
 * hash_equals('', '') is TRUE, so a POST with no session cookie AND no csrf
 * field used to pass verification on every form on the site. csrf_verify()
 * must reject an empty expected token or an empty submitted token outright.
 *
 * Run: php tests/test-csrf.php
 */

declare(strict_types=1);

// csrf_verify() calls exit() on failure, so each case runs in its own subprocess
// and is judged by exit code: 0 = accepted, non-zero = rejected.
if (($argv[1] ?? '') === '--case') {
    // Stub the session and POST that this case describes, then verify.
    $case = $argv[2];

    $_SESSION = [];
    $_POST = [];

    $valid = str_repeat('a', 64);

    switch ($case) {
        case 'no-session-no-token':
            break;                                        // both empty
        case 'no-session-with-token':
            $_POST['csrf'] = $valid;
            break;
        case 'session-no-token':
            $_SESSION['csrf'] = $valid;
            break;
        case 'session-wrong-token':
            $_SESSION['csrf'] = $valid;
            $_POST['csrf'] = str_repeat('b', 64);
            break;
        case 'session-matching-token':
            $_SESSION['csrf'] = $valid;
            $_POST['csrf'] = $valid;
            break;
    }

    // Pull in csrf_verify() without booting config.php's session/DB handling.
    $src = file_get_contents(__DIR__ . '/../config/helpers.php');
    preg_match('/function csrf_verify\(\): void\s*\{.*?\n\}/s', $src, $m)
        || exit(2);
    eval($m[0]);

    csrf_verify();

    // csrf_verify() rejects with exit('...'), which is still exit status 0 — so
    // acceptance is signalled by this marker, not by the status code.
    echo 'CSRF_ACCEPTED';
    exit(0);
}

// --- driver ---------------------------------------------------------------
$cases = [
    // [case, should be accepted?]
    ['no-session-no-token',    false], // the bug: empty === empty passed
    ['no-session-with-token',  false],
    ['session-no-token',       false],
    ['session-wrong-token',    false],
    ['session-matching-token', true],
];

$failures = 0;
$php = PHP_BINARY;

foreach ($cases as [$case, $shouldAccept]) {
    $cmd = escapeshellarg($php) . ' ' . escapeshellarg(__FILE__) . ' --case ' . escapeshellarg($case);
    $out = [];
    exec($cmd, $out, $code);
    $accepted = in_array('CSRF_ACCEPTED', array_map('trim', $out), true);

    if ($accepted !== $shouldAccept) {
        fwrite(STDERR, sprintf(
            "FAIL: %s\n  expected: %s\n  actual:   %s\n",
            $case,
            $shouldAccept ? 'accepted' : 'rejected',
            $accepted ? 'accepted' : 'rejected'
        ));
        $failures++;
        continue;
    }
    printf("  ok  %-24s %s\n", $case, $shouldAccept ? 'accepted' : 'rejected');
}

if ($failures > 0) {
    fwrite(STDERR, "\n$failures CSRF check(s) failed\n");
    exit(1);
}
echo "\nAll CSRF checks passed.\n";
