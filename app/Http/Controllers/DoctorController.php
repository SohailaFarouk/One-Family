<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DoctorController extends Controller
{
    public function index(){
        $doctors = DB::table('doctors')->get();
        return response()->json(['doctors' => $doctors]);
    }


    public function showReservedParents(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:doctors,user_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $doctor = DB::table('doctor_appointment')->where('user_id', $request->user_id)->get('appointment_id');
        if ($doctor->isEmpty()){
            return response()->json(['error' => 'No appointments made'], 404);
        }
    
        $parents = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.user_id')
            ->whereIn('sessions.appointment_id', $doctor->pluck('appointment_id'))
            ->select('users.first_name', 'users.last_name', 'users.marital_status', 'users.date_of_birth', 'sessions.session_type', 'sessions.session_time', 'sessions.session_date')
            ->get();
    
        return response()->json(['Patient list' => $parents]);
    }
    
}
