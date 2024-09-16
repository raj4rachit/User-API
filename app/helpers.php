<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

function checkPermissions($permissions)
{

    $userId = Auth::user()->id;

    $control = User::where('id', $userId)
        ->whereHas('roles.permissions', function ($q) use ($permissions) {
            $q->whereIn('name', $permissions);
        })->first();

    return $control ? true : false;
}
function activeUser()
{
    $user = Auth::user();
    return $user;
}
