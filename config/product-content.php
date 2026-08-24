<?php
/**
 * Product detail page content helpers.
 *
 * Kept out of product-details.php so the spec parser can be unit-tested without
 * booting the page (and its DB connection). See tests/test-product-specs.php.
 */

/**
 * Turns the free-text `specs` column into label/value rows.
 *
 * The column is authored by hand in the admin panel as a pipe-delimited string,
 * e.g. '540W | 21% efficiency | 25-year warranty'. Each part is usually a value
 * with the label implied ('540W') or trailing it ('21% efficiency').
 *
 * ponytail: keyword lookup + "leading number means value-first" heuristic.
 * Good enough for hand-written spec strings; if products ever need structured
 * specs, add a JSON column and drop this.
 *
 * @return list<array{0:string,1:string}> [label, value] pairs
 */
function product_specs(?string $specs): array
{
    $rows = [];

    foreach (explode('|', (string) $specs) as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        // 'Warranty: 25 years' — explicit label always wins.
        if (str_contains($part, ':')) {
            [$label, $value] = array_map('trim', explode(':', $part, 2));
            if ($label !== '' && $value !== '') {
                $rows[] = [$label, $value];
                continue;
            }
        }

        // '21% efficiency', '25-year warranty' — value first, label is the rest.
        if (preg_match('/^([\d.]+\s*[^\s]*)\s+(.+)$/u', $part, $m)) {
            $rows[] = [ucfirst($m[2]), $m[1]];
            continue;
        }

        // Bare value like '540W' or '5kWh' — infer the label from its unit.
        $rows[] = [product_spec_label($part), $part];
    }

    return $rows;
}

/**
 * Best-effort label for a bare spec value. Falls back to a neutral heading
 * rather than guessing wrong, so a row is never mislabelled.
 */
function product_spec_label(string $value): string
{
    $units = [
        '/\d\s*kwh$/i'   => 'Capacity',
        '/\d\s*(kw|w)$/i' => 'Output',
        '/\d\s*v$/i'     => 'Voltage',
        '/\d\s*ah$/i'    => 'Battery Capacity',
        '/\d\s*%$/i'     => 'Efficiency',
        '/cycles?$/i'    => 'Cycle Life',
        '/warranty$/i'   => 'Warranty',
        '/mppt/i'        => 'Charge Controller',
        '/wi-?fi/i'      => 'Monitoring',
    ];

    foreach ($units as $pattern => $label) {
        if (preg_match($pattern, $value)) {
            return $label;
        }
    }

    return 'Specification';
}

/**
 * Static marketing copy for the detail page, varied by product category.
 * Unknown categories fall through to a generic solar block so the page is
 * never half-empty.
 */
