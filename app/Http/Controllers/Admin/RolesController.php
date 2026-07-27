<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        try {
            $rules = [
                'role_name'  => [
                    'required',
                    'max:255',
                    Rule::unique('roles', 'name')->ignore($request->role_id)
                ],
            ];
            $request->validate($rules);
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
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
