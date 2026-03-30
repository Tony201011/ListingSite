<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeleteRate;
use App\Actions\DeleteRateGroup;
use App\Actions\GetMyRatePageData;
use App\Actions\StoreRate;
use App\Actions\UpdateRate;
use App\Actions\UpdateRateGroup;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRateRequest;
use App\Http\Requests\UpdateRateGroupRequest;
use App\Http\Requests\UpdateRateRequest;
use App\Models\Rate;
use App\Models\RateGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MyRateController extends Controller
{
    public function __construct(
        private GetMyRatePageData $getMyRatePageData,
        private StoreRate $storeRate,
        private UpdateRate $updateRate,
        private DeleteRate $deleteRate,
        private UpdateRateGroup $updateRateGroup,
        private DeleteRateGroup $deleteRateGroup
    ) {
    }

    public function index(): View
    {
        return view('profile.my-rate', $this->getMyRatePageData->execute(Auth::user()));
    }

    public function store(StoreRateRequest $request): JsonResponse
    {
        $this->authorize('create', Rate::class);

        $rate = $this->storeRate->execute(
            Auth::user(),
            $request->validated()
        );

        return response()->json($rate, 201);
    }

    public function update(UpdateRateRequest $request, Rate $rate): JsonResponse
    {
        $this->authorize('update', $rate);

        $rate = $this->updateRate->execute(
            Auth::user(),
            $rate,
            $request->validated()
        );

        return response()->json($rate);
    }

    public function destroy(Rate $rate): JsonResponse
    {
        $this->authorize('delete', $rate);

        $this->deleteRate->execute(Auth::user(), $rate);

        return response()->json(['success' => true]);
    }

    public function updateGroup(UpdateRateGroupRequest $request, RateGroup $group): JsonResponse
    {
        $this->authorize('update', $group);

        $group = $this->updateRateGroup->execute(
            Auth::user(),
            $group,
            $request->validated()
        );

        return response()->json($group);
    }

    public function destroyGroup(RateGroup $group): JsonResponse
    {
        $this->authorize('delete', $group);

        $this->deleteRateGroup->execute(Auth::user(), $group);

        return response()->json(['success' => true]);
    }
}
