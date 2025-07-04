<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteCollectionSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function getSchedules()
    {
        $schedules = WasteCollectionSchedule::all();

        return response()->json([
            'status' => 'success',
            'data' => $schedules->map(function ($item) {
                return [
                    'id' => $item->id_schedule,
                    'month' => $item->month,
                    'dates' => $item->dates,
                    'activity' => $item->activity,
                    'photo' => $item->photo,
                ];
            })
        ]);
    }

    public function updateSchedule(Request $request, $id)
    {
        $schedule = WasteCollectionSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Schedule not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'month' => 'string|max:255',
            'dates' => 'array',
            'activity' => 'string|max:255',
            'photo' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $schedule->update([
            'month' => $request->month ?? $schedule->month,
            'dates' => $request->dates ?? $schedule->dates,
            'activity' => $request->activity ?? $schedule->activity,
            'photo' => $request->photo ?? $schedule->photo,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $schedule->id_schedule,
                'month' => $schedule->month,
                'dates' => $schedule->dates,
                'activity' => $schedule->activity,
                'photo' => $schedule->photo,
            ]
        ]);
    }
}
