<?php

namespace App\Http\Controllers\Profile;

use App\Actions\DeleteRate;
use App\Actions\DeleteRateGroup;
use App\Actions\GetActiveProviderProfile;
use App\Actions\GetMyRatePageData;
use App\Actions\StoreRate;
use App\Actions\StoreRateGroup;
use App\Actions\UpdateRate;
use App\Actions\UpdateRateGroup;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRateGroupRequest;
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
        private StoreRateGroup $storeRateGroup,
        private UpdateRateGroup $updateRateGroup,
        private DeleteRateGroup $deleteRateGroup,
        private GetActiveProviderProfile $getActiveProviderProfile
    ) {}

    public function index(): View
    {
        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        return view('profile.my-rate', $this->getMyRatePageData->execute($profile));
    }

    public function store(StoreRateRequest $request): JsonResponse
    {
        $this->authorize('create', Rate::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->storeRate->execute(
            $profile,
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }

    public function update(UpdateRateRequest $request, Rate $rate): JsonResponse
    {
        $this->authorize('update', $rate);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->updateRate->execute(
            $profile,
            $rate,
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }

    public function destroy(Rate $rate): JsonResponse
    {
        $this->authorize('delete', $rate);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->deleteRate->execute($profile, $rate);

        return response()->json($result->toPayload(), $result->status());
    }

    public function storeGroup(StoreRateGroupRequest $request): JsonResponse
    {
        $this->authorize('create', RateGroup::class);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->storeRateGroup->execute(
            $profile,
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }

    public function updateGroup(UpdateRateGroupRequest $request, RateGroup $group): JsonResponse
    {
        $this->authorize('update', $group);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->updateRateGroup->execute(
            $profile,
            $group,
            $request->validated()
        );

        return response()->json($result->toPayload(), $result->status());
    }

    public function destroyGroup(RateGroup $group): JsonResponse
    {
        $this->authorize('delete', $group);

        $profile = $this->getActiveProviderProfile->execute(Auth::user());

        $result = $this->deleteRateGroup->execute($profile, $group);

        return response()->json($result->toPayload(), $result->status());
    }
}
