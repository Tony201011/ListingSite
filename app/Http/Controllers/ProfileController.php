<?php

namespace App\Http\Controllers;

use App\Actions\CreateProfileAction;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function store(Request $request, CreateProfileAction $createProfileAction): RedirectResponse
    {
        $this->authorize('create', Profile::class);

        $profile = $createProfileAction->execute(
            $request->user(),
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'headline' => ['nullable', 'string', 'max:255'],
                'bio' => ['nullable', 'string'],
                'phone' => ['nullable', 'string', 'max:30'],
                'location' => ['nullable', 'string', 'max:255'],
                'is_active' => ['sometimes', 'boolean'],
            ])
        );

        return redirect()
            ->back()
            ->with('success', "Profile {$profile->name} created successfully.");
    }
}
