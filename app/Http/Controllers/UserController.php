<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function reset_password(Request $request){

        if (User::where('phone', $request['phone'])->exists()) {
            $fields = $request->validate([
                    'password' => 'required|string|min:6',
            ]);

            $user = User::where('phone', $request['phone'])->first();

            $user->update([
                'password' => Hash::make($request['password'])
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
                'parol' => 'Parol muvaffaqiyatli o\'zgartirildi'
            ], 409);
        }
    }
}
