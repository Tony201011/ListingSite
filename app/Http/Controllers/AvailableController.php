<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvailableStatusRequest;
use App\Models\AvailableNow;
use Illuminate\Support\Facades\Auth;

class AvailableController extends Controller
{
    public function availableNow()
    {
        $user = Auth::user();
        $status = false;
        $remainingUses = 2;
        $expiresAt = null;

        if ($user) {
            $available = AvailableNow::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'offline',
                    'usage_date' => today(),
                    'usage_count' => 0,
                ]
            );

            $available->resetDailyUsageIfNeeded();

            if (
                $available->status === 'online' &&
                $available->available_expires_at &&
                now()->greaterThanOrEqualTo($available->available_expires_at)
            ) {
                $available->status = 'offline';
                $available->available_started_at = null;
                $available->available_expires_at = null;
            }

            $available->save();

            $status = $available->isCurrentlyAvailable();
            $remainingUses = max(0, 2 - $available->usage_count);
            $expiresAt = optional($available->available_expires_at)?->toIso8601String();
        }

        return view('available-now', compact('status', 'remainingUses', 'expiresAt'));
    }

    public function availableUpdateStatus(UpdateAvailableStatusRequest $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $available = AvailableNow::firstOrCreate(
            ['user_id' => $user->id],
            [
                'status' => 'offline',
                'usage_date' => today(),
                'usage_count' => 0,
            ]
        );

        $available->resetDailyUsageIfNeeded();

        if (
            $available->status === 'online' &&
            $available->available_expires_at &&
            now()->greaterThanOrEqualTo($available->available_expires_at)
        ) {
            $available->status = 'offline';
            $available->available_started_at = null;
            $available->available_expires_at = null;
        }

        if ($request->status === 'online') {
            if ($available->isCurrentlyAvailable()) {
                return response()->json([
                    'success' => true,
                    'status' => 'online',
                    'message' => 'You are already available now.',
                    'remaining_uses' => max(0, 2 - $available->usage_count),
                    'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
                ]);
            }

            if ($available->usage_count >= 2) {
                return response()->json([
                    'success' => false,
                    'status' => 'offline',
                    'message' => 'You have already used Available Now 2 times today.',
                    'remaining_uses' => 0,
                    'expires_at' => null,
                ], 422);
            }

            $available->status = 'online';
            $available->usage_date = today();
            $available->usage_count += 1;
            $available->available_started_at = now();
            $available->available_expires_at = now()->addHours(2);
            $available->save();

            return response()->json([
                'success' => true,
                'status' => 'online',
                'message' => 'You are now available for enquiries for 2 hours.',
                'remaining_uses' => max(0, 2 - $available->usage_count),
                'expires_at' => optional($available->available_expires_at)?->toIso8601String(),
            ]);
        }

        $available->status = 'offline';
        $available->available_started_at = null;
        $available->available_expires_at = null;
        $available->save();

        return response()->json([
            'success' => true,
            'status' => 'offline',
            'message' => 'You are now unavailable.',
            'remaining_uses' => max(0, 2 - $available->usage_count),
            'expires_at' => null,
        ]);
    }
}
