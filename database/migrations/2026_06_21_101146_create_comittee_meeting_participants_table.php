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
        Schema::create('comittee_meeting_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('comittee_meetings')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('comittee_users')->cascadeOnDelete();
            $table->enum('status', ['present', 'absent', 'pending'])->default('pending'); // 出席, 欠席, 未定
            $table->text('note')->nullable(); // 欠席理由・連絡事項
            $table->timestamps();

            // 会議とユーザーのペアは一意
            $table->unique(['meeting_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_meeting_participants');
    }
};
