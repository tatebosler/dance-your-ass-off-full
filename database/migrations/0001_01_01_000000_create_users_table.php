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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();
            $table->enum('rsvp', ['yes', 'no', 'maybe'])->nullable();
            $table->enum('state_fair_rsvp', ['yes', 'no', 'maybe'])->nullable();
            $table->enum('pool_rsvp', ['yes', 'no', 'maybe'])->nullable();
            $table->boolean('extra_guest_allowed')->default(false);
            $table->string('extra_guest_name')->nullable();
            $table->enum('extra_guest_rsvp', ['yes', 'no', 'maybe'])->nullable();
            $table->string('magicToken')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->string('token', 6);
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('tokens');
        Schema::dropIfExists('sessions');
    }
};
