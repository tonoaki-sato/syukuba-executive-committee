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
        Schema::create('comittee_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_kana');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('profession');
            $table->string('affiliation')->nullable();
            $table->text('skills')->nullable(); // 得意分野
            $table->json('roles')->nullable();  // 会員属性・ロール（一般会員, 幹事, システム管理等）
            $table->foreignId('referrer_id')->nullable()->constrained('comittee_users')->nullOnDelete();
            $table->string('referrer_text')->nullable();
            $table->string('line_display_name');
            $table->enum('status', ['temporary', 'active', 'suspended', 'expelled', 'rejected'])->default('temporary');
            $table->foreignId('approved_by')->nullable()->constrained('comittee_users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('comittee_password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_users');
        Schema::dropIfExists('comittee_password_reset_tokens');
    }
};
