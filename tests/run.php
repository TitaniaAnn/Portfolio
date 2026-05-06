<?php
// tests/run.php — Run all tests/test_*.php files and exit non-zero on failure.
//
// Usage:
//     php tests/run.php
//     php tests/run.php util         # filter by basename substring

declare(strict_types=1);

require_once __DIR__ . '/harness.php';

$filter = $argv[1] ?? '';
$files  = glob(__DIR__ . '/test_*.php') ?: [];
sort($files);

if ($filter !== '') {
    $files = array_values(array_filter(
        $files,
        static fn(string $f) => str_contains(basename($f), $filter)
    ));
}

if (!$files) {
    fwrite(STDERR, "no test files matched\n");
    exit(2);
}

echo "Running " . count($files) . " test file" . (count($files) === 1 ? '' : 's') . "\n";
foreach ($files as $f) {
    echo "\n=== " . basename($f) . " ===";
    require $f;
}

echo "\n\n----------------------------------------\n";
printf("Passed: %d   Failed: %d\n", T::$passed, T::$failed);

if (T::$failed > 0) {
    echo "\nFailures:\n" . implode("\n", T::$failures) . "\n";
    exit(1);
}
echo "All tests passed.\n";
exit(0);
