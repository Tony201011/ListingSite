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
    }
}
