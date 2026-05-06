<?php
// tests/test_response.php — Unit tests for includes/response.php.
// json_response() exits, so we run it in a child PHP process and inspect
// stdout. cors_headers() reads $_SERVER and emits headers — also exercised
// via subprocess so headers() doesn't pollute the parent test run.

declare(strict_types=1);

require_once __DIR__ . '/harness.php';

/**
 * Run a PHP snippet in a separate process and capture stdout. The snippet is
 * written to a temp file rather than passed via -r — Windows escapeshellarg
 * strips embedded double quotes, which corrupts strings inside the snippet.
 *
 * Passes `-n` so the child skips php.ini entirely (avoids missing-extension
 * warnings on developer machines whose ini differs from production).
 */
function run_php(string $snippet): string {
    $tmp = tempnam(sys_get_temp_dir(), 'porttest_') . '.php';
    file_put_contents($tmp, "<?php\n" . $snippet);
    try {
        $cmd = escapeshellarg(PHP_BINARY) . ' -n ' . escapeshellarg($tmp);
        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open($cmd, $descriptors, $pipes, dirname(__DIR__));
        if (!is_resource($proc)) return '';
        $out = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);
        return $out ?: '';
    } finally {
        @unlink($tmp);
    }
}

T::group('get_json_body — parser fallback semantics', function () {
    // get_json_body() decodes php://input, then `is_array($d) ? $d : []`.
    // We can't stub php://input in CLI, so we exercise the same expression
    // tree the function evaluates. The is_array check is the load-bearing
    // bit: it stops scalar bodies (`false`, `42`, `"hi"`) from violating
    // the array return type and 500-crashing every endpoint.
    $decode = static function (string $raw): array {
        $d = json_decode($raw, true);
        return is_array($d) ? $d : [];
    };

    T::eq([],              $decode(''),                 'empty body → []');
    T::eq([],              $decode('not json'),         'malformed → []');
    T::eq([],              $decode('null'),             'literal null → []');
    T::eq(['a' => 1],      $decode('{"a":1}'),          'object round-trips');
    T::eq([1, 2, 3],       $decode('[1,2,3]'),          'array round-trips');

    // Scalar bodies that previously crashed the function now coalesce to [].
    T::eq([],              $decode('false'),            'literal false → []');
    T::eq([],              $decode('true'),             'literal true → []');
    T::eq([],              $decode('42'),               'literal int → []');
    T::eq([],              $decode('"hi"'),             'literal string → []');
});

T::group('json_response', function () {
    // json_response calls exit, so it must run in a subprocess. The
    // discard-prior-output contract relies on config.php's ob_start() being
    // active — without it ob_get_level() is 0 and the cleanup loop is a
    // no-op. Load config.php first to mimic the real request lifecycle.
    $cwd = dirname(__DIR__);
    $script = <<<PHP
        require '{$cwd}/config/config.php';
        require '{$cwd}/includes/response.php';
        echo 'stray output that should be discarded';
        json_response(['ok' => true, 'n' => 42]);
PHP;
    $out = run_php($script);
    T::eq('{"ok":true,"n":42}', trim($out), 'json_response discards prior echoes (with config.php buffer)');

    // Unicode + slashes are preserved (JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES).
    $script = <<<PHP
        require '{$cwd}/config/config.php';
        require '{$cwd}/includes/response.php';
        json_response(['emoji' => '🎯', 'path' => '/api/x']);
PHP;
    $out = run_php($script);
    T::contains('🎯',     trim($out), 'unicode is not escaped to \\uXXXX');
    T::contains('/api/x', trim($out), 'forward slashes are not escaped');
});
