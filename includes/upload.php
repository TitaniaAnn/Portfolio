<?php
// includes/upload.php — Shared file-upload validation used by both
// /api/uploads/ (project images) and /api/uploads/resume.php.

require_once __DIR__ . '/response.php';

/**
 * Validate a single $_FILES entry. On success returns:
 *   ['mime' => string, 'ext' => string]
 *
 * On failure, emits a JSON error response and exits — callers do not need to
 * branch on the result.
 *
 * @param array  $file         the $_FILES['name'] entry
 * @param array  $allowedMimes map of mime => extension, e.g. ['image/png' => 'png']
 * @param int    $maxBytes     maximum byte size
 */
function validate_upload(array $file, array $allowedMimes, int $maxBytes): array {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        json_response(['error' => 'No file uploaded'], 422);
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        json_response(['error' => 'Upload error code: ' . (int)$file['error']], 422);
    }
    if (($file['size'] ?? 0) > $maxBytes) {
        $mb = round($maxBytes / (1024 * 1024));
        json_response(['error' => "File too large (max {$mb} MB)"], 422);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mime, $allowedMimes)) {
        $list = implode(', ', array_values($allowedMimes));
        json_response(['error' => "Invalid file type. Allowed: {$list}"], 422);
    }

    return ['mime' => $mime, 'ext' => $allowedMimes[$mime]];
}

/**
 * Generate a random hex filename with the given extension. The filename is
 * not user-controlled so there is no path-traversal risk.
 */
function random_filename(string $ext): string {
    return bin2hex(random_bytes(16)) . '.' . $ext;
}
