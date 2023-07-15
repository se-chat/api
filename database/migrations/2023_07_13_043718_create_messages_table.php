<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->index('sender_id');
            $table->morphs('receiver', 'receiver');
            $table->string('type')->default('text')->comment('消息类型 text:文本 image:图片 file:文件');
            $table->longText('content')->comment('消息内容');
            $table->timestamp('expired_at')->nullable()->comment('到期时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
