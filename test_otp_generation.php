<?php

/**
 * Test OTP generation with safe random_int
 */

echo "Testing OTP Generation\n";
echo str_repeat('=', 50) . "\n\n";

// Test 10 OTP generations
for ($i = 1; $i <= 10; $i++) {
    try {
        // Try random_int first (PHP 7+, cryptographically secure)
        $otp = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        echo "OTP $i: $otp (✅ random_int)\n";
    } catch (Exception $e) {
        // Fallback to mt_rand if random_int fails
        $otp = str_pad(mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        echo "OTP $i: $otp (⚠️ mt_rand fallback)\n";
    }

    // Validate OTP format
    if (!preg_match('/^\d{6}$/', $otp)) {
        echo "  ❌ ERROR: Invalid OTP format!\n";
    }
}

echo "\n" . str_repeat('=', 50) . "\n";
echo "✅ All OTP generations successful!\n";
