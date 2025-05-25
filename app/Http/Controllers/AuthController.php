<?php

namespace App\Http\Controllers;

// Import library yang dibutuhkan
use App\Http\Requests\RegisterStoreRequest;  // Request class untuk validasi register
use App\Http\Resources\UserResource;         // Resource class untuk format response user
use App\Models\User;                         // Model User
use Exception;                               // Class untuk menangani error
use Illuminate\Support\Facades\Hash;         // Facade untuk hashing password
use Illuminate\Http\Request;                 // Class untuk menangani HTTP request
use Illuminate\Support\Facades\Auth;         // Facade untuk autentikasi
use Illuminate\Support\Facades\Validator;    // Facade untuk validasi
use Illuminate\Support\Facades\DB;           // Facade untuk database transaction

class AuthController extends Controller
{
    /**
     * Fungsi untuk login user
     * Method: POST
     * Endpoint: /api/login
     * Body: {
     *    "email": "user@example.com",
     *    "password": "password123"
     * }
     */
    public function login(Request $request)
    {
        try {
            // ===== VALIDASI INPUT =====
            // Validator::make() digunakan untuk memvalidasi input
            // Parameter pertama: data yang akan divalidasi
            // Parameter kedua: rules validasi
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',    // Email harus diisi dan format email valid
                'password' => 'required'        // Password harus diisi
            ]);

            // Jika validasi gagal, kembalikan pesan error
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422); // 422 = Unprocessable Entity
            }

            // ===== PROSES LOGIN =====
            // Auth::guard('web') digunakan untuk autentikasi
            // attempt() mencoba login dengan credentials
            // only() mengambil hanya field yang dibutuhkan
            if (!Auth::guard('web')->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Unauthorized',
                    'data' => null
                ], 401); // 401 = Unauthorized
            }

            // ===== JIKA LOGIN BERHASIL =====
            // Auth::user() mengambil data user yang sedang login
            $user = Auth::user();
            
            // createToken() membuat token baru untuk user
            // plainTextToken mengambil string token
            $token = $user->createToken('auth_token')->plainTextToken;

            // Kembalikan response sukses
            return response()->json([
                'message' => 'Login Berhasil',
                'data' => [
                    'token' => $token,                    // Token untuk autentikasi
                    'user' => new UserResource($user)     // Data user yang diformat
                ]
            ], 200); // 200 = OK

        } catch (Exception $e) {
            // Tangani error yang tidak terduga
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500); // 500 = Internal Server Error
        }
    }

    /**
     * Fungsi untuk mengambil data user yang sedang login
     * Method: GET
     * Endpoint: /api/me
     * Header: Authorization: Bearer {token}
     */
    public function me()
    {
        try {
            // Ambil data user yang sedang login
            $user = Auth::user();
            
            return response()->json([
                'message' => "Profile User Berhasil Diambil",
                'data' => new UserResource($user)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi untuk logout user
     * Method: POST
     * Endpoint: /api/logout
     * Header: Authorization: Bearer {token}
     */
    public function logout()
    {
        try {
            // Ambil user yang sedang login
            $user = Auth::user();

            // Cek apakah user ditemukan
            if (!$user) {
                return response()->json([
                    'message' => 'User tidak ditemukan',
                    'data' => null
                ], 404); // 404 = Not Found
            }

            // Hapus token yang sedang digunakan
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => "Berhasil Logout",
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Terjadi Kesalahan Logout",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi untuk registrasi user baru
     * Method: POST
     * Endpoint: /api/register
     * Body: {
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "password": "password123",
     *    "password_confirmation": "password123"
     * }
     */
    public function register(RegisterStoreRequest $request)
    {
        // Ambil data yang sudah divalidasi
        $data = $request->validated();

        // Mulai transaction database
        // Transaction memastikan semua query berhasil atau tidak sama sekali
        DB::beginTransaction();

        try {
            // Buat user baru
            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            // Hash password untuk keamanan
            $user->password = Hash::make($data['password']);
            $user->save();

            // Buat token untuk user baru
            $token = $user->createToken("auth_token")->plainTextToken;

            // Commit transaction jika semua berhasil
            DB::commit();

            // Kembalikan response sukses
            return response()->json([
                "message" => "Registrasi Berhasil",
                "data" => [
                    'token' => $token,
                    'user' => new UserResource($user)
                ]
            ], 201); // 201 = Created

        } catch (Exception $e) {
            // Rollback transaction jika terjadi error
            DB::rollBack();

            return response()->json([
                'message' => "Registrasi Gagal",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}