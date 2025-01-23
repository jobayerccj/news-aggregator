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
        Schema::table('articles', function (Blueprint $table) {
            $table->index('author_id');
            $table->index('source_id');
            $table->index('category_id');
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('author_id');
            $table->index('source_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['author_id']);
            $table->dropIndex(['source_id']);
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['author_id']);
            $table->dropIndex(['source_id']);
            $table->dropIndex(['category_id']);
        });
    }
};
