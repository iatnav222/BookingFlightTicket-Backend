<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChuyenBay;
use App\Models\GiaVe;
use App\Models\SanBay;
use App\Models\HangHangKhong;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChuyenBayController extends Controller
{
    // Danh sach / tim kiem chuyen bay cho client
    // GET /api/client/chuyen-bay?maSanBayDi=&maSanBayDen=&ngayBay=YYYY-MM-DD&nguon=duffel
    public function danhSach(Request $request, \App\DichVu\DichVuDuffel $dichVuDuffel)
    {
        // MẶC ĐỊNH: Luôn gọi Duffel API (nguồn chính)
        try {
            // Tham so map sang api format cua Duffel
            $thamSoDuffel = [
                'origin' => $request->get('maSanBayDi', 'HAN'),
                'destination' => $request->get('maSanBayDen', 'SGN'),
                'departureDate' => $request->get('ngayBay', date('Y-m-d')),
                'adults' => (int) $request->get('adults', 1)
            ];

            $ketQuaDuffel = $dichVuDuffel->timChuyenBay($thamSoDuffel);

            // Lấy danh sách sân bay từ DB để map tên
            $sanBayMap = SanBay::pluck('tenSanBay', 'maCode')->toArray();
            $sanBayHinhAnhMap = SanBay::pluck('hinhAnh', 'maCode')->toArray();

            // Format lại data để giống với format cũ cho FE
            $danhSachFormatted = [];
            foreach ($ketQuaDuffel['danh_sach'] as $offer) {
                $maSanBayDi = $offer['di']['ma_san_bay'];
                $maSanBayDen = $offer['den']['ma_san_bay'];
                
                $danhSachFormatted[] = [
                    // ID từ Duffel (không phải từ DB)
                    'maChuyenBay' => $offer['id'], // Dùng offer_id làm ID tạm
                    
                    // Thông tin hãng
                    'hang_hang_khong' => [
                        'tenHang' => $offer['hang_xac_nhan'],
                        'logo' => $offer['logo_hang'],
                        'maCode' => null,
                    ],
                    
                    // Thông tin sân bay (map với DB nếu có)
                    'san_bay_di' => [
                        'maCode' => $maSanBayDi,
                        'tenSanBay' => $sanBayMap[$maSanBayDi] ?? $maSanBayDi,
                        'hinhAnh' => $sanBayHinhAnhMap[$maSanBayDi] ?? null,
                    ],
                    'san_bay_den' => [
                        'maCode' => $maSanBayDen,
                        'tenSanBay' => $sanBayMap[$maSanBayDen] ?? $maSanBayDen,
                        'hinhAnh' => $sanBayHinhAnhMap[$maSanBayDen] ?? null,
                    ],
                    
                    // Thời gian
                    'ngayGioCatCanh' => $offer['di']['thoi_gian'],
                    'ngayGioHaCanh' => $offer['den']['thoi_gian'],
                    
                    // Giá (giá thấp nhất từ offer)
                    'gia_thap_nhat' => $offer['gia'],
                    'tien_te' => $offer['tien_te'],
                    
                    // Thông tin bổ sung
                    'chi_tiet_chuyen' => $offer['chi_tiet_chuyen'],
                    'soGheConLai' => 999, // Không biết chính xác, để số lớn
                    'trangThai' => 1,
                    
                    // Lưu raw data để đặt vé sau
                    'duffel_offer_id' => $offer['id'],
                    'duffel_raw' => $offer['raw'],
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Lay danh sach chuyen bay thanh cong',
                'nguon' => 'duffel',
                'data' => $danhSachFormatted,
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => count($danhSachFormatted),
                    'total' => count($danhSachFormatted),
                ],
                'meta' => [
                    'offer_request_id' => $ketQuaDuffel['raw_offer_request_id']
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loi khi goi Duffel: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Chi tiet chuyen bay (offer) tu Duffel
    // GET /api/client/chuyen-bay/{offer_id}
    public function chiTiet(Request $request, $id)
    {
        // $id ở đây là duffel_offer_id
        // Vì FE gọi với offer_id từ danh sách, ta cần trả về chi tiết
        
        // Tạm thời trả về thông báo cần implement
        // Hoặc có thể lưu offer vào session/cache khi tìm kiếm
        
        return response()->json([
            'success' => true,
            'message' => 'Chi tiet offer',
            'data' => [
                'duffel_offer_id' => $id,
                'note' => 'Offer details should be retrieved from Duffel API or cache'
            ],
        ], 200);
    }
}
