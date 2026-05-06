<?php
// tests/harness.php — Tiny zero-dependency test harness.
//
// Why hand-rolled instead of PHPUnit: the project has no Composer, no build
// step, and runs on shared hosting with whatever PHP cPanel ships. A 60-line
// harness keeps the "edit and deploy" workflow intact and the CI surface small.
//
// Usage from the repo root:
//     php tests/run.php

declare(strict_types=1);

final class T {
    public static int $passed = 0;
    public static int $failed = 0;
    public static array $failures = [];

    public static function eq(mixed $expected, mixed $actual, string $msg): void {
        if ($expected === $actual) {
            self::$passed++;
            return;
        }
        self::$failed++;
        self::$failures[] = sprintf(
            "  ✗ %s\n      expected: %s\n      actual:   %s",
            $msg,
            self::dump($expected),
            self::dump($actual)
        );
    }

    public static function true(bool $cond, string $msg): void {
        if ($cond) { self::$passed++; return; }
        self::$failed++;
        self::$failures[] = "  ✗ {$msg} (expected true)";
    }

    public static function contains(string $needle, string $haystack, string $msg): void {
        if (str_contains($haystack, $needle)) { self::$passed++; return; }
        self::$failed++;
        self::$failures[] = sprintf(
            "  ✗ %s\n      needle:   %s\n      haystack: %s",
            $msg, self::dump($needle), self::dump($haystack)
        );
    }

    public static function notContains(string $needle, string $haystack, string $msg): void {
        if (!str_contains($haystack, $needle)) { self::$passed++; return; }
        self::$failed++;
        self::$failures[] = sprintf(
            "  ✗ %s\n      forbidden needle was present: %s\n      haystack: %s",
            $msg, self::dump($needle), self::dump($haystack)
        );
    }

    public static function group(string $name, callable $body): void {
        echo "\n[{$name}]\n";
        $before = self::$failed;
        $body();
        $delta = self::$failed - $before;
        if ($delta > 0) echo "  ({$delta} failure" . ($delta === 1 ? '' : 's') . " in this group)\n";
    }

    private static function dump(mixed $v): string {
        return var_export($v, true);
    }
}
