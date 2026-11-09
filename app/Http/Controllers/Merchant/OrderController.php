<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())->latest()->get();

        $products = Product::with('variants')->get(['id', 'name', 'price', 'stock']);

    return view('frontend.merchant-orders', compact('orders', 'products'));

    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        
        $products = Product::with('variants')
            ->where('name', 'LIKE', "%{$search}%")
            ->take(15)
            ->get(['id', 'name', 'price', 'stock']);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'stock' => 'required|integer|min:1',  
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_address' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty = (int) $request->stock; 
        
        DB::beginTransaction();
        try {
            if ($request->filled('product_variant_id')) {
                $variant = ProductVariant::findOrFail($request->product_variant_id);
                if ($variant->stock < $qty) {
                    return back()->withErrors(['stock' => 'عذراً، لقد نفد مخزون هذا المقاس أو الكمية المطلوبة غير متوفرة حالياً!'])->withInput();
                }
                $variant->decrement('stock', $qty);
            } else {
                if ($product->stock < $qty) {
                    return back()->withErrors(['stock' => 'عذراً، لقد نفد مخزون هذا المنتج أو الكمية المطلوبة غير متوفرة حالياً!'])->withInput();
                }
                $product->decrement('stock', $qty);
            }

            if ($request->selling_price < $product->price) {
                return back()->withErrors(['selling_price' => 'سعر البيع للعميل يجب ألا يكون أقل من سعر أساس المنتج!'])->withInput();
            }

            // حساب الربح بدقة تامة (سعر البيع - سعر التكلفة) * الكمية
            $profit = ($request->selling_price - $product->price) * $qty;

            Order::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'product_price' => $product->price,
                'selling_price' => $request->selling_price,
                'profit' => $profit,
                'stock' => $qty, 
                'status' => 'pending',
                'is_paid_to_wallet' => false,
            ]);

            DB::commit();
            return redirect()->route('merchant.orders.index')->with('success', 'تم تسجيل الأوردر !');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ ما، يرجى المحاولة لاحقاً: ' . $e->getMessage()]);
        }
    }


  public function cancel(Order $order)
{
    // التأكد من الملكية أولاً
    if ($order->user_id !== Auth::id()) {
        return back()->withErrors(['error' => 'عذراً، لا تملك صلاحية الوصول لهذا الأوردر!']);
    }

    DB::beginTransaction();
    try {
        $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

        if ($lockedOrder->status !== 'pending') {
            DB::rollBack();
            return back()->withErrors(['error' => 'عذراً، لقد تغيرت حالة الأوردر بواسطة الإدارة، ولم يعد متاحاً إلغاؤه!']);
        }

        $quantityToRestore = $lockedOrder->stock;

        if ($lockedOrder->product_variant_id) {
            $variant = ProductVariant::find($lockedOrder->product_variant_id);
            if ($variant) {
                $variant->increment('stock', $quantityToRestore);
            }
        } else {
            $product = Product::find($lockedOrder->product_id);
            if ($product) {
                $product->increment('stock', $quantityToRestore);
            }
        }

        $lockedOrder->update([
            'status' => 'cancelled'
        ]);

        DB::commit();
        return redirect()->route('merchant.orders.index')->with('success', 'تم إلغاء الأوردر !');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'حدث خطأ أثناء إلغاء الأوردر: ' . $e->getMessage()]);
    }
}


}