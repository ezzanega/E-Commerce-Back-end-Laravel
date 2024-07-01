<?php

namespace App\Http\Controllers;

use App\Events\CartUpdated;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LDAP\Result;

class CartController extends Controller
{

    public function addToCart(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Find the cart item
        $cart = Cart::where('user_id', $request->user_id)
                    ->where('product_id', $request->product_id)
                    ->first();

        if ($cart) {
            // If the item exists in the cart, update the quantity
            $cart->quantity += $request->quantity;
            $cart->save();
        } else {
            // If the item does not exist in the cart, create a new cart item
            $cart = Cart::create([
                'user_id' => $request->user_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
            ]);
        }
        event(new CartUpdated(Cart::where('user_id', $request->user_id)->with('product')->get(), $request->user_id));

        return response()->json(['message' => 'Product added to cart', 'cart' => $cart], 200);
    }

    public function getCart(Request $request)
    {
        validator($request->all(), [
            'user_id' => 'required|exists:users,id'
        ])->validate();

        $cart = Cart::where('user_id', $request->user_id)->with('product')->get();
        return response()->json($cart);
    }

    public function getCartItemCount(Request $request)
    {
        $count = Cart::where('user_id',$request->user_id)->sum('quantity');
        return response()->json(['count' => $count]);
    }

    public function removeFromCart(Request $request)
    {
        $cart = Cart::where('user_id', $request->user_id)
                    ->where('product_id', $request->product_id)
                    ->first();

        if ($cart) {
            $cart->delete();
            return response()->json(['message' => 'Product removed from cart'], 200);
        }

        return response()->json(['message' => 'Product not found in cart'], 404);
    }


    public function updateCartItemQuantity(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::where('user_id', $request->user_id)
                    ->where('product_id', $request->product_id)
                    ->first();

        if ($cart) {
            $cart->quantity = $request->quantity;
            $cart->save();
            return response()->json(['message' => 'Cart item updated successfully'], 200);
        }

        return response()->json(['message' => 'Cart item not found'], 404);
    }

    public function clearCart(Request $request)
    {
        $user_id = $request->user_id;

        Cart::where('user_id', $user_id)->delete();

        return response()->json(['message' => 'Cart cleared successfully'], 200);
    }



}
