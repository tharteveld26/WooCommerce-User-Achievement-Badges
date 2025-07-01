<?php
require 'woocommerce-user-achievement-badges.php';

// Simulate user & badge data
$user_id = 123;
$badge_id = 456;

// Fake order/product logic here if needed...

if (function_exists('tbc_user_has_unlocked_badge')) {
    echo "Function loaded ✅\n";
} else {
    echo "Function missing ❌\n";
}
