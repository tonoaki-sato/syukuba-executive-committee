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
        Schema::create('comittee_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fiscal_year')->index(); // 開催年（年度管理と連動）
            $table->enum('type', ['board', 'general', 'subcommittee']); // 幹事会, 総会, 部会
            $table->string('name'); // 会議名（例：「6月定例総会」）
            $table->dateTime('held_at'); // 開催日時
            $table->string('location')->default('実行委員会事務所'); // 開催場所
            $table->text('agenda')->nullable(); // アジェンダ
            $table->text('minutes')->nullable(); // 議事録（文章化した内容）
            $table->json('whiteboard_images')->nullable(); // ホワイトボード画像パス（複数保存対応）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comittee_meetings');
    }
};
