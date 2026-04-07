<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function view()
    {
        $units = Unit::paginate(10);
        return view('admin.unit.view', compact('units'));
    }

    public function save(Request $request)
    {
        try {
            $rules = [
                'unit_name' => 'required|max:255',
                'unit_status' => 'required|boolean',
                'unit_short_name' => 'required|max:255',
            ];
            // Validation
            $request->validate($rules);

            // Update or create
            if (!empty($request['unit_id'])) {
                $unit = Unit::findOrFail($request['unit_id']);
                $message = 'Unit Updated successfully';
            } else {
                $unit = new Unit();
                $message = 'Unit saved successfully';
            }

            $unit->name = $request['unit_name'];
            $unit->status = $request['unit_status'];
            $unit->short_name = $request['unit_short_name'];
            $unit->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'unit' => $unit
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'unit ID is required'], 400);
        }
        $unit = Unit::find($request->id);
        if (!$unit) {
            return response()->json(['success' => false, 'message' => 'unit not found'], 404);
        }
        $unit->delete();
        return response()->json(['success' => true, 'message' => 'unit deleted successfully']);
    }
}
