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
        $orders = Order::with(['product', 'variant', 'user'])->where('user_id', Auth::id())->latest()->get();
        $products = Product::with('variants')->get(['id', 'name', 'sku', 'price', 'stock']);
        
        return view('frontend.merchant-orders', compact('orders', 'products'));
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        
        $products = Product::with('variants')
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('sku', 'LIKE', "%{$search}%");
            })
            ->take(30)
            ->get(['id', 'name', 'sku', 'price', 'stock']);

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stock' => 'required|integer|min:1',  
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_address' => 'required|string',
            'selling_price' => 'required|numeric|min:0',
        ]);

        $product = Product::with('variants')->findOrFail($request->product_id);
        $qty = (int) $request->stock; 

        $variantId = null;
        if ($product->variants->count() > 0) {
            if (!$request->filled('product_variant_id')) {
                return back()->withErrors(['product_variant_id' => 'هذا المنتج يحتوي على مقاسات وألوان، يجب اختيار المقاس المطلوب بدقة!'])->withInput();
            }

            $variant = $product->variants()->where('id', $request->product_variant_id)->first();
            if (!$variant) {
                return back()->withErrors(['product_variant_id' => 'المقاس المختار غير صالح لهذا المنتج!'])->withInput();
            }

            $variantId = $variant->id;
        }
        
        DB::beginTransaction();
        try {
            if ($request->selling_price < $product->price) {
                return back()->withErrors(['selling_price' => 'سعر البيع للعميل يجب ألا يكون أقل من سعر أساس المنتج!'])->withInput();
            }

            // الخصم وتفعيل الموديل لتحديث الاستوك الأساسي أوتوماتيك
            if ($variantId) {
                $variant = ProductVariant::lockForUpdate()->findOrFail($variantId);
                if ($variant->stock < $qty) {
                    return back()->withErrors(['stock' => 'عذراً، نفد مخزون هذا المقاس بالكمية المطلوبة حالياً!'])->withInput();
                }
                $variant->stock -= $qty;
                $variant->save(); // بيحدث المقاس ويحدث استوك المنتج الأساسي أوتوماتيك
            } else {
                $productToStock = Product::lockForUpdate()->findOrFail($product->id);
                if ($productToStock->stock < $qty) {
                    return back()->withErrors(['stock' => 'عذراً، نفد مخزون هذا المنتج بالكمية المطلوبة حالياً!'])->withInput();
                }
                $productToStock->stock -= $qty;
                $productToStock->save();
            }

            $profit = ($request->selling_price - $product->price) * $qty;

            Order::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
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
            return redirect()->route('merchant.orders.index')->with('success', 'تم تسجيل الأوردر بنجاح!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ ما، يرجى المحاولة لاحقاً: ' . $e->getMessage()])->withInput();
        }
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return back()->withErrors(['error' => 'عذراً، لا تملك صلاحية الوصول لهذا الأوردر!']);
        }

        $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->first();

            if ($lockedOrder->status !== 'pending') {
                DB::rollBack();
                return back()->withErrors(['error' => 'عذراً، لقد تغيرت حالة الأوردر بواسطة الإدارة، ولم يعد متاحاً إلغاؤه!']);
            }

            $quantityToRestore = $lockedOrder->stock;

            // إرجاع المخزون وتحديث الاستوك الأساسي للمنتج أوتوماتيك
            if ($lockedOrder->product_variant_id) {
                $variant = ProductVariant::find($lockedOrder->product_variant_id);
                if ($variant) {
                    $variant->stock += $quantityToRestore;
                    $variant->save(); // هيشغل الـ booted event ويحدث استوك الأب أوتوماتيك
                }
            } else {
                $product = Product::find($lockedOrder->product_id);
                if ($product) {
                    $product->stock += $quantityToRestore;
                    $product->save();
                }
            }

            $lockedOrder->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason ?? 'تم إلغاء الطلب بواسطة التاجر.'
            ]);

            DB::commit();
            return redirect()->route('merchant.orders.index')->with('success', 'تم إلغاء الأوردر بنجاح وإعادة المخزون!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء إلغاء الأوردر: ' . $e->getMessage()]);
        }
    }
}