<?php
// tests/test_upload.php — Tests for includes/upload.php pure helpers.
// validate_upload() exits on failure (it calls json_response) so it isn't
// covered here; it's exercised end-to-end via the upload endpoints.

declare(strict_types=1);

require_once __DIR__ . '/harness.php';
require_once __DIR__ . '/../includes/upload.php';

T::group('random_filename', function () {
    $a = random_filename('png');
    $b = random_filename('png');
    T::true($a !== $b, 'two calls return different names');
    T::true(str_ends_with($a, '.png'), 'extension preserved');
    T::eq(1, preg_match('/^[a-f0-9]{32}\.png$/', $a), '32 hex chars + ext');
});

T::group('delete_local_upload — guards', function () {
    $base   = realpath(dirname(__DIR__) . '/uploads');
    T::true($base !== false, 'uploads/ exists in the working tree');

    // Create a sandbox file we own and can delete safely.
    $sandbox = $base . DIRECTORY_SEPARATOR . 'projects' . DIRECTORY_SEPARATOR . '__test_delete_' . bin2hex(random_bytes(4)) . '.png';
    file_put_contents($sandbox, 'fake png');
    T::true(file_exists($sandbox), 'sandbox file created');

    $url = '/uploads/projects/' . basename($sandbox);
    delete_local_upload($url);
    T::eq(false, file_exists($sandbox), 'happy path: file at /uploads/... is removed');

    // External URLs are no-ops.
    delete_local_upload('https://other.example.com/uploads/projects/x.png');
    delete_local_upload('https://attacker/etc/passwd');
    T::true(true, 'external URL is silently ignored (no error)');

    // Path traversal: a URL that resolves outside uploads/ must NOT delete
    // anything. Drop a tripwire next to the project root and confirm it
    // survives any attempt that crafts ../ segments.
    $tripwire = dirname(__DIR__) . DIRECTORY_SEPARATOR . '__tripwire_' . bin2hex(random_bytes(4)) . '.txt';
    file_put_contents($tripwire, 'do not delete');
    try {
        delete_local_upload('/uploads/../' . basename($tripwire));
        delete_local_upload('/uploads/projects/../../' . basename($tripwire));
        T::true(file_exists($tripwire), 'tripwire outside uploads/ is preserved against ../ traversal');
    } finally {
        @unlink($tripwire);
    }

    // Already-missing files are tolerated silently.
    delete_local_upload('/uploads/projects/does-not-exist-' . bin2hex(random_bytes(4)) . '.png');
    T::true(true, 'missing file is a no-op (no exception)');
});
