<?php

namespace App\Http\Controllers;

use App\User;
use JWTAuth;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return response()->json(['token' => $token], 201);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Register a new user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validate User Input
        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required|confirmed|min:8',
        ]);

        // Validation Fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Create User Account
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Return $user and $token
        if ($user) {
            $token = JWTAuth::fromUser($user);
            return response()->json(['user' => $user, 'token' => $token], 201);
        }
    }

    /**
     * Delete a new user
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUser(Request $request)
    {
        if (Auth::id() == $request->id) {
            return response()->json(['message' => 'You cannot delete your account.'], 202);
        } else {
            $user = User::find($request->id);
            if (!$user) {
                return response()->json(['message' => 'User not found']);
            } else {
                $user->delete();
                return response()->json(['message' => 'User deleted successfully']);
            }
        }
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
