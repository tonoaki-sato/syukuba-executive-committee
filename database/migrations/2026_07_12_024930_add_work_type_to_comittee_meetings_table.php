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
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE comittee_meetings MODIFY COLUMN type ENUM('board', 'general', 'subcommittee', 'work') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        \Illuminate\Support\Facades\DB::statement("ALTER TABLE comittee_meetings MODIFY COLUMN type ENUM('board', 'general', 'subcommittee') NOT NULL");
    }
};
