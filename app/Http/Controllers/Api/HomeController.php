<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required','email'],
            'password' => ['required','string','min:4'],
            'device_name' => ['nullable','string','max:100'], // optional
        ]);


        $user = User::where('email', $data['email'])->first();


        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }



        // (Optional) old tokens delete (single device login chahiye to)
        // $user->tokens()->delete();



        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        // current token revoke
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully',
        ], 200);
    }

    public function user(Request $request){
        $user = $request->user();
        return response()->json([
            'status' => true,
            'user' => $user,
            'business' => $user->businesses,
        ]);
    }
}
