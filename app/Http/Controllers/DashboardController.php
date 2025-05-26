<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardResource;
use App\Models\Complain;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class DashboardController extends Controller
{
    public function getStatistics()
    {
        try {
            // Set tanggal awal dan akhir bulan ini
            $currentMonth = Carbon::now()->startOfMonth();
            $endMonth = $currentMonth->copy()->endOfMonth();

            // Validasi tanggal
            if (!$currentMonth->isValid() || !$endMonth->isValid()) {
                throw new Exception('Invalid date range');
            }

            // Total keseluruhan complain bulan ini
            $totalComplain = Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                ->count();

            // Total complain yang belum resolved
            $activeComplain = Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                ->where('status', '!=', 'resolved')
                ->count();

            // Total complain yang sudah resolved
            $resolvedComplain = Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                ->where('status', 'resolved')
                ->count();

            // Waktu rata-rata menyelesaikan complain (dalam jam)
            // Menggunakan updated_at sebagai waktu resolusi
            $avgResolutionTime = Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                ->where('status', 'resolved')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_time'))
                ->value('avg_time') ?? 0;

            // Distribusi status complain
            $statusDistribution = [
                'open' => Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                    ->where('status', 'open')
                    ->count(),

                'onprogres' => Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                    ->where('status', 'onprogres')
                    ->count(),

                'resolved' => Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                    ->where('status', 'resolved')
                    ->count(),

                'rejected' => Complain::whereBetween('created_at', [$currentMonth, $endMonth])
                    ->where('status', 'rejected')
                    ->count(),
            ];

            // Pembentukan array $dashboardData
            $dashboardData = [
                'total_complains'      => $totalComplain,
                'active_complains'     => $activeComplain,
                'resolved_complains'   => $resolvedComplain,
                'avg_resolution_time'  => round($avgResolutionTime, 1),
                'status_distribution'  => $statusDistribution,
            ];

            // Format response
            return response()->json([
                'message' => 'Statistik berhasil diambil',
                'data' => new DashboardResource($dashboardData)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}