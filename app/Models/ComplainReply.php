<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplainReply extends Model
{
    // ===== KONFIGURASI MODEL =====
    // Field yang bisa diisi secara massal
    protected $fillable = [
        'complain_id',  // ID complain yang di-reply
        'user_id',      // ID user yang membuat reply
        'content'       // Isi reply
    ];

    // ===== RELASI DENGAN MODEL LAIN =====
    // Satu reply dimiliki oleh satu complain
    public function complain()
    {
        return $this->belongsTo(Complain::class);
    }

    // Satu reply dimiliki oleh satu user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}