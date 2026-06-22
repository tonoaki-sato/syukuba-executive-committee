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
        Schema::create('comittee_gozaichi_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('comittee_gozaichi_events')->onDelete('cascade');
            $table->string('shop_name', 100);
            $table->string('exhibitor_name', 50);
            $table->boolean('is_member')->default(false);
            $table->string('introducer_name', 100)->nullable();
            $table->string('introducer_contact', 100)->nullable();
            $table->enum('status', ['draft', 'submitted', 'accepted', 'rejected'])->default('draft');
            $table->string('spot_code', 20)->nullable();
            $table->unsignedTinyInteger('section_count')->default(1);
            $table->enum('first_section_type', ['general', 'A', 'B']);
            $table->enum('subsequent_section_type', ['general', 'A', 'B'])->nullable();
            $table->boolean('has_fire')->default(false);
            $table->string('fire_equipment', 100)->nullable();
            $table->unsignedTinyInteger('fire_equipment_count')->default(0);
            $table->string('fire_fuel', 100)->nullable();
            $table->boolean('has_food')->default(false);
            $table->boolean('has_food_pledge')->default(false);
            $table->json('rentals')->nullable();
            $table->integer('exhibition_fee')->nullable();
            $table->integer('equipment_fee')->nullable();
            $table->integer('equipment_fee_override')->nullable();
            $table->integer('trash_bag_fee')->nullable();
            $table->integer('total_fee')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->timestamp('payment_received_at')->nullable();
            $table->boolean('permit_issued')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_gozaichi_applications');
    }
};
