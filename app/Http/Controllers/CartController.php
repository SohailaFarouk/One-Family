<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Event;
use App\Models\Product;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{

    public function index(){
        $carts = Cart::get();
        return response()->json(['carts' => $carts]);
    }
    /* -------------------------------------------------------------------------- */
    /* --------------------------- add product to cart -------------------------- */
    public function productToCart(Request $request){
        $validator = Validator::make($request->all(), [
                    'user_id' => 'required|exists:parents,user_id',
                    'product_id' => 'required|exists:products,product_id',
                    'quantity' => 'required|integer|min:1'
                ]);
                
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $product = Product::find($request->input('product_id'));

        if (!$product) {
            return response()->json(['error' => 'Product not available '], 404);
        }
        if ( $product->quantity < $request->input('quantity')) {
            return response()->json(['error' => 'Product not available in the requested quantity'], 404);
        }

        $user_id = $request->input('user_id');
        $quantity = $request->input('quantity');

      $cart = Cart::find($request->user_id);
      $product_cart = DB::table('product_cart')
      ->where('product_id', $request->product_id)->whereNotNull('cart_id')
      ->first();
      
      // Check if the parent exists in the cart and if the product is already added to the cart
      if ($cart && $product_cart !== null) {
        return response()->json(['message' => 'you already added this item to the cart'], 404);
    } 
      $product->parents()->attach($user_id, ['quantity' => $quantity]);
      $product->quantity -= $quantity;
      $product->save();

    $totalAmount = $product->product_price * $quantity;
        
    $cart = Cart::find($request->user_id);
    if ($cart){
        $cart->total_amount += $totalAmount;
        $cart->save();
        $product->cart()->attach($cart->cart_id);
        return response()->json(['message' => 'Product reserved and Cart updated successfully' , 'product'=> $product ], 200);
    }
      // Create or get the cart
      $cart = new Cart(); 
      $cart->total_amount = $totalAmount;
      $cart->user_id = $user_id;
      $cart->save();
      // Attach product to cart
      $product->cart()->attach($cart->cart_id);
      return response()->json(['message' => 'Product reserved and added to cart successfully'], 404);
    }
    /* -------------------------------------------------------------------------- */
    /* --------------------------- add session to cart -------------------------- */
    public function sessionToCart(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:parents,user_id',
            'session_id' => 'required|exists:sessions,session_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $session = Session::find($request->session_id);
        $user_id = $request->input('user_id');

        if ($session->user_id == $user_id) {
            return response()->json(['message' => 'Session Already Reserved'], 400);
        }if ($session->user_id !== $user_id && $session->user_id !=null) {
            return response()->json(['message' => 'Session Not Available'], 400);
        }
        $totalAmount = $session->session_fees;
//if user already has other items in cart update the total amount
        $cart = Cart::find($request->user_id);
        if ($cart){
            $cart->total_amount += $totalAmount;
            $cart->save();
            Session::where('session_id', $request->session_id)
            ->update(['cart_id' => $cart->cart_id , 'user_id' => $request->user_id]);
            return response()->json(['message' => 'Session reserved and Cart updated successfully', 'session'=> $session ], 200);
        }
        //if user doesn't have items in cart create new cart
        $cart = new Cart();
        $cart->total_amount += $totalAmount;
        $cart->user_id = $user_id;
        $cart->save();
        
        // Update the sessions table with the new cart_id
            Session::where('session_id', $request->session_id)
       ->update(['cart_id' => $cart->cart_id, 'user_id' => $request->user_id]);

            return response()->json(['message' => 'Session reserved and added to cart successfully', 'session'=> $session], 200);
    }
    /* -------------------------------------------------------------------------- */
    /* --------------------------- add event to cart -------------------------- */
    public function eventToCart(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:parents,user_id',
            'event_id' => 'required|exists:events,event_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $event= Event::find($request->event_id);

        $user_id = $request->input('user_id');

        if ($event->event_status === 'Cancelled'){
            return response()->json(['message' => 'Event is cancelled'], 400);
        }
        if ($event->event_status === 'Completed'){
            return response()->json(['message' => 'Event is finished'], 400);
        }
        
        $cart = Cart::where('user_id', $user_id)->first();
        $event_cart = Cart::where('event_id', $request->event_id)->first();
                    DB::table('parents')
            ->where('user_id', $request->user_id)
            ->update(['event_id' => $request->event_id]); 
        if ($cart && $event_cart) {
            return response()->json(['message' => 'Event Already Reserved'], 400);
        }
        
        if ($cart){
            $cart->total_amount += $event->event_price;
            $cart->save();
            Cart::where('user_id', $request->user_id)
            ->update(['event_id' => $event->event_id ]);
            return response()->json(['message' => 'Event reserved and Cart updated successfully', 'event'=> $event ], 200);
        }
        //if user doesn't have items in cart create new cart
        $cart = new Cart();
        $cart->total_amount += $event->event_price; // Set initial total amount
        $cart->user_id = $user_id;
        $cart->event_id = $event->event_id;
        $cart->save();
      
    }
    }
    


