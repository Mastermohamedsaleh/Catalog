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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('size')->nullable();   // مثل: S, M, L, XL
            $table->string('color')->nullable();  // مثل: Red, Blue أو كود اللون
            $table->integer('stock')->default(0); // المخزون الخاص بالمقاس/اللون ده
            $table->decimal('price_modifier', 8, 2)->default(0); // فرق السعر لو فيه زيادة للمقاس ده
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
