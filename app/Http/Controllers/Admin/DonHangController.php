<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DonHangController extends Controller
{
    // =============================================
    // API Lấy danh sách đơn hàng (GET)
    // /api/admin/don-hang
    // Hỗ trợ: tìm kiếm theo mã/thông tin liên hệ, lọc theo trạng thái, phân trang
    // =============================================
    public function index(Request $request)
    {
        $query = DonHang::with('taikhoan');

        // 1. Tìm kiếm theo mã đơn hàng hoặc thông tin liên hệ (tên/email/sdt)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('maCodeDonHang',   'like', "%{$search}%")
                  ->orWhere('thongTinLienHe', 'like', "%{$search}%");
            });
        }

        // 2. Lọc theo trạng thái
        // 0: Chờ thanh toán | 1: Đã thanh toán | 2: Đã hủy
        if ($request->has('trangThai') && $request->trangThai !== null) {
            $query->where('trangThai', $request->trangThai);
        }

        // 3. Lọc theo phương thức thanh toán (vnpay / paypal)
        if ($request->filled('phuongThucThanhToan')) {
            $query->where('phuongThucThanhToan', $request->phuongThucThanhToan);
        }

        // 4. Lọc theo ngày đặt (từ ngày - đến ngày)
        if ($request->filled('tuNgay')) {
            $query->whereDate('ngayDat', '>=', $request->tuNgay);
        }
        if ($request->filled('denNgay')) {
            $query->whereDate('ngayDat', '<=', $request->denNgay);
        }

        // 5. Phân trang (mặc định 10 bản ghi / trang)
        $perPage  = $request->get('perPage', 10);
        $danhSach = $query->orderBy('ngayDat', 'desc')->paginate($perPage);

        // Decode thongTinLienHe và duffel_raw_data từ JSON string sang object cho FE dễ dùng
        $items = collect($danhSach->items())->map(function ($item) {
            if (is_string($item->thongTinLienHe)) {
                $item->thongTinLienHe = json_decode($item->thongTinLienHe);
            }
            if (!empty($item->duffel_raw_data) && is_string($item->duffel_raw_data)) {
                $item->duffel_raw_data = json_decode($item->duffel_raw_data);
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách đơn hàng thành công',
            'data'    => $items,
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page'    => $danhSach->lastPage(),
                'per_page'     => $danhSach->perPage(),
                'total'        => $danhSach->total(),
            ]
        ], 200);
    }

    // =============================================
    // API Xem chi tiết đơn hàng (GET)
    // /api/admin/don-hang/{id}
    // Trả về: thông tin đơn + vé + hành khách + chuyến bay + thanh toán
    // =============================================
    public function show($id)
    {
        $donhang = DonHang::with([
            'taikhoan',
            'thanh_toans',
            'ves.hanh_khach',
            'ves.chuyen_bay.hang_hang_khong',
            'ves.chuyen_bay.san_bay_di',
            'ves.chuyen_bay.san_bay_den',
            'ves.gia_ve',
            'ves.ma_giam_gia',
        ])->find($id);

        if (!$donhang) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng này.'
            ], 404);
        }

        // Decode thongTinLienHe và duffel_raw_data từ JSON string sang object
        if (is_string($donhang->thongTinLienHe)) {
            $donhang->thongTinLienHe = json_decode($donhang->thongTinLienHe);
        }
        if (!empty($donhang->duffel_raw_data) && is_string($donhang->duffel_raw_data)) {
            $donhang->duffel_raw_data = json_decode($donhang->duffel_raw_data);
        }

        if ($donhang->ves) {
            foreach ($donhang->ves as $ve) {
                if (!empty($ve->duffel_passenger_data) && is_string($ve->duffel_passenger_data)) {
                    $ve->duffel_passenger_data = json_decode($ve->duffel_passenger_data);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy chi tiết đơn hàng thành công',
            'data'    => $donhang
        ], 200);
    }

    // =============================================
    // API Cập nhật đơn hàng (PUT)
    // /api/admin/don-hang/{id}
    // Cho phép cập nhật: trangThai và/hoặc maDatChoHang (mã PNR từ hãng)
    // =============================================
    public function update(Request $request, $id)
    {
        $donhang = DonHang::find($id);

        if (!$donhang) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng này.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'trangThai'    => 'sometimes|required|integer|in:0,1,2',
            'maDatChoHang' => 'nullable|string|max:20',
        ], [
            'trangThai.in' => 'Trạng thái không hợp lệ (0: Chờ TT, 1: Đã TT, 2: Đã hủy).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        if ($request->has('trangThai'))    $donhang->trangThai    = $request->trangThai;
        if ($request->has('maDatChoHang')) $donhang->maDatChoHang = $request->maDatChoHang;

        $donhang->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đơn hàng thành công!',
            'data'    => $donhang
        ], 200);
    }

    // =============================================
    // API Xóa đơn hàng (DELETE)
    // /api/admin/don-hang/{id}
    // Điều kiện: Chỉ xóa được đơn ở trạng thái "Chờ thanh toán" (trangThai = 0)
    // =============================================
    public function destroy($id)
    {
        $donhang = DonHang::find($id);

        if (!$donhang) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng để xóa.'
            ], 404);
        }

        // Bảo vệ dữ liệu: không xóa đơn đã thanh toán hoặc đã hủy có lịch sử
        if ($donhang->trangThai == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa đơn hàng đã thanh toán! Vui lòng chuyển sang trạng thái Hủy nếu cần.'
            ], 400);
        }

        // Xóa vé và thanh toán liên quan (nếu DB chưa cài CASCADE)
        $donhang->ves()->delete();
        $donhang->thanh_toans()->delete();
        $donhang->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa đơn hàng và các vé liên quan thành công!'
        ], 200);
    }
}