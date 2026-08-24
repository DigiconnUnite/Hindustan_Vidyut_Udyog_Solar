<?php
/**
 * Per-page SEO tags. Pages set $pageTitle / $metaDescription / $ogImage / $jsonLd
 * before requiring components/header.php; everything falls back to a site default.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function seo_canonical(): string
{
    // Query strings that change the content get kept; tracking params do not.
    $keep = ['id', 'slug', 'category'];
    $query = array_intersect_key($_GET, array_flip($keep));
    $url = APP_URL . strtok($_SERVER['REQUEST_URI'], '?');
    return $query ? $url . '?' . http_build_query($query) : $url;
}

/** Organization + LocalBusiness — emitted on every page so search engines see it site-wide. */
function seo_organization_schema(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => 'Hindustan Vidyut Udyog Solar',
        'url' => APP_URL,
        'logo' => APP_URL . '/assets/images/hvul-logo.png',
        'image' => APP_URL . '/assets/images/hvul-logo.png',
        'description' => 'Rooftop solar installation, PM Surya Ghar subsidy assistance, panels, inverters and battery storage in Gurgaon, Haryana.',
        'telephone' => setting('company_phone', '+91 98765 43210'),
        'email' => setting('company_email', 'info@hvusolar.com'),
        'priceRange' => '₹₹',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => '810 & 811, 8th Floor, SVH 83 Metro Street, Sector 83',
            'addressLocality' => 'Gurgaon',
            'addressRegion' => 'Haryana',
            'postalCode' => '122004',
            'addressCountry' => 'IN',
        ],
        'areaServed' => ['Gurgaon', 'Delhi NCR', 'Faridabad', 'Noida', 'Haryana'],
        'sameAs' => array_values(array_filter([
            setting('social_facebook'),
            setting('social_twitter'),
            setting('social_youtube'),
        ], fn($u) => $u !== '' && $u !== '#')),
    ];
}

function seo_breadcrumb_schema(string $title): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => APP_URL . '/'],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => seo_canonical()],
        ],
    ];
}

function seo_json_ld(array $schema): string
{
    return '<script type="application/ld+json">'
        . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        . '</script>';
}
