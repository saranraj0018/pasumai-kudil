<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function view(){
       $categories = Category::with('products')->paginate(10);
        return view('admin.category.view', compact('categories'));
    }

    public function save(Request $request){
        $rules = [
            'category_name' => 'required|max:255',
            'category_status' => 'required|boolean',
        ];

        // only require image if creating OR no existing image provided
        if (empty($request['category_id']) && !$request->has('existing_image')) {
            $rules['category_image'] = 'required|image|mimes:jpeg,png,jpg';
        } else if ($request->hasFile('category_image')) {
            // validate new uploaded image if provided
            $rules['category_image'] = 'image|mimes:jpeg,png,jpg';
        }

        $request->validate($rules);

        if (!empty($request['category_id'])) {
            $message = 'Category Updated successfully';
            $category = Category::find($request['category_id']);
        } else{
            $category = new Category();
            $message = 'Category saved successfully';
        }

        $category->name = $request['category_name'];
        $category->status = $request['category_status'];
        $category->admin_id = Auth::guard('admin')->id();

        // handle image upload
        if ($request->hasFile('category_image')) {
            $img_name = $request->file('category_image')->getClientOriginalName();
            $img_name = time() . '_' . $img_name;
            $request->category_image->storeAs('categories/', $img_name, 'public');
            $category->image =  'categories/'.$img_name;
        } else if ($request->has('existing_image')) {
            // keep the existing image path
            $category->image = $request->existing_image;
        }

        $category->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'category' => $category
        ]);
    }

    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'category ID is required'], 400);
        }

        $category = Category::find($request->id);
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'category not found'], 404);
        }

        $category->delete();
        return response()->json(['success' => true, 'message' => 'category deleted successfully']);
    }


}
