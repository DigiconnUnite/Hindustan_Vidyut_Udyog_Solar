<?php
/**
 * Self-check for the sizing/subsidy/EMI maths. Run: php tests/test-solar-calc.php
 * Plain asserts, no framework — same style as test-product-specs.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/solar-calc.php';

$failures = 0;

function check(string $label, $actual, $expected): void
{
    global $failures;
    if ($actual !== $expected) {
        fwrite(STDERR, "FAIL: $label\n  expected: " . var_export($expected, true) . "\n  actual:   " . var_export($actual, true) . "\n");
        $failures++;
        return;
    }
    echo "  ok  $label\n";
}

// --- PM Surya Ghar subsidy slabs -----------------------------------------
// Official slabs (pmsuryaghar.gov.in): ₹30,000/kW to 2 kW, ₹18,000/kW for the
// 3rd, capped at ₹78,000.
check('subsidy 0 kW',    solar_subsidy(0),   0);
check('subsidy 1 kW',    solar_subsidy(1),   30000);
check('subsidy 2 kW',    solar_subsidy(2),   60000);
check('subsidy 3 kW',    solar_subsidy(3),   78000);
check('subsidy 5 kW capped',  solar_subsidy(5),  78000);
check('subsidy 10 kW capped', solar_subsidy(10), 78000);
check('subsidy 1.5 kW mid-slab', solar_subsidy(1.5), 45000);
check('subsidy 2.5 kW mid-slab', solar_subsidy(2.5), 69000);

// --- Sizing ---------------------------------------------------------------
// ₹3000/month at ₹8/unit = 375 units => 375 / (4 × 30) = 3.125 kW => rounds to 3.0
$e = solar_estimate(3000.0);
check('3k bill sizes to 3 kW', $e['system_kw'], 3.0);
check('3k bill subsidy is the cap', $e['subsidy'], 78000);
check('3k bill gross cost', $e['gross_cost'], 180000);
check('3k bill net cost', $e['net_cost'], 102000);
check('3 kW needs 6 panels', $e['panels'], 6);
check('3 kW needs 240 sqft', $e['area_sqft'], 240);

// Units given directly must beat the bill-derived figure.
check('explicit units win', solar_estimate(9999.0, 8.0, 240.0)['system_kw'], 2.0);

// A small roof caps the system even when the bill wants more.
$small = solar_estimate(6000.0, 8.0, null, 100.0);
check('roof caps system', $small['system_kw'], 1.0);
check('roof cap is flagged', $small['roof_limited'], true);

// Savings never exceed consumption — a 1 kW system on a 100 sqft roof generates
// 1460 units/yr but the household uses 9000, so all of it offsets.
check('savings capped at usage', solar_estimate(800.0, 8.0)['annual_savings'], 9600);

// --- Payback --------------------------------------------------------------
check('payback of zero savings is null', solar_payback_years(100000, 0.0), null);
check('payback rounds to 1 dp', solar_payback_years(100000, 30000.0), 3.3);
check('3k bill pays back under 5 yrs', $e['payback_years'] < 5.0, true);
check('lifetime savings beat net cost', $e['lifetime_savings'] > $e['net_cost'], true);

// --- EMI ------------------------------------------------------------------
// ₹1,00,000 at 10% over 12 months ≈ ₹8,792/month.
check('emi 1L @10% 12m', solar_emi(100000, 10.0, 12), 8792);
check('emi at 0% is straight division', solar_emi(120000, 0.0, 12), 10000);
check('emi of nothing is nothing', solar_emi(0, 10.0, 12), 0);
check('emi over zero months is nothing', solar_emi(100000, 10.0, 0), 0);

// --- Indian currency formatting ------------------------------------------
check('inr lakh grouping', inr(123456), '₹1,23,456');
check('inr thousands', inr(78000), '₹78,000');
check('inr small', inr(500), '₹500');
check('inr crore grouping', inr(12345678), '₹1,23,45,678');
check('inr negative', inr(-5000), '-₹5,000');

if ($failures > 0) {
    fwrite(STDERR, "\n$failures check(s) failed\n");
    exit(1);
}
echo "\nAll solar-calc checks passed.\n";
