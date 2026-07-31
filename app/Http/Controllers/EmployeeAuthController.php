<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class EmployeeAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $employee = Employee::where('email', $request->email)->first();

        if (!$employee || !Hash::check($request->password, $employee->password)) {
            return response()->json(['error' => 'Invalid email or password'], 401);
        }

        $token = $employee->createToken('employee-token')->plainTextToken;
        $cookie = cookie('employee_auth_token', $token, 60 * 24 * 7, '/', null, false, true);

        return response()->json([
            'message' => 'Login successful',
            'user' => $employee,
            'role' => 'employee'
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $cookie = cookie()->forget('employee_auth_token');
        return response()->json(['message' => 'Logged out successfully'])->withCookie($cookie);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
            'role' => 'employee'
        ]);
    }
}