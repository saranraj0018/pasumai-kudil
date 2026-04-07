<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function view()
    {
        $faqs = Faq::paginate(10);
        return view('admin.faq.view', compact('faqs'));
    }

    public function save(Request $request)
    {
        try {
            $rules = [
                'question' => 'required|max:255',
                'answer' => 'required',
                'sort_order' => 'required|integer|min:1',
                'faq_status' => 'required|in:0,1',
            ];
            // Validation
            $request->validate($rules);
            $existingsort = Faq::where('sort_order', $request['sort_order'])
                ->where('id', '!=', $request['faq_id'] ?? 0)
                ->first();

            if ($existingsort) {
                return response()->json([
                    'status' => 409,
                    'message' => 'A FAQ with this sort order already exists.',
                ], 409);
            }
            // Update or create
            if (!empty($request['faq_id'])) {
                $Faq = Faq::findOrFail($request['faq_id']);
                $message = 'Faq Updated successfully';
            } else {
                $Faq = new Faq();
                $message = 'Faq saved successfully';
            }

            $Faq->question = $request['question'];
            $Faq->answer = $request['answer'];
            $Faq->sort_order = $request['sort_order'];
            $Faq->status = $request['faq_status'];
            $Faq->save();

            return response()->json([
                'success' => true,
                'message' => $message,
                'Faq' => $Faq
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
            return response()->json(['success' => false, 'message' => 'Faq ID is required'], 400);
        }
        $Faq = Faq::find($request->id);
        if (!$Faq) {
            return response()->json(['success' => false, 'message' => 'Faq not found'], 404);
        }
        $Faq->delete();
        return response()->json(['success' => true, 'message' => 'Faq deleted successfully']);
    }
}
