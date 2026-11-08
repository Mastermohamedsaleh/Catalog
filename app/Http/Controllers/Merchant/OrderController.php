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
        return view('frontend.merchant-orders', compact('orders'));
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

    // حفظ الأوردر الجديد وخصم المخزون بحسب المقاس أو المنتج الأساسي
   public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'product_variant_id' => 'nullable|exists:product_variants,id',
        'quantity' => 'required|integer|min:1',
        'customer_name' => 'required|string|max:255',
        'customer_phone' => 'required|string|max:50',
        'customer_address' => 'required|string',
        'selling_price' => 'required|numeric|min:0',
    ]);

    $product = Product::findOrFail($request->product_id);

    DB::beginTransaction();
    try {
        // 1. التحقق الحقيقي من الاستوك لحظة الطلب (حماية ضد نفاذ الكمية)
        if ($request->filled('product_variant_id')) {
            $variant = ProductVariant::findOrFail($request->product_variant_id);
            if ($variant->stock < $request->quantity) {
                return back()->withErrors(['quantity' => 'عذراً، لقد نفد مخزون هذا المقاس أو الكمية المطلوبة غير متوفرة حالياً!'])->withInput();
            }
            $variant->decrement('stock', $request->quantity);
        } else {
            if ($product->stock < $request->quantity) {
                return back()->withErrors(['quantity' => 'عذراً، لقد نفد مخزون هذا المنتج أو الكمية المطلوبة غير متوفرة حالياً!'])->withInput();
            }
            $product->decrement('stock', $request->quantity);
        }

        if ($request->selling_price < $product->price) {
            return back()->withErrors(['selling_price' => 'سعر البيع للعميل يجب ألا يكون أقل من سعر أساس المنتج!'])->withInput();
        }

        $profit = ($request->selling_price - $product->price) * $request->quantity;

        Order::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'product_price' => $product->price,
            'selling_price' => $request->selling_price,
            'profit' => $profit,
            'status' => 'pending',
            'is_paid_to_wallet' => false,
        ]);

        DB::commit();
        return redirect()->route('merchant.orders.index')->with('success', 'تم تسجيل الأوردر وخصم المخزون بنجاح!');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withErrors(['error' => 'حدث خطأ ما، يرجى المحاولة لاحقاً.']);
    }
}
}