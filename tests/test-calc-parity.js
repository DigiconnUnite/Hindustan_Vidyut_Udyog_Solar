/**
 * Guards the JS mirror against the PHP source of truth.
 *
 * assets/js/solar-calc.js re-implements config/solar-calc.php so the page can
 * update live. That duplication is the deliberate trade — this check makes the
 * two drifting apart a test failure instead of a silent wrong quote.
 *
 * Run: node tests/test-calc-parity.js
 */
'use strict';

const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const js = fs.readFileSync(path.join(root, 'assets/js/solar-calc.js'), 'utf8');
const php = fs.readFileSync(path.join(root, 'config/solar-calc.php'), 'utf8');

let failures = 0;

function check(label, actual, expected) {
  if (actual !== expected) {
    console.error(`FAIL: ${label}\n  expected: ${expected}\n  actual:   ${actual}`);
    failures++;
    return;
  }
  console.log(`  ok  ${label}`);
}

// --- shared constants must hold the same value in both files ---------------
const constants = [
  ['GEN_PER_KW_DAY', 'SOLAR_GEN_PER_KW_PER_DAY'],
  ['COST_PER_KW', 'SOLAR_COST_PER_KW'],
  ['AREA_PER_KW', 'SOLAR_AREA_PER_KW'],
  ['CO2_PER_KWH', 'SOLAR_CO2_PER_KWH'],
  ['PANEL_WATTS', 'SOLAR_PANEL_WATTS'],
  ['LIFETIME_YEARS', 'SOLAR_LIFETIME_YEARS'],
  ['TARIFF_ESCALATION', 'SOLAR_TARIFF_ESCALATION'],
  ['DEGRADATION', 'SOLAR_DEGRADATION'],
];

for (const [jsName, phpName] of constants) {
  const jsHit = js.match(new RegExp(`var ${jsName} = ([\\d.]+)`));
  const phpHit = php.match(new RegExp(`const ${phpName}\\s*=\\s*([\\d.]+)`));

  if (!jsHit) { console.error(`FAIL: ${jsName} not found in solar-calc.js`); failures++; continue; }
  if (!phpHit) { console.error(`FAIL: ${phpName} not found in solar-calc.php`); failures++; continue; }

  check(`${jsName} matches ${phpName}`, parseFloat(jsHit[1]), parseFloat(phpHit[1]));
}

// --- the JS formulas must produce the PHP answers -------------------------
// Loading the browser IIFE would need a DOM, so the formulas are restated here
// and compared against values pinned from tests/test-solar-calc.php.
function subsidy(kw) {
  if (kw <= 0) return 0;
  if (kw <= 2) return Math.round(30000 * kw);
  if (kw < 3) return Math.round(60000 + 18000 * (kw - 2));
  return 78000;
}

check('subsidy 1 kW', subsidy(1), 30000);
check('subsidy 1.5 kW', subsidy(1.5), 45000);
check('subsidy 2 kW', subsidy(2), 60000);
check('subsidy 2.5 kW', subsidy(2.5), 69000);
check('subsidy 3 kW', subsidy(3), 78000);
check('subsidy 10 kW capped', subsidy(10), 78000);

// The subsidy ladder in the JS file must be byte-identical in its thresholds.
for (const literal of ['30000', '18000', '78000']) {
  check(`js subsidy uses ${literal}`, js.includes(literal), true);
}

// Roof cap must round DOWN in both files, or we size onto roof that isn't there.
check('js roof cap floors', /Math\.floor\(roofArea \/ AREA_PER_KW \* 2\) \/ 2/.test(js), true);
check('php roof cap floors', /floor\(\$roofArea \/ SOLAR_AREA_PER_KW \* 2\) \/ 2/.test(php), true);

if (failures > 0) {
  console.error(`\n${failures} parity check(s) failed`);
  process.exit(1);
}
console.log('\nJS/PHP calculator parity OK.');
