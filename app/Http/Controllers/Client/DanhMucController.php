<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\HangHangKhong;
use App\Models\SanBay;
use Illuminate\Http\Request;

class DanhMucController extends Controller
{
    // Danh sach san bay cho client (dropdown tim kiem)
    public function danhSachSanBay(Request $request)
    {
        $query = SanBay::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tenSanBay', 'like', "%{$search}%")
                    ->orWhere('maCode', 'like', "%{$search}%")
                    ->orWhere('thanhPho', 'like', "%{$search}%");
            });
        }

        $danhSach = $query->orderBy('thanhPho', 'asc')->orderBy('tenSanBay', 'asc')->get();

        $danhSach->transform(function ($item) {
            $item->hinh_anh_url = $item->hinhAnh ?? null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lay danh sach san bay thanh cong',
            'data' => $danhSach,
        ], 200);
    }

    // Danh sach hang hang khong dang hoat dong
    public function danhSachHangHangKhong(Request $request)
    {
        $query = HangHangKhong::query()->where('trangThai', 1);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tenHang', 'like', "%{$search}%")
                    ->orWhere('maCode', 'like', "%{$search}%");
            });
        }

        $danhSach = $query->orderBy('tenHang', 'asc')->get();

        $danhSach->transform(function ($item) {
            $item->logo_url = $item->logo ?? null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lay danh sach hang hang khong thanh cong',
            'data' => $danhSach,
        ], 200);
    }
}
