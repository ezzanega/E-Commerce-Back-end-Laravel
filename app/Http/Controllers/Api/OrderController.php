<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
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
            'note' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Create the order
        $order = Order::create([
            'user_id' => $request->user_id,
            'total_amount' => $request->total_amount,
            'status' => 'pending',
            'note' => $request->note,
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
        $orders = Order::with('user')->orderBy('created_at','desc')->paginate($perPage);
        return response()->json($orders);
    }

    public function getOrderCounts(Request $request)
    {
        $allCount = Order::count();
        $pendingCount = Order::where('status', 'pending')->count();
        $sentCount = Order::where('status', 'completed')->count();
        $processingCount = Order::where('status', 'processing')->count();
        $canceledCount = Order::where('status', 'cancelled')->count();

        return response()->json([
            'all' => $allCount,
            'pending' => $pendingCount,
            'sent' => $sentCount,
            'processing' => $processingCount,
            'canceled' => $canceledCount,
        ]);
    }



    public function updateUser(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'city' => 'required|max:255',
            'address' => 'required|string|max:255',
            'postcode' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
           // 'email' => 'required|string|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the user by ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Update the user information
        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->city = $request->input('city');
        $user->address = $request->input('address');
        $user->postcode = $request->input('postcode');
        $user->phone = $request->input('phone');
       // $user->email = $request->input('email');
        $user->save();

        return response()->json(['message' => 'User information updated successfully', 'user' => $user], 200);
    }


    public function updateOrderStatus(Request $request, $id)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,processing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Find the order by ID
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Update the order status
        $order->status = $request->input('status');
        $order->save();

        return response()->json(['message' => 'Order status updated successfully', 'order' => $order], 200);
    }
}
