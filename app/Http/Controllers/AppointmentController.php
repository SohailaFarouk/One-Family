<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::get();
        return response()->json(['appointments' => $appointments]);
    }
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date',
            'user_id' => 'required|exists:doctors,user_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $appointment = new Appointment();
        $appointment->appointment_date = $request->input('appointment_date');
        $appointment->save();

        $doctorId = $request->input('user_id');
        $appointment->doctors()->attach($doctorId);

        return response()->json(['message' => 'Appointment created successfully', 'appointment_data' => $appointment]);
    }
}