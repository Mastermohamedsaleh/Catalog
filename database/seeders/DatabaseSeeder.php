<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إنشاء الأدمن الأساسي والمشرف
        User::updateOrCreate(['email' => 'admin@catalogpro.com'], [
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_super' => true,
        ]);

        User::updateOrCreate(['email' => 'manager@catalogpro.com'], [
            'name' => 'Store Manager',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_super' => false,
        ]);

        // 2. إنشاء 200 مستخدم (تجار / مسوقين)
        $merchants = [];
        for ($i = 1; $i <= 200; $i++) {
            $merchants[] = User::create([
                'name' => "Merchant {$i}",
                'email' => "merchant{$i}@catalogpro.com",
                'password' => Hash::make('password'),
                'role' => 'app_client_usr',
                'is_super' => false,
            ]);
        }

        // 3. إنشاء 9 أقسام رئيسية وتحت كل قسم أقسام فرعية
        $allCategories = [];
        for ($i = 1; $i <= 9; $i++) {
            $parent = Category::create([
                'name' => "Main Category {$i}",
                'slug' => "main-category-{$i}",
                'description' => "Description for main category {$i}",
                'is_active' => true,
            ]);
            $allCategories[] = $parent;

            for ($j = 1; $j <= 2; $j++) {
                $sub = Category::create([
                    'name' => "Sub Category {$i}-{$j}",
                    'slug' => "sub-category-{$i}-{$j}",
                    'parent_id' => $parent->id,
                    'description' => "Sub category description",
                    'is_active' => true,
                ]);
                $allCategories[] = $sub;
            }
        }

        $subCategories = Category::whereNotNull('parent_id')->get();
        $targetCategories = $subCategories->count() > 0 ? $subCategories : Category::all();

        // 4. إنشاء 500 منتج مع مقاسات متطابقة تماماً في المخزون
        $allProducts = [];
        
        for ($p = 1; $p <= 500; $p++) {
            $category = $targetCategories->random();
            $price = rand(100, 2000);
            
            // توليد SKU فريد واحترافي لكل منتج
            $sku = 'PRD-' . strtoupper(Str::random(6));
            while (Product::where('sku', $sku)->exists()) {
                $sku = 'PRD-' . strtoupper(Str::random(6));
            }
            
            // إنشاء المنتج مبدئياً بمخزون 0
            $product = Product::create([
                'category_id' => $category->id,
                'name' => "Product Item {$p}",
                'sku' => $sku,
                'slug' => "product-item-{$p}-" . Str::random(5),
                'description' => "Detailed specifications and high quality description for product {$p}.",
                'price' => $price,
                'stock' => 0, // سيتم تحديثه بناءً على مجموع المقاسات
                'main_image' => 'https://picsum.photos/seed/prod' . $p . '/600/600',
                'gallery_images' => json_encode([
                    'https://picsum.photos/seed/prod' . $p . '_1/600/600',
                    'https://picsum.photos/seed/prod' . $p . '_2/600/600',
                ]),
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'is_active' => true,
            ]);

            // إضافة مقاسات وألوان (Variants) وحساب مجموع المخزون الحقيقي بدقة
            $sizes = ['S', 'M', 'L', 'XL', 'XXL'];
            $colors = ['Red', 'Blue', 'Black', 'White'];
            
            $totalVariantStock = 0;
            $selectedSizes = array_rand(array_flip($sizes), rand(2, 4));
            if (!is_array($selectedSizes)) {
                $selectedSizes = [$selectedSizes];
            }

            foreach ($selectedSizes as $size) {
                $variantStock = rand(5, 30);
                $totalVariantStock += $variantStock;

                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'color' => $colors[array_rand($colors)],
                    'stock' => $variantStock,
                    'price_modifier' => rand(0, 50),
                ]);
            }

            // تحديث مخزون المنتج الأساسي ليساوي مجموع مخزون المقاسات تماماً
            $product->update(['stock' => $totalVariantStock]);

            $allProducts[] = $product;
        }

        // 5. إنشاء 90 أوردر بحالات مختلفة وتوزيعها على التجار
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];
        $cancellationReasons = [
            'العميل لم يرد على الاتصالات وتم إلغاء الطلب.',
            'تم إلغاء الطلب بناءً على رغبة العميل.',
            'المنتج غير متوفر بالمقاس المطلوب وتم إرجاعه.',
            'العنوان غير واضح ولم يستطع مندوب الشحن الوصول.',
            'رفض العميل استلام الطلب عند الإرسال.'
        ];

        for ($o = 1; $o <= 90; $o++) {
            $merchant = $merchants[array_rand($merchants)];
            $product = $allProducts[array_rand($allProducts)];
            
            // جلب عشوائي لأحد مقاسات المنتج لو لديه مقاسات
            $variant = $product->variants()->inRandomOrder()->first();
            
            $status = $statuses[array_rand($statuses)];
            $qty = rand(1, 3);
            $sellingPrice = $product->price + rand(50, 300);
            
            // حساب الربح بالشكل السليم والصحيح
            $profit = ($sellingPrice - $product->price) * $qty;

            $cancellationReason = null;
            if (in_array($status, ['cancelled', 'returned'])) {
                $cancellationReason = $cancellationReasons[array_rand($cancellationReasons)];
            }

            Order::create([
                'user_id' => $merchant->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant ? $variant->id : null, // ربط المقاس المختار بالأوردر
                'customer_name' => "Customer Test {$o}",
                'customer_phone' => '01' . rand(100000000, 999999999),
                'customer_address' => 'Cairo, Egypt - Street ' . $o,
                'product_price' => $product->price,
                'selling_price' => $sellingPrice,
                'profit' => $profit,
                'stock' => $qty,
                'status' => $status,
                'cancellation_reason' => $cancellationReason,
                'is_paid_to_wallet' => ($status === 'delivered'),
            ]);
        }
    }
}