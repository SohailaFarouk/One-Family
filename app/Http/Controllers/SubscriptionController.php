<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\Subscription;
use Illuminate\Http\Request;
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
        
        return response()->json([
            'subscription_plan' => $subscription->subscription_plan,
            'subscription_price' => $subscription->subscription_price,
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
    
        if ($parent && $parent->subscription_id) {
            return response()->json(['error' => 'You are already subscribed.'], 422);
        }
    
        if ($parent) {
            $parent->subscription_id = $request->input('subscription_id');
            $parent->subscription_date = $subscriptionDate;
            $parent->save();
        } else {
            Parents::create([
                'user_id' => $request->input('user_id'),
                'subscription_id' => $request->input('subscription_id'),
                'subscription_date' => $subscriptionDate,
            ]);
        }
    
        return response()->json(['message' => 'You subscribed successfully']);
    }
    
}