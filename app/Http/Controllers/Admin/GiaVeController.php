<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiaVe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GiaVeController extends Controller
{
    // =============================================
    // API Lấy danh sách Giá vé (GET)
    // /api/admin/gia-ve
    // Hỗ trợ: lọc theo chuyến bay, loại hành khách, loại ghế, phân trang
    // =============================================
    public function index(Request $request)
    {
        $query = GiaVe::with('chuyen_bay');

        // 1. Lọc theo chuyến bay
        if ($request->filled('maChuyenBay')) {
            $query->where('maChuyenBay', $request->maChuyenBay);
        }

        // 2. Lọc theo loại hành khách
        if ($request->filled('loaiHanhKhach')) {
            $query->where('loaiHanhKhach', $request->loaiHanhKhach);
        }

        // 3. Lọc theo loại ghế
        if ($request->filled('loaiGhe')) {
            $query->where('loaiGhe', $request->loaiGhe);
        }

        // 4. Tìm kiếm chung (theo ghi chú)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('ghiChu', 'like', "%{$search}%");
        }

        // 5. Phân trang (mặc định 10 bản ghi / trang)
        $perPage = $request->get('perPage', 10);
        $danhSach = $query->orderBy('maGiaVe', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách giá vé thành công',
            'data'    => $danhSach->items(),
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page'    => $danhSach->lastPage(),
                'per_page'     => $danhSach->perPage(),
                'total'        => $danhSach->total(),
            ]
        ], 200);
    }

    // =============================================
    // API Thêm mới giá vé (POST)
    // /api/admin/gia-ve
    // =============================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'maChuyenBay'   => 'required|integer|exists:chuyen_bay,maChuyenBay',
            'loaiHanhKhach' => 'required|string|max:50',
            'loaiGhe'       => 'required|string|max:50',
            'giaTien'       => 'required|numeric|min:0',
            'ghiChu'        => 'nullable|string'
        ], [
            'maChuyenBay.required'   => 'Vui lòng chọn chuyến bay.',
            'maChuyenBay.exists'     => 'Chuyến bay không tồn tại.',
            'loaiHanhKhach.required' => 'Vui lòng nhập loại hành khách.',
            'loaiGhe.required'       => 'Vui lòng nhập loại ghế.',
            'giaTien.required'       => 'Vui lòng nhập giá tiền.',
            'giaTien.numeric'        => 'Giá tiền phải là số.',
            'giaTien.min'            => 'Giá tiền không được âm.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $giaVe = GiaVe::create($request->all());
        $giaVe->load('chuyen_bay');

        return response()->json([
            'success' => true,
            'message' => 'Thêm giá vé thành công!',
            'data'    => $giaVe
        ], 201);
    }

    // =============================================
    // API Lấy chi tiết 1 giá vé (GET)
    // /api/admin/gia-ve/{id}
    // =============================================
    public function show($id)
    {
        $giaVe = GiaVe::with('chuyen_bay')->find($id);

        if (!$giaVe) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy giá vé này.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'Lấy thông tin giá vé thành công', 'data' => $giaVe], 200);
    }

    // =============================================
    // API Cập nhật giá vé (PUT)
    // /api/admin/gia-ve/{id}
    // =============================================
    public function update(Request $request, $id)
    {
        $giaVe = GiaVe::find($id);

        if (!$giaVe) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy giá vé này.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'maChuyenBay'   => 'sometimes|required|integer|exists:chuyen_bay,maChuyenBay',
            'loaiHanhKhach' => 'sometimes|required|string|max:50',
            'loaiGhe'       => 'sometimes|required|string|max:50',
            'giaTien'       => 'sometimes|required|numeric|min:0',
            'ghiChu'        => 'nullable|string'
        ], [
            'maChuyenBay.exists' => 'Chuyến bay không tồn tại.',
            'giaTien.numeric'    => 'Giá tiền phải là số.',
            'giaTien.min'        => 'Giá tiền không được âm.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $giaVe->update($request->all());
        $giaVe->load('chuyen_bay');

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật giá vé thành công!',
            'data'    => $giaVe
        ], 200);
    }

    // =============================================
    // API Xóa giá vé (DELETE)
    // /api/admin/gia-ve/{id}
    // =============================================
    public function destroy($id)
    {
        $giaVe = GiaVe::find($id);

        if (!$giaVe) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy giá vé để xóa.'], 404);
        }

        // Rào chắn bảo vệ an toàn dữ liệu: Không xóa nếu giá vé này đã được áp dụng trong 1 vé nào đó đã phát hành
        if ($giaVe->ves()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa! Giá vé này đã có khách hàng mua vé, chỉ có thể chỉnh sửa.'
            ], 400);
        }

        $giaVe->delete();

        return response()->json(['success' => true, 'message' => 'Đã xóa giá vé thành công!'], 200);
    }
}
