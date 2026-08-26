<?php
/**
 * Kits moved into the Products section — they are listed in the products grid and
 * each has its own detail page at /product-details.php?kit=<slug>.
 *
 * Kept as a redirect because the URL was published and linked. Old deep links used a
 * '#<slug>' fragment, which browsers never send to the server, so a specific kit
 * cannot be recovered here; the filtered grid is the closest honest destination.
 */
require_once __DIR__ . '/config/helpers.php';

// 301, not the helper's 302: the move is permanent, so the ranking should follow.
header('Location: /products.php?category=kit', true, 301);
exit;
