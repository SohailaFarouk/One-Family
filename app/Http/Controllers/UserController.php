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
            'nat_id' => 'required|numeric',
            'gender' => 'required',
            'marital_status' => 'required',
            'phone_number' => 'required|digits:11',
            'number_of_children' => 'required_if:marital_status,married|numeric|min:1',
            'children_names.*' => 'required_if:marital_status,married', // Require at least one name
            'children_date_of_birth.*' => 'required_if:marital_status,married|date_format:Y-m-d',
            'children_genders.*' => 'required_if:marital_status,married|in:male,female',
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
            'marital_status' => $request->marital_status,
            'phone_number' => $request->phone_number,
        ]);
    
        Parents::create([
            'user_id' => $user->id,
        ]);
    
        if ($request->marital_status === 'married' ) {
            $childrenCount = is_array($request->number_of_children) ? count($request->number_of_children) : (int) $request->number_of_children;
        
            if ($childrenCount !== count($request->children_names)) {
                return response()->json(['error' => 'Number of children names does not match declared number'], 422);
            }
    
            $childrenData = [];
            for ($i = 0; $i <= $childrenCount; $i++) {
                $childrenData[] = [
                    'user_id' => $user->id,
                    'name' => $request->children_names[$i],
                    'date_of_birth' => $request->children_date_of_birth[$i],
                    'gender' => $request->children_genders[$i],
                    'number_of_children' => $request->number_of_children, 
                ];
            }
    
            DB::table('childrens')->insert($childrenData);
        }
    
        return response()->json(['message' => 'User registered successfully','user'=> $user , 'children'=> $childrenData], 200);
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
