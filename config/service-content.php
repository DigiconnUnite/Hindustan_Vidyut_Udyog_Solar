<?php
/**
 * Long-form service page copy, keyed by the services table's `icon` column.
 *
 * The DB `description` stays the one-line summary used in cards and listings;
 * this is the extra detail only services.php renders. Kept here (not in the DB)
 * so it can be edited without a migration — same reasoning as config/product-content.php.
 *
 * Any service with no entry here simply renders its DB description alone.
 *
 * @return array{body:string, points:list<string>}|null
 */
function service_content(?string $icon): ?array
{
    static $content = [
        'clipboard' => [
            'body' => 'Every system starts with a visit. We measure usable roof area, check shading through
                the day, inspect your existing wiring and sanctioned load, and read your last few
                electricity bills — because the right system size comes from what you actually consume,
                not from how big the roof is.',
            'points' => [
                'Free of cost, with no obligation to buy',
                'Shading and structural assessment of the roof',
                'System size and generation estimate based on your real bills',
                'Written quote with subsidy and payback worked out',
            ],
        ],
        'wrench' => [
            'body' => 'Our own certified technicians handle the whole installation — mounting structure,
                ALMM-listed panels, inverter, wiring, earthing and safety gear. We also file the
                paperwork: DISCOM application, net-metering and the PM Surya Ghar subsidy claim are
                handled end to end, so you are not chasing approvals yourself.',
            'points' => [
                'Turnkey install by in-house certified technicians',
                'ALMM-listed panels and BIS-certified components',
                'DISCOM liaison, net-metering and subsidy filing included',
                'Most residential systems commissioned in days, not weeks',
            ],
        ],
        'shield' => [
            'body' => 'Solar is low maintenance, not no maintenance. Dust on the panels alone can cost you a
                noticeable share of output over a dry season, and a failing string often shows up in the
                data long before you would spot it on your bill. Our AMC covers scheduled cleaning,
                electrical checks and performance monitoring.',
            'points' => [
                'Scheduled panel cleaning and visual inspection',
                'Inverter health and electrical safety checks',
                'Generation monitoring with underperformance alerts',
                'Priority breakdown support and warranty coordination',
            ],
        ],
        'arrow-up' => [
            'body' => 'Households add load over time — an EV, another AC, a new floor. If your existing array
                no longer covers the bill, we assess what the current structure and inverter can carry and
                extend it, rather than starting over. Systems installed by other vendors are welcome.',
            'points' => [
                'Capacity review of your existing inverter and structure',
                'Add panels, upgrade the inverter, or add battery backup',
                'Works on systems installed by other vendors',
                'Revised net-metering paperwork handled for you',
            ],
        ],
    ];

    return $content[$icon] ?? null;
}
