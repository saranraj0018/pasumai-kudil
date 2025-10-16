<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use Illuminate\Http\Request;

class HubController extends Controller
{
   public function view(Request $request) {
       $hub_list = Hub::with('user')->orderBy('created_at', 'desc')->paginate(10);
       return view('admin.hub.view', compact('hub_list'));
   }
}
