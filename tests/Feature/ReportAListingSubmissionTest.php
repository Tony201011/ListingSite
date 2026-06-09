<?php

namespace Tests\Feature;

use App\Models\ListingContentReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportAListingSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_a_listing_page_prefills_listing_fields_from_query_parameters(): void
    {
        $response = $this->get(route('report-a-listing', [
            'listing_url' => 'https://hotescort.com.au/escorts/vic/melbourne/example',
            'listing_id' => '12345',
            'advertiser_name' => 'Example Advertiser',
        ]));

        $response->assertOk();
        $response->assertSee('value="https://hotescort.com.au/escorts/vic/melbourne/example"', false);
        $response->assertSee('value="12345"', false);
        $response->assertSee('value="Example Advertiser"', false);
    }

    public function test_user_can_submit_report_a_listing_with_evidence_upload(): void
    {
        Storage::fake('public');
        config(['media.upload_disk' => 'public']);

        $response = $this->post(route('report-a-listing.submit'), [
            'category' => 'underage_or_age_concern',
            'listing_url' => 'https://hotescort.com.au/escorts/nsw/sydney/sample',
            'advertiser_name' => 'Sample Advertiser',
            'listing_id' => '9876',
            'listing_phone' => '0400000000',
            'listing_location' => 'Sydney',
            'reporter_name' => 'John Doe',
            'reporter_email' => 'john@example.com',
            'reporter_phone' => '0411222333',
            'description' => 'This listing may involve underage content and should be reviewed immediately.',
            'is_urgent' => '1',
            'is_person_shown' => '1',
            'declaration_accuracy' => '1',
            'declaration_contact' => '1',
            'evidence' => [
                UploadedFile::fake()->image('proof.jpg')->size(500),
                UploadedFile::fake()->create('statement.pdf', 500, 'application/pdf'),
            ],
        ]);

        $response->assertRedirect(route('report-a-listing'));
        $response->assertSessionHas('success', 'Thank you. Your report has been submitted successfully.');

        $report = ListingContentReport::query()->first();

        $this->assertNotNull($report);
        $this->assertSame('9876', $report->listing_id);
        $this->assertSame('Sample Advertiser', $report->advertiser_name);
        $this->assertSame('underage_or_age_concern', $report->category);
        $this->assertSame(ListingContentReport::PRIORITY_HIGH, $report->priority_level);
        $this->assertSame(ListingContentReport::STATUS_NEW, $report->status);
        $this->assertTrue($report->is_urgent);
        $this->assertTrue($report->is_person_shown);
        $this->assertCount(2, $report->uploaded_evidence ?? []);

        foreach ($report->uploaded_evidence as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }
}
