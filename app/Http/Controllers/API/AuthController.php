<?php

namespace App\Http\Controllers\API;
use App\Http\Requests\SignupRequest;
use App\Models\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;


class AuthController extends Controller
{
   public function signup(SignupRequest $request){



        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'account_created_by' => $request->account_created_by,
            'otp' => $request->otp,
            'otp_expires_at' => $request->otp_expires_at,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);

    }

   public function login(LoginRequest $request){



        if(Auth::attempt(['email'=> $request->email , 'password'=> $request->password])){

            $user = Auth::user();
            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'data' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
                'token_type' => 'Bearer',
            ], 200);



        }

        else{
            return response()->json([
                'status' => false,
                'message' => 'Authentication Failed',

            ], 401);
        }

    }

    public function logout(Request $request){


     $user =request()->user();
     $user->tokens()->delete();
     return response()->json([
         'status' => true,
         'message' => 'User logged out successfully',

     ], 200);
    }
}
