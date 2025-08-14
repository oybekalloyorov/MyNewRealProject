<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // 1 savol javob
        if (User::where('phone', $request['phone'])->exists()) {
            return response()->json([
                'message' => 'Bu raqam bilan oldin ro‘yxatdan o‘tilgan'
        ], 409);
        }

        $fields = $request->validate([
            'phone' => 'required|string|unique:users,phone',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);


        $user = User::create([
            'phone' => $fields['phone'],
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'password' => bcrypt($fields['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function me(Request $request){

        return $request->user();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Tizimdan chiqildi']);
    }
}
