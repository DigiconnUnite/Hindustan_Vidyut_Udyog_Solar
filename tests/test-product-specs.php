<?php
/**
 * Self-check for the spec-string parser. Run: php tests/test-product-specs.php
 * No framework on purpose — plain asserts, exits non-zero on failure.
 */

declare(strict_types=1);

ini_set('assert.exception', '1');

require_once __DIR__ . '/../config/product-content.php';

function check(string $label, array $actual, array $expected): void
{
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: $label\n  expected: " . json_encode($expected) . "\n  actual:   " . json_encode($actual) . "\n");
        exit(1);
    }
    echo "  ok  $label\n";
}

// The three seeded products (database/schema.sql).
check('panel specs', product_specs('540W | 21% efficiency | 25-year warranty'), [
    ['Output', '540W'],
    ['Efficiency', '21%'],
    ['Warranty', '25-year'],
]);

check('inverter specs', product_specs('5kW | MPPT | Wi-Fi monitoring'), [
    ['Output', '5kW'],
    ['Charge Controller', 'MPPT'],
    // no leading digit, so the whole token stays the value
    ['Monitoring', 'Wi-Fi monitoring'],
]);

check('battery specs', product_specs('5kWh | 6000+ cycles | Wall-mounted'), [
    ['Capacity', '5kWh'],
    ['Cycles', '6000+'],
    ['Specification', 'Wall-mounted'],
]);

// Edge cases — none of these may produce a broken or half-empty row.
check('null specs', product_specs(null), []);
check('empty specs', product_specs(''), []);
check('whitespace only', product_specs('   |  | '), []);
check('single value, no pipe', product_specs('540W'), [['Output', '540W']]);
check('explicit label wins', product_specs('Warranty: 25 years'), [['Warranty', '25 years']]);
check('unparseable token', product_specs('Wall-mounted'), [['Specification', 'Wall-mounted']]);
check('trailing pipe', product_specs('5kWh |'), [['Capacity', '5kWh']]);
// Empty value after the colon, so the explicit-label branch is skipped and the
// token is shown verbatim rather than as a row with a blank value.
check('dangling colon', product_specs('Warranty:'), [['Specification', 'Warranty:']]);

// Every row must be a [string, string] pair — the template unpacks with list().
foreach (product_specs('540W | 21% efficiency | odd | Label: value') as $row) {
    assert(count($row) === 2, 'row must have exactly 2 elements');
    assert(is_string($row[0]) && $row[0] !== '', 'label must be a non-empty string');
    assert(is_string($row[1]) && $row[1] !== '', 'value must be a non-empty string');
}
echo "  ok  every row is a non-empty [label, value] pair\n";

echo "OK\n";
