<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Setting;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function view()
    {
        $faqs = Faq::where('status', 1)->orderBy('sort_order', 'asc')->get();
        return response()->json([
            'success' => true,
            'faqs' => $faqs
        ], 200);
    }

    // Get Terms & Conditions
    public function getTerms()
    {
        $terms = Setting::where('data_key', 'terms')
            ->value('policy');

        return response()->json([
            'status'  => 200,
            'message' => 'Terms & Conditions retrieved successfully.',
            'data'    => $terms ?? 'No terms available'
        ]);
    }

    // Get Privacy Policy
    public function getPrivacy()
    {
        $policy = Setting::where('data_key', 'policy')
            ->value('policy');

        return response()->json([
            'status'  => 200,
            'message' => 'Privacy Policy retrieved successfully.',
            'data'    => $policy ?? 'No policy available'
        ]);
    }
}
