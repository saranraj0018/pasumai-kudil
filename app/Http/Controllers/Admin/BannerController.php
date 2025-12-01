<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function view(){
        $banners = Banner::paginate(10);
        return view('admin.banner.view', compact('banners'));
    }
    public function save(Request $request){
        $rules = [
           'type' => 'required|in:GroceryMain,GrocerySub,MilkMain,MilkSub',
        ];

        // Image validation
        if (empty($request['banner_id']) && !$request->has('existing_image')) {
            $rules['banner_image'] = 'required|image|mimes:jpeg,png,jpg';
        } elseif ($request->hasFile('banner_image')) {
            $rules['banner_image'] = 'image|mimes:jpeg,png,jpg';
        }

        $request->validate($rules);

        $banner = $request['banner_id'] ? Banner::find($request['banner_id']) : new Banner();

        // Handle image upload
        if ($request->hasFile('banner_image')) {
            $img_name = time().'_'.$request->file('banner_image')->getClientOriginalName();
            $request->banner_image->storeAs('banners', $img_name, 'public');
            $banner->image_url = 'banners/'.$img_name;
        } elseif ($request->has('existing_image')) {
            $banner->image_url = $request->existing_image;
        }

        $banner->type = $request->type;
        $banner->save();

        return response()->json([
            'success' => true,
            'message' => $request['banner_id'] ? 'Banner updated successfully' : 'Banner saved successfully',
            'banner' => $banner
        ]);
    }

    public function destroy(Request $request)
    {
        if (!$request->id) {
            return response()->json(['success' => false, 'message' => 'Banner ID is required'], 400);
        }

        $banner = Banner::find($request->id);
        if (!$banner) {
            return response()->json(['success' => false, 'message' => 'Banner not found'], 404);
        }

        $banner->delete();
        return response()->json(['success' => true, 'message' => 'Banner deleted successfully']);
    }
}
