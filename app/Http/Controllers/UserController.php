<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
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

    //Update
    public function update(Request $request){
        $user = User::where('phone', $request['phone'])->first();
        // dd($user->id);
        if (!$user){
            return ApiResponse::error('Bunday telefon raqam bilan foydalanuvchi topilmadi!', 500);
        }

        // dd($request);
        //validate
        $fields = $request->validate([
            'phone' => 'required|unique:users,phone,'.$user->id,
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'password' => 'required|min:6'
        ]);

        $user->update([
            'phone' => $fields['phone'],
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'password' => Hash::make($fields['password']),
        ]);

        //token
        $token = $user->createToken('auth-token')->plainTextToken;

        return ApiResponse::success('Malumotlaringiz muvaffaqiyatli o\'zgartirildi!');
    }
}
