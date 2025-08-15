<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        if (User::where('phone', $request['phone'])->exists()) {
             return ApiResponse::error('Bu raqam bilan oldin ro‘yxatdan o‘tilgan',400);
        }

        $fields = $request->validate([
            'phone' => 'required|string|unique:users,phone',
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        // 1 savol javob yuqorida

        $user = User::create([
            'phone' => $fields['phone'],
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'password' => bcrypt($fields['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        return ApiResponse::success('Siz muvaffaqiyatli Ro\'yhatdan o\'tdingiz',$data, 200);
        //zadacha data
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        $token = $user->createToken('auth_token')->plainTextToken;

        $data = [
            'user' => $user,
            'token' => $token
        ];

        return ApiResponse::success('Siz tizimga muvaffaqiyatli kirdingiz!', $data, 200);
    }

    public function reset_password(Request $request){
        $fields = $request->validate([
            'phone' => 'required|string|min:9',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        if ( $user ) {

            $user->update([
                'password' => Hash::make($request['password'])
            ]);

            $data = [
                'user' => $user
            ];

            return ApiResponse::success("Parolingiz muvaffaqiyatli o'\zgartirildi!",$data, 200);

        } else {
            return ApiResponse::error("User not found", 400);
        }
    }

    //Update
    public function update(Request $request){
        // dd($request);
        $fields = $request->validate([
            'phone' => 'required|unique:users,phone,' .$request->user()->id,
            'firstname' => 'required|max:255',
            'lastname' => 'required|max:255',
            'password' => 'required|min:6'
        ]);

        $user = User::where('phone', $fields['phone'])->first();

        // dd($user);

        if (!$user){
            return ApiResponse::error('Bunday telefon raqam bilan foydalanuvchi topilmadi!', 400);
        }

        $user->update([
            'phone' => $fields['phone'],
            'firstname' => $fields['firstname'],
            'lastname' => $fields['lastname'],
            'password' => Hash::make($fields['password']),
        ]);

        $data = [
            'user' => $user
        ];

        return ApiResponse::success('Malumotlaringiz muvaffaqiyatli o\'zgartirildi!', $data, 200);
    }
    //data zadacha bajarildi

    public function me(Request $request){
        // dd($request->user()->id);
        $fields = $request->validate([
            'phone' => 'required|unique:users,phone,'.$request->user()->id,
            'password' => 'required|min:6',
        ]);

        $user = User::where('phone', $fields['phone'])->first();
        $data = [
            'user' => $user
        ];
        if($user){
            return ApiResponse::success('Siz muvaffaqiyatli kirdingiz!',$data, 200);
        }
        return ApiResponse::error('Telefon raqam yoki Parolda xatolik!', 400);
    }

    public function logout(Request $request)
    {
        $fields = $request->validate([
            'phone' => 'required|unique:users,phone,' .$request->user()->id,
            'password' => 'required|min:6'
        ]);
        $user = User::where('phone', $fields['phone'])->first();
        // dd($user['phone']);
        if($user){

            $request->user()->currentAccessToken()->delete();

            return ApiResponse::success("Tizimdan chiqildi!", 200);
        }else{
            return ApiResponse::error('Siz Tizimga kirmagansiz!', 400);
        }

    }
}
