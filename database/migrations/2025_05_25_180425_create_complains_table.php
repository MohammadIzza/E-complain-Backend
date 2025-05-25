<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Membuat tabel complains
        Schema::create('complains', function (Blueprint $table) {
            $table->id();                                    // Primary key
            $table->foreignId('user_id')->constrained();     // Foreign key ke users
            $table->string('code')->unique();                // Kode unik complain
            $table->text('title');                           // Judul complain
            $table->longText('description');                 // Deskripsi complain
            $table->enum('status',[                          // Status complain
                'open',
                'onprogres',
                'resolved',
                'rejected'
            ])->default('open');
            $table->enum('priority',[                        // Prioritas complain
                'low',
                'medium',
                'high'
            ]);
            $table->timestamps();                            // created_at dan updated_at
            $table->timestamp('completed_at')->nullable();   // Waktu selesai
        });
    }

    public function down(): void
    {
        // Menghapus tabel jika rollback
        Schema::dropIfExists('complains');
    }
};