<?php

namespace App\Http\Controllers;

use App\Models\UserProfile;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{

    public function Login(Request $request)
    {
        $us = User::where('name', $request->name)->first();
        if($us == null)
        return Response(['status' => 'fail', 'message' => 'Unauthorized'], 403);

        $hashedPassword = $us->password;

        if (Hash::check($request->password, $hashedPassword)) {
            $token = auth('api')->login($us);
            return $this->respondWithToken($us->id, $token);
        }
        return Response(['status' => 'fail', 'message' => 'Unauthorized'], 403);
    }
    public function SignUp(Request $request)
    {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password'=> Hash::make($request->password),
            'type' => $request->type ?? UserProfile::CUSTOMER
        ]);
        return Response(['status' => 'success', 'message' => 'created'], 201);
    }
    protected function respondWithToken($user_id,$token)
    {
        return response()->json([
            'user_id' => $user_id,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}