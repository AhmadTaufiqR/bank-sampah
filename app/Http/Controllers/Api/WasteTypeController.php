<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WasteType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
                    'price_ons' => number_format($item->price / 10, 0, '.', ',') . '/ons',
                    'photo' => $item->photo,
                ];
            })
        ]);
    }

    public function createWasteType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_admin' => 'required|integer|exists:admins,id_admin',
            'waste_type' => 'nullable|string|max:255',
            'price' => 'nullable|numeric',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Simpan foto jika ada
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('waste_photos', 'public');
        }

        $wasteType = WasteType::create([
            'id_admin' => $request->id_admin,
            'waste_type' => $request->waste_type,
            'price' => $request->price,
            'photo' => $photoPath,
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $wasteType->id_waste_type,
                'waste_type' => $wasteType->waste_type,
                'price' => number_format($wasteType->price, 0, '.', ',') . '/kg',
                'price_ons' => number_format($wasteType->price / 10, 0, '.', ',') . '/ons',
                'photo_url' => $photoPath ? asset('storage/' . $photoPath) : null,
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
            'id_admin' => 'sometimes|integer|exists:admins,id_admin',
            'waste_type' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'remove_photo' => 'nullable|in:true,false',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 400);
        }

        // Handle remove photo
        if ($request->input('remove_photo') === 'true' && $wasteType->photo) {
            Storage::disk('public')->delete($wasteType->photo);
            $wasteType->photo = null;
        }

        // Handle upload photo baru
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($wasteType->photo) {
                Storage::disk('public')->delete($wasteType->photo);
            }

            $photoPath = $request->file('photo')->store('waste_photos', 'public');
            $wasteType->photo = $photoPath;
        }

        // Update field lain jika ada
        if ($request->has('id_admin')) {
            $wasteType->id_admin = $request->id_admin;
        }

        if ($request->has('waste_type')) {
            $wasteType->waste_type = $request->waste_type;
        }

        if ($request->has('price')) {
            $wasteType->price = $request->price;
        }

        $wasteType->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $wasteType->id_waste_type,
                'waste_type' => $wasteType->waste_type,
                'price' => number_format($wasteType->price, 0, '.', ',') . '/kg',
                'price_ons' => number_format($wasteType->price / 10, 0, '.', ',') . '/ons',
                'photo_url' => $wasteType->photo ? asset('storage/' . $wasteType->photo) : null,
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
