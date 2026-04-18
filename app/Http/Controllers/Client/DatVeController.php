<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\DonHang;
use App\Models\Ve;
use App\Models\HanhKhach;
use App\DichVu\DichVuDuffel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatVeController extends Controller
{
    protected $dichVuDuffel;

    public function __construct(DichVuDuffel $dichVuDuffel)
    {
        $this->dichVuDuffel = $dichVuDuffel;
    }

    /**
     * Tạo đơn hàng từ Duffel offer
     * POST /api/client/dat-ve/tao-don-hang
     * 
     * Body: {
     *   "duffel_offer_id": "off_xxx",
     *   "hanh_khach": [{
     *     "ho": "Nguyen",
     *     "ten": "Van A",
     *     "ngaySinh": "1990-01-01",
     *     "gioiTinh": "Nam",
     *     "soHoChieu": "ABC123456"
     *   }],
     *   "thongTinLienHe": {
     *     "email": "test@example.com",
     *     "soDienThoai": "0123456789"
     *   }
     * }
     */
    public function taoDonHang(Request $request)
    {
        $request->validate([
            'duffel_offer_id' => 'required|string',
            'hanh_khach' => 'required|array|min:1',
            'hanh_khach.*.ho' => 'required|string',
            'hanh_khach.*.ten' => 'required|string',
            'thongTinLienHe' => 'required|array',
            'thongTinLienHe.email' => 'required|email',
        ]);

        DB::beginTransaction();
        try {
            $duffelOfferId = $request->duffel_offer_id;
            
            // TODO: Gọi Duffel Create Order API
            // Hiện tại tạm thời lưu thông tin cơ bản
            
            // Tạo mã đơn hàng
            $maCodeDonHang = 'DH' . date('YmdHis') . strtoupper(Str::random(4));
            
            // Tạo đơn hàng
            $donHang = DonHang::create([
                'maTK' => auth()->id() ?? null,
                'maCodeDonHang' => $maCodeDonHang,
                'ngayDat' => now(),
                'tongTien' => 0, // Sẽ cập nhật sau khi có response từ Duffel
                'trangThai' => 0, // Pending
                'thongTinLienHe' => json_encode($request->thongTinLienHe),
                'duffel_offer_id' => $duffelOfferId,
                'duffel_order_id' => null, // Sẽ cập nhật sau khi create order
                'duffel_booking_reference' => null,
                'duffel_raw_data' => null,
            ]);

            // Tạo hành khách và vé
            foreach ($request->hanh_khach as $hkData) {
                // Tạo hành khách
                $hanhKhach = HanhKhach::create([
                    'ho' => $hkData['ho'],
                    'ten' => $hkData['ten'],
                    'ngaySinh' => $hkData['ngaySinh'] ?? null,
                    'gioiTinh' => $hkData['gioiTinh'] ?? null,
                    'soHoChieu' => $hkData['soHoChieu'] ?? null,
                    'soCMND' => $hkData['soCMND'] ?? null,
                    'quocTich' => $hkData['quocTich'] ?? 'VN',
                ]);

                // Tạo vé (tạm thời, sẽ cập nhật sau khi có response từ Duffel)
                Ve::create([
                    'maDonHang' => $donHang->maDonHang,
                    'maChuyenBay' => 0, // Tạm thời
                    'maHanhKhach' => $hanhKhach->maHanhKhach,
                    'maGiaVe' => 0, // Tạm thời
                    'giaMuaThucTe' => 0,
                    'trangThaiVe' => 'pending',
                    'maGhe' => null,
                    'duffel_slice_id' => null,
                    'duffel_segment_id' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tao don hang thanh cong',
                'data' => [
                    'maDonHang' => $donHang->maDonHang,
                    'maCodeDonHang' => $maCodeDonHang,
                    'trangThai' => 'pending',
                    'note' => 'Can implement Duffel Create Order API de hoan thanh booking'
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Loi khi tao don hang: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xem chi tiết đơn hàng
     * GET /api/client/dat-ve/don-hang/{id}
     */
    public function xemDonHang($id)
    {
        $donHang = DonHang::with(['ves.hanh_khach'])
            ->where('maDonHang', $id)
            ->first();

        if (!$donHang) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay don hang',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lay thong tin don hang thanh cong',
            'data' => $donHang,
        ], 200);
    }

    /**
     * Danh sách đơn hàng của user
     * GET /api/client/dat-ve/don-hang
     */
    public function danhSachDonHang(Request $request)
    {
        $query = DonHang::with(['ves.hanh_khach'])
            ->orderBy('ngayDat', 'desc');

        // Nếu có user đăng nhập, lọc theo user
        if (auth()->check()) {
            $query->where('maTK', auth()->id());
        }

        $perPage = (int) $request->get('perPage', 10);
        $danhSach = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lay danh sach don hang thanh cong',
            'data' => $danhSach->items(),
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page' => $danhSach->lastPage(),
                'per_page' => $danhSach->perPage(),
                'total' => $danhSach->total(),
            ],
        ], 200);
    }
}
