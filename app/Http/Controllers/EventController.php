<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::get();
        return response()->json(['events' => $events]);
    }


    /* -------------------------------------------------------------------------- */

    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'event_name' => 'required|string|max:255',
            'event_description' => 'required|string',
            'event_price' => 'required|numeric|min:0',
            'event_location' => 'required|string',
            'start_date' => 'required',
            'end_date' => 'required',            
            'event_status' => 'required|string|in:On going,Cancelled,Completed',
        ]);

        // If validation fails, return the validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = new Event();
        $event->event_name = $request->input('event_name');
        $event->event_description = $request->input('event_description');
        $event->event_price = $request->input('event_price');
        $event->event_location = $request->input('event_location');
        $event->start_date = $request->input('start_date');
        $event->end_date = $request->input('end_date');
        $event->event_status = $request->input('event_status');
        $event->save();
        return response()->json(['message' => 'event created successfully', 'event' => $event], 201);
    }



    /* -------------------------------------------------------------------------- */
    public function show(request $request)
    {
        $event_id = $request->input('event_id');
        $event = Event::find($event_id);
        if ($event == null) {
            return response()->json(["message" => "event not found"], 404);
        }
        return response()->json(["event" => $event]);
    }

    /* -------------------------------------------------------------------------- */
    public function edit(string $event_id)
    {
        $event = Event::findOrFail($event_id);
        return response()->json(["event" => $event]);
    }



    /* -------------------------------------------------------------------------- */
    public function update(Request $request)
    {

        $event_id = $request->input('event_id');
        $event = Event::find($event_id);
        if (!$event) {
            return response()->json(['error' => 'event not found'], 404);
        }

        if ($request->filled('event_name')) {
            $event->event_name = $request->input('event_name');
        }
        if ($request->filled('event_description')) {
            $event->event_description = $request->input('event_description');
        }
        if ($request->filled('event_price')) {
            $event->event_price = $request->input('event_price');
        }
        if ($request->filled('event_location')) {
            $event->event_location = $request->input('event_location');
        }
        if ($request->filled('start_date')) {
            $event->start_date = $request->input('start_date');
        }
        if ($request->filled('end_date')) {
            $event->end_date = $request->input('end_date');
        }
        if ($request->filled('event_status')) {
            $event->event_status = $request->input('event_status');
        }

        $event->save();

        return response()->json(['message' => 'event updated successfully', 'event' => $event]);
    }

    /* -------------------------------------------------------------------------- */
    public function destroy(Request $request)
    {
        $event_id = $request->input('event_id');
        $event = Event::find($event_id);

        if (!$event) {
            return response()->json(['error' => 'event not found'], 404);
        }

        $event->delete();
        DB::statement('ALTER TABLE events AUTO_INCREMENT = 1');

        return response()->json(['message' => 'event deleted successfully']);
    }
    /* -------------------------------------------------------------------------- */
    public function reserve(Request $request ){
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:parents,user_id',
                'event_id' => 'required|exists:events,event_id',
            ]);
        
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()->first()], 422);
            }
        
            $event = Event::find($request->input('event_id'));

            if (!$event || $event->event_status === 'Cancelled') {
                return response()->json(['error' => 'The event is cancelled'], 404);
            } 
            DB::table('parents')
            ->where('user_id', $request->user_id)
            ->update(['event_id' => $request->event_id]);   

            return response()->json(['message' => 'Event reserved successfully']);
        
    }
}
