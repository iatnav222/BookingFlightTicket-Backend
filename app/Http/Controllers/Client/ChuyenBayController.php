<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

            // Lấy tỷ giá từ config
            $exchangeRates = config('currency.exchange_rates');

            // Format lại data để giống với format cũ cho FE
            $danhSachFormatted = [];
            foreach ($ketQuaDuffel['danh_sach'] as $offer) {
                $maSanBayDi = $offer['di']['ma_san_bay'];
                $maSanBayDen = $offer['den']['ma_san_bay'];
                $tienTe = $offer['tien_te'];
                $giaGoc = (float) $offer['gia'];
                
                // Quy đổi giá sang VND
                $giaVND = $giaGoc;
                if (isset($exchangeRates[$tienTe])) {
                    $giaVND = $giaGoc * $exchangeRates[$tienTe];
                }
                $giaVND = round($giaVND, 0); // Làm tròn
                
                // Lấy thông tin từ segments
                $segments = $offer['chi_tiet_chuyen'] ?? [];
                $firstSegment = $segments[0] ?? null;
                $lastSegment = $segments[count($segments) - 1] ?? $firstSegment;
                
                // Thông tin hãng từ Duffel
                $tenHang = $offer['hang_xac_nhan'] ?? 'Unknown Airline';
                $logoHang = $offer['logo_hang'];
                $maHang = null;
                
                if ($firstSegment) {
                    $maHang = $firstSegment['marketing_carrier']['iata_code'] ?? null;
                    if (!$tenHang || $tenHang === 'Unknown Airline') {
                        $tenHang = $firstSegment['marketing_carrier']['name'] ?? 'Unknown Airline';
                    }
                }
                
                // Thông tin sân bay từ Duffel
                // Lấy origin từ segment đầu tiên, destination từ segment cuối cùng
                $tenSanBayDi = $firstSegment['origin']['city_name'] ?? $firstSegment['origin']['name'] ?? $maSanBayDi;
                $tenSanBayDen = $lastSegment['destination']['city_name'] ?? $lastSegment['destination']['name'] ?? $maSanBayDen;
                
                $danhSachFormatted[] = [
                    // ID từ Duffel
                    'maChuyenBay' => $offer['id'],
                    
                    // Thông tin hãng từ Duffel
                    'hang_hang_khong' => [
                        'tenHang' => $tenHang,
                        'logo' => $logoHang,
                        'maCode' => $maHang,
                    ],
                    
                    // Thông tin sân bay từ Duffel
                    'san_bay_di' => [
                        'maCode' => $maSanBayDi,
                        'tenSanBay' => $tenSanBayDi,
                        'hinhAnh' => null, // Duffel không cung cấp hình ảnh sân bay
                    ],
                    'san_bay_den' => [
                        'maCode' => $maSanBayDen,
                        'tenSanBay' => $tenSanBayDen,
                        'hinhAnh' => null,
                    ],
                    
                    // Thời gian
                    'ngayGioCatCanh' => $offer['di']['thoi_gian'],
                    'ngayGioHaCanh' => $offer['den']['thoi_gian'],
                    
                    // Giá đã quy đổi sang VND
                    'gia_thap_nhat' => $giaVND,
                    'tien_te' => 'VND',
                    'gia_goc' => $giaGoc,
                    'tien_te_goc' => $tienTe,
                    
                    // Thông tin bổ sung
                    'chi_tiet_chuyen' => $offer['chi_tiet_chuyen'],
                    'soGheConLai' => 999,
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

    /**
     * Xem chi tiết chuyến bay + giá vé từ Duffel offer
     * GET /api/client/chuyen-bay/{offer_id}
     */
    public function chiTiet(Request $request, $id)
    {
        // $id là duffel_offer_id
        // Vì offer chỉ tồn tại trong thời gian ngắn, 
        // FE nên lưu offer data từ danh sách tìm kiếm
        
        // Tạm thời trả về message hướng dẫn
        return response()->json([
            'success' => false,
            'message' => 'Offer chi tiet can duoc lay tu danh sach tim kiem. Offer ID chi ton tai trong thoi gian ngan.',
            'note' => 'FE nen luu offer data tu API /chuyen-bay khi tim kiem, khong goi lai API nay',
            'duffel_offer_id' => $id,
        ], 400);
    }
}
