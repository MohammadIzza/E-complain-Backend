<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membuat tabel complain_replies
        Schema::create('complain_replies', function (Blueprint $table) {
            $table->id();                                    // Primary key
            $table->foreignId('complain_id')->constrained(); // Foreign key ke complains
            $table->foreignId('user_id')->constrained();     // Foreign key ke users
            $table->longText('content');                     // Isi reply
            $table->timestamps();                            // created_at dan updated_at
        });
    }

    public function down(): void
    {
        // Menghapus tabel jika rollback
        Schema::dropIfExists('complain_replies');
    }
};