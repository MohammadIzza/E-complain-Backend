<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    // ===== TRAIT YANG DIGUNAKAN =====
    // Trait adalah fitur PHP untuk menggunakan kembali kode
    use HasFactory, Notifiable, HasApiTokens;
    // - HasFactory: untuk membuat data dummy saat testing
    // - Notifiable: untuk fitur notifikasi
    // - HasApiTokens: untuk fitur token API (Sanctum)

    // ===== KONFIGURASI MODEL =====
    // Field yang bisa diisi secara massal
    protected $fillable = [
        'name',     // Nama user
        'email',    // Email user
        'password', // Password user
        'role'      // Role user
    ];

    // Field yang tidak akan ditampilkan dalam response
    protected $hidden = [
        'password',        // Sembunyikan password
        'remember_token',  // Sembunyikan remember token
    ];

    // Konfigurasi tipe data field
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Konversi ke tipe datetime
            'password' => 'hashed',            // Hash password
        ];
    }

    // ===== RELASI DENGAN MODEL LAIN =====
    // Satu user bisa memiliki banyak complain
    public function complain(){
        return $this->hasMany(Complain::class);
    }

    // Satu user bisa memiliki banyak reply complain
    public function complaiReplies(){
        return $this->hasMany(ComplainReply::class);
    }
}