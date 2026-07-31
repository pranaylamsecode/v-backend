<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        $cookie = cookie('auth_token', $token, 60 * 24 * 7, '/', null, false, true);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $cookie = cookie()->forget('auth_token');
        return response()->json(['message' => 'Logged out successfully'])->withCookie($cookie);
    }
}
