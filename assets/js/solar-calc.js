/**
 * Live solar estimate for solar-calculator.php.
 *
 * Mirrors config/solar-calc.php so the numbers on screen match what the server
 * writes into the lead. If you change a constant or a formula there, change it
 * here too — tests/test-solar-calc.php guards the PHP side, this file follows it.
 */
(function () {
  'use strict';

  var GEN_PER_KW_DAY = 4.0;
  var COST_PER_KW = 60000;
  var AREA_PER_KW = 80;
  var CO2_PER_KWH = 0.82;
  var PANEL_WATTS = 545;
  var LIFETIME_YEARS = 25;
  var TARIFF_ESCALATION = 0.03;
  var DEGRADATION = 0.005;
  var FINANCE_MONTHS = 60;
  var FINANCE_RATE = 9.5;

  var form = document.getElementById('calc-form');
  if (!form) return;

  var el = function (id) { return document.getElementById(id); };

  /** '₹1,23,456' — Indian digit grouping. Intl handles this with the en-IN locale. */
  var inr = new Intl.NumberFormat('en-IN', {
    style: 'currency', currency: 'INR', maximumFractionDigits: 0
  });
  var num = new Intl.NumberFormat('en-IN');

  // ₹30,000/kW for the first 2 kW, ₹18,000/kW for the 3rd, capped at ₹78,000.
  function subsidy(kw) {
    if (kw <= 0) return 0;
    if (kw <= 2) return Math.round(30000 * kw);
    if (kw < 3) return Math.round(60000 + 18000 * (kw - 2));
    return 78000; // cap
  }

  function roundKw(kw) {
    return Math.max(1, Math.round(kw * 2) / 2);
  }

  function emi(principal, annualRate, months) {
    if (principal <= 0 || months <= 0) return 0;
    var r = annualRate / 12 / 100;
    if (r <= 0) return Math.round(principal / months);
    var f = Math.pow(1 + r, months);
    return Math.round(principal * r * f / (f - 1));
  }

  function lifetimeSavings(annualSavings, netCost) {
    var total = 0;
    for (var y = 0; y < LIFETIME_YEARS; y++) {
      total += annualSavings * Math.pow(1 + TARIFF_ESCALATION, y) * Math.pow(1 - DEGRADATION, y);
    }
    return Math.round(total - netCost);
  }

  function estimate(bill, tariff, monthlyUnits, roofArea) {
    tariff = tariff > 0 ? tariff : 8.0;
    var units = monthlyUnits > 0 ? monthlyUnits : Math.max(0, bill) / tariff;

    var kw = roundKw(units / (GEN_PER_KW_DAY * 30));

    // Roof cap rounds DOWN — rounding up would need more roof than exists.
    var roofLimited = false;
    if (roofArea > 0) {
      var maxKw = Math.max(1, Math.floor(roofArea / AREA_PER_KW * 2) / 2);
      if (maxKw < kw) { kw = maxKw; roofLimited = true; }
    }

    var annualUnits = kw * GEN_PER_KW_DAY * 365;
    var gross = Math.round(kw * COST_PER_KW);
    var sub = subsidy(kw);
    var net = gross - sub;

    // Exported surplus earns less than it offsets, so cap savings at consumption.
    var offsetUnits = Math.min(annualUnits, units * 12);
    var annualSavings = offsetUnits * tariff;

    return {
      kw: kw,
      panels: Math.ceil(kw * 1000 / PANEL_WATTS),
      area: Math.round(kw * AREA_PER_KW),
      roofLimited: roofLimited,
      gross: gross,
      subsidy: sub,
      net: net,
      annualUnits: Math.round(annualUnits),
      monthlySavings: Math.round(annualSavings / 12),
      paybackYears: annualSavings > 0 ? Math.round(net / annualSavings * 10) / 10 : null,
      lifetime: lifetimeSavings(annualSavings, net),
      co2: Math.round(annualUnits * CO2_PER_KWH / 1000 * 10) / 10,
      trees: Math.round(annualUnits * CO2_PER_KWH / 21)
    };
  }

  function render() {
    var bill = Number(el('bill').value) || 0;
    var tariff = Number(el('tariff').value) || 8.0;
    var units = Number(el('units').value) || 0;
    var roof = Number(el('roof').value) || 0;

    var r = estimate(bill, tariff, units, roof);

    el('bill-out').textContent = inr.format(bill);
    el('out-kw').textContent = r.kw.toFixed(1) + ' kW';
    el('out-net').textContent = inr.format(r.net);
    el('out-net-2').textContent = inr.format(r.net);
    el('out-savings').textContent = inr.format(r.monthlySavings);
    el('out-payback').textContent = r.paybackYears === null ? '—' : r.paybackYears + ' yrs';
    el('out-gross').textContent = inr.format(r.gross);
    el('out-subsidy').textContent = '− ' + inr.format(r.subsidy);
    el('out-emi').textContent = inr.format(emi(r.net, FINANCE_RATE, FINANCE_MONTHS));
    el('out-panels').textContent = r.panels;
    el('out-area').textContent = num.format(r.area) + ' sq ft';
    el('out-annual').textContent = num.format(r.annualUnits) + ' units';
    el('out-lifetime').textContent = inr.format(r.lifetime);
    el('out-co2').textContent = r.co2;
    el('out-trees').textContent = num.format(r.trees);

    el('roof-note').classList.toggle('hidden', !r.roofLimited);

    // Carry the shown figures into the lead so sales sees what the customer saw.
    el('lead-kw').value = r.kw;
    el('lead-bill').value = bill;
  }

  form.addEventListener('input', render);
  render();
})();
