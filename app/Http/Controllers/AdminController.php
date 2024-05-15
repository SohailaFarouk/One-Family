<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
  public function updateParent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,user_id',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $request->input('user_id'), // Unique validation considering user ID
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'nat_id' => 'nullable|numeric',
            'gender' => 'nullable',
            'marital_status' => 'nullable',
            'phone_number' => 'nullable|digits:11',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $userId = $request->input('user_id');
        $updates = [];
    
        if ($request->filled('first_name')) {
            $updates['first_name'] = $request->input('first_name');
        }
        if ($request->filled('last_name')) {
            $updates['last_name'] = $request->input('last_name');
        }
        if ($request->filled('email')) {
            $updates['email'] = $request->input('email');
        }
        if ($request->filled('date_of_birth')) {
            $updates['date_of_birth'] = $request->input('date_of_birth');
        }
        if ($request->filled('address')) {
            $updates['address'] = $request->input('address');
        }
        if ($request->filled('nat_id')) {
            $updates['nat_id'] = $request->input('nat_id');
        }
        if ($request->filled('gender')) {
            $updates['gender'] = $request->input('gender');
        }
        if ($request->filled('marital_status')) {
            $updates['marital_status'] = $request->input('marital_status');
        }
        if ($request->filled('phone_number')) {
            $updates['phone_number'] = $request->input('phone_number');
        }
    
        if (!empty($updates)) {
            DB::table('users')
                ->where('user_id', $userId)
                ->update($updates);
        }
    
        return response()->json(['message' => 'User data updated successfully' , 'updates' => $updates]);
    }
  

/* -------------------------------------------------------------------------- */
public function deleteParent(Request $request)
{
  $validator = Validator::make($request->all(), [
    'user_id' => 'required|exists:users,user_id',  // Validate user ID existence
  ]);

  if ($validator->fails()) {
    return response()->json(['error' => $validator->errors()->first()], 422);
  }

  $userId = $request->input('user_id');

  // Delete sessions associated with user's appointments
  DB::table('sessions')
    ->whereIn('appointment_id', function ($query) use ($userId) {
      $query->select('appointment_id')
        ->from('doctor_appointment')
        ->where('user_id', $userId);
    })
    ->delete();
  DB::table('products')
    ->whereIn('product_id', function ($query) use ($userId) {
      $query->select('product_id')
        ->from('admin_product')
        ->where('user_id', $userId);
    })
    ->delete();
  DB::table('events')
    ->whereIn('event_id', function ($query) use ($userId) {
      $query->select('event_id')
        ->from('admins')
        ->where('user_id', $userId);
    })
    ->delete();

  // Delete user's appointments
  DB::table('doctor_appointment')
    ->where('user_id', $userId)
    ->delete();

  DB::table('admin_product')
    ->where('user_id', $userId)
    ->delete();
    
  DB::table('childrens')
    ->where('user_id', $userId)
    ->delete();

  // Delete user 
  DB::table('users')
    ->where('user_id', $userId)
    ->delete();

  return response()->json(['message' => 'User deleted successfully']);
}

}
