<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubscriptionController extends Controller
{
    public function subscriptionCard(Request $request)
    {
    
        $validator = Validator::make($request->all(), [
            'subscription_plan' => 'required|in:premium,regular',
            'subscription_price' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->input('subscription_plan') === 'premium' && intval($value) !== 200) {
                        $fail('The subscription price must be 200 for the premium plan.');
                    }
                },
            ],
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
    
        $subscription = new Subscription();
        $subscription->subscription_plan = $request->input('subscription_plan');
        $subscription->subscription_price = $request->input('subscription_price');
        $subscription->save();
        
        return response()->json([  'subscription details'=>[
            'subscription_plan' => $subscription->subscription_plan,
            'subscription_price' => $subscription->subscription_price,]
        ]);
    }

    /* -------------------------------------------------------------------------- */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subscription_id' => 'required|exists:subscriptions,subscription_id',
            'user_id' => 'required|exists:parents,user_id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $subscription = Subscription::find($request->input('subscription_id'));
        $subscriptionDate = $subscription->subscription_plan === 'free' ? null : now();
    
        $parent = Parents::where('user_id', $request->input('user_id'))->first();
    
        if (!$parent) {
            return response()->json(['error' => 'Parent not found.'], 404);
        }
    
        // Check if the parent already has a subscription
        if ($parent->subscription_id) {
            // Update the existing subscription 
            DB::table('parents')
                ->where('user_id', $parent->user_id)
                ->update([
                    'subscription_id' => $subscription->subscription_id,
                    'subscription_date' => $subscriptionDate,
                ]);
    
            return response()->json(['message' => 'Subscription updated successfully' ,
            'subscription details'=> $subscription]);
        } else {
            // Create a new subscription for the parent
            $parent->subscription_id = $request->input('subscription_id');
            $parent->subscription_date = $subscriptionDate;
            $parent->save();
            return response()->json(['message' => 'You subscribed successfully']);
        }
    }
}