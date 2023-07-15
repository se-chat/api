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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('no')->nullable()->index('no')->comment('编号');
            $table->string('nickname')->comment('昵称');
            $table->string('avatar')->comment('头像');
            $table->string('address')->comment('钱包地址')->index('address');
            $table->string('pub_key')->comment('钱包公钥');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