function product_copy(string $category): array
{
    $common = [
        'steps' => [
            ['Free Site Survey', 'Our engineer visits your property, measures usable roof area and studies your last few electricity bills.'],
            ['System Design & Quote', 'You get a written proposal with system size, expected generation, subsidy amount and final out-of-pocket cost.'],
            ['Installation', 'Certified technicians complete the install, typically in two to three days for a residential rooftop.'],
            ['Net Metering & Handover', 'We file the paperwork with your DISCOM, commission the system and walk you through monitoring it.'],
        ],
    ];

    $copy = [
        'panel' => [
            'trust' => ['25-year performance warranty', 'BIS certified', 'Subsidy eligible'],
            'benefits_title' => 'Why Choose This Panel',
            'benefits' => [
                ['sun', 'High Output in Real Conditions', 'Monocrystalline cells hold their output better in low light and Indian summer heat than cheaper polycrystalline alternatives.'],
                ['shield', 'Built for Indian Weather', 'Tempered glass and an anodised aluminium frame rated for high wind load, hail and monsoon exposure.'],
                ['arrow-up', 'Slow, Predictable Degradation', 'Typically retains over 80% of rated output at year 25, so your savings stay dependable for decades.'],
                ['clipboard', 'Fully Subsidy Compliant', 'Meets the ALMM and BIS requirements needed to claim your PM Surya Ghar subsidy without complications.'],
            ],
            'faqs' => [
                ['How many panels will my home need?', 'Most Indian homes need between 6 and 12 panels for a 3kW to 5kW system. The exact count depends on your monthly consumption and usable roof area, both of which we confirm during the free site survey.'],
                ['How much roof space is required?', 'Budget roughly 70 to 100 square feet per kilowatt. A typical 3kW system needs about 250 to 300 square feet of shade-free roof.'],
                ['What maintenance do panels need?', 'Very little. A rinse every few weeks during dry and dusty months keeps output high. We also offer an annual maintenance contract that includes cleaning and a performance check.'],
                ['Do panels work during the monsoon?', 'Yes, though output drops on heavily overcast days. Annual generation estimates already account for the monsoon, and grid or battery backup covers the shortfall.'],
            ],
        ],
        'inverter' => [
            'trust' => ['5-year warranty', 'Wi-Fi monitoring', 'Battery ready'],
            'benefits_title' => 'Why Choose This Inverter',
            'benefits' => [
                ['sun', 'MPPT Tracking', 'Continuously finds the optimal operating point of your array, pulling meaningfully more energy out of the same panels than a basic inverter.'],
                ['shield', 'Backup Through Outages', 'Hybrid design keeps essential circuits running from battery when the grid drops, with no manual switching.'],
                ['settings', 'Monitor From Your Phone', 'Built-in Wi-Fi reports live generation, consumption and faults, so a problem is visible the day it starts rather than at the next bill.'],
                ['arrow-up', 'Room to Grow', 'Add panels or battery capacity later without replacing the inverter as your household load increases.'],
            ],
            'faqs' => [
                ['What size inverter do I need?', 'The inverter is normally matched to your array size, often slightly under it since panels rarely hit peak output. We size it against your actual load profile during the survey.'],
                ['Will it run my air conditioner?', 'On a suitably sized system with adequate battery capacity, yes. AC units draw heavily at startup, so we confirm the surge rating covers your specific appliances before quoting.'],
                ['Does it work without a battery?', 'Yes. It runs grid-tied without a battery and can have storage added later, which is a common way to spread the cost.'],
                ['What happens during a power cut?', 'With a battery connected, backed-up circuits switch over automatically within milliseconds. Without one, the inverter safely shuts down as grid safety rules require.'],
            ],
        ],
        'battery' => [
            'trust' => ['6000+ charge cycles', 'Wall mounted', 'Zero maintenance'],
            'benefits_title' => 'Why Choose This Battery',
            'benefits' => [
                ['shield', 'Power Through Outages', 'Keeps lights, fans, Wi-Fi and the fridge running through a cut, with no fuel, no fumes and no noise.'],
                ['arrow-up', 'Long Service Life', 'Over 6000 charge cycles — roughly a decade and a half of daily use, several times what a lead-acid bank delivers.'],
                ['settings', 'Genuinely Maintenance Free', 'Sealed lithium chemistry needs no water topping, no ventilated battery room and no periodic servicing.'],
                ['package', 'Compact Wall Mount', 'Mounts flat against a wall in a utility area or garage instead of occupying floor space.'],
            ],
            'faqs' => [
                ['How long will it run my home?', 'A 5kWh battery typically covers essential loads — lights, fans, router, television and refrigerator — for eight to twelve hours. Heavy appliances such as air conditioners draw that down considerably faster.'],
                ['How long does the battery last?', 'Rated for 6000+ cycles, which works out to roughly 15 years of daily cycling while retaining most of its usable capacity.'],
                ['Can I add a battery to an existing system?', 'Usually yes, provided your inverter is hybrid or battery-ready. We check compatibility during the survey and tell you plainly if the inverter needs changing.'],
                ['Is lithium safe indoors?', 'Yes. The chemistry used here is thermally stable, sealed and vents no gas, with a battery management system that cuts off on over-temperature, over-charge or over-current.'],
            ],
        ],
    ];

    $default = [
        'trust' => ['Certified equipment', 'Expert installation', 'Subsidy eligible'],
        'benefits_title' => 'Why Choose This Product',
        'benefits' => [
            ['sun', 'Cut Your Electricity Bill', 'A correctly sized solar system offsets most of a typical household daytime load, and the savings compound every year rates rise.'],
            ['shield', 'Certified & Warranty Backed', 'We supply only BIS certified equipment from established manufacturers, with the full warranty registered in your name.'],
            ['wrench', 'Installed by Our Own Team', 'Certified in-house technicians handle the install — not subcontractors — so accountability for the work stays with us.'],
            ['clipboard', 'Subsidy Paperwork Handled', 'We prepare and file the PM Surya Ghar application and DISCOM net metering forms on your behalf.'],
        ],
        'faqs' => [
            ['How much can I save?', 'Most households offset a large share of their bill. The precise figure depends on your consumption, roof orientation and local tariff, all of which the free site survey establishes.'],
            ['What subsidy am I eligible for?', 'Residential rooftop systems can claim a central subsidy under the PM Surya Ghar scheme, with the amount scaling by system size. We confirm your eligibility before you commit.'],
            ['How long does installation take?', 'A residential rooftop install is usually finished in two to three days once material is on site. Net metering approval from the DISCOM adds a few weeks.'],
            ['Do you provide after-sales service?', 'Yes. Every install includes a warranty, and we offer annual maintenance contracts covering cleaning, inspection and performance checks.'],
        ],
    ];

    return array_merge($common, $copy[$category] ?? $default);
}
