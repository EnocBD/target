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
        Schema::create('blocks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('page_id')->nullable()->index('blocks_page_id_foreign');
            $table->unsignedBigInteger('parent_id')->nullable()->index('blocks_parent_id_foreign');
            $table->string('block_type');
            $table->json('data')->nullable();
            $table->json('data_preview')->nullable();
            $table->json('styles')->nullable();
            $table->string('image_path')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
