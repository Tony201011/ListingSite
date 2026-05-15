<?php

namespace Tests\Unit;

use App\Support\PhotoVerificationGalleryRenderer;
use Tests\TestCase;

class PhotoVerificationGalleryRendererTest extends TestCase
{
    public function test_render_outputs_popup_gallery_markup_for_all_images(): void
    {
        $html = PhotoVerificationGalleryRenderer::render(
            '["https://example.com/photo-1.jpg","https://example.com/photo-2.jpg"]',
            60,
            60,
            1,
        )->toHtml();

        $this->assertStringContainsString('x-teleport="body"', $html);
        $this->assertStringContainsString('https://example.com/photo-1.jpg', $html);
        $this->assertStringContainsString('https://example.com/photo-2.jpg', $html);
        $this->assertStringContainsString('+1 more', $html);
        $this->assertStringContainsString('openGallery(1)', $html);
        $this->assertStringContainsString('@click.stop="openGallery(0)"', $html);
        $this->assertStringContainsString('@click.stop="openGallery(1)"', $html);
        $this->assertSame(2, substr_count($html, '@click.stop="openGallery(1)"'));
    }

    public function test_render_can_disable_popup_overlay_markup(): void
    {
        $html = PhotoVerificationGalleryRenderer::render(
            '["https://example.com/photo-1.jpg","https://example.com/photo-2.jpg"]',
            60,
            60,
            null,
            false,
            false,
        )->toHtml();

        $this->assertStringNotContainsString('x-teleport="body"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringNotContainsString('@click.stop="openGallery(0)"', $html);
    }

    public function test_render_single_mode_limits_output_to_first_image(): void
    {
        $html = PhotoVerificationGalleryRenderer::render(
            '["https://example.com/photo-1.jpg","https://example.com/photo-2.jpg"]',
            60,
            60,
            null,
            true,
            false,
        )->toHtml();

        $this->assertStringContainsString('https://example.com/photo-1.jpg', $html);
        $this->assertStringNotContainsString('https://example.com/photo-2.jpg', $html);
        $this->assertStringNotContainsString('+1 more', $html);
    }
}
