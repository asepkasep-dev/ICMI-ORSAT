<?php

namespace Tests\Unit;

use App\Support\ContentSanitizer;
use Tests\TestCase;

class ContentSanitizerTest extends TestCase
{
    public function test_script_tags_are_removed(): void
    {
        $clean = ContentSanitizer::clean('<p>Halo</p><script>alert("xss")</script>');

        $this->assertStringNotContainsStringIgnoringCase('<script', $clean);
        $this->assertStringNotContainsString('alert(', $clean);
        $this->assertStringContainsString('<p>Halo</p>', $clean);
    }

    public function test_event_handler_attributes_are_removed(): void
    {
        $clean = ContentSanitizer::clean('<p onclick="alert(1)" onmouseover="alert(2)">Teks</p>');

        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onmouseover', $clean);
        $this->assertStringContainsString('Teks', $clean);
    }

    public function test_javascript_urls_are_removed(): void
    {
        $clean = ContentSanitizer::clean('<a href="javascript:alert(1)">Klik</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
    }

    public function test_img_iframe_and_style_tags_are_removed(): void
    {
        $clean = ContentSanitizer::clean('<img src=x onerror=alert(1)><iframe src="https://evil.example"></iframe><style>body{display:none}</style><p>Aman</p>');

        $this->assertStringNotContainsStringIgnoringCase('<img', $clean);
        $this->assertStringNotContainsStringIgnoringCase('<iframe', $clean);
        $this->assertStringNotContainsStringIgnoringCase('<style', $clean);
        $this->assertStringContainsString('<p>Aman</p>', $clean);
    }

    public function test_inline_style_attributes_are_removed(): void
    {
        $clean = ContentSanitizer::clean('<p style="background:url(javascript:alert(1))">Teks</p>');

        $this->assertStringNotContainsString('style=', $clean);
    }

    public function test_safe_formatting_is_preserved(): void
    {
        $clean = ContentSanitizer::clean('<p><strong>Tebal</strong> dan <em>miring</em></p><ul><li>Poin</li></ul><blockquote>Kutipan</blockquote>');

        $this->assertStringContainsString('<strong>Tebal</strong>', $clean);
        $this->assertStringContainsString('<em>miring</em>', $clean);
        $this->assertStringContainsString('<li>Poin</li>', $clean);
        $this->assertStringContainsString('<blockquote>Kutipan</blockquote>', $clean);
    }

    public function test_external_links_get_nofollow_and_blank_target(): void
    {
        $clean = ContentSanitizer::clean('<a href="https://example.com">Tautan</a>');

        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('rel="nofollow', $clean);
        $this->assertStringContainsString('target="_blank"', $clean);
    }

    public function test_plaintext_is_wrapped_in_paragraphs(): void
    {
        $clean = ContentSanitizer::clean("Paragraf pertama.\n\nParagraf kedua.");

        $this->assertStringContainsString('<p>Paragraf pertama.</p>', $clean);
        $this->assertStringContainsString('<p>Paragraf kedua.</p>', $clean);
    }

    public function test_html_entities_in_plaintext_are_escaped(): void
    {
        $clean = ContentSanitizer::clean('<script>alert(1)</script>');

        $this->assertStringNotContainsStringIgnoringCase('<script', $clean);
    }

    public function test_text_helper_returns_plain_text(): void
    {
        $text = ContentSanitizer::text("<p>Halo <strong>dunia</strong>!</p>\n\n<p>Baris kedua.</p>");

        $this->assertSame('Halo dunia! Baris kedua.', $text);
    }

    public function test_empty_content_returns_empty_string(): void
    {
        $this->assertSame('', ContentSanitizer::clean(null));
        $this->assertSame('', ContentSanitizer::clean('   '));
        $this->assertSame('', ContentSanitizer::text(null));
    }
}