<?php
/**
 * Solar sizing, subsidy and payback maths.
 *
 * Pure functions, no DB and no output — so tests/test-solar-calc.php can run the
 * whole thing from CLI. The page (solar-calculator.php) and the JS mirror of it
 * both go through these numbers; keep the JS in assets/js/solar-calc.js in sync.
 */

declare(strict_types=1);

/**
 * Tunables. Real installs vary by roof pitch, shading, dust and DISCOM tariff —
 * these are Delhi-NCR field averages, not physics constants. Adjust after a few
 * real jobs rather than trusting them forever.
 */
const SOLAR_GEN_PER_KW_PER_DAY = 4.0;   // kWh generated per kW per day (NCR annual avg)
const SOLAR_COST_PER_KW        = 60000; // ₹ turnkey, before subsidy
const SOLAR_AREA_PER_KW        = 80;    // sq ft of shadow-free roof
const SOLAR_CO2_PER_KWH        = 0.82;  // kg CO2 avoided per kWh (CEA grid factor)
const SOLAR_PANEL_WATTS        = 545;   // per-module wattage used for the panel count
const SOLAR_LIFETIME_YEARS     = 25;
const SOLAR_TARIFF_ESCALATION  = 0.03;  // grid tariff rises ~3%/yr
const SOLAR_DEGRADATION        = 0.005; // panels lose ~0.5% output/yr

/**
 * PM Surya Ghar central subsidy for residential grid-connected rooftop.
 *
 * Official structure (pmsuryaghar.gov.in): ₹30,000 per kW for the first 2 kW,
 * then ₹18,000 per kW for capacity from 2 kW to 3 kW, capped at ₹78,000.
 * So 1 kW = ₹30,000, 2 kW = ₹60,000, 3 kW and above = ₹78,000.
 */
function solar_subsidy(float $kw): int
{
    if ($kw <= 0) {
        return 0;
    }
    if ($kw <= 2) {
        return (int) round(30000 * $kw);
    }
    if ($kw < 3) {
        return (int) round(60000 + 18000 * ($kw - 2));
    }
    return 78000; // cap
}

/** Rounds a raw kW figure to a size that is actually sold (0.5 kW steps, min 1 kW). */
function solar_round_kw(float $kw): float
{
    return max(1.0, round($kw * 2) / 2);
}

/**
 * Sizes a system from a monthly bill or monthly units.
 *
 * @param float      $monthlyBill  ₹ per month. Ignored when $monthlyUnits is given.
 * @param float      $tariff       ₹ per unit, used to derive units from the bill.
 * @param float|null $monthlyUnits kWh per month, when the customer knows it.
 * @param float|null $roofArea     sq ft available; caps the system when it is the binding limit.
 * @return array<string,mixed>
 */
function solar_estimate(
    float $monthlyBill,
    float $tariff = 8.0,
    ?float $monthlyUnits = null,
    ?float $roofArea = null
): array {
    $tariff = $tariff > 0 ? $tariff : 8.0;
    $units = $monthlyUnits !== null && $monthlyUnits > 0
        ? $monthlyUnits
        : max(0.0, $monthlyBill) / $tariff;

    // kW needed to generate those units: units/month ÷ (gen per kW per day × 30)
    $needed = $units / (SOLAR_GEN_PER_KW_PER_DAY * 30);
    $kw = solar_round_kw($needed);

    // A small roof, not the bill, can be the real constraint. Round the cap DOWN:
    // rounding 1.25 kW up to 1.5 would need 120 sqft of a 100 sqft roof.
    $roofLimited = false;
    if ($roofArea !== null && $roofArea > 0) {
        $maxKw = max(1.0, floor($roofArea / SOLAR_AREA_PER_KW * 2) / 2);
        if ($maxKw < $kw) {
            $kw = $maxKw;
            $roofLimited = true;
        }
    }

    $annualUnits  = $kw * SOLAR_GEN_PER_KW_PER_DAY * 365;
    $grossCost    = (int) round($kw * SOLAR_COST_PER_KW);
    $subsidy      = solar_subsidy($kw);
    $netCost      = $grossCost - $subsidy;

    // Savings are capped at what the customer actually consumes — exported surplus
    // earns far less than it offsets, so counting it at full tariff overstates payback.
    $offsetUnits    = min($annualUnits, $units * 12);
    $annualSavings  = $offsetUnits * $tariff;
    $monthlySavings = $annualSavings / 12;

    return [
        'system_kw'        => $kw,
        'monthly_units'    => round($units),
        'tariff'           => $tariff,
        'panels'           => (int) ceil($kw * 1000 / SOLAR_PANEL_WATTS),
        'area_sqft'        => (int) round($kw * SOLAR_AREA_PER_KW),
        'roof_limited'     => $roofLimited,
        'gross_cost'       => $grossCost,
        'subsidy'          => $subsidy,
        'net_cost'         => $netCost,
        'annual_units'     => (int) round($annualUnits),
        'monthly_savings'  => (int) round($monthlySavings),
        'annual_savings'   => (int) round($annualSavings),
        'payback_years'    => solar_payback_years($netCost, $annualSavings),
        'lifetime_savings' => solar_lifetime_savings($annualSavings, $netCost),
        'co2_tonnes_year'  => round($annualUnits * SOLAR_CO2_PER_KWH / 1000, 1),
        'trees_equivalent' => (int) round($annualUnits * SOLAR_CO2_PER_KWH / 21),
    ];
}

/** Years to recover net cost. Returns null when savings are zero (never pays back). */
function solar_payback_years(int $netCost, float $annualSavings): ?float
{
    if ($annualSavings <= 0) {
        return null;
    }
    return round($netCost / $annualSavings, 1);
}

/**
 * 25-year net savings, compounding the tariff rise against panel degradation.
 * A flat annualSavings × 25 understates it badly, which loses the sale.
 */
function solar_lifetime_savings(float $annualSavings, int $netCost): int
{
    $total = 0.0;
    for ($year = 0; $year < SOLAR_LIFETIME_YEARS; $year++) {
        $total += $annualSavings
            * (1 + SOLAR_TARIFF_ESCALATION) ** $year
            * (1 - SOLAR_DEGRADATION) ** $year;
    }
    return (int) round($total - $netCost);
}

/**
 * Equated monthly instalment. $annualRate as a percentage (e.g. 9.5).
 * Falls back to simple division at 0% so the form never divides by zero.
 */
function solar_emi(int $principal, float $annualRate, int $months): int
{
    if ($principal <= 0 || $months <= 0) {
        return 0;
    }
    $r = $annualRate / 12 / 100;
    if ($r <= 0) {
        return (int) round($principal / $months);
    }
    $factor = (1 + $r) ** $months;
    return (int) round($principal * $r * $factor / ($factor - 1));
}

/** '₹1,23,456' — Indian digit grouping, which number_format() cannot do. */
function inr(int $amount): string
{
    $sign = $amount < 0 ? '-' : '';
    $n = (string) abs($amount);
    if (strlen($n) <= 3) {
        return $sign . '₹' . $n;
    }
    $last3 = substr($n, -3);
    $rest = substr($n, 0, -3);
    $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
    return $sign . '₹' . $rest . ',' . $last3;
}
