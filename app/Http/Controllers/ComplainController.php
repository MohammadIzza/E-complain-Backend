<?php

namespace App\Http\Controllers;

// Import class-class yang dibutuhkan

use App\Http\Requests\ComplainReplyRequest;
use App\Http\Requests\ComplainStoreRequest; // Class untuk validasi request
use App\Http\Resources\ComplainResource;    // Class untuk format response
use App\Models\Complain;                    // Model Complain
use App\Models\ComplainReply;
use Exception;                              // Class untuk menangani error
use Illuminate\Http\Request;                // Class untuk menangani HTTP request
use Illuminate\Support\Facades\DB;          // Facade untuk database operations
use Illuminate\Support\Facades\Auth;        // Facade untuk autentikasi

class ComplainController extends Controller
{
    /**
     * Menyimpan complain baru
     * Method: POST
     * Endpoint: /api/complains
     * 
     * @param ComplainStoreRequest $request - Request yang sudah divalidasi
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ComplainStoreRequest $request)
    {
        // Ambil data yang sudah divalidasi dari request
        // Validasi dilakukan di ComplainStoreRequest
        $data = $request->validated();

        // Mulai transaction database
        // Transaction memastikan semua operasi database berhasil atau tidak sama sekali
        DB::beginTransaction();
        
        try {
            // Buat instance baru dari model Complain
            $complain = new Complain;
            
            // Set nilai-nilai untuk complain baru
            $complain->user_id = Auth::user()->id;  // Ambil ID user yang sedang login
            $complain->code = 'TIC-' . rand(1000, 999999);  // Generate kode unik
            $complain->title = $data['title'];      // Set judul dari request
            $complain->description = $data['description'];  // Set deskripsi dari request
            $complain->priority = $data['priority'];  // Set prioritas dari request
            $complain->status = 'open';  // Set status default ke 'open'
            $complain->save();  // Simpan data ke database

            // Commit transaction jika semua operasi berhasil
            DB::commit();

            // Kembalikan response sukses dengan status code 201 (Created)
            return response()->json([
                'message' => 'Complain berhasil dibuat',
                'data' => new ComplainResource($complain)  // Format data menggunakan Resource
            ], 201);

        } catch (Exception $e) {
            // Rollback transaction jika terjadi error
            // Ini akan membatalkan semua perubahan yang sudah dilakukan
            DB::rollBack();

            // Kembalikan response error dengan status code 500 (Server Error)
            return response()->json([
                'message' => "Complain Gagal",
                'error' => $e->getMessage()  // Ambil pesan error
            ], 500);
        }
    }

    /**
     * Mengambil semua data complain
     * Method: GET
     * Endpoint: /api/complains
     * 
     * @param Request $request - Request dari client
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Buat query builder untuk model Complain
            $query = Complain::query();

            // Urutkan data berdasarkan created_at terbaru
            $query->orderBy('created_at', 'desc');

            // Pencarian berdasarkan kode atau judul
            // Menggunakan LIKE untuk pencarian partial
            if ($request->search) {
                $query->where('code', 'like', '%' . $request->search . '%')
                      ->orWhere('title', 'like', '%' . $request->search . '%');
            }

            // Filter berdasarkan status
            // Hanya filter jika status diberikan dalam request
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan prioritas
            // Hanya filter jika prioritas diberikan dalam request
            if ($request->priority) {
                $query->where('priority', $request->priority);
            }

            // Filter berdasarkan role 
            // Jika user, hanya tampilkan complain miliknya
            if (Auth::user()->role == 'user') {
                $query->where('user_id', Auth::user()->id);
            }

            // ambil semua data 
            $complains = $query->get();

            // Ambil data dengan pagination (10 data per halaman)
            // $complains = $query->paginate(10);

            // Kembalikan response sukses dengan status code 200 (OK)
            return response()->json([
                'message' => 'Data complain berhasil diambil',
                'data' => ComplainResource::collection($complains)
            ], 200);

        } catch (Exception $e) {
            // Kembalikan response error jika terjadi masalah
            return response()->json([
                'message' => 'Gagal mengambil data complain',
                'error' => $e->getMessage()
            ], 500);
        }
    }


/**
     * Menampilkan detail complain berdasarkan kode
     * Method: GET
     * Endpoint: /api/complain/{code}
     * 
     * @param string $code - Kode complain yang akan ditampilkan
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($code)
    {
        try {
            // Cari complain berdasarkan kode
            $complain = Complain::where('code', $code)->first();

            // Jika complain tidak ditemukan, kembalikan response 404
            if(!$complain) {
                return response()->json([
                    'message' => "Tiket tidak ditemukan"
                ], 404);
            }

            // Cek akses user
            // Jika user biasa, hanya bisa melihat complain miliknya sendiri
            if(Auth::user()->role == 'user' && $complain->user_id != Auth::user()->id) {
                return response()->json([
                    'message' => "Tidak diperbolehkan mengakses Complain ini"
                ], 403);
            }

            // Kembalikan response sukses dengan data complain
            return response()->json([
                "message" => "Menampilkan Detail Complain",
                "data" => new ComplainResource($complain)
            ], 200);

        } catch (Exception $e) {
            // Kembalikan response error jika terjadi masalah
            return response()->json([
                'message' => 'Error tidak bisa mengakses API',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // meke response complain 
    public function storeReply(ComplainReplyRequest $request, $code)
    {
        // Ambil data yang sudah divalidasi
        $data = $request->validated();
    
        // Mulai transaction database
        DB::beginTransaction();
    
        try {
            // Cari complain berdasarkan kode
            $complain = Complain::where('code', $code)->first();
    
            // Jika complain tidak ditemukan
            if(!$complain) {
                return response()->json([   
                    'message' => 'Complain tidak ditemukan'
                ], 404);
            }
    
            // Cek akses user
            // Jika user biasa, hanya bisa membalas complain miliknya sendiri
            if(Auth::user()->role == 'user' && $complain->user_id != Auth::user()->id) {
                return response()->json([
                    'message' => 'Anda tidak diperbolehkan membalas tiket ini'
                ], 403);
            }
    
            // Buat reply baru
            $complainReply = new ComplainReply();
            $complainReply->complain_id = $complain->id;
            $complainReply->user_id = Auth::user()->id;
            $complainReply->content = $data['content'];
            $complainReply->save();
    
            // Jika admin, update status complain
            if(Auth::user()->role == 'admin') {
                $complain->status = $data['status'];
                if($data['status'] == 'resolved') {
                    $complain->completed_at = now();
                }
                $complain->save();
            }
    
            // Commit transaction
            DB::commit();
    
            // Kembalikan response sukses
            return response()->json([
                'message' => 'Response Complain berhasil dibuat',
                'data' => new ComplainResource($complain)
            ], 201);
    
        } catch (Exception $e) {
            // Rollback transaction jika terjadi error
            DB::rollBack();
    
            // Kembalikan response error
            return response()->json([
                'message' => "Gagal membuat response",
                'error' => $e->getMessage()
            ], 500);
        }
    }
}