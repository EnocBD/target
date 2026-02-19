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
            $table->bigIncrements('id');
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('category_id')->nullable()->index('products_category_id_foreign');
            $table->unsignedBigInteger('brand_id')->nullable()->index('products_brand_id_foreign');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('name');
            $table->text('text')->nullable();
            $table->decimal('price', 10)->nullable();
            $table->decimal('list_price', 10)->nullable();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->string('specifications')->nullable();
            $table->boolean('active')->default(true);
            $table->integer('order')->default(0);
            $table->text('meta_title')->nullable();
            $table->text('meta_description')->nullable();
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
