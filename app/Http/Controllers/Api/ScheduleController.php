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
                    'dates' => json_decode($item->dates, true),
                    'photo' => $item->photo,
                ];
            })
        ]);
    }

    public function updateDates(Request $request, $id)
    {
        $schedule = WasteCollectionSchedule::find($id);

        if (!$schedule) {
            return response()->json([
                'status' => 'error',
                'message' => 'Schedule not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'dates' => 'required|array', // Expect dates as an array
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Parse dates if sent as a string (e.g., "[\"2025-07-05\",\"2025-07-12\"]")
        $dates = is_string($request->dates) ? json_decode($request->dates, true) : $request->dates;

        // Update only the dates field, keeping other fields unchanged
        $schedule->update([
            'dates' => json_encode($dates), // Store as JSON string
        ]);

        // Prepare response with decoded dates for consistency
        $updatedSchedule = [
            'id' => $schedule->id_schedule,
            'month' => $schedule->month,
            'dates' => json_decode($schedule->dates, true), // Decode back to array for response
            'photo' => $schedule->photo,
        ];

        return response()->json([
            'status' => 'success',
            'data' => $updatedSchedule
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            '*.month' => 'required|string|max:255',
            '*.dates' => 'required|array', // Expect dates as an array
            '*.photo' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $schedulesData = $request->all();
        $insertedSchedules = [];

        foreach ($schedulesData as $data) {
            // Parse dates if sent as a string (e.g., "[\"2025-07-05\",\"2025-07-12\"]")
            $dates = is_string($data['dates']) ? json_decode($data['dates'], true) : $data['dates'];

            $schedule = WasteCollectionSchedule::create([
                'id_admin' => $data['id_admin'] ?? 1, // Default to 1 if not provided
                'month' => $data['month'],
                'dates' => json_encode($dates), // Store as JSON string
                'photo' => $data['photo'],
            ]);

            $insertedSchedules[] = [
                'id' => $schedule->id_schedule,
                'month' => $schedule->month,
                'dates' => json_decode($schedule->dates, true), // Decode back to array for response
                'photo' => $schedule->photo,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $insertedSchedules
        ], 201);
    }

    public function deleteAll()
    {
        // Hapus semua data dari tabel WasteCollectionSchedule
        WasteCollectionSchedule::truncate();

        return response()->json([
            'status' => 'success',
            'message' => 'All schedules have been deleted.'
        ]);
    }
}
