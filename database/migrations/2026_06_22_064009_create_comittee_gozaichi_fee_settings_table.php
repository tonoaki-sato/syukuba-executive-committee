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
        Schema::create('comittee_gozaichi_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('comittee_gozaichi_events')->onDelete('cascade');
            $table->string('fee_key', 50);
            $table->integer('fee_value');
            $table->timestamps();
            $table->unique(['event_id', 'fee_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_gozaichi_fee_settings');
    }
};
