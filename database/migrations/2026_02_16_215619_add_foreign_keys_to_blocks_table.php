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
        Schema::table('blocks', function (Blueprint $table) {
            $table->foreign(['page_id'])->references(['id'])->on('pages')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parent_id'])->references(['id'])->on('blocks')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blocks', function (Blueprint $table) {
            $table->dropForeign('blocks_page_id_foreign');
            $table->dropForeign('blocks_parent_id_foreign');
        });
    }
};
