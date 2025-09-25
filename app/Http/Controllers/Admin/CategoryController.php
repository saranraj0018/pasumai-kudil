<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function view(){
        $categories = Category::all();
        return view('admin.category.view', compact('categories'));
    }

    public function save(Request $request){
        $request->validate([
            'category_name' => 'required|max:255',
            'category_image' => 'required|image|mimes:jpeg,png,jpg',
            'category_status' => 'required|boolean',
        ]);

        $category = new Category();
        $category->name = $request['category_name'];

        if ($request->hasFile('category_image')) {
            $img_name = $request->file('category_image')->getClientOriginalName();
            $img_name = time() . '_' . $img_name;
            $request->category_image->storeAs('categories/', $img_name, 'public');
            $category->image =  'categories/'.$img_name;
        }

        $category->status  = $request['category_status'];
        $category->admin_id = Auth::guard('admin')->id();
        $category->save();
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category' => $category
        ]);
    }
}
