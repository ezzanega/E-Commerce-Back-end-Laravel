<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function sendOrder(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create the order
        $order = Order::create([
            'user_id' => $request->user_id,
            'total_amount' => $request->total_amount,
            'status' => 'pending',
        ]);

        // Create order items
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->prix, // Assuming 'prix' is the price column in the products table
                ]);
            }
        }

        Cart::where('user_id', $request->user_id)->delete();

        return response()->json(['message' => 'Order placed successfully', 'order' => $order], 201);
    }


    public function getAllOrders(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $orders = Order::with('user')->paginate($perPage);
        return response()->json($orders);
    }

    public function getOrderCounts(Request $request)
    {
        $allCount = Order::count();
        $pendingCount = Order::where('status', 'pending')->count();
        $sentCount = Order::where('status', 'sent')->count();
        $canceledCount = Order::where('status', 'canceled')->count();

        return response()->json([
            'all' => $allCount,
            'pending' => $pendingCount,
            'sent' => $sentCount,
            'canceled' => $canceledCount,
        ]);
    }
}
