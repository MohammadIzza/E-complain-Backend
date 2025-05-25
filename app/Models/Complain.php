<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complain extends Model
{   
    // ===== KONFIGURASI MODEL =====
    // Field yang bisa diisi secara massal
    protected $fillable = [
        'user_id',      // ID user yang membuat complain
        'code',         // Kode unik complain
        'title',        // Judul complain
        'description',  // Deskripsi complain
        'status',       // Status complain (open/onprogres/resolved/rejected)
        'priority',     // Prioritas complain (low/medium/high)
        'completed_at'  // Waktu selesai complain
    ];

    // ===== RELASI DENGAN MODEL LAIN =====
    // Satu complain dimiliki oleh satu user
    public function user(){
        return $this->belongsTo(User::class);
    }
    
    // Satu complain bisa memiliki banyak reply
    public function complainReplies(){
        return $this->hasMany(ComplainReply::class);
    }
}