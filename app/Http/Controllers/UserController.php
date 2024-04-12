<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{ 
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'DOB' => 'required',
            'address' => 'required',
            'nat_id' => 'required|numeric', // Remove 'in:14' validation unless nat_id must be exactly 14
            'gender' => 'required',
            'marital_status' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'DOB' => $request->DOB,
            'address' => $request->address,
            'nat_id' => $request->nat_id,
            'gender' => $request->gender,
            'marital_status' => $request->marital_status
        ]);
    
        $parent = Parents::create([
            'user_id' => $user->id
        ]);
    
        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
    }
    
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
    
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;
            return response()->json(['token' => $token, 'user' => $user]);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

}
}