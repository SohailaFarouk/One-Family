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
    /* -------------------------------------------------------------------------- */
    public function store(Request $request)
    {
      $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $appointment = new Appointment();
        $appointment->appointment_date = $request->input('appointment_date');
        $appointment->save();

        $doctorId = $user_id;
        $appointment->doctors()->attach($doctorId);

        return response()->json(['message' => 'Appointment created successfully', 'appointment_data' => $appointment]);
    }
    /* -------------------------------------------------------------------------- */
    public function destroy(Request $request)
{
  // Validate appointment ID
  $validator = Validator::make($request->all(), [
    'appointment_id' => 'required|integer|exists:doctor_appointment,appointment_id',
  ]);

  if ($validator->fails()) {
    return response()->json(['error' => $validator->errors()], 422);
  }
    $appointment_id = $request->input('appointment_id');
  // Find the appointment
  $appointment = Appointment::find($appointment_id);

  // If appointment not found, return error
  if (!$appointment) {
    return response()->json(['error' => 'Appointment not found'], 404);
  }

  // Detach doctor relationship 
  $appointment->doctors()->detach();

  // Delete the appointment
  $appointment->delete();

  return response()->json(['message' => 'Appointment deleted successfully']);
}

}