<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\MaGiamGia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KhuyenMaiController extends Controller
{
    // Danh sach khuyen mai dang hoat dong (phuc vu hien thi khi dat ve)
    // GET /api/client/khuyen-mai?type=&limit=
    public function danhSach(Request $request)
    {
        $today = Carbon::now();

        $query = MaGiamGia::query()
            ->where('trangThai', 1)
            ->where('ngayBatDau', '<=', $today)
            ->where('ngayKetThuc', '>=', $today)
            ->where('soLuongToiDa', '>', 0);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ten_km', 'like', "%{$search}%");
        }

        $limit = (int) $request->get('limit', 10);
        $limit = max(1, min(50, $limit));

        $danhSach = $query->orderBy('ngayBatDau', 'desc')->take($limit)->get();

        $danhSach->transform(function ($item) {
            $item->anh_url = $item->anh ?? null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lay danh sach khuyen mai thanh cong',
            'data' => $danhSach,
        ], 200);
    }
}
