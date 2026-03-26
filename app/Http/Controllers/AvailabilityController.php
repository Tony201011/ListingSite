<?php

namespace App\Http\Controllers;

use App\Actions\GetUserAvailability;
use App\Actions\UpdateUserAvailability;
use App\Http\Requests\UpdateAvailabilityRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AvailabilityController extends Controller
{
    public function __construct(
        private UpdateUserAvailability $updateUserAvailability,
        private GetUserAvailability $getUserAvailability
    ) {
    }

    public function edit(): View
    {
        $saved = $this->getUserAvailability->forEdit(Auth::id());

        return view('set-your-availability', [
            'days' => $this->getUserAvailability->days(),
            'saved' => $saved,
        ]);
    }

    public function update(UpdateAvailabilityRequest $request): JsonResponse|RedirectResponse
    {
        $this->updateUserAvailability->execute(
            Auth::id(),
            $request->validated()['availability'] ?? []
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Availability updated successfully.',
            ]);
        }

        return redirect()
            ->route('availability.edit')
            ->with('success', 'Availability updated successfully.');
    }

    public function show(): View
    {
        $data = $this->getUserAvailability->forShow(Auth::id());

        return view('my-availability', $data);
    }
}
