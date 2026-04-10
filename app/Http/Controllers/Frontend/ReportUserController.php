<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserReportRequest;
use App\Jobs\SendUserReportEmailJob;
use App\Models\UserReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReportUserController extends Controller
{
    public function store(StoreUserReportRequest $request): JsonResponse
    {
        $report = UserReport::create([
            'provider_profile_id' => $request->validated('provider_profile_id'),
            'reporter_name' => $request->validated('reporter_name'),
            'reporter_email' => $request->validated('reporter_email'),
            'reason' => $request->validated('reason'),
            'description' => $request->validated('description'),
            'status' => 'pending',
            'is_read' => false,
        ]);

        try {
            SendUserReportEmailJob::dispatch($report->id);
        } catch (Throwable $e) {
            Log::warning('User report email dispatch failed after report was saved.', [
                'user_report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Report submitted successfully.']);
    }
}
