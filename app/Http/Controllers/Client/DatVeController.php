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
     *   "offer_data": {...}, // Toàn bộ offer data từ API tìm kiếm
     *   "hanh_khach": [{
     *     "ho": "Nguyen",
     *     "ten": "Van A", 
     *     "hoTen": "Nguyen Van A",
     *     "ngaySinh": "1990-01-01",
     *     "gioiTinh": "Nam",
     *     "loaiHanhKhach": "NguoiLon",
     *     "soHoChieu": "ABC123456",
     *     "soCMND": "123456789",
     *     "email": "test@example.com",
     *     "sdt": "0123456789"
     *   }],
     *   "thongTinLienHe": {
     *     "ten": "Nguyen Van A",
     *     "email": "test@example.com",
     *     "sdt": "0123456789"
     *   }
     * }
     */
    public function taoDonHang(Request $request)
    {
        $request->validate([
            'offer_data' => 'required|array',
            'offer_data.duffel_offer_id' => 'required|string',
            'offer_data.gia_thap_nhat' => 'required',
            'hanh_khach' => 'required|array|min:1',
            'hanh_khach.*.hoTen' => 'required|string',
            'thongTinLienHe' => 'required|array',
            'thongTinLienHe.email' => 'required|email',
            'thongTinLienHe.sdt' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $offerData = $request->offer_data;
            $duffelOfferId = $offerData['duffel_offer_id'];
            $tongTien = (float) $offerData['gia_thap_nhat'] * count($request->hanh_khach);
            
            // Tạo mã đơn hàng
            $maCodeDonHang = 'DH' . date('YmdHis') . strtoupper(Str::random(4));
            
            // Tạo đơn hàng
            $donHang = DonHang::create([
                'maTK' => auth()->id() ?? null,
                'maCodeDonHang' => $maCodeDonHang,
                'ngayDat' => now(),
                'tongTien' => $tongTien,
                'trangThai' => 0, // 0: Chờ thanh toán
                'phuongThucThanhToan' => $request->phuongThucThanhToan ?? 'VNPAY',
                'thongTinLienHe' => json_encode($request->thongTinLienHe),
                'duffel_offer_id' => $duffelOfferId,
                'duffel_order_id' => null,
                'duffel_booking_reference' => null,
                'duffel_raw_data' => json_encode($offerData),
            ]);

            // Tạo hành khách và vé
            $danhSachVe = [];
            foreach ($request->hanh_khach as $hkData) {
                // Tạo hành khách
                $hanhKhach = HanhKhach::create([
                    'maTK' => auth()->id() ?? null,
                    'hoTen' => $hkData['hoTen'],
                    'ngaySinh' => $hkData['ngaySinh'] ?? now()->subYears(25),
                    'gioiTinh' => $hkData['gioiTinh'] ?? 'Nam',
                    'loaiHanhKhach' => $hkData['loaiHanhKhach'] ?? 'NguoiLon',
                    'soCMND' => $hkData['soCMND'] ?? null,
                    'email' => $hkData['email'] ?? $request->thongTinLienHe['email'],
                    'sdt' => $hkData['sdt'] ?? $request->thongTinLienHe['sdt'],
                ]);

                // Tạo vé
                $ve = Ve::create([
                    'maDonHang' => $donHang->maDonHang,
                    'maChuyenBay' => 0, // Không dùng bảng chuyen_bay nữa
                    'maHanhKhach' => $hanhKhach->maHanhKhach,
                    'maGiaVe' => 0, // Không dùng bảng gia_ve nữa
                    'giaMuaThucTe' => (float) $offerData['gia_thap_nhat'],
                    'trangThaiVe' => 'DaDat',
                    'maTK' => auth()->id() ?? null,
                    'maGhe' => '0', // Sẽ cập nhật sau khi chọn ghế
                    'duffel_slice_id' => $offerData['duffel_offer_id'] ?? null,
                    'duffel_segment_id' => null,
                    'duffel_passenger_data' => json_encode($hkData),
                ]);

                $danhSachVe[] = [
                    'maVe' => $ve->maVe,
                    'hanhKhach' => $hanhKhach->hoTen,
                    'giaMuaThucTe' => $ve->giaMuaThucTe,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tao don hang thanh cong',
                'data' => [
                    'maDonHang' => $donHang->maDonHang,
                    'maCodeDonHang' => $maCodeDonHang,
                    'tongTien' => $tongTien,
                    'trangThai' => 'ChoThanhToan',
                    'danhSachVe' => $danhSachVe,
                    'thongTinChuyenBay' => [
                        'hangHangKhong' => $offerData['hang_hang_khong']['tenHang'] ?? null,
                        'sanBayDi' => $offerData['san_bay_di']['tenSanBay'] ?? null,
                        'sanBayDen' => $offerData['san_bay_den']['tenSanBay'] ?? null,
                        'ngayGioBay' => $offerData['ngayGioCatCanh'] ?? null,
                    ]
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

        // Parse thông tin chuyến bay từ duffel_raw_data
        $offerData = json_decode($donHang->duffel_raw_data, true);
        
        return response()->json([
            'success' => true,
            'message' => 'Lay thong tin don hang thanh cong',
            'data' => [
                'maDonHang' => $donHang->maDonHang,
                'maCodeDonHang' => $donHang->maCodeDonHang,
                'ngayDat' => $donHang->ngayDat,
                'tongTien' => $donHang->tongTien,
                'trangThai' => $this->getTrangThaiText($donHang->trangThai),
                'phuongThucThanhToan' => $donHang->phuongThucThanhToan,
                'thongTinLienHe' => json_decode($donHang->thongTinLienHe),
                'thongTinChuyenBay' => $offerData,
                'danhSachVe' => $donHang->ves->map(function($ve) {
                    return [
                        'maVe' => $ve->maVe,
                        'hanhKhach' => $ve->hanh_khach->hoTen ?? null,
                        'loaiHanhKhach' => $ve->hanh_khach->loaiHanhKhach ?? null,
                        'giaMuaThucTe' => $ve->giaMuaThucTe,
                        'trangThaiVe' => $ve->trangThaiVe,
                        'maGhe' => $ve->maGhe,
                    ];
                }),
            ],
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

        // Lọc theo trạng thái
        if ($request->filled('trangThai')) {
            $query->where('trangThai', $request->trangThai);
        }

        $perPage = (int) $request->get('perPage', 10);
        $danhSach = $query->paginate($perPage);

        $data = $danhSach->items();
        $formatted = array_map(function($donHang) {
            $offerData = json_decode($donHang->duffel_raw_data, true);
            return [
                'maDonHang' => $donHang->maDonHang,
                'maCodeDonHang' => $donHang->maCodeDonHang,
                'ngayDat' => $donHang->ngayDat,
                'tongTien' => $donHang->tongTien,
                'trangThai' => $this->getTrangThaiText($donHang->trangThai),
                'soLuongVe' => $donHang->ves->count(),
                'thongTinChuyenBay' => [
                    'hangHangKhong' => $offerData['hang_hang_khong']['tenHang'] ?? null,
                    'sanBayDi' => $offerData['san_bay_di']['tenSanBay'] ?? null,
                    'sanBayDen' => $offerData['san_bay_den']['tenSanBay'] ?? null,
                    'ngayGioBay' => $offerData['ngayGioCatCanh'] ?? null,
                ],
            ];
        }, $data);

        return response()->json([
            'success' => true,
            'message' => 'Lay danh sach don hang thanh cong',
            'data' => $formatted,
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page' => $danhSach->lastPage(),
                'per_page' => $danhSach->perPage(),
                'total' => $danhSach->total(),
            ],
        ], 200);
    }

    /**
     * Danh sách vé của user
     * GET /api/client/dat-ve/ve
     */
    public function danhSachVe(Request $request)
    {
        $query = Ve::with(['hanh_khach', 'don_hang'])
            ->orderBy('maVe', 'desc');

        // Nếu có user đăng nhập, lọc theo user
        if (auth()->check()) {
            $query->where('maTK', auth()->id());
        }

        // Lọc theo trạng thái vé
        if ($request->filled('trangThaiVe')) {
            $query->where('trangThaiVe', $request->trangThaiVe);
        }

        $perPage = (int) $request->get('perPage', 10);
        $danhSach = $query->paginate($perPage);

        $data = $danhSach->items();
        $formatted = array_map(function($ve) {
            $offerData = json_decode($ve->don_hang->duffel_raw_data ?? '{}', true);
            return [
                'maVe' => $ve->maVe,
                'maCodeDonHang' => $ve->don_hang->maCodeDonHang ?? null,
                'hanhKhach' => $ve->hanh_khach->hoTen ?? null,
                'loaiHanhKhach' => $ve->hanh_khach->loaiHanhKhach ?? null,
                'giaMuaThucTe' => $ve->giaMuaThucTe,
                'trangThaiVe' => $ve->trangThaiVe,
                'maGhe' => $ve->maGhe,
                'thongTinChuyenBay' => [
                    'hangHangKhong' => $offerData['hang_hang_khong']['tenHang'] ?? null,
                    'sanBayDi' => $offerData['san_bay_di']['tenSanBay'] ?? null,
                    'sanBayDen' => $offerData['san_bay_den']['tenSanBay'] ?? null,
                    'ngayGioBay' => $offerData['ngayGioCatCanh'] ?? null,
                ],
            ];
        }, $data);

        return response()->json([
            'success' => true,
            'message' => 'Lay danh sach ve thanh cong',
            'data' => $formatted,
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page' => $danhSach->lastPage(),
                'per_page' => $danhSach->perPage(),
                'total' => $danhSach->total(),
            ],
        ], 200);
    }

    /**
     * Hủy đơn hàng
     * PUT /api/client/dat-ve/don-hang/{id}/huy
     */
    public function huyDonHang($id)
    {
        $donHang = DonHang::where('maDonHang', $id)->first();

        if (!$donHang) {
            return response()->json([
                'success' => false,
                'message' => 'Khong tim thay don hang',
            ], 404);
        }

        // Kiểm tra quyền (chỉ user tạo đơn mới được hủy)
        if (auth()->check() && $donHang->maTK != auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Ban khong co quyen huy don hang nay',
            ], 403);
        }

        // Chỉ cho phép hủy đơn hàng chưa thanh toán
        if ($donHang->trangThai != 0) {
            return response()->json([
                'success' => false,
                'message' => 'Chi co the huy don hang chua thanh toan',
            ], 400);
        }

        // Cập nhật trạng thái
        $donHang->trangThai = 3; // 3: Đã hủy
        $donHang->save();

        // Cập nhật trạng thái vé
        Ve::where('maDonHang', $id)->update(['trangThaiVe' => 'DaHuy']);

        return response()->json([
            'success' => true,
            'message' => 'Huy don hang thanh cong',
        ], 200);
    }

    /**
     * Helper: Chuyển mã trạng thái thành text
     */
    private function getTrangThaiText($trangThai)
    {
        $map = [
            0 => 'ChoThanhToan',
            1 => 'DaThanhToan',
            2 => 'DaXacNhan',
            3 => 'DaHuy',
        ];
        return $map[$trangThai] ?? 'KhongXacDinh';
    }
}
