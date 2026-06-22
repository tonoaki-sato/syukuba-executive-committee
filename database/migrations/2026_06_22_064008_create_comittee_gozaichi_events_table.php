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
        Schema::create('comittee_gozaichi_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fiscal_year')->unique();
            $table->timestamp('recruitment_start_at')->nullable();
            $table->timestamp('recruitment_end_at')->nullable();
            $table->enum('recruitment_status', ['closed', 'open'])->default('closed');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_gozaichi_events');
    }
};
