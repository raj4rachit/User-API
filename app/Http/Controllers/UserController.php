<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\NewUserEmail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
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
                return ['full_access', 'user_view'];
            case 'edit':
            case 'update':
                return ['full_access', 'user_edit'];
            case 'create':
            case 'store':
                return ['full_access', 'user_create'];
            case 'destroy':
                return ['full_access', 'user_delete'];
            default:
                return [];
        }
    }

    public function index(Request $request)
    {
        $query = User::select();

        $limit = $request->limit ?? 50;
        $data = $query->paginate($limit);

        $data->getCollection()->transform(function ($user) {
            $roleNames = $user->roles->pluck('name')->implode(', ');
            $user->role_name = $roleNames;
            unset($user->roles);
            return $user;
        });

        return $this->success($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rolName = Role::get();
        return $this->success($rolName);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required',
            'email' => 'required|string|email|max:255|unique:users',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id'
        ]);
        if ($validator->fails()) {
            return $this->error('Warning', 400, [
                "errors" => $validator->errors()
            ]);
        }
        try {
            DB::Begintransaction();

            $password = Str::random(9);
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->password =  Hash::make($password);
            $user->save();
            if ($request->role_ids) {
                foreach ($request->role_ids as $roleId) {
                    $role = Role::find($roleId);
                    if ($role) {
                        $user->roles()->attach($role);
                    }
                }
            }

            if ($request->send_mail) {
                Mail::to($user->email)->send(new NewUserEmail($user, $password));
            }
            DB::commit();
            return $this->success($user);
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
    public function show($id)
    {
        $user = User::with('roles.permissions')->where('id', $id)->first();
        return $this->success($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::with('roles.permissions')->where('id', $id)->first();
        return $this->success($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id'
        ]);
        if ($validator->fails()) {
            return $this->error('Warning', 400, [
                "errors" => $validator->errors()
            ]);
        }
        try {
            DB::Begintransaction();

            $user = User::findOrFail($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;

            if ($request->password) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            if ($request->role_ids) {
                $user->roles()->sync($request->role_ids);
            } else {
                $user->roles()->detach();
            }

            if ($request->send_mail) {
                Mail::to($user->email)->send(new NewUserEmail($user, $request->password ?? null));
            }
            DB::commit();
            return $this->success($user);
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
            DB::Begintransaction();

            $user = User::findOrFail($id);

            $user->roles()->detach();

            $user->delete();

            DB::commit();

            return $this->success('user_deleted_successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Warning', 401, [
                "errors" => [$e->getMessage()]
            ]);
        }
    }
}
