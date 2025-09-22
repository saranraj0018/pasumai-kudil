<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function view(){
        $categories = Category::all();
        return view('admin.category.view', compact('categories'));
    }
}
