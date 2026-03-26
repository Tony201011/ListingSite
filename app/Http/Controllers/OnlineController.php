<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOnlineStatusRequest;
use App\Models\OnlineUser;
use Illuminate\Support\Facades\Auth;

class OnlineController extends Controller
{
    public function onlineNow()
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        $onlineStatus = false;
        $remainingUses = 4;
        $expiresAt = null;

        if ($user) {
            $onlineUser = OnlineUser::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'offline',
                    'usage_date' => today(),
                    'usage_count' => 0,
                ]
            );

            $onlineUser->resetDailyUsageIfNeeded();

            if (
                $onlineUser->status === 'online' &&
                $onlineUser->online_expires_at &&
                now()->greaterThanOrEqualTo($onlineUser->online_expires_at)
            ) {
                $onlineUser->status = 'offline';
                $onlineUser->online_started_at = null;
                $onlineUser->online_expires_at = null;
            }

            $onlineUser->save();

            $onlineStatus = $onlineUser->isCurrentlyOnline();
            $remainingUses = max(0, 4 - $onlineUser->usage_count);
            $expiresAt = optional($onlineUser->online_expires_at)?->toIso8601String();
        }

        return view('online-now', compact('onlineStatus', 'remainingUses', 'expiresAt'));
    }

    public function onlineUpdateStatus(UpdateOnlineStatusRequest $request)
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], 401);
        }

        $onlineUser = OnlineUser::firstOrCreate(
            ['user_id' => $user->id],
            [
                'status' => 'offline',
                'usage_date' => today(),
                'usage_count' => 0,
            ]
        );

        $onlineUser->resetDailyUsageIfNeeded();

        if (
            $onlineUser->status === 'online' &&
            $onlineUser->online_expires_at &&
            now()->greaterThanOrEqualTo($onlineUser->online_expires_at)
        ) {
            $onlineUser->status = 'offline';
            $onlineUser->online_started_at = null;
            $onlineUser->online_expires_at = null;
        }

        if ($request->validated('status') === 'online') {
            if ($onlineUser->isCurrentlyOnline()) {
                return response()->json([
                    'status' => 'online',
                    'message' => 'You are already online.',
                    'remaining_uses' => max(0, 4 - $onlineUser->usage_count),
                    'expires_at' => optional($onlineUser->online_expires_at)?->toDateTimeString(),
                ]);
            }

            if ($onlineUser->usage_count >= 4) {
                return response()->json([
                    'status' => 'offline',
                    'message' => 'You have already used Online Now 4 times today.',
                    'remaining_uses' => 0,
                ], 422);
            }

            $onlineUser->status = 'online';
            $onlineUser->usage_date = today();
            $onlineUser->usage_count += 1;
            $onlineUser->online_started_at = now();
            $onlineUser->online_expires_at = now()->addMinutes(60);
            $onlineUser->save();

            return response()->json([
                'status' => 'online',
                'message' => 'Online Now enabled for 60 minutes.',
                'remaining_uses' => max(0, 4 - $onlineUser->usage_count),
                'expires_at' => optional($onlineUser->online_expires_at)?->toIso8601String(),
            ]);
        }

        $onlineUser->status = 'offline';
        $onlineUser->online_started_at = null;
        $onlineUser->online_expires_at = null;
        $onlineUser->save();

        return response()->json([
            'status' => 'offline',
            'message' => 'Online Now disabled.',
            'remaining_uses' => max(0, 4 - $onlineUser->usage_count),
            'expires_at' => null,
        ]);
    }
}
