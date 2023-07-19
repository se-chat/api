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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('文件名');
            $table->string('path')->comment('文件路径');
            $table->string('mime_type')->comment('文件类型');
            $table->string('size')->comment('文件大小');
            $table->string('hash')->comment('文件哈希');
            $table->string('ext')->comment('文件后缀');
            $table->timestamp('expired_at')->nullable()->comment('过期时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
