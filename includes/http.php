<?php
// includes/http.php — Small curl wrappers shared by the OAuth callbacks.
// Both functions throw RuntimeException on transport / parse failure so the
// caller can surface a meaningful error instead of a misleading "missing
// field" message later in the flow.

function http_post(string $url, array $form, array $extraHeaders = []): array {
    $headers = array_merge(['Accept: application/json'], $extraHeaders);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($form),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_USERAGENT      => 'PortfolioApp/1.0',
    ]);
    return curl_exec_json($ch, $url);
}

function http_get(string $url, array $headers = []): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_USERAGENT      => 'PortfolioApp/1.0',
    ]);
    return curl_exec_json($ch, $url);
}

/** Convenience: bearer-token GET that returns parsed JSON. */
function http_get_bearer(string $url, string $token, array $extraHeaders = []): array {
    $headers = array_merge(["Authorization: Bearer $token", 'Accept: application/json'], $extraHeaders);
    return http_get($url, $headers);
}

function curl_exec_json($ch, string $url): array {
    $body  = curl_exec($ch);
    $errno = curl_errno($ch);
    $err   = curl_error($ch);
    $code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($errno !== 0) {
        throw new RuntimeException("curl error {$errno} fetching {$url}: {$err}");
    }
    if ($code >= 400) {
        throw new RuntimeException("HTTP {$code} fetching {$url}");
    }
    $decoded = json_decode($body, true);
    if (!is_array($decoded)) {
        throw new RuntimeException("Invalid JSON from {$url}");
    }
    return $decoded;
}
