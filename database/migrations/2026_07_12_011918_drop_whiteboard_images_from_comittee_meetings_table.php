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
        Schema::table('comittee_meetings', function (Blueprint $table) {
            $table->dropColumn('whiteboard_images');
        });

        // 過去のホワイトボード写真の物理データを削除
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('whiteboards')) {
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('whiteboards');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comittee_meetings', function (Blueprint $table) {
            $table->json('whiteboard_images')->nullable();
        });
    }
};
