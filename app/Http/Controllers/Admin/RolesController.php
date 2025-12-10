<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RolesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('query');
        $this->data['roles'] = Role::orderBy('created_at', 'desc')
                               ->paginate(10);
         return view('admin.roles.roles_list')->with($this->data);
    }

    public function roleSave(Request $request)
    {
        $rules = [
            'role_name'  => 'required',
        ];
        $request->validate($rules);
        try {
            $role_exists = Role::where('name', $request['role_name'])->first();
            if(!empty($role_exists)){
                return response()->json([
                    'success' => false,
                    'message' => 'Role Name Already Exists!'
                ]);
            }
            if (!empty($request['role_id'])) {
                $message = 'Role Updated successfully';
                $role = Role::find($request['role_id']);
            } else {
                $role = new Role();
                $message = 'Role saved successfully';
            }


            $role->name = $request['role_name'];
            $role->slug = Str::slug($request['role_name']);
            $role->save();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save role',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
