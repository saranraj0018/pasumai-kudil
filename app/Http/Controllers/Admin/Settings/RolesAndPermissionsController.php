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

        // The sidebar hides a whole menu group unless its main menu ability is
        // granted, so grant the main menu of every ticked submenu permission.
        $abilityIds = collect($request->abilities ?? [])->map(fn($id) => (int) $id);
        $allAbilities = Ability::get(['id', 'menu_id', 'main_menu'])->keyBy('id');

        foreach ($abilityIds->all() as $id) {
            $current = $allAbilities[$id] ?? null;
            while ($current && $current->main_menu !== 'y' && $current->menu_id) {
                $current = $allAbilities[$current->menu_id] ?? null;
            }
            if ($current && $current->main_menu === 'y') {
                $abilityIds->push($current->id);
            }
        }

        $role->abilities()->sync($abilityIds->unique()->values()->all());

        return redirect()->route('roles_and_permission', ['role_id' => $role->id])->with('success', 'Abilities updated successfully!');
    }
}
