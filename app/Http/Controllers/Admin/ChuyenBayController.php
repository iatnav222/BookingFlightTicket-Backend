<?php

namespace App\Http\Controllers\Admin;

use App\Models\ChuyenBay;
use App\Models\MayBay;
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
        if ($request->filled('ngayBay')) {
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
        // if ($request->has('trangThai') && $request->trangThai !== null) {
        //     $query->where('trangThai', $request->trangThai);
        // }

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
        $validated = $request->validate([
            'maMayBay'       => 'required|integer',
            'maHang'         => 'required|integer',
            'maSanBayDi'     => 'required|integer',
            'maSanBayDen'    => 'required|integer|different:maSanBayDi', 
            'ngayGioCatCanh' => 'required|date',
            'ngayGioHaCanh'  => 'required|date|after:ngayGioCatCanh',
            // XÓA ĐIỀU KIỆN 'soGheTong' ở đây đi vì FE không cần gửi nữa
            'trangThai'      => 'nullable|boolean'
        ], [
            'maSanBayDen.different' => 'Sân bay đến không được trùng với sân bay đi.',
            'ngayGioHaCanh.after'   => 'Thời gian hạ cánh phải diễn ra sau thời gian cất cánh.'
        ]);

        // LOGIC MỚI: Truy tìm máy bay và tự động gán số ghế
        $mayBay = MayBay::find($request->maMayBay);
        if (!$mayBay) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy máy bay này!'], 404);
        }

        $validated['soGheTong'] = $mayBay->soGheTong;
        $validated['soGheConLai'] = $mayBay->soGheTong; // Mới tạo thì còn trống 100%

        $validated['trangThai'] = $request->has('trangThai') ? $request->trangThai : 1;

        $chuyenBay = ChuyenBay::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Thêm chuyến bay thành công!',
            'data'    => $chuyenBay
        ], 201);
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
            return response()->json(['success' => false, 'message' => 'Không tìm thấy chuyến bay'], 404);
        }

        $validated = $request->validate([
            'maMayBay'       => 'sometimes|required|integer',
            'maHang'         => 'sometimes|required|integer',
            'maSanBayDi'     => 'sometimes|required|integer',
            'maSanBayDen'    => 'sometimes|required|integer|different:maSanBayDi',
            'ngayGioCatCanh' => 'sometimes|required|date',
            'ngayGioHaCanh'  => 'sometimes|required|date|after:ngayGioCatCanh',
            'trangThai'      => 'sometimes|nullable|boolean'
        ]);

        // LOGIC MỚI: Nếu Admin thực hiện thao tác đổi Máy bay
        if ($request->has('maMayBay') && $request->maMayBay != $chuyenBay->maMayBay) {
            $mayBayMoi = MayBay::find($request->maMayBay);
            
            if (!$mayBayMoi) {
                return response()->json(['success' => false, 'message' => 'Máy bay mới không tồn tại!'], 404);
            }

            // Tính số vé đã bán
            $soVeDaBan = $chuyenBay->soGheTong - $chuyenBay->soGheConLai;

            // Kiểm tra an toàn: Máy bay mới phải có sức chứa lớn hơn hoặc bằng số khách đã mua vé
            if ($mayBayMoi->soGheTong < $soVeDaBan) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể đổi máy bay! Máy bay mới chỉ có {$mayBayMoi->soGheTong} chỗ, nhưng đã bán {$soVeDaBan} vé."
                ], 400);
            }

            // Nếu an toàn, cập nhật lại toàn bộ thông số ghế
            $validated['soGheTong'] = $mayBayMoi->soGheTong;
            $validated['soGheConLai'] = $mayBayMoi->soGheTong - $soVeDaBan;
        }

        $chuyenBay->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật chuyến bay thành công!',
            'data'    => $chuyenBay
        ], 200);
    }
    // API Xóa chuyến bay (DELETE)
    public function destroy($id)
    {
        $chuyenBay = ChuyenBay::find($id);

        if (!$chuyenBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy chuyến bay để xóa'
            ], 404);
        }

        // Rào chắn bảo vệ an toàn dữ liệu
        // Nếu số ghế còn lại NHỎ HƠN tổng số ghế -> Đã có người mua vé
        if ($chuyenBay->soGheConLai < $chuyenBay->soGheTong) {
            return response()->json([
                'success' => false,
                'message' => 'Chuyến bay đã phát sinh giao dịch đặt vé, không thể xóa. Vui lòng chuyển trạng thái sang Hủy chuyến!'
            ], 400); // Trả về lỗi 400 Bad Request
        }

        // Nếu an toàn (chưa bán được vé nào), tiến hành xóa vĩnh viễn
        $chuyenBay->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa chuyến bay thành công!'
        ], 200);
    }
}
