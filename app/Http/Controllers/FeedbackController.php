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
        return response()->json(['success' => true,'feedback' => $feedback]);
    }

    /* -------------------------------------------------------------------------- */
   public function show(request $request)
    { 
        $user_id = $request->header('user_id');
        $validator = Validator::make($request->all(), [
            'feedback_id' => 'required|exists:feedbacks,feedback_id',
        ]);    
    
        if ($validator->fails()) {
            return response()->json(['success'=> false ,'error' => $validator->errors()->first()], 422);
        }
    
        $feedbackId = $request->input('feedback_id');    
        // Fetch the feedback record using Feedback model
        $feedback = Feedback::find($feedbackId);
    
        if ($feedback == null) {
            return response()->json(['success'=> false ,"error" => "Feedback not found"], 404);
        }
    
        // Insert into admin_feedback table
        DB::table('admin_feedback')->insert([
            'feedback_id' => $feedbackId,
            'user_id' => $user_id,
        ]);
    
        return response()->json(['success' => true,"feedback" => $feedback]);
    }
    /* -------------------------------------------------------------------------- */
    public function makeFeedback(Request $request){
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id',
            'feedback_content'=> 'required',
          ]);    
        
        
        if ($validator->fails()) {
            return response()->json(['success'=> false ,'error' => $validator->errors()->first()], 422);
        }
        $feedbackId = DB::table('feedbacks')->insertGetId([
            'order_id' => $request->input('order_id'),
            'feedback_content' => $request->input('feedback_content'),
        ]);
    
        DB::table('parent_feedback')->insert([
            'feedback_id' => $feedbackId,
            'user_id' => $user_id,
        ]);

    // Return a success response
    return response()->json(['success' => true,'message' => 'Thanks for your feedback'], 200);

    
    
    }
}
