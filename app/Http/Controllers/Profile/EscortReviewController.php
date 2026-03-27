<?php

namespace App\Http\Controllers\Profile;
use App\Http\Controllers\Controller;
use App\Actions\GetEscortReviewPage;
use App\Actions\UpdateEscortReviewPage;
use App\Http\Requests\UpdateEscortReviewRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EscortReviewController extends Controller
{
    public function __construct(
        private GetEscortReviewPage $getEscortReviewPage,
        private UpdateEscortReviewPage $updateEscortReviewPage
    ) {
    }

    public function show(): View
    {
        $escortReviewPage = $this->getEscortReviewPage->execute();

        return view('profile.escort-review', compact('escortReviewPage'));
    }

    public function edit(): View
    {
        $escortReviewPage = $this->getEscortReviewPage->execute();

        return view('profile.escort-review-edit', compact('escortReviewPage'));
    }

    public function update(UpdateEscortReviewRequest $request): RedirectResponse
    {
        $this->updateEscortReviewPage->execute(
            $request->validated('content')
        );

        return redirect()
            ->route('admin.escort-review.edit')
            ->with('success', 'Escort review page updated.');
    }
}
