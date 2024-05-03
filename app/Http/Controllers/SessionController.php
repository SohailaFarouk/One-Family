<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SessionController extends Controller
{
    public function index()
    {
        $sessions = Session::get();
        return response()->json(['sessions' => $sessions]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id' => 'required|exists:appointments,appointment_id',
            'session_date' => 'required|date',
            'session_fees' => 'required|numeric',
            'session_time' => 'required|date_format:H:i',
            'session_type' => 'required|in:Therapist,Psychiatrist,Physiatrist,Prosthetist'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the existing appointment
        $appointment = Appointment::find($request->input('appointment_id'));

        // Check if appointment exists (optional)
        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        // Validate session_date matches appointment_date
        if ($request->input('session_date') !== $appointment->appointment_date) {
            return response()->json(['error' => 'Session date must match appointment date'], 422);
        }

        // Create a new Session instance and link it to the existing appointment
        $session = new Session();
        $session->appointment_id = $request->input('appointment_id');
        $session->session_date = $request->input('session_date'); // Now validated
        $session->session_fees = $request->input('session_fees');
        $session->session_time = $request->input('session_time');
        $session->session_type = $request->input('session_type');
        $session->save();

      

        return response()->json(['message' => 'Session created successfully', 'session data' => $session]);
    }
    public function reserve(Request $request)
{
    // Validate the request data
    $request->validate([
        'user_id' => 'required|exists:parents,user_id',
        'session_id' => 'required|exists:sessions,session_id',
    ]);

    // Retrieve the session
    $session = Session::find($request->session_id);

    // Check if the session exists
    if (!$session) {
        return response()->json(['message' => 'Session not found.'], 404);
    }

    // Check if the session is already reserved
    if ($session->user_id !== null) {
        return response()->json(['message' => 'Session already reserved.'], 400);
    }

    DB::table('sessions')
        ->where('session_id', $request->session_id)
        ->update(['user_id' => $request->user_id]);

    return response()->json(['message' => 'Session reserved successfully.'], 201);
}

    
}
