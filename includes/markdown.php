<?php
// includes/markdown.php — Thin wrapper around Parsedown.
// Safe mode is on so raw HTML in the body is escaped, not rendered. The
// admin is the only writer, but defense in depth costs us nothing here.

require_once __DIR__ . '/Parsedown.php';

function render_markdown(string $md): string {
    static $parser = null;
    if ($parser === null) {
        $parser = new Parsedown();
        $parser->setSafeMode(true);
        $parser->setUrlsLinked(true);
    }
    return $parser->text($md);
}

/**
 * Plain-text excerpt from markdown — strips formatting and trims to N chars
 * at a word boundary. Used as a fallback when no manual excerpt is provided.
 */
function markdown_excerpt(string $md, int $maxLen = 200): string {
    $html  = render_markdown($md);
    $text  = trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $text  = preg_replace('/\s+/', ' ', $text);
    if (mb_strlen($text) <= $maxLen) return $text;
    $cut = mb_substr($text, 0, $maxLen);
    $sp  = mb_strrpos($cut, ' ');
    if ($sp !== false && $sp > $maxLen * 0.6) $cut = mb_substr($cut, 0, $sp);
    return rtrim($cut, " ,;:.-") . '…';
}
