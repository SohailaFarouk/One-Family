<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(){
        $users = User::get();
        return response()->json(['users' => $users]);
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'date_of_birth' => 'required',
            'address' => 'required',
            'nat_id' => 'required|numeric|',
            'gender' => 'required',
            'marital_status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'nat_id' => $request->nat_id,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status
        ]);

        Parents::create([
            'user_id' => $user->id
        ]);


        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = DB::table('users')
            ->where('email', $credentials['email'])
            ->first();

        if ($user && $user->password) {
            $token = bin2hex(random_bytes(16));
            DB::table('users')
                ->where('email', $credentials['email'])
                ->update(['token' => $token]);

            if ($user->role === 'admin') {
                return response()->json(['message' => 'Login successful as admin', 'role' => 'admin', 'token' => $token]);
            }
            if ($user->role === 'parent') {
                return response()->json(['message' => 'Login successful as parent', 'role' => 'parent', 'token' => $token]);
            }
            if ($user->role === 'doctor') {
                return response()->json(['message' => 'Login successful as doctor', 'role' => 'doctor', 'token' => $token]);
            }
        }

        return response()->json(['error' => 'Invalid username or password'], 401);
    }

    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        return response()->json(['message' => 'you are Logged out']);
    }
}
