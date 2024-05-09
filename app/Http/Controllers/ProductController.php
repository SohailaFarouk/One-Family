<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::get();
        return response()->json(['products' => $products]);
    }
    /* -------------------------------------------------------------------------- */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'product_specification' => 'nullable|string',
            'product_price' => 'required|numeric|min:0',
            'quantity' => 'nullable|',
            'product_type' => 'required|string|in:Books,Coloring Books,Medications,Prosthetic Tools',
            'product_image' => 'nullable|image|max:2048',
            'user_id' => 'required|exists:admins,user_id',

        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $product = new Product();
        $product->product_name = $request->input('product_name');
        $product->product_description = $request->input('product_description');
        $product->product_specification = $request->input('product_specification');
        $product->product_price = $request->input('product_price');
        $product->product_type = $request->input('product_type');
        $product->quantity = $request->input('quantity');

        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images');
            $product->product_image = $imagePath;
        }
        $product->save();

        $admin_id = $request->input('user_id');
        $product->admin()->attach($admin_id);

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }
    /* -------------------------------------------------------------------------- */
    public function show(request $request)
    {
        $product_id = $request->input('product_id');
        $product = Product::find($product_id);
        if ($product == null) {
            return response()->json(["message" => "product not found"], 404);
        }
        return response()->json(["product" => $product]);
    }
    /* -------------------------------------------------------------------------- */
    public function edit(string $product_id)
    {
        $product = Product::findOrFail($product_id);
        return response()->json(["product" => $product]);
    }
    /* -------------------------------------------------------------------------- */
    public function update(Request $request)
    {

        $product_id = $request->input('product_id');
        $product = Product::find($product_id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        if ($request->filled('product_name')) {
            $product->product_name = $request->input('product_name');
        }
        if ($request->filled('product_description')) {
            $product->product_description = $request->input('product_description');
        }
        if ($request->filled('product_specification')) {
            $product->product_specification = $request->input('product_specification');
        }
        if ($request->filled('product_price')) {
            $product->product_price = $request->input('product_price');
        }
        if ($request->filled('product_type')) {
            $product->product_type = $request->input('product_type');
        }
        if ($request->filled('quantity')) {
            $product->quantity = $request->input('quantity');
        }
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images');
            $product->product_image = $imagePath;
        }

        $product->save();

        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }

    /* -------------------------------------------------------------------------- */
    public function destroy(Request $request)
    {
        $product_id = $request->input('product_id');
        $product = Product::find($product_id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->delete();
        DB::statement('ALTER TABLE products AUTO_INCREMENT = 1');

        return response()->json(['message' => 'Product deleted successfully']);
    }
    public function shop(Request $request) {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:parents,user_id',
            'product_id' => 'required|exists:products,product_id',
            'quantity' => 'required|integer|min:1|exists:products,quantity'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }
    
        $product = Product::find($request->input('product_id'));
    
        if (!$product || $product->quantity < $request->input('quantity')) {
            return response()->json(['error' => 'Product not available in the requested quantity'], 404);
        }
    
        $user_id = $request->input('user_id');
        $quantity = $request->input('quantity');
        $product->parents()->attach($user_id, ['quantity' => $quantity]);
        $product->quantity -= $quantity;
        $product->save();
        return response()->json(['message' => 'Product reserved successfully']);
    }
    
}
