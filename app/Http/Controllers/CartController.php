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

    public function index(Request $request)
    {
        $user_id = $request->header('user_id');
    
        
        // Check if the parent has a cart
        $cart = DB::table('carts')->where('user_id', $user_id)->first();
        
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
    
        // Get the session for the user
        $session = DB::table('sessions')->where('user_id', $user_id)->first();
    
        // Get the parent's product and quantity
        $parentProduct = DB::table('parent_product')->where('user_id', $user_id)->first();
    
        if (!$parentProduct) {
            return response()->json(['error' => 'Parent product not found'], 404);
        }
    
        // Get the product details for the parent's product
        $product = DB::table('products')->where('product_id', $parentProduct->product_id)->first();
    
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    
        // Get the parent's event ID
        $parentEventId = DB::table('parents')->where('user_id', $user_id)->pluck('event_id')->first();
    
        // Get the event details
        $event = DB::table('events')->where('event_id', $parentEventId)->first();
    
        return response()->json([
            'cart' => $cart,
            'session' => $session,
            'product' => [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_description' => $product->product_description,
                'product_specification' => $product->product_specification,
                'product_price' => $product->product_price,
                'product_type' => $product->product_type,
                'product_image' => $product->product_image,
                'quantity' => $parentProduct->quantity // Use the input quantity instead
            ],
            'event' => $event,
        ]);
    }
    
    /* -------------------------------------------------------------------------- */
    /* --------------------------- add product to cart -------------------------- */
    public function productToCart(Request $request)
{
    $user_id = $request->header('user_id');

    $validator = Validator::make($request->all(), [
        'product_id' => 'required|exists:products,product_id',
        'quantity' => 'required|integer|min:1'
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
    }

    $product_id = $request->input('product_id');
    $quantity = $request->input('quantity');

    $product = Product::find($product_id);

    if (!$product) {
        return response()->json(['error' => 'Product not available'], 404);
    }

    if ($product->quantity < $quantity) {
        return response()->json(['error' => 'Insufficient product quantity'], 400); // Use 400 for Bad Request
    }

    $cart = Cart::where('user_id', $user_id)->first();

    // Check if product already in cart
    $productInCart = DB::table('product_cart')
        ->where('product_id', $product_id)
        ->where('cart_id', $cart ? $cart->cart_id : null) // Check for existing cart
        ->first();

    if ($productInCart) {
        return response()->json([
            'message' => 'Product already added to your cart',
            'product' => [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_description' => $product->product_description,
                'product_specification' => $product->product_specification,
                'product_price' => $product->product_price,
                'product_type' => $product->product_type,
                'product_image' => $product->product_image,
                'quantity' => $quantity // Use the input quantity instead
            ]
        ], 200);
    }

    $product->parents()->attach($user_id, ['quantity' => $quantity]);
    $product->quantity -= $quantity;
    $product->save();

    $totalAmount = $product->product_price * $quantity;

    if ($cart) {
        $cart->total_amount += $totalAmount;
        $cart->save();
        $product->cart()->attach($cart->cart_id);
        $updatedQuantity = DB::table('parent_product')
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->value('quantity'); // Get updated quantity from parent_product
        return response()->json([
            'message' => 'Product reserved and Cart updated successfully',
            'product' => [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'product_description' => $product->product_description,
                'product_specification' => $product->product_specification,
                'product_price' => $product->product_price,
                'product_type' => $product->product_type,
                'product_image' => $product->product_image,
                'quantity' => $updatedQuantity
            ]
        ], 200);
    }

    // Create new cart if needed
    $cart = new Cart([
        'user_id' => $user_id,
        'total_amount' => $totalAmount
    ]);
    $cart->save();
    $product->cart()->attach($cart->cart_id);
    return response()->json([
        'message' => 'Product reserved and added to cart successfully',
        'product' => [
            'product_id' => $product->product_id,
            'product_name' => $product->product_name,
            'product_description' => $product->product_description,
            'product_specification' => $product->product_specification,
            'product_price' => $product->product_price,
            'product_type' => $product->product_type,
            'product_image' => $product->product_image,
            'quantity' => $quantity // Use the input quantity instead
        ]
    ], 200);
}

    /* -------------------------------------------------------------------------- */
    /* --------------------------- add session to cart -------------------------- */
    public function sessionToCart(Request $request)
    {
        $user_id = $request->header('user_id');

      $validator = Validator::make($request->all(), [
        'session_id' => 'required|exists:sessions,session_id',
      ]);
    
      if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
      }
    
      $session = Session::find($request->session_id);
    
      // Check if session already belongs to the user
      if ($session->user_id === $user_id) {
        return response()->json(['message' => 'Session Already Reserved', 'session' => $session], 400);
      }
    
      // Check if session is available (not reserved by another user)
      if ($session->user_id !== null) {
        return response()->json(['message' => 'Session Not Available'], 400);
      }
    
      $totalAmount = $session->session_fees;
    
      // Find or create cart for the user
      $cart = Cart::where('user_id', $user_id)->first();
      if (!$cart) {
        $cart = new Cart();
        $cart->user_id = $user_id;
      }
    
      // Update cart total amount
      $cart->total_amount += $totalAmount;
    
      // Save cart and update session with cart association
      if (!$cart->save()) {
        // Handle saving cart errors (consider logging)
        return response()->json(['error' => 'Failed to save cart'], 500);
      }
    
      Session::where('session_id', $request->session_id)
        ->update(['cart_id' => $cart->cart_id, 'user_id' => $user_id]);
    
      $message = 'Session reserved and added to cart successfully';
    
      return response()->json(['message' => $message, 'session' => $session], 200);
    }
    
    /* -------------------------------------------------------------------------- */
    /* --------------------------- add event to cart -------------------------- */
    public function eventToCart(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:events,event_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
        $event = Event::find($request->event_id);

        if ($event->event_status === 'Cancelled') {
            return response()->json(['message' => 'Event is cancelled'], 400);
        }
        if ($event->event_status === 'Completed') {
            return response()->json(['message' => 'Event is finished'], 400);
        }

        $cart = Cart::where('user_id', $user_id)->first();
        $event_cart = Cart::where('event_id', $request->event_id)->first();
        DB::table('parents')
            ->where('user_id', $user_id)
            ->update(['event_id' => $request->event_id]);
        if ($cart && $event_cart) {
            return response()->json(['message' => 'Event Already Reserved', 'event' => $event], 400);
        }

        if ($cart) {
            $cart->total_amount += $event->event_price;
            $cart->save();
            Cart::where('user_id',$user_id)
                ->update(['event_id' => $event->event_id]);
            return response()->json(['message' => 'Event reserved and Cart updated successfully', 'event' => $event], 200);
        }
        //if user doesn't have items in cart create new cart
        $cart = new Cart();
        $cart->total_amount += $event->event_price; // Set initial total amount
        $cart->user_id = $user_id;
        $cart->event_id = $event->event_id;
        $cart->save();
    }
    /* -------------------------------------------------------------------------- */
    /* --------------------------- edit product from cart -------------------------- */
    public function editCart(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|integer|min:1'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $product_id = $request->input('product_id');
        $quantity = $request->input('quantity');
    
        // Find product
        $product = DB::table('products')->where('product_id', $product_id)->first();
        if (!$product) {
            return response()->json(['error' => 'Product not available'], 404);
        }
    
        // Find user's cart
        $cart = DB::table('carts')->where('user_id', $user_id)->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
    
        // Find existing product in cart
        $existingProduct = DB::table('parent_product')
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->first();
    
        if (!$existingProduct) {
            return response()->json(['error' => 'Product not reserved'], 404);
        }
    
        // Calculate quantity difference
        $quantityDifference = $quantity - $existingProduct->quantity;
    
        // Update product quantity in parent_product table
        DB::table('parent_product')
            ->where('user_id', $user_id)
            ->where('product_id', $product_id)
            ->update(['quantity' => $quantity]);
    
        // Update product quantity in products table (same as before)
        DB::table('products')
            ->where('product_id', $product_id)
            ->decrement('quantity', abs($quantityDifference));
    
        // Update total amount in the cart
    
        // Get current total amount (if any)
        $currentTotal = $cart->total_amount ?? 0;
    
        // Calculate price change based on quantity difference and product price
        $priceChange = $quantityDifference * $product->product_price;
    
        // Update total amount considering existing value
        $newTotal = $currentTotal + $priceChange;
    
        DB::table('carts')
            ->where('cart_id', $cart->cart_id)
            ->update(['total_amount' => $newTotal]);
    
        return response()->json(['message' => 'Cart updated successfully', 'cart' => $cart], 200);
    }
    
    /* -------------------------------------------------------------------------- */
    /* ------------------------ delete product from cart ------------------------ */
    public function deleteProduct(Request $request)
    {
        $user_id = $request->header('user_id');
      $validator = Validator::make($request->all(), [
        'product_id' => 'required|exists:product_cart,product_id',
      ]);
    
      if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
      }
    
      // Extract user and product IDs
      $product_id = $request->input('product_id');
    
      // Find the user's cart
      $cart = Cart::where('user_id', $user_id)->first();
    
      if (!$cart) {
        return response()->json(['error' => 'Cart not found'], 404);
      }
    
      $cart_id = $cart->cart_id;
    
      // Check if the product exists in the cart
      $existingProduct = DB::table('product_cart')
        ->where('cart_id', $cart_id)
        ->where('product_id', $product_id)
        ->first();
      $parentProduct = DB::table('parent_product')
        ->where('user_id', $user_id)
        ->where('product_id', $product_id)
        ->first();
    
      if (!$existingProduct) {
        return response()->json(['error' => 'Product not found in the cart'], 404);
      }
    
      // Retrieve the product's price
      $productPrice = DB::table('products')
        ->where('product_id', $product_id)
        ->value('product_price');
    
      // Delete product from cart
      DB::table('product_cart')
        ->where('cart_id', $cart_id)
        ->where('product_id', $product_id)
        ->delete();
    
      // Delete product association from parent_product (reduces stock)
      DB::table('parent_product')
        ->where('product_id', $product_id)
        ->delete();
    
      // Retrieve the quantity from the cart item 
      $quantityToUpdate = $parentProduct->quantity ?? 0;
    
      // Increase product quantity in the main products table 
      Product::where('product_id', $product_id)
        ->increment('quantity', $quantityToUpdate);
    
      // Only update cart total amount if it's not null already
      if (!is_null($cart->total_amount)) {
        $newTotalAmount = $cart->total_amount - $productPrice;
        if ($newTotalAmount >= 0) { 
          $cart->update(['total_amount' => $newTotalAmount]);
        }
      }
      return response()->json(['message' => 'Product deleted from cart successfully'], 200);
    }
    /* -------------------------------------------------------------------------- */
    /* ------------------------- delete event from cart ------------------------- */
    public function deleteEvent(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'event_id' => 'required|exists:carts,event_id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $event_id = $request->input('event_id');
    
        $cart = Cart::where('user_id', $user_id)->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
    
        // Detach the event from the cart using DB facade
        DB::table('carts')->where('cart_id', $cart->cart_id)->where('event_id', $event_id)->update(['event_id' => null]);
    
        // delete related records from the parents table
        DB::table('parents')->where('event_id', $event_id)->update(['event_id' => null]);
    
        // Update the cart's total amount
        $eventPrice = DB::table('events')->where('event_id', $event_id)->value('event_price');
        $newTotalAmount = max(0, $cart->total_amount - $eventPrice);
        DB::table('carts')->where('cart_id', $cart->cart_id)->update(['total_amount' => $newTotalAmount]);
    
        return response()->json(['message' => 'Event deleted from cart successfully'], 200);
    }
    /* -------------------------------------------------------------------------- */
    /* ------------------------ delete session from cart ------------------------ */
    public function deleteSession(Request $request)
    {
        $user_id = $request->header('user_id');

        $validator = Validator::make($request->all(), [
            'session_id' => 'required|exists:sessions,session_id',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $session_id = $request->input('session_id');
    
        // Get the session associated with the user
        $session = Session::where('user_id', $user_id)->where('session_id', $session_id)->first();
        if (!$session) {
            return response()->json(['error' => 'Session not found'], 404);
        }
    
        // Get the cart associated with the user
        $cart = Cart::where('user_id', $user_id)->first();
        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
    
        // Detach the session from the cart using DB facade
        DB::table('sessions')->where('cart_id', $cart->cart_id)->where('session_id', $session_id)->update(['cart_id' => null]);
    
        // Update the session's user_id to null
        DB::table('sessions')->where('session_id', $session_id)->update(['user_id' => null]);
    
        // Update the cart's total amount
        $sessionPrice = $session->session_fees;
        $newTotalAmount = max(0, $cart->total_amount - $sessionPrice);
        DB::table('carts')->where('cart_id', $cart->cart_id)->update(['total_amount' => $newTotalAmount]);
    
        return response()->json(['message' => 'Session deleted from cart successfully'], 200);
    }
}
