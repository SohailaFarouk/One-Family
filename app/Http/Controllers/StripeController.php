<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class StripeController extends Controller
{
    public function checkout(){
        $order = Order::get();
        return response()->json(['order' => $order]);
    }
    public function confirmPayment(Request $request){
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,order_id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }    

        $order_id = $request->input('order_id');
        $order = DB::table('orders')->where('order_id', $order_id)->first();
        Stripe::setApiKey(config('stripe.sk'));
    
        // Check if the order exists
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }
    
        try {
            // Create line items for Stripe Checkout
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'EGP',
                        'unit_amount' => $order->order_amount * 100, // Amount in cents
                        'product_data' => [
                            'name' => 'Order Details',
                        ],
                    ],
                    'quantity' => 1, // Assuming quantity is 1 for this line item
                ],
            ];
    
            // Create the Checkout Session
            $checkoutSession = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => 'http://127.0.0.1:8000/products/',
                'cancel_url' => 'http://127.0.0.1:8000/products/'        
            ]);
    
            // Redirect the user to the Checkout page
            return redirect()->to($checkoutSession->url);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    

}
