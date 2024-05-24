<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Event;
use App\Models\Order;
use App\Models\Product;
use App\Models\Session;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::all();
    
        if ($orders->isEmpty()) {
            return response()->json(['success' => false, 'error' => 'No orders found'], 404);
        }
            $allOrders = $orders->map(function($order) {
            $orderDetails = json_decode($order->order_details, true);
            return [
                'order_id' => $order->order_id,
                'cart_id' => $order->cart_id,
                'order_amount' => $order->order_amount,
                'order_details' => $orderDetails
            ];
        });
    
        return response()->json(['success' => true, 'orders' => $allOrders], 200);
    }
    
    public function confirmOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_id' => 'required|exists:carts,cart_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['success'=> false ,'error' => $validator->errors()->first()], 422);
        }
    
        $cart = Cart::find($request->input('cart_id'));
    
        // Check if an order already exists for this cart
        $existingOrder = Order::where('cart_id', $cart->cart_id)->first();
        if ($existingOrder) {
            $orderDetails = json_decode($existingOrder->order_details, true);
            return response()->json(['success'=> false ,'error' => 'Order is already confirmed', 'orderDetails' => $orderDetails ,             
            'The total'=>$existingOrder->order_amount,
        ], 404);
        }
    
        // Retrieve products associated with the cart
        $productIds = DB::table('product_cart')
            ->where('cart_id', $cart->cart_id)
            ->pluck('product_id')
            ->toArray();
    
            $products = Product::whereIn('products.product_id', $productIds) // 
            ->join('parent_product', 'products.product_id', '=', 'parent_product.product_id')
            ->select('products.*', 'parent_product.quantity')
            ->get();
        
        // Retrieve sessions associated with the cart
        $sessions = Session::where('cart_id', $cart->cart_id)->get();
    
        // Retrieve events associated with the cart
        $eventIds = DB::table('carts')
            ->where('cart_id', $cart->cart_id)
            ->pluck('event_id')
            ->toArray();
    
        $events = Event::whereIn('event_id', $eventIds)->get();
    
        if ($events->isEmpty()) {
            $events = [];
        }
    
        // Create order details array to store session, product, and event information
        $orderDetails = [
            'sessions' => $sessions,
            'products' => $products,
            'events' => $events,
        ];
    
        // Convert order details array to JSON string
        $orderDetailsJson = json_encode($orderDetails);
    
        // Create the order
        $order = new Order();
        $order->order_amount = $cart->total_amount; // Set the order amount
        $order->order_details = $orderDetailsJson;
        $order->order_number = $cart->cart_id; 
        $order->cart_id = $cart->cart_id; 
        $order->save();
    
        $response = ['success' => true,
            'message' => 'Order confirmed successfully',
            'order details' => $orderDetails, 
            'The total'=>$order->order_amount,
            'order id' => $order->order_id,
        ];
    
        return response()->json($response, 200);
    }   
    
    
    public function applyVoucher(Request $request)
{
    $validator = Validator::make($request->all(), [
        'order_id' => 'required|exists:orders,order_id',
        'voucher_code' => 'required|exists:vouchers,voucher_code',
    ]);

    if ($validator->fails()) {
        return response()->json(['success'=> false ,'error' => $validator->errors()->first()], 422);
    }

    $order = Order::find($request->input('order_id'));
    $voucher = Voucher::where('voucher_code', $request->input('voucher_code'))->first();

    if ($order && $voucher) {
        $order->voucher_id = $voucher->voucher_id;

        $order->order_amount = $order->order_amount * (1 - $voucher->voucher_percentage);
        $order->save();

        $cart = Cart::find($order->cart_id);
        
        if ($cart) {
            $user_id = $cart->user_id;
            $voucher->parents()->attach($user_id);
        }

        return response()->json(['success' => true,'message' => 'Voucher applied successfully', 'order' => $order], 200);
    }

    return response()->json(['success'=> false ,'error' => 'Order or Voucher not found'], 404);
}

    
}
