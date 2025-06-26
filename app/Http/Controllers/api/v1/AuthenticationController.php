<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Support\Facades\Password;

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
            'role' => 'required|in:Pemilik,Kasir,Pelanggan',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $role = $request->role ?? 'Pelanggan';

        // Create the new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        $idCard = str_pad(strval(random_int(0, 9999999999999999)), 16, '0', STR_PAD_LEFT);

        UserDetails::create([
            'user_id' => $user->id,
            'id_card' => $idCard,
            'phone' => '',
            'address' => '',
            'photo' => '',
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

        if ($user->role === 'Kasir') {
            if (!$user->outlet) {
                return response()->json(['message' => 'Your account has not been assigned to an outlet.'], 403);
            }
        }

        // Create a token for the user using Sanctum
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'token' => $token,
        ], 200);
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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->role === 'Kasir') {
            $user->load('outlet');
        }
        
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

    public function sendResetLink(Request $request) {
        $request->validate(['email' => 'required|email']);
        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 400);
    }

    public function resetPassword(Request $request) {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|confirmed|min:8',
        ]);
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => bcrypt($password)])->save();
            }
        );
        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['message' => __($status)], 400);
    }

    public function updatePassword(Request $request) 
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|confirmed|min:8',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        User::where('id', $user->id)->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'Password updated successfully'], 200);
    }

    public function getCashiers(Request $request)
    {
        $ownerId = Auth::id();

        $cashiers = User::where('created_by', $ownerId)
                        ->where('role', 'Kasir')
                        ->with('details')
                        ->get();

        return response()->json($cashiers);
    }

    public function createCashier(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $owner = Auth::user();
        if (!$owner || $owner->role !== 'Pemilik') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'Kasir',
            'created_by' => $owner->id,
        ]);

        $idCard = str_pad(strval(random_int(0, 9999999999999999)), 16, '0', STR_PAD_LEFT);

        UserDetails::create([
            'user_id' => $user->id,
            'id_card' => $idCard,
            'phone' => '',
            'address' => '',
            'photo' => '',
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function deleteCashier($id)
    {
        $ownerId = Auth::id();

        $cashier = User::where('id', $id)
                    ->where('created_by', $ownerId)
                    ->where('role', 'Kasir')
                    ->first();

        if (!$cashier) {
            return response()->json(['message' => 'Cashier not found or unauthorized'], 404);
        }

        $cashier->delete();

        return response()->json(['message' => 'Cashier deleted successfully']);
    }
}
