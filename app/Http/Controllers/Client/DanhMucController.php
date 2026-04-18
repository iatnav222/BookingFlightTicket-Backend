<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\DichVu\DichVuDuffel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DanhMucController extends Controller
{
    /**
     * Danh sách sân bay từ Duffel API
     * GET /api/client/san-bay?search=hanoi
     */
    public function danhSachSanBay(Request $request)
    {
        try {
            $token = config('services.duffel.access_token');
            $searchQuery = $request->get('search', '');
            
            // Nếu không có search query, trả về danh sách sân bay phổ biến
            if (empty($searchQuery)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lay danh sach san bay thanh cong',
                    'nguon' => 'static',
                    'data' => $this->getDanhSachSanBayPhoBien(),
                ], 200);
            }
            
            // Gọi Duffel API để lấy danh sách sân bay
            $response = Http::withToken($token)
                ->withHeaders([
                    'Duffel-Version' => config('services.duffel.api_version', 'v2'),
                    'Accept' => 'application/json',
                ])
                ->get('https://api.duffel.com/places/suggestions', [
                    'query' => $searchQuery, // Duffel dùng 'query' không phải 'search'
                ]);

            if (!$response->successful()) {
                // Nếu lỗi, trả về danh sách tĩnh
                return response()->json([
                    'success' => true,
                    'message' => 'Lay danh sach san bay thanh cong (fallback)',
                    'nguon' => 'static',
                    'data' => $this->getDanhSachSanBayPhoBien(),
                ], 200);
            }

            $data = $response->json('data', []);
            
            // Format lại data cho FE
            $danhSach = [];
            foreach ($data as $place) {
                // Chỉ lấy airports
                if ($place['type'] === 'airport') {
                    $danhSach[] = [
                        'maSanBay' => $place['id'],
                        'maCode' => $place['iata_code'] ?? $place['iata_city_code'] ?? null,
                        'tenSanBay' => $place['name'],
                        'thanhPho' => $place['city_name'] ?? $place['name'],
                        'quocGia' => $place['iata_country_code'] ?? null,
                        'hinhAnh' => null,
                        'latitude' => $place['latitude'] ?? null,
                        'longitude' => $place['longitude'] ?? null,
                    ];
                }
            }
            
            // Nếu không tìm thấy kết quả, trả về danh sách tĩnh
            if (empty($danhSach)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Khong tim thay ket qua, hien thi san bay pho bien',
                    'nguon' => 'static',
                    'data' => $this->getDanhSachSanBayPhoBien(),
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lay danh sach san bay thanh cong',
                'nguon' => 'duffel',
                'data' => $danhSach,
            ], 200);

        } catch (\Exception $e) {
            // Fallback về danh sách tĩnh nếu có lỗi
            return response()->json([
                'success' => true,
                'message' => 'Lay danh sach san bay thanh cong (fallback)',
                'nguon' => 'static',
                'data' => $this->getDanhSachSanBayPhoBien(),
            ], 200);
        }
    }
    
    /**
     * Danh sách sân bay phổ biến (fallback)
     */
    private function getDanhSachSanBayPhoBien()
    {
        return [
            [
                'maSanBay' => 'arp_han_vn',
                'maCode' => 'HAN',
                'tenSanBay' => 'Noi Bai International Airport',
                'thanhPho' => 'Hanoi',
                'quocGia' => 'VN',
                'hinhAnh' => null,
                'latitude' => 21.221192,
                'longitude' => 105.807178,
            ],
            [
                'maSanBay' => 'arp_sgn_vn',
                'maCode' => 'SGN',
                'tenSanBay' => 'Tan Son Nhat International Airport',
                'thanhPho' => 'Ho Chi Minh City',
                'quocGia' => 'VN',
                'hinhAnh' => null,
                'latitude' => 10.818797,
                'longitude' => 106.651856,
            ],
            [
                'maSanBay' => 'arp_dad_vn',
                'maCode' => 'DAD',
                'tenSanBay' => 'Da Nang International Airport',
                'thanhPho' => 'Da Nang',
                'quocGia' => 'VN',
                'hinhAnh' => null,
                'latitude' => 16.043917,
                'longitude' => 108.199144,
            ],
            [
                'maSanBay' => 'arp_pqc_vn',
                'maCode' => 'PQC',
                'tenSanBay' => 'Phu Quoc International Airport',
                'thanhPho' => 'Phu Quoc',
                'quocGia' => 'VN',
                'hinhAnh' => null,
                'latitude' => 10.227025,
                'longitude' => 103.967169,
            ],
            [
                'maSanBay' => 'arp_cam_vn',
                'maCode' => 'CXR',
                'tenSanBay' => 'Cam Ranh International Airport',
                'thanhPho' => 'Nha Trang',
                'quocGia' => 'VN',
                'hinhAnh' => null,
                'latitude' => 11.998153,
                'longitude' => 109.219372,
            ],
        ];
    }

    /**
     * Danh sách hãng hàng không từ Duffel API
     * GET /api/client/hang-hang-khong?search=vietnam
     */
    public function danhSachHangHangKhong(Request $request)
    {
        try {
            $token = config('services.duffel.access_token');
            
            // Gọi Duffel API để lấy danh sách airlines
            $response = Http::withToken($token)
                ->withHeaders([
                    'Duffel-Version' => config('services.duffel.api_version', 'v2'),
                    'Accept' => 'application/json',
                ])
                ->get('https://api.duffel.com/air/airlines');

            if (!$response->successful()) {
                throw new \Exception('Loi khi goi Duffel API: ' . $response->body());
            }

            $data = $response->json('data', []);
            
            // Lọc theo search nếu có
            $search = strtolower($request->get('search', ''));
            
            // Format lại data cho FE
            $danhSach = [];
            foreach ($data as $airline) {
                // Lọc theo search
                if ($search && 
                    !str_contains(strtolower($airline['name']), $search) && 
                    !str_contains(strtolower($airline['iata_code'] ?? ''), $search)) {
                    continue;
                }
                
                $danhSach[] = [
                    'maHang' => $airline['id'],
                    'tenHang' => $airline['name'],
                    'maCode' => $airline['iata_code'] ?? null,
                    'logo' => $airline['logo_symbol_url'] ?? null,
                    'logoLockup' => $airline['logo_lockup_url'] ?? null,
                    'trangThai' => 1,
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Lay danh sach hang hang khong thanh cong',
                'nguon' => 'duffel',
                'data' => $danhSach,
                'total' => count($danhSach),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Loi khi lay danh sach hang hang khong: ' . $e->getMessage(),
            ], 500);
        }
    }
}
