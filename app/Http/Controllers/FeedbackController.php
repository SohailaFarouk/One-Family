<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function index(){
        $feedback = DB::table('feedbacks')->get();
        return response()->json(['feedback' => $feedback]);
    }

    /* -------------------------------------------------------------------------- */
   public function show(request $request)
    { 
        $validator = Validator::make($request->all(), [
            'feedback_id' => 'required|exists:feedbacks,feedback_id',
            'user_id' => 'required|exists:admins,user_id',
        ]);    
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $feedbackId = $request->input('feedback_id');
        $userId = $request->input('user_id');
    
        // Fetch the feedback record using Feedback model
        $feedback = Feedback::find($feedbackId);
    
        if ($feedback == null) {
            return response()->json(["error" => "Feedback not found"], 404);
        }
    
        // Insert into admin_feedback table
        DB::table('admin_feedback')->insert([
            'feedback_id' => $feedbackId,
            'user_id' => $userId,
        ]);
    
        return response()->json(["feedback" => $feedback]);
    }
    /* -------------------------------------------------------------------------- */
    public function makeFeedback(Request $request){
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id',
            'user_id'=>'required|exists:parents,user_id',
            'feedback_content'=> 'required',
          ]);    
        
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $feedbackId = DB::table('feedbacks')->insertGetId([
            'order_id' => $request->input('order_id'),
            'feedback_content' => $request->input('feedback_content'),
        ]);
    
        DB::table('parent_feedback')->insert([
            'feedback_id' => $feedbackId,
            'user_id' => $request->input('user_id'),
        ]);

    // Return a success response
    return response()->json(['success' => 'Thanks for your feedback'], 200);

    
    
    }
}
