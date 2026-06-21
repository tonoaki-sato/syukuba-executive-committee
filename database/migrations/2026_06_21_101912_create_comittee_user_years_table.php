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
        Schema::create('comittee_user_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('comittee_users')->cascadeOnDelete();
            $table->unsignedInteger('fiscal_year');
            $table->json('roles'); // その年度における会員属性・ロール（一般会員, 幹事, システム管理等）
            $table->enum('status', ['temporary', 'active', 'suspended', 'expelled', 'rejected'])->default('active');
            $table->timestamps();

            // 同じユーザーが同一年度に重複登録されるのを防ぐ
            $table->unique(['user_id', 'fiscal_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_user_years');
    }
};
