<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
$table->id();
        
        // ربط المنتج بالقسم (Foreign Key)
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->integer('stock')->default(0);
        // الميديا والملفات الجاهزة للتحميل
        $table->string('main_image')->nullable();
        $table->json('gallery_images')->nullable(); // لتخزين أكتر من صورة
        $table->string('downloadable_file')->nullable(); // ملف التفاصيل الـ PDF أو ZIP
        $table->string('video_url')->nullable();
        
        $table->boolean('is_active')->default(true);
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
