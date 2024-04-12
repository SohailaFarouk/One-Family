<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ParentController extends Controller
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
            'nat_id' => 'required|numeric|in:14',
            'gender' => 'required',
            'marital_status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = Parents::create([
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

        return response()->json(['message' => 'User registered successfully', 'user' => $user]);
    }

}
