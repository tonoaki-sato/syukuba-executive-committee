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
        // 1. 備品マスタ
        Schema::create('comittee_equipments', function (Blueprint $table) {
            $table->id();
            $table->string('ownership_type'); // 'owned' or 'rental'
            $table->string('name');
            $table->string('specifications')->nullable();
            $table->integer('quantity');
            $table->string('unit');
            $table->integer('unit_price')->nullable();
            $table->string('category');
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. 保管場所
        Schema::create('comittee_storage_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. 拠点別在庫
        Schema::create('comittee_equipment_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('comittee_equipments')->onDelete('cascade');
            $table->foreignId('storage_location_id')->constrained('comittee_storage_locations')->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->unique(['equipment_id', 'storage_location_id'], 'equip_stock_unique');
        });

        // 4. 貸出・割当履歴
        Schema::create('comittee_equipment_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year');
            $table->foreignId('equipment_id')->constrained('comittee_equipments')->onDelete('cascade');
            $table->string('borrower_type'); // 'gozaichi' or 'staff'
            $table->integer('borrower_id');
            $table->integer('quantity_requested')->default(0);
            $table->integer('quantity_loaned')->default(0);
            $table->integer('quantity_returned')->default(0);
            $table->dateTime('loaned_at')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->string('status')->default('pending'); // 'pending'/'loaned'/'returned'/'partial'/'lost'
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5. 破損・補充
        Schema::create('comittee_equipment_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('fiscal_year');
            $table->foreignId('equipment_id')->constrained('comittee_equipments')->onDelete('cascade');
            $table->foreignId('storage_location_id')->nullable()->constrained('comittee_storage_locations')->onDelete('set null');
            $table->string('log_type'); // 'repair'/'discard'/'lost'/'replenish'
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->dateTime('recorded_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_equipment_maintenance_logs');
        Schema::dropIfExists('comittee_equipment_loans');
        Schema::dropIfExists('comittee_equipment_stocks');
        Schema::dropIfExists('comittee_storage_locations');
        Schema::dropIfExists('comittee_equipments');
    }
};
