<?php

namespace App\Http\Controllers;

use App\Models\ChuyenBay;
use Illuminate\Http\Request;

class ChuyenBayController extends Controller
{
    // API Lấy danh sách chuyến bay (hỗ trợ lọc và tìm kiếm)
    public function index(Request $request)
    {
        // with() sẽ nhúng toàn bộ dữ liệu từ các bảng liên kết vào JSON trả về
        $query = ChuyenBay::with(['hang_hang_khong', 'may_bay', 'san_bay_di', 'san_bay_den']);

        // 1. Lọc theo Sân bay đi
        // Hàm filled() sẽ kiểm tra xem tham số có được FE gửi lên VÀ có giá trị khác rỗng hay không
        if ($request->filled('maSanBayDi')) {
            $query->where('maSanBayDi', $request->maSanBayDi);
        }

        // 2. Lọc theo Sân bay đến
        if ($request->filled('maSanBayDen')) {
            $query->where('maSanBayDen', $request->maSanBayDen);
        }

        // 3. Lọc theo Ngày khởi hành
        // FE sẽ gửi lên định dạng YYYY-MM-DD (VD: 2025-12-20). whereDate tự động bỏ qua phần giờ phút giây trong DB.
        if ($request->filled('ngayDi')) {
            $query->whereDate('ngayGioCatCanh', $request->ngayDi);
        }

        // 4. Tìm kiếm nhanh (Search)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('maChuyenBay', 'like', "%{$search}%");
        }

        // 5. Sắp xếp chuyến bay theo thời gian cất cánh gần nhất đưa lên đầu, và lấy dữ liệu
        $danhSach = $query->orderBy('ngayGioCatCanh', 'asc')->get();

        // 6. Trả về format JSON chuẩn xác cho FE
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách chuyến bay thành công',
            'data' => $danhSach
        ], 200);
    }
}
