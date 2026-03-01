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
        Schema::create('token_blacklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('token')->index();
            $table->uuid('user_id')->nullable()->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['token', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_blacklists');
    }
};
