<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\Role;
use Illuminate\Http\Request;

class RolesAndPermissionsController extends Controller
{
    public function roleAbilities(Request $request)
    {
        $this->data['roles'] = Role::all();
        $role = null;
        $roleAbilities = [];

        if ($request->has('role_id')) {
            $role = Role::find($request->role_id);
            $roleAbilities = $role ? $role->abilities()->pluck('abilities.id')->toArray() : [];
        }

        // Get all abilities
        $this->data['abilities'] = Ability::orderBy('id')->get();
        $this->data['role'] = $role;
        $this->data['roleAbilities'] = $roleAbilities;

        return view('admin.roles.abilities')->with($this->data);
    }

    public function updateRoleAbilities(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'abilities' => 'array',
        ]);

        $role = Role::findOrFail($request->role_id);
        $role->abilities()->sync($request->abilities ?? []);

        return redirect()->route('roles_and_permission', ['role_id' => $role->id])->with('success', 'Abilities updated successfully!');
    }
}
