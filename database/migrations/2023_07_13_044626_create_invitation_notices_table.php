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
        Schema::create('invitation_notices', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('标题');
            $table->unsignedBigInteger('member_id')->index('member_id');
            $table->morphs('business', 'business');
            $table->timestamp('expired_at')->nullable()->comment('过期时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitation_notices');
    }
};
