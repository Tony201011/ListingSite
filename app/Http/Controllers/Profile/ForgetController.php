<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetSetAndForgetState;
use App\Actions\SaveSetAndForget;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveSetAndForgetRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ForgetController extends Controller
{
    public function __construct(
        private GetSetAndForgetState $getSetAndForgetState,
        private SaveSetAndForget $saveSetAndForget
    ) {}

    public function setForget(): View
    {
        $data = $this->getSetAndForgetState->execute(Auth::user());

        return view('profile.set-forget', $data);
    }

    public function save(SaveSetAndForgetRequest $request): JsonResponse
    {
        $result = $this->saveSetAndForget->execute(
            $request->user(),
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }
}
