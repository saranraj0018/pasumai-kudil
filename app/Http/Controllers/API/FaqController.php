<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Faq;
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
}
