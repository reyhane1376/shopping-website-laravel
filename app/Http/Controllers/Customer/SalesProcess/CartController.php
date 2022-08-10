<?php

namespace App\Http\Controllers\Customer\SalesProcess;

use App\Http\Controllers\Controller;
use App\Models\Market\CartItem;
use App\Models\Market\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function cart()
    {
        Auth::loginUsingId(1);
        if(Auth::check()){
            $cartItems = CartItem::where('user_id',Auth::user()->id)->get();
            $relatedProducts = Product::all();
            return view('customer.sales-process.cart',compact('cartItems','relatedProducts'));
        }else{
            return redirect()->route('auth.customer.login-register-form');
        }
    }

    public function updateCart(Request $request)
    {
        $inputs = $request->all();
        $cartItems = CartItem::where('user_id', Auth::user()->id)->get();
        foreach($cartItems as $cartItem){
            if(isset($inputs['number'][$cartItem->id]))
            {
                $cartItem->update(['number' => $inputs['number'][$cartItem->id]]);
            }
        }
        return redirect()->route('customer.sales-process.address-and-delivery');
    }
    public function addToCart(Product $product, Request $request)
    {
        if(Auth::check()){
            $request->validate([
                'color' => 'nullable|exists:product_colors,id',
                'guarantee' => 'nullable|exists:guarantees,id',
                'number' => 'numeric|min:1|max:5'
            ]);
            $cartItems = CartItem::where('product_id',$product->id)->where('user_id',Auth::user()->id)->get();
            if(!isset($request->color)){
                $request->color =null;
            }
            if(!isset($request->guaratee)){
                $request->guaratee =null;
            }
            foreach($cartItems as $cartItem){
                if($cartItem->color == $request->color && $cartItem->guaratee == $request->guaratee){
                    if($cartItem->number != $request->number){
                        $cartItem->update(['number' => $request->number]);
                    }
                    return back()->with('swal-success','محصول شما با موفقیت ویرایش شد');
                }
            }
            $inputs =[];
            $inputs['color_id'] = $request->color;
            $inputs['guaratee'] = $request->guaratee;
            $inputs['user_id'] = Auth::user()->id;
            $inputs['product_id'] = $product->id;

            CartItem::create($inputs);
            return back()->with('swal-success','محصول شما با موفقیت به سبد خرید اضافه شد');
        }
        else{
            return redirect()->route('auth.customer.login-register-form');
        }
    }
    public function removeFromCart(CartItem $cartItem)
    {
        if($cartItem->user_id === Auth::user()->id){
            $cartItem->delete();
        }
        return back()->with('swal-success','محصول شما با موفقیت از سبد خرید حذف شد');
    }
}
