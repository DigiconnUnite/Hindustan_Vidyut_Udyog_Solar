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

// --- kit_as_product() -------------------------------------------------------
// Kits render through the product templates, so the normalised row has to satisfy
// everything those templates read. A missing key is a notice on a live page.

function check_val(string $label, mixed $actual, mixed $expected): void
{
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: $label\n  expected: " . var_export($expected, true) . "\n  actual:   " . var_export($actual, true) . "\n");
        exit(1);
    }
    echo "  ok  $label\n";
}

// Shaped like the seeded 3 kW on-grid kit (database/migration-003-seed.sql).
$kitRow = [
    'id' => 3,
    'name' => '3 kW On-Grid Rooftop Kit',
    'slug' => '3kw-ongrid',
    'system_kw' => '3.00',
    'kit_type' => 'ongrid',
    'price' => '178000.00',
    'subsidy' => '78000.00',
    'monthly_units' => 360,
    'suits' => '3 BHK · bill ₹2,000–3,000 · full subsidy',
    'includes' => "6 × 545W Mono PERC panels\n3 kW on-grid inverter\nMounting structure & rails",
    'image_path' => null,
    'is_featured' => 1,
    'is_active' => 1,
    'sort_order' => 3,
];

$k = kit_as_product($kitRow);

check_val('kit id is prefixed, never an int collision', $k['id'], 'kit-3kw-ongrid');
check_val('kit id casts to 0, so int lookups cannot hit a real product', (int) $k['id'], 0);
check_val('kit category', $k['category'], 'kit');
check_val('price is net of subsidy', $k['price'], 100000.0);
check_val('mrp is the gross price', $k['mrp'], 178000.0);
check_val('description comes from suits', $k['description'], $kitRow['suits']);
check_val('system_kw becomes watts', $k['wattage'], 3000);
check_val('kit is always purchasable', $k['in_stock'], 1);
check_val('raw kit is retained', $k['_kit'], $kitRow);

// The discount badge divides by mrp, so it must never be 0 when subsidy > 0.
check_val('subsidy reads as a discount', (int) round(100 - ($k['price'] / $k['mrp'] * 100)), 44);

// includes is newline-authored; the templates split it on pipes.
check_val('includes are pipe-joined', explode(' | ', $k['specs']), [
    '6 × 545W Mono PERC panels',
    '3 kW on-grid inverter',
    'Mounting structure & rails',
]);

// No subsidy (the commercial kit) must not produce a struck-through price.
$noSubsidy = kit_as_product(['subsidy' => '0.00'] + $kitRow);
check_val('no subsidy leaves price gross', $noSubsidy['price'], 178000.0);
check_val('no subsidy means no mrp', $noSubsidy['mrp'], null);

// Every key the product templates read must be present, or they emit notices.
foreach (['id', 'name', 'category', 'description', 'specs', 'image_path', 'price', 'mrp',
          'wattage', 'brand', 'warranty_years', 'datasheet_path', 'in_stock', 'rating',
          'review_count'] as $key) {
    assert(array_key_exists($key, $k), "normalised kit is missing '$key'");
}
echo "  ok  normalised kit has every key the product templates read\n";

// --- product_url() ----------------------------------------------------------
check_val('kit url is slug-keyed', product_url($k), '/product-details.php?kit=3kw-ongrid');
check_val('product url is id-keyed', product_url(['id' => 7]), '/product-details.php?id=7');

echo "OK\n";
