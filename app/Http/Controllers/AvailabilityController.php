<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAvailabilityRequest;
use App\Models\Availability;
use Illuminate\Support\Facades\Auth;

class AvailabilityController extends Controller
{
    protected array $days = [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ];

    public function edit()
    {
        $saved = Availability::where('user_id', Auth::id())
            ->get()
            ->keyBy('day');

        return view('set-your-availability', [
            'days' => $this->days,
            'saved' => $saved,
        ]);
    }

    public function update(UpdateAvailabilityRequest $request)
    {
        $availabilityData = $request->validated()['availability'] ?? [];

        foreach ($this->days as $day) {
            $dayData = $availabilityData[$day] ?? [];

            $enabled = ! empty($dayData['enabled']);
            $allDay = ! empty($dayData['all_day']);
            $tillLate = ! empty($dayData['till_late']);
            $byAppointment = ! empty($dayData['by_appointment']);

            $fromTime = $dayData['from'] ?? null;
            $toTime = $dayData['to'] ?? null;

            if (! $enabled) {
                $fromTime = null;
                $toTime = null;
                $allDay = false;
                $tillLate = false;
                $byAppointment = false;
            }

            if ($allDay) {
                $fromTime = null;
                $toTime = null;
            }

            Availability::updateOrCreate(
                [
                    'user_id' => Auth::id(),
                    'day' => $day,
                ],
                [
                    'enabled' => $enabled ? 1 : 0,
                    'from_time' => $fromTime,
                    'to_time' => $toTime,
                    'till_late' => $tillLate ? 1 : 0,
                    'all_day' => $allDay ? 1 : 0,
                    'by_appointment' => $byAppointment ? 1 : 0,
                ]
            );
        }

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

    public function show()
    {
        $availabilities = Availability::where('user_id', Auth::id())
            ->orderByRaw("
                FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
            ")
            ->get();

        $availabilityCount = Availability::where('user_id', Auth::id())->count();

        return view('my-availability', compact('availabilities', 'availabilityCount'));
    }
}
