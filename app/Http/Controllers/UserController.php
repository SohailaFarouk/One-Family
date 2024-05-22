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
        'date_of_birth' => 'required|date',
        'address' => 'required|string|max:255',
        'nat_id' => 'required|numeric',
        'gender' => 'required|string|in:male,female,other',
        'marital_status' => 'required|string|in:single,married,divorced,widowed',
        'phone_number' => 'required|digits:11',
        'number_of_children' => 'nullable|numeric|min:1',
        'children_names.*' => 'nullable|string|max:255',
        'children_date_of_birth.*' => 'nullable|date_format:Y-m-d',
        'children_genders.*' => 'nullable|in:male,female,other',
    ]);

    if ($validator->fails()) {
        return response()->json(['success'=> false ,'error' => $validator->errors()], 422);
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
        'marital_status' => $request->marital_status,
        'phone_number' => $request->phone_number,
    ]);

    Parents::create([
        'user_id' => $user->id,
    ]);

    $childrenData = [];
    if (!empty($request->number_of_children) && is_array($request->children_names)) {
        $childrenCount = (int) $request->number_of_children;
        
        if ($childrenCount !== count($request->children_names)) {
            return response()->json(['success'=> false ,'error' => 'Number of children names does not match declared number'], 422);
        }

        for ($i = 0; $i < $childrenCount; $i++) {
            $childrenData[] = [
                'user_id' => $user->id,
                'name' => $request->children_names[$i],
                'date_of_birth' => $request->children_date_of_birth[$i],
                'gender' => $request->children_genders[$i],
                'number_of_children' => $childrenCount, 
            ];
        }

        DB::table('childrens')->insert($childrenData);
    }

    return response()->json(['success' => true,'message' => 'User registered successfully', 'user' => $user,'user_id'=>$user->id, 'children' => $childrenData], 200);
}


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = DB::table('users')
        ->where('email', $credentials['email'])
        ->first();

    // Check if user exists and if the password matches
    if ($user && Hash::check($credentials['password'], $user->password)) {
        // Generate a token
        $token = bin2hex(random_bytes(16));
        DB::table('users')
            ->where('email', $credentials['email'])
            ->update(['token' => $token]);

        // Check user role and respond accordingly
        if ($user->role === 'admin') {
            return response()->json(['success' => true, 'message' => 'Login successful as admin', 'role' => 'admin', 'token' => $token, 'user_id' => $user->user_id]);
        }
        if ($user->role === 'parent') {
            return response()->json(['success' => true, 'message' => 'Login successful as parent', 'role' => 'parent', 'token' => $token, 'user_id' => $user->user_id]);
        }
        if ($user->role === 'doctor') {
            return response()->json(['success' => true, 'message' => 'Login successful as doctor', 'role' => 'doctor', 'token' => $token, 'user_id' => $user->user_id]);
        }
    
        }
        return response()->json(['success'=> false ,'error' => 'Invalid username or password'], 401);
    }

    public function logout(Request $request)
    {
        $token = $request->header('Authorization');
        return response()->json(['success' => true,'message' => 'you are Logged out']);
    }
}