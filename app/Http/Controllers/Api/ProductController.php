<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'tag', 'images'])->orderBy('created_at', 'desc')->paginate(10);
        return response()->json($products);
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'prix' => 'required|numeric',
            'old_price' => 'nullable|numeric',
            'sku' => 'required|string|max:255|unique:products,sku',
            'categorie_id' => 'required|exists:categories,id',
            'tag_id' => 'required|exists:tags,id',
            'color' => 'nullable|string|max:255',
            'image_initiale' => 'required|image|mimes:jpeg,png,jpg,gif,svg',
            'description' => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg'
        ]);

        // Handle the main image upload
        if ($request->hasFile('image_initiale')) {
            $imagePath = $request->file('image_initiale')->store('products', 'public');
            $validatedData['image_initiale'] = $imagePath;
        }

        // Create a new product
       // $product = Product::create($validatedData);

        // Handle additional images if any
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('products', 'public');
                // Image::create([
                //     'product_id' => $product->id,
                //     'image' => $imagePath
                // ]);

                return response()->json( 'Images :'.$imagePath);
            }


        }
        else{
            return response()->json('Pas dimage');
        }
        return response()->json($request->all());
        // Return a response
        // return response()->json([
        //     'message' => 'Product created successfully',
        //     'product' => $product,
        // ], 201);
    }


    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Optionally delete associated images
        $product->images()->delete();

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ], 200);
    }

    public function getProductById($id)
    {
        $product = Product::with(['category', 'tag', 'images'])->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json($product);
    }
}
