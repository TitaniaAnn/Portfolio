<?php
// tests/test_markdown.php — Unit tests for includes/markdown.php.
// The wrapper is thin (Parsedown does the real work) but the safe-mode
// guarantee is security-critical, so we lock it here.

declare(strict_types=1);

require_once __DIR__ . '/harness.php';
require_once __DIR__ . '/../includes/markdown.php';

T::group('render_markdown — basic formatting', function () {
    T::contains('<h1>Hello</h1>',         render_markdown("# Hello"),                 'h1');
    T::contains('<strong>bold</strong>',  render_markdown('**bold**'),                'bold');
    T::contains('<em>italic</em>',        render_markdown('*italic*'),                'italic');
    T::contains('<code>x</code>',         render_markdown('`x`'),                     'inline code');
    T::contains('<pre>',                  render_markdown("```\ncode\n```"),          'fenced code block');
    T::contains('<a href="https://x.test">x</a>', render_markdown('[x](https://x.test)'), 'link');
});

T::group('render_markdown — safe mode', function () {
    // Parsedown safe mode escapes raw HTML rather than rendering it. This is
    // load-bearing: the writing/post.php page outputs render_markdown() result
    // directly without further escaping, and Parsedown is the only thing
    // standing between a hostile body and the DOM.
    $out = render_markdown('<script>alert(1)</script>');
    T::notContains('<script>',  $out, 'raw <script> tag is escaped, not rendered');
    T::contains('&lt;script&gt;', $out, 'raw <script> appears as escaped text');

    $out = render_markdown('<img src="x" onerror="alert(1)">');
    T::notContains('<img',  $out, 'raw <img> tag is escaped');

    // Markdown-style links to javascript: URLs should be neutralized by safe
    // mode (Parsedown drops the dangerous href).
    $out = render_markdown('[click](javascript:alert(1))');
    T::notContains('javascript:', $out, 'javascript: link href is filtered');

    // Inline-style raw HTML attributes are also escaped — the whole <a> tag
    // becomes plain text. We can't just grep for "href=" (that survives in the
    // escaped output), so instead assert the rendered HTML contains no live
    // anchor tag with a real href attribute.
    $out = render_markdown('<a href="javascript:alert(1)">click</a>');
    T::eq(0, preg_match('/<a\s[^>]*href=/i', $out), 'raw <a href="..."> tag is not rendered');
});

T::group('markdown_excerpt', function () {
    T::eq('',                              markdown_excerpt(''),                          'empty markdown');
    T::eq('plain text',                    markdown_excerpt('plain text'),                'short stays short');
    T::eq('hello world',                   markdown_excerpt('# hello world'),             'strips heading marker');
    T::eq('bold italic',                   markdown_excerpt('**bold** *italic*'),         'strips emphasis');

    // Long markdown collapses whitespace and ends with an ellipsis.
    $long = str_repeat('word ', 100);
    $excerpt = markdown_excerpt($long, 40);
    T::true(mb_strlen($excerpt) <= 41,                           'respects maxLen (allow ellipsis byte)');
    T::contains('…',                       $excerpt,             'ends with ellipsis when truncated');
    T::notContains('  ',                   $excerpt,             'collapses repeated spaces');

    // No truncation when the rendered text is already shorter than maxLen.
    $short = markdown_excerpt('# hi', 40);
    T::notContains('…', $short, 'no ellipsis when not truncated');
});
