<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::get();
        return response()->json(['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'product_name' => 'required|string|max:255',
            'product_description' => 'required|string',
            'product_specification' => 'nullable|string',
            'product_price' => 'required|numeric|min:0',
            'product_type' => 'required|string|in:Books,Coloring Books,Medications,Prosthetic Tools',
            'product_image' => 'nullable|image|max:2048'
        ]);

        // If validation fails, return the validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = new Product();
        $product->product_name = $request->input('product_name');
        $product->product_description = $request->input('product_description');
        $product->product_specification = $request->input('product_specification');
        $product->product_price = $request->input('product_price');
        $product->product_type = $request->input('product_type');
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images');
            $product->product_image = $imagePath;
        }
        $product->save();

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(request $request)
    {
        $product_id = $request->input('product_id');
        $product = Product::find($product_id);        
        if ($product == null) {
            return response()->json(["message" => "product not found"], 404);
        }
        return response()->json(["product" => $product]);        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $product_id)
    {
        $product = Product::findOrFail($product_id);
        return response()->json(["product" => $product]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
    
        // Retrieve the product to update
        $product_id = $request->input('product_id');
        $product = Product::find($product_id);     
        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }
    
        // Update product attributes based on the request data
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
        if ($request->hasFile('product_image')) {
            $imagePath = $request->file('product_image')->store('product_images');
            $product->product_image = $imagePath;
        }
    
        // Save the updated product
        $product->save();
    
        return response()->json(['message' => 'Product updated successfully', 'product' => $product]);
    }
    


    /**
     * Remove the specified resource from storage.
     */


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

}
