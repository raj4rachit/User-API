<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $permissions = $this->getMethodPermissions($request->route()->getActionMethod());

            if (!checkPermissions($permissions)) {
                abort(403, 'Unauthorized action.');
            }

            return $next($request);
        });
    }

    protected function getMethodPermissions($method)
    {
        switch ($method) {
            case 'index':
            case 'show':
                return ['full_access', 'role_view'];
            case 'edit':
            case 'update':
                return ['full_access', 'role_edit'];
            case 'create':
            case 'store':
                return ['full_access', 'role_create'];
            case 'destroy':
                return ['full_access', 'role_delete'];
            default:
                return [];
        }
    }
    public function index(Request $request)
    {

        $column = ['id', 'name'];
        $query = Role::orderBy('created_at');

        $limit = $request->limit ?? 50;
        $roles = $query->paginate($limit);

        $roles->getCollection()->transform(function ($role) {
            $role->user_count = $role->usersCount();
            $role->permissions;
            return $role;
        });

        return $this->success($roles);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $responseData = [];
        $responseData["permissions"] = $this->permissions();
        $responseData["users"] = $this->users();
        return $this->success($responseData);
    }
    private function users()
    {
        $users = User::select('id', 'name', 'email', 'phone', 'photo_url')->get();
        return $users;
    }
    private function permissions()
    {
        $permissions = Permission::get();
        return $permissions;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);
        if ($validator->fails()) {
            return $this->error('Warning', 400, [
                "errors" => $validator->errors()
            ]);
        }
        try {
            DB::beginTransaction();
            if (Role::where('name', $request->name)->exists()) {
                return $this->error('Warning', 401, [
                    "errors" => [
                        "this_role_name_already_exists"
                    ]
                ]);
            }
            $role = Role::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name
            ]);

            $validPermissionIds = [];
            $invalidPermissionIds = [];

            if ($request->has('permission_ids')) {
                foreach ($request->permission_ids as $permissionId) {
                    // Permission kontrol
                    if (Permission::where('id', $permissionId)->exists()) {
                        $validPermissionIds[] = $permissionId;
                    } else {
                        $invalidPermissionIds[] = $permissionId;
                    }
                }
            }

            if (!empty($invalidPermissionIds)) {
                return $this->error('Warning', 400, [
                    "errors" => [
                        "Invalid permissions: " . implode(', ', $invalidPermissionIds)
                    ]
                ]);
            }
            if (!empty($validPermissionIds)) {
                $role->permissions()->sync($validPermissionIds);
            }
            if ($request->has('user_ids')) {
                $userIds = $request->user_ids;
                // Kullanıcı atama
                foreach ($userIds as $userId) {
                    $user = User::find($userId);
                    if ($user) {
                        $user->assignRole($role->name);
                    }
                }
            }


            DB::commit();
            return $this->success([]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Warning', 401, [
                "errors" => [$e->getMessage()]
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $data = Role::with(['permissions', 'users'])->where('id', $id)->first();
        return $this->success($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $data = Role::with(['permissions', 'users'])->where('id', $id)->first();
        return $this->success($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Warning', 400, [
                "errors" => $validator->errors()
            ]);
        }

        try {
            DB::beginTransaction();

            $role = Role::findOrFail($id);
            $role->update([
                'name' => $request->name,
                'guard_name' => $request->guard_name
            ]);

            // Güncel izin ve kullanıcı id
            $existingPermissionIds = $role->permissions->pluck('id')->toArray();
            $existingUserIds = $role->users->pluck('id')->toArray();

            // Yeni ids
            $newPermissionIds = $request->permission_ids ?? [];
            $newUserIds = $request->user_ids ?? [];

            // İzinleri güncelle
            $role->permissions()->sync($newPermissionIds);

            // Kullanıcıları güncelle
            foreach ($newUserIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->assignRole($role->name); // Kullanıcıya rol ataması yap
                }
            }

            // Gelmeyen permission ids kaldırılacak.
            $role->permissions()->detach(array_diff($existingPermissionIds, $newPermissionIds));

            // Gelmeyen user ids kaldırılacak.
            $role->users()->detach(array_diff($existingUserIds, $newUserIds));

            DB::commit();

            return $this->success([], 'Rol başarılı bir şekilde güncellendi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Warning', 401, [
                "errors" => [$e->getMessage()]
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $role = Role::findOrFail($id);

            $role->permissions()->detach();
            $role->users()->detach();

            $role->delete();
            DB::commit();

            return $this->success([], 'Rol ve bağlı olan kullanıcı bilgisi silindi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Warning', 401, [
                "errors" => [$e->getMessage()]
            ]);
        }
    }
}
