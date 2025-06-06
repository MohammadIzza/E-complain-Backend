<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ===== PENJELASAN ROUTE =====
// Route adalah cara kita menentukan URL apa yang bisa diakses di aplikasi kita
// Format: Route::method('/url', [Controller::class, 'function']);

// Route untuk login
// POST /api/login - Digunakan untuk proses login user
// Kenapa POST? Karena kita mengirim data sensitif (password)
Route::post('/login',[AuthController::class,'login']); 

// Registrasi
Route::post('/register',[AuthController::class,'register']);

// ===== PENJELASAN MIDDLEWARE =====
// Middleware adalah pengecekan yang dilakukan sebelum request sampai ke controller
// auth:sanctum artinya route ini hanya bisa diakses oleh user yang sudah login
// Semua route di dalam group ini membutuhkan token yang valid
Route::middleware('auth:sanctum')->group(function(){
     
    // Route untuk mendapatkan data user yang sedang login
    // GET /api/me - Digunakan untuk mengambil data user yang sedang login
    Route::get('/me',[AuthController::class,'me']);

    // logout
    Route::post('/logout',[AuthController::class,'logout']);

    // Menginput data complain
    Route::post('/complain',[ComplainController::class,'store']);

    // Mengambil semua data complain berdasarkan id, jika user
    // jika admin akan diamnbil semua
    Route::get('/complain',[ComplainController::class,'index']);

    // Mengambil data complain berdasarkan kode
    Route::get('/complain/{code}', [ComplainController::class, 'show']);

    // Menambahkan repy complain oleh admin
    Route::post('/complain-reply/{code}', [ComplainController::class, 'storeReply']);

    // api untuk mengambil data statistic
    Route::get('/dashboard/statistics',[DashboardController::class,'getStatistics']);
}); 