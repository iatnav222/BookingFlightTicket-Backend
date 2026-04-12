<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MayBay;
use App\Models\HangHangKhong;
use App\Models\ChuyenBay;
use Illuminate\Http\Request;

class MayBayController extends Controller
{
    // API Lấy danh sách máy bay (hỗ trợ lọc và tìm kiếm)
    public function index(Request $request)
    {
        // with() nhúng thông tin hãng hàng không vào kết quả trả về
        $query = MayBay::with('hang_hang_khong');

        // 1. Lọc theo hãng hàng không
        if ($request->filled('maHang')) {
            $query->where('maHang', $request->maHang);
        }

        // 2. Lọc theo loại máy bay (VD: "Thân hẹp", "Thân rộng")
        if ($request->filled('loai')) {
            $query->where('loai', $request->loai);
        }

        // 3. Lọc theo hãng sản xuất (VD: "Boeing", "Airbus")
        if ($request->filled('hangSanXuat')) {
            $query->where('hangSanXuat', $request->hangSanXuat);
        }

        // 4. Tìm kiếm theo tên máy bay hoặc tên/mã hãng hàng không
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('tenMayBay', 'like', "%{$keyword}%")
                  ->orWhere('hangSanXuat', 'like', "%{$keyword}%")
                  ->orWhereHas('hang_hang_khong', function ($qHang) use ($keyword) {
                      $qHang->where('tenHang', 'like', "%{$keyword}%")
                            ->orWhere('maCode', 'like', "%{$keyword}%");
                  });
            });
        }

        $danhSach = $query->orderBy('maMayBay', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách máy bay thành công',
            'data'    => $danhSach
        ], 200);
    }

    // API Thêm mới máy bay (POST)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'maHang'      => 'required|integer|exists:hang_hang_khong,maHang',
            'tenMayBay'   => 'required|string|max:100',
            'loai'        => 'required|string|max:50',
            'soGheTong'   => 'required|integer|min:1',
            'hangSanXuat' => 'nullable|string|max:100',
        ], [
            'maHang.required'    => 'Vui lòng chọn hãng hàng không.',
            'maHang.exists'      => 'Hãng hàng không này không tồn tại.',
            'tenMayBay.required' => 'Vui lòng nhập tên máy bay.',
            'loai.required'      => 'Vui lòng nhập loại máy bay.',
            'soGheTong.required' => 'Vui lòng nhập số ghế.',
            'soGheTong.min'      => 'Số ghế phải lớn hơn 0.',
        ]);

        $mayBay = MayBay::create($validated);

        // Load thêm thông tin hãng để FE hiển thị đẹp
        $mayBay->load('hang_hang_khong');

        return response()->json([
            'success' => true,
            'message' => 'Thêm máy bay thành công!',
            'data'    => $mayBay
        ], 201);
    }

    // API Lấy thông tin chi tiết 1 máy bay (GET)
    public function show($id)
    {
        $mayBay = MayBay::with('hang_hang_khong')->find($id);

        if (!$mayBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy máy bay này.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin máy bay thành công',
            'data'    => $mayBay
        ], 200);
    }

    // API Cập nhật máy bay (PUT)
    public function update(Request $request, $id)
    {
        $mayBay = MayBay::find($id);

        if (!$mayBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy máy bay này.'
            ], 404);
        }

        $validated = $request->validate([
            'maHang'      => 'sometimes|required|integer|exists:hang_hang_khong,maHang',
            'tenMayBay'   => 'sometimes|required|string|max:100',
            'loai'        => 'sometimes|required|string|max:50',
            'soGheTong'   => 'sometimes|required|integer|min:1',
            'hangSanXuat' => 'sometimes|nullable|string|max:100',
        ], [
            'maHang.exists'  => 'Hãng hàng không này không tồn tại.',
            'soGheTong.min'  => 'Số ghế phải lớn hơn 0.',
        ]);

        // Nếu admin thay đổi soGheTong, kiểm tra an toàn với các chuyến bay đang dùng máy bay này
        if ($request->has('soGheTong') && $request->soGheTong != $mayBay->soGheTong) {
            // Tìm số vé đã bán nhiều nhất trong 1 chuyến bay đang dùng máy bay này
            $soVeDaBanToiDa = ChuyenBay::where('maMayBay', $id)
                ->selectRaw('(soGheTong - soGheConLai) as soVeDaBan')
                ->orderByDesc('soVeDaBan')
                ->value('soVeDaBan');

            if ($soVeDaBanToiDa !== null && $request->soGheTong < $soVeDaBanToiDa) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể giảm số ghế! Máy bay đang có chuyến bay đã bán tới {$soVeDaBanToiDa} vé."
                ], 400);
            }
        }

        $mayBay->update($validated);
        $mayBay->load('hang_hang_khong');

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật máy bay thành công!',
            'data'    => $mayBay
        ], 200);
    }

    // API Xóa máy bay (DELETE)
    public function destroy($id)
    {
        $mayBay = MayBay::find($id);

        if (!$mayBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy máy bay để xóa.'
            ], 404);
        }

        // Kiểm tra ràng buộc: không xóa nếu máy bay đang được dùng trong chuyến bay
        $coChuyenBay = ChuyenBay::where('maMayBay', $id)->exists();

        if ($coChuyenBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa! Máy bay này đang được sử dụng trong các chuyến bay.'
            ], 400);
        }

        $mayBay->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa máy bay thành công!'
        ], 200);
    }
}