<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membuat tabel users
        Schema::create('users', function (Blueprint $table) {
            $table->id();                                    // Primary key
            $table->string('name');                          // Nama user
            $table->string('email')->unique();               // Email unik
            $table->timestamp('email_verified_at')->nullable(); // Waktu verifikasi email
            $table->string('password');                      // Password
            $table->enum('role',['user','admin'])->default('user'); // Role user
            $table->rememberToken();                         // Token untuk "remember me"
            $table->timestamps();                            // created_at dan updated_at
            $table->softDeletes();                           // Untuk soft delete
        });

        // Membuat tabel password_reset_tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();              // Email sebagai primary key
            $table->string('token');                         // Token reset password
            $table->timestamp('created_at')->nullable();     // Waktu pembuatan token
        });

        // Membuat tabel sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();                 // ID session
            $table->foreignId('user_id')->nullable()->index(); // ID user
            $table->string('ip_address', 45)->nullable();    // IP address
            $table->text('user_agent')->nullable();          // User agent
            $table->longText('payload');                     // Data session
            $table->integer('last_activity')->index();       // Waktu aktivitas terakhir
        });
    }

    public function down(): void
    {
        // Menghapus tabel jika rollback
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};