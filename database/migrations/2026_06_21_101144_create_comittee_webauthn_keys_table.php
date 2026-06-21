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
        Schema::create('comittee_webauthn_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('comittee_users')->cascadeOnDelete();
            // Credential ID はバイナリデータ等を含むため、BASE64エンコードなどでテキスト化したものを格納。一意である必要がある。
            $table->string('credential_id', 255)->unique();
            $table->text('public_key');
            $table->string('device_name')->default('パスキー デバイス');
            $table->string('aaguid', 64)->nullable();
            $table->unsignedInteger('counter')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_webauthn_keys');
    }
};
