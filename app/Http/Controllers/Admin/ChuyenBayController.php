<?php

namespace App\Http\Controllers\Admin;

use App\Models\ChuyenBay;
use App\Http\Controllers\Controller;
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
        // 4. Lọc theo Hãng hàng không (Khách chỉ thích bay VietJet hoặc VN Airlines)
        if ($request->filled('maHang')) {
            $query->where('maHang', $request->maHang);
        }

        // 5. Lọc theo Máy bay (Khách muốn né các máy bay thân hẹp, chỉ thích đi Boeing thân rộng)
        if ($request->filled('maMayBay')) {
            $query->where('maMayBay', $request->maMayBay);
        }


        // 6. Lọc theo trạng thái, Cho phép Admin lọc theo trạng thái (0: Hủy, 1: Hoạt động)
        // Dùng has() thay vì filled() vì giá trị 0 đôi khi bị filled() coi là rỗng
        if ($request->has('trangThai') && $request->trangThai !== null) {
            $query->where('trangThai', $request->trangThai);
        }

        // 7. Tìm kiếm nâng cao (Search)
        if ($request->filled('search')) {
            $search = $request->search;
            
            // Dùng where(function...) để gom nhóm các điều kiện OR, tránh xung đột với các điều kiện Lọc ở trên
            $query->where(function($q) use ($search) {
                // Tìm theo mã chuyến bay
                $q->where('maChuyenBay', 'like', "%{$search}%")
                  // Hoặc tìm theo tên/mã hãng hàng không
                  ->orWhereHas('hang_hang_khong', function($qHang) use ($search) {
                      $qHang->where('tenHang', 'like', "%{$search}%")
                            ->orWhere('maCode', 'like', "%{$search}%");
                  })
                  // Hoặc tìm theo thành phố/tên sân bay đi
                  ->orWhereHas('san_bay_di', function($qSbDi) use ($search) {
                      $qSbDi->where('tenSanBay', 'like', "%{$search}%")
                            ->orWhere('thanhPho', 'like', "%{$search}%");
                  })
                  // Hoặc tìm theo thành phố/tên sân bay đến
                  ->orWhereHas('san_bay_den', function($qSbDen) use ($search) {
                      $qSbDen->where('tenSanBay', 'like', "%{$search}%")
                            ->orWhere('thanhPho', 'like', "%{$search}%");
                  });
            });
        }

        // 8. Sắp xếp chuyến bay theo thời gian cất cánh gần nhất đưa lên đầu, và lấy dữ liệu
        $danhSach = $query->orderBy('ngayGioCatCanh', 'asc')->get();

        // 9. Trả về format JSON chuẩn xác cho FE
        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách chuyến bay thành công',
            'data' => $danhSach
        ], 200);
    }
    // API Thêm mới chuyến bay (POST)
    public function store(Request $request)
    {
        // 1. Dựng bức tường kiểm tra dữ liệu (Validation)
        $validated = $request->validate([
            'maMayBay'       => 'required|integer',
            'maHang'         => 'required|integer',
            'maSanBayDi'     => 'required|integer',
            // Sân bay đến bắt buộc phải KHÁC sân bay đi
            'maSanBayDen'    => 'required|integer|different:maSanBayDi', 
            'ngayGioCatCanh' => 'required|date',
            // Giờ hạ cánh bắt buộc phải SAU giờ cất cánh
            'ngayGioHaCanh'  => 'required|date|after:ngayGioCatCanh',
            'soGheTong'      => 'required|integer|min:1',
            'trangThai'      => 'nullable|boolean'
        ], [
            // Tùy chỉnh câu báo lỗi sang tiếng Việt để FE hiển thị trực tiếp cho người dùng
            'maSanBayDen.different' => 'Sân bay đến không được trùng với sân bay đi.',
            'ngayGioHaCanh.after'   => 'Thời gian hạ cánh phải diễn ra sau thời gian cất cánh.',
            'required'              => 'Trường :attribute không được để trống.',
            'integer'               => 'Trường :attribute phải là số.'
        ]);

        // 2. Xử lý Logic nghiệp vụ tự động
        // Khi mới tạo chuyến bay, chưa ai đặt vé nên Số ghế còn lại = Tổng số ghế
        $validated['soGheConLai'] = $validated['soGheTong'];

        // Nếu FE không truyền trạng thái lên, ta cho mặc định là 1 (Hoạt động)
        $validated['trangThai'] = $request->has('trangThai') ? $request->trangThai : 1;

        // 3. Lưu dữ liệu vào Database
        // Nhờ bạn đã thiết lập $fillable trong Model rất chuẩn, ta chỉ cần 1 dòng lệnh create()
        $chuyenBay = ChuyenBay::create($validated);

        // 4. Trả về kết quả
        return response()->json([
            'success' => true,
            'message' => 'Thêm chuyến bay thành công!',
            'data'    => $chuyenBay
        ], 201); // HTTP Status 201: Created (Đã tạo thành công)
    }
    // API Lấy thông tin chi tiết 1 chuyến bay (GET)
    public function show($id)
    {
        // Vẫn dùng with() để lấy kèm tên hãng, tên sân bay cho FE hiển thị đẹp
        $chuyenBay = ChuyenBay::with(['hang_hang_khong', 'may_bay', 'san_bay_di', 'san_bay_den'])->find($id);

        if (!$chuyenBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chuyến bay'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin chuyến bay thành công',
            'data'    => $chuyenBay
        ], 200);
    }
    // API Cập nhật chuyến bay (PUT)
    public function update(Request $request, $id)
    {
        $chuyenBay = ChuyenBay::find($id);

        if (!$chuyenBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chuyến bay để cập nhật'
            ], 404);
        }

        // 1. Kiểm tra dữ liệu gửi lên
        $validated = $request->validate([
            // 'sometimes' nghĩa là: Nếu FE có gửi trường này lên thì mới bắt buộc kiểm tra các điều kiện phía sau
            'maMayBay'       => 'sometimes|required|integer',
            'maHang'         => 'sometimes|required|integer',
            'maSanBayDi'     => 'sometimes|required|integer',
            'maSanBayDen'    => 'sometimes|required|integer|different:maSanBayDi',
            'ngayGioCatCanh' => 'sometimes|required|date',
            'ngayGioHaCanh'  => 'sometimes|required|date|after:ngayGioCatCanh',
            'soGheTong'      => 'sometimes|required|integer|min:1',
            'trangThai'      => 'sometimes|nullable|boolean'
        ], [
            'maSanBayDen.different' => 'Sân bay đến không được trùng với sân bay đi.',
            'ngayGioHaCanh.after'   => 'Thời gian hạ cánh phải diễn ra sau thời gian cất cánh.',
            'required'              => 'Trường :attribute không được để trống.',
            'integer'               => 'Trường :attribute phải là số.'
        ]);

        // 2. Logic cập nhật ghế (Rất quan trọng cho Lead BE)
        // Nếu Admin thay đổi TỔNG SỐ GHẾ, ta phải tính lại SỐ GHẾ CÒN LẠI để không bị âm
        if ($request->has('soGheTong') && $request->soGheTong != $chuyenBay->soGheTong) {
            $soGheDaBan = $chuyenBay->soGheTong - $chuyenBay->soGheConLai; // Tìm ra số ghế đã bán
            $validated['soGheConLai'] = $validated['soGheTong'] - $soGheDaBan; // Tính lại ghế còn trống
            
            // Nếu admin sửa tổng ghế thấp hơn số vé đã bán ra -> Chặn lại ngay!
            if ($validated['soGheConLai'] < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tổng số ghế mới không thể nhỏ hơn số vé đã bán ra!'
                ], 422);
            }
        }

        // 3. Thực hiện cập nhật
        $chuyenBay->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chuyến bay thành công!',
            'data'    => $chuyenBay
        ], 200);
    }
}
