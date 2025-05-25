<?php

namespace App\Http\Controllers;

// Import class-class yang dibutuhkan
use App\Http\Requests\ComplainStoreRequest; // Class untuk validasi request
use App\Http\Resources\ComplainResource;    // Class untuk format response
use App\Models\Complain;                    // Model Complain
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
                'dataa' => ComplainResource::collection($complains)
            ], 200);

        } catch (Exception $e) {
            // Kembalikan response error jika terjadi masalah
            return response()->json([
                'message' => 'Gagal mengambil data complain',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}