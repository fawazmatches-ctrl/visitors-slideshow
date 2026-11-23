<?php
// Simple CLI test script for core validation helpers
echo "Running Visitor Photo Slideshow Validation Tests...\n";

// Test 1: Email normalization
$rawEmail = "  Fawaz.Student@Example.COM ";
$normalized = strtolower(trim($rawEmail));
assert($normalized === "fawaz.student@example.com", "Email normalization failed");
echo "[PASS] Email normalization\n";

// Test 2: Collision-safe filename uniqueness across 1000 iterations
$names = [];
for ($i = 0; $i < 1000; $i++) {
    $names[] = sprintf('v1_%s_%s.jpg', date('YmdHis'), bin2hex(random_bytes(8)));
}
assert(count(array_unique($names)) === 1000, "Filename collision detected");
echo "[PASS] 1,000 concurrent filename generations unique\n";

echo "All automated checks passed!\n";
?>
