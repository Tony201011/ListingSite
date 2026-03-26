<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEscortReviewRequest;
use App\Models\EscortReviewPage;

class EscortReviewController extends Controller
{
    public function show()
    {
        $escortReviewPage = EscortReviewPage::first();

        return view('escort-review', compact('escortReviewPage'));
    }

    public function edit()
    {
        $escortReviewPage = EscortReviewPage::first();

        return view('admin.escort-review-edit', compact('escortReviewPage'));
    }

    public function update(UpdateEscortReviewRequest $request)
    {
        $escortReviewPage = EscortReviewPage::first();

        if (! $escortReviewPage) {
            $escortReviewPage = new EscortReviewPage();
        }

        $escortReviewPage->content = $request->validated('content');
        $escortReviewPage->save();

        return redirect()
            ->route('admin.escort-review.edit')
            ->with('success', 'Escort review page updated.');
    }
}
