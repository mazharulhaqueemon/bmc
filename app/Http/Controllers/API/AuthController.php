<?php

namespace App\Http\Controllers\API;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Plan;

class AuthController extends Controller
{
    
    // Register a new user
    
    public function signup(SignupRequest $request){
        
        $freePlan = Plan::firstOrCreate(
            ['plan_name' => 'Free'], 
            [
                'profile_picture_limit' => 1,
                'phone_request_limit' => 2,
                'chat_duration_days' => 0,
                'description' => 'Default free plan',
            ]
        );

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, 
            'account_created_by' => $request->account_created_by,
            'phone_number' => $request->phone_number ?? null, 
            'plan_id' => $freePlan->id, 
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    
    // Authenticate user and generate Laravel Sanctum token
     
    public function login(LoginRequest $request){
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            return response()->json([
                'status' => true,
                'message' => 'User logged in successfully',
                'data' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
                'token_type' => 'Bearer',
            ], 200);
        }
        return response()->json([
            'status' => false,
            'message' => 'Authentication Failed',
        ], 401);
    }

    
    // Logout and revoke tokens
    
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'User logged out successfully',
        ], 200);
    }
}
