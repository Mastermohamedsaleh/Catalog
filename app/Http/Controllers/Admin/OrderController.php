<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // عرض جميع الأوردرات في الداشبورد
    public function index()
    {
        $orders = Order::with(['user', 'product'])->latest()->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    // حفظ أوردر جديد (من قبل المسوق أو الأدمن)
    public function store(Request $request)
    {
$request->validate([
        'product_id' => 'required|exists:products,id',
        'customer_name' => 'required|string|max:255',
        
        'customer_phone' => ['required', 'string', 'regex:/^01[0125][0-9]{8}$/'],
        
        'customer_address' => 'required|string|min:10|max:500',
        
        'selling_price' => 'required|numeric|min:0',
    ], [
        'customer_phone.regex' => 'رقم التليفون غير صحيح، يجب أن يكون رقم محمول مصري مكون من 11 رقم (مثل 01012345678).',
        'customer_address.min' => 'يجب كتابة تفاصيل العنوان بشكل أوضح (المدينة، الشارع، رقم الحطة).',
    ]);
        $product = Product::findOrFail($request->product_id);
        
        if ($product->stock <= 0) {
        return back()->with('error', 'عذراً، هذا المنتج نفد من المخزون ولا يمكن طلبة حالياً!');
    }

        $profit = $request->selling_price - $product->price;

        if ($profit < 0) {
            return back()->with('error', 'سعر البيع لا يمكن أن يكون أقل من سعر الجملة الأساسي!');
        }

        Order::create([
            'user_id' => auth()->id(), // المستخدم الحالي (مسوق أو أدمن)
            'product_id' => $product->id,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'product_price' => $product->price,
            'selling_price' => $request->selling_price,
            'profit' => $profit,
            'status' => 'pending', // حالة الأوردر الابتدائية معلق
        ]);

        return redirect()->back()->with('success', 'تم تسجيل الطلب بنجاح، جاري المراجعة!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,returned',
        ]);

        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        if ($order->status == 'delivered' && $oldStatus != 'delivered' && !$order->is_paid_to_wallet) {
            $user = $order->user;
            $user->wallet_balance = ($user->wallet_balance ?? 0) + $order->profit;
            $user->save();

            $order->is_paid_to_wallet = true;
            $order->save();
        }

        return redirect()->back()->with('success', 'تم تحديث حالة الطلب بنجاح!');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->back()->with('success', 'تم حذف الطلب بنجاح.');
    }
}