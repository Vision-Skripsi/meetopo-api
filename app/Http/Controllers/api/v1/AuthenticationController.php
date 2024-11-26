<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthenticationController extends Controller
{
    /**
     * Registration Function
     * This allows Client to register new user
     * The client must follow the validation rules
     */
    public function registration(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create the new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Return a successful response with the created user data
        return response()->json(['user' => $user], 201);
    }

    /**
     * Login Function
     * This will return token
     * which the token can be used to access all protedted
     * api, please notes that token has expiration time.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create a token for the user using Sanctum
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    /**
     * GET User Data Function
     * This function is being protected by Laravel Sanctum
     * Please make sure Client passes the token before hit 
     * the route that accesses this function
     */
    public function getUserData(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();
        
        // Return the user data
        return response()->json($user, 200);
    }

    /**
     * Missing Token
     * Customize this to inform Client
     * about the token validation
     */
    public function missingToken(){
        return response()->json([
            'error' => 'You need token to verify and access protected API'
        ], 401);
    }
}
