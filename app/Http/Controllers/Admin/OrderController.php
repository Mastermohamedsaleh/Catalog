<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // عرض جميع الأوردرات في الداشبورد مع دعم البحث وفلترة أوردرات تاجر معين (user_id)
    public function index(Request $request)
    {
        $query = Order::with(['user', 'product', 'variant'])->latest();

        // فلترة أوردرات تاجر معين عند الضغط على اسمه من صفحة العملاء
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // تفعيل فلتر البحث الشامل
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    // حفظ أوردر جديد (من قبل الأدمن) مع خصم المخزون ودعم المقاسات
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'stock' => 'required|integer|min:1',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'string', 'regex:/^01[0125][0-9]{8}$/'],
            'customer_address' => 'required|string|min:10|max:500',
            'selling_price' => 'required|numeric|min:0',
        ], [
            'customer_phone.regex' => 'رقم التليفون غير صحيح، يجب أن يكون رقم محمول مصري مكون من 11 رقم (مثل 01012345678).',
            'customer_address.min' => 'يجب كتابة تفاصيل العنوان بشكل أوضح (المدينة، الشارع، رقم الحطة).',
        ]);

        $product = Product::findOrFail($request->product_id);
        $qty = (int) $request->stock;
        $variantId = null;

        DB::beginTransaction();
        try {
            // التحقق وخصم المخزون (سواء منتج عادي أو برودكت فاريانت)
            if ($request->filled('product_variant_id')) {
                $variant = ProductVariant::findOrFail($request->product_variant_id);
                if ($variant->stock < $qty) {
                    return back()->with('error', 'عذراً، الكمية المطلوبة غير متوفرة لهذا المقاس/اللون بالمخزون!');
                }
                $variant->decrement('stock', $qty);
                $variantId = $variant->id;
            } else {
                if ($product->stock < $qty) {
                    return back()->with('error', 'عذراً، هذا المنتج نفد من المخزون أو الكمية غير متفورة!');
                }
                $product->decrement('stock', $qty);
            }

            // حساب الربح بشكل صحيح (سعر البيع ناقص سعر الجملة مضروباً في الكمية)
            $profit = ($request->selling_price - $product->price) * $qty;

            if ($profit < 0) {
                DB::rollBack();
                return back()->with('error', 'سعر البيع لا يمكن أن يكون أقل من سعر الجملة الأساسي!');
            }

            Order::create([
                'user_id' => auth()->id(), // المستخدم الحالي (الأدمن)
                'product_id' => $product->id,
                'product_variant_id' => $variantId,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'product_price' => $product->price,
                'selling_price' => $request->selling_price,
                'profit' => $profit,
                'stock' => $qty,
                'status' => 'pending', // حالة الأوردر الابتدائية معلق
                'is_paid_to_wallet' => false,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'تم تسجيل الطلب بنجاح، جاري المراجعة!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ ما: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $order->status;
            $order->status = $request->status;
            $order->save();

            // إضافة الأرباح لمحفظة المستخدم عند تسليم الأوردر لأول مرة
            if ($order->status == 'delivered' && $oldStatus != 'delivered' && !$order->is_paid_to_wallet) {
                $user = $order->user;
                if ($user) {
                    // التحقق مما إذا كان حقل المحفظة موجود أو استخدام العمود المتاح لديك
                    if (Schema::hasColumn('users', 'wallet_balance')) {
                        $user->wallet_balance = ($user->wallet_balance ?? 0) + $order->profit;
                        $user->save();
                    } elseif (Schema::hasColumn('users', 'wallet')) {
                        $user->wallet = ($user->wallet ?? 0) + $order->profit;
                        $user->save();
                    }
                }

                $order->is_paid_to_wallet = true;
                $order->save();
            }

            DB::commit();
            return redirect()->back()->with('success', 'تم تحديث حالة الطلب بنجاح!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء التحديث: ' . $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        // إعادة المخزون لو الأوردر اتحذف وهو لسه مش محذوف نهائي (اختياري حسب رغبتك)
        $order->delete();
        return redirect()->back()->with('success', 'تم حذف الطلب بنجاح.');
    }
}