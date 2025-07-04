<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WasteTypeController extends Controller
{
    public function getWasteTypes()
    {
        $wasteTypes = WasteType::all();

        return response()->json([
            'status' => 'success',
            'data' => $wasteTypes->map(function ($item) {
                return [
                    'id' => $item->id_waste_type,
                    'waste_type' => $item->waste_type,
                    'price' => number_format($item->price, 0, '.', ',') . '/kg',
                    'photo' => $item->photo,
                ];
            })
        ]);
    }

    public function createWasteType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_user' => 'required|integer|exists:users,id_user',
            'id_admin' => 'required|integer|exists:admins,id_admin',
            'waste_type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'photo' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $wasteType = WasteType::create([
            'id_user' => $request->id_user,
            'id_admin' => $request->id_admin,
            'waste_type' => $request->waste_type,
            'price' => $request->price,
            'photo' => $request->photo,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $wasteType->id_waste_type,
                'waste_type' => $wasteType->waste_type,
                'price' => number_format($wasteType->price, 0, '.', ',') . '/kg',
                'photo' => $wasteType->photo,
            ]
        ], 201);
    }

    public function updateWasteType(Request $request, $id)
    {
        $wasteType = WasteType::find($id);

        if (!$wasteType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waste type not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_user' => 'integer|exists:users,id_user',
            'id_admin' => 'integer|exists:admins,id_admin',
            'waste_type' => 'string|max:255',
            'price' => 'numeric',
            'photo' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        $wasteType->update($request->all());

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $wasteType->id_waste_type,
                'waste_type' => $wasteType->waste_type,
                'price' => number_format($wasteType->price, 0, '.', ',') . '/kg',
                'photo' => $wasteType->photo,
            ]
        ]);
    }

    public function deleteWasteType($id)
    {
        $wasteType = WasteType::find($id);

        if (!$wasteType) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waste type not found'
            ], 404);
        }

        $wasteType->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Waste type deleted successfully'
        ]);
    }
}
