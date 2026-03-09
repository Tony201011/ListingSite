<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EscortReviewPage;

class EscortReviewController extends Controller
{
    public function show()
    {
        $escortReviewPage = EscortReviewPage::first();
        return view('escort-review', compact('escortReviewPage'));
    }

    // Admin edit form
    public function edit()
    {
        $escortReviewPage = EscortReviewPage::first();
        return view('admin.escort-review-edit', compact('escortReviewPage'));
    }

    // Admin update
    public function update(Request $request)
    {
        $escortReviewPage = EscortReviewPage::first();
        $escortReviewPage->content = $request->input('content');
        $escortReviewPage->save();
        return redirect()->route('admin.escort-review.edit')->with('success', 'Escort review page updated.');
    }
}
