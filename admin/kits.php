<?php
/**
 * Kits are managed from the Products screen now — one catalogue, one place to edit it.
 * Kept as a redirect because the URL may be bookmarked.
 */
require_once __DIR__ . '/../config/auth.php';
require_admin();

redirect('/admin/products.php');
