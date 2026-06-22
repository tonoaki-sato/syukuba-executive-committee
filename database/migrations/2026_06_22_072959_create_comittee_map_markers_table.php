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
        Schema::create('comittee_map_markers', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('fiscal_year');
            $table->enum('marker_type', ['gozaichi', 'facility', 'water', 'event', 'claim']);
            $table->string('sub_type', 50)->nullable();
            $table->double('x_position');
            $table->double('y_position');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->foreignId('application_id')->nullable()->constrained('comittee_gozaichi_applications')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_map_markers');
    }
};
