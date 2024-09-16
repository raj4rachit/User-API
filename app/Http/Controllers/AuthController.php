<?php


namespace App\Http\Controllers;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'client' => 'required'
        ]);

        // Doğrulama başarısız
        if ($validator->fails()) {
            return response()->json(['message' => 'invalid_login_information', 'errors' => $validator->errors()], 400);
        }
        // Kullanıcı kontrol
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Warning', 401, [
                "errors" => ["invalid_credentials"]
            ]);
        }
        // İstemci adı
        $clientName = $request->client;

        $user = $request->user();
        $token = $user->createToken($clientName)->accessToken;

        return response()->json(['token' => $token], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json(['message' => 'successfully_logged_out'], 200);
    }

    public function me(Request $request)
    {
        $userId = Auth::id();
        if (!$request->bearerToken()) {
            return $this->error('Warning', 401, [
                "errors" => ["bearer_token_not_found"]
            ]);
        }
        $user = User::find($userId);

        return $this->success([
            "auth" => [
                "token" => $request->bearerToken(),
            ],
            "detail" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "phone" => $user->phone,
                "photo_url" => $user->photo_url,
            ],
            "role" => $request->user()->getRoleNames(),
            "permission" => $request->user()->getAllPermissions()->pluck('name'),
        ]);
    }
}
