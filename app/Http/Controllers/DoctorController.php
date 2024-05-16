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


    public function showReservedParents(Request $request)
    {
        $user_id = $request->header('user_id');

    
        $doctorAppointments = DB::table('doctor_appointment')
            ->where('user_id', $user_id)
            ->pluck('appointment_id');
    
        if ($doctorAppointments->isEmpty()) {
            return response()->json(['error' => 'No appointments made'], 404);
        }
    
        $parents = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.user_id')
            ->whereIn('sessions.appointment_id', $doctorAppointments)
            ->select(
                'users.user_id',
                'users.first_name',
                'users.last_name',
                'users.marital_status',
                'users.date_of_birth',
                'sessions.session_type',
                'sessions.session_time',
                'sessions.session_date'
            )
            ->get();
    
        $userIDs = $parents->pluck('user_id');
    
        $children = DB::table('childrens')
            ->whereIn('user_id', $userIDs)
            ->get();
    
        $response = [];
        foreach ($parents as $parent) {
            $responseItem = [
                'first_name' => $parent->first_name,
                'last_name' => $parent->last_name,
                'marital_status' => $parent->marital_status,
                'date_of_birth' => $parent->date_of_birth,
                'session_type' => $parent->session_type,
                'session_time' => $parent->session_time,
                'session_date' => $parent->session_date,
                'children' => [],
            ];
    
            foreach ($children as $child) {
                if ($child->user_id === $parent->user_id) {
                    $responseItem['children'][] = [
                        'name' => $child->name,
                        'date_of_birth' => $child->date_of_birth,
                    ];
                }
            }
    
            $response[] = $responseItem;
        }
    
        return response()->json(['Patient list' => $response]);
    }
    
    
    
}
