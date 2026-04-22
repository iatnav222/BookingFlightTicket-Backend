<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DonHang;
use App\Models\HanhKhach;
use App\Models\Ve;
use App\Models\ThanhToan;
use App\DichVu\DichVuDuffel;
use App\Http\Requests\Client\KhoiTaoDonHangRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatVeController extends Controller
{
    /**
     * BƯỚC 1: NHẬP THÔNG TIN VÀ KHỞI TẠO ĐƠN HÀNG (GIỮ CHỖ)
     */
    public function khoiTaoDonHang(KhoiTaoDonHangRequest $request)
    {
        // Vì đã qua form request nên data chắc chắn đã an toàn, hợp lệ
        $offerId = $request->input('duffel_offer_id');
        $danhSachHanhKhach = $request->input('hanh_khach', []);
        $thongTinLienHe = $request->input('thong_tin_lien_he', []);
        $tongTien = $request->input('tong_tien', 0);
        $offerRaw = $request->input('duffel_raw_data', null);

        DB::beginTransaction();
        try {
            // 1. Tạo đơn hàng (Trạng thái = 0: Chờ thanh toán)
            $maCodeDonHang = strtoupper(Str::random(8)); // Sinh mã đơn hàng ngẫu nhiên
            
            $donHang = new DonHang();
            $donHang->maCodeDonHang = $maCodeDonHang;
            $donHang->ngayDat = now();
            $donHang->tongTien = $tongTien;
            $donHang->phuongThucThanhToan = null;
            $donHang->trangThai = 0; 
            $donHang->thongTinLienHe = json_encode($thongTinLienHe);
            
            // Lưu field của Duffel
            $donHang->duffel_offer_id = $offerId;
            if ($offerRaw) {
                $donHang->duffel_raw_data = is_array($offerRaw) ? json_encode($offerRaw) : $offerRaw;
            }
            
            // Nếu có user đang đăng nhập
            if (auth('sanctum')->check()) {
                $donHang->maTK = auth('sanctum')->id();
            }

            $donHang->save();

            // 2. Lưu thông tin hành khách và vé tương ứng
            foreach ($danhSachHanhKhach as $hkData) {
                // Tạo hành khách
                $hk = new HanhKhach();
                $hk->ho = $hkData['ho'] ?? '';
                $hk->ten = $hkData['ten'] ?? '';
                $hk->hoTen = trim($hk->ho . ' ' . $hk->ten);
                $hk->ngaySinh = $hkData['ngaySinh'] ?? null;
                $hk->gioiTinh = $hkData['gioiTinh'] ?? 'Khong xac dinh';
                $hk->loaiHanhKhach = $hkData['loaiHanhKhach'] ?? 'adult';
                $hk->soCMND = $hkData['soCMND'] ?? null;
                $hk->email = $hkData['email'] ?? ($thongTinLienHe['email'] ?? null);
                $hk->sdt = $hkData['soDienThoai'] ?? ($thongTinLienHe['soDienThoai'] ?? null);
                $hk->save();

                // Tạo vé tham chiếu tới đơn hàng và hành khách
                // Lưu ý: Do DB hiện tại yêu cầu maChuyenBay, maGiaVe nhưng ta đang dùng Duffel
                // Tạm thời ta set maChuyenBay = 0 hoặc null nếu DB cho phép.
                // Các bạn nên ALTER TABLE Ve để cho phép maChuyenBay nullable.
                $ve = new Ve();
                $ve->maDonHang = $donHang->maDonHang;
                $ve->maHanhKhach = $hk->maHanhKhach;
                $ve->maChuyenBay = $hkData['maChuyenBay'] ?? 0; // Để tạm 0 tránh lỗi nếu ko nullable
                $ve->maGiaVe = 0; 
                $ve->giaMuaThucTe = $tongTien / count($danhSachHanhKhach); // chia đều tạm
                $ve->trangThaiVe = 'ChoXuatVe';
                
                // Save passenger ID of Duffel if passed from frontend
                if (isset($hkData['duffel_passenger_id'])) {
                    $ve->duffel_passenger_data = json_encode(['id' => $hkData['duffel_passenger_id']]);
                }

                $ve->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Khởi tạo đơn hàng thành công, vui lòng tiếp tục thanh toán',
                'data' => [
                    'maDonHang' => $donHang->maDonHang,
                    'maCodeDonHang' => $donHang->maCodeDonHang,
                    'tongTien' => $donHang->tongTien
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi khởi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * BƯỚC 2: TẠO LINK THANH TOÁN VNPAY
     */
    public function taoThanhToanVNPay(Request $request)
    {
        $maDonHang = $request->input('maDonHang');
        $donHang = DonHang::find($maDonHang);

        if (!$donHang) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Cấu hình VNPay (Nên để trong config/services.php hoặc .env)
        $vnp_TmnCode = env('VNP_TMN_CODE', 'YOUR_VNPAY_TMN_CODE'); // Thay bằng mã của bạn
        $vnp_HashSecret = env('VNP_HASH_SECRET', 'YOUR_VNPAY_HASH_SECRET'); // Thay bằng chuỗi bí mật của bạn
        $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
        
        // Frontend hoặc Backend URL xử lý return
        $vnp_Returnurl = url('/api/client/dat-ve/vnpay-return'); 

        $vnp_TxnRef = $donHang->maCodeDonHang . '_' . time(); // Mã giao dịch unique
        $vnp_OrderInfo = "Thanh toan don hang may bay " . $donHang->maCodeDonHang;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $donHang->tongTien * 100; // VNPay yêu cầu x100
        $vnp_Locale = 'vn';
        $vnp_IpAddr = $request->ip();

        $inputData = array(
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_Returnurl,
            "vnp_TxnRef" => $vnp_TxnRef
        );

        if ($request->has('bank_code') && $request->input('bank_code') != '') {
            $inputData['vnp_BankCode'] = $request->input('bank_code');
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $vnp_Url . "?" . $query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo link VNPay thành công',
            'data' => [
                'payment_url' => $vnp_Url
            ]
        ]);
    }

    /**
     * BƯỚC 3: XỬ LÝ KẾT QUẢ TRẢ VỀ TỪ VNPAY
     */
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET', 'YOUR_VNPAY_HASH_SECRET');

        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }
        
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Mã đơn hàng ban đầu được tách từ TxnRef (Do lúc tạo có nối thêm time())
        $txnRefParts = explode('_', $inputData['vnp_TxnRef']);
        $maCodeDonHang = $txnRefParts[0];

        $donHang = DonHang::where('maCodeDonHang', $maCodeDonHang)->first();

        // FRONTEND URL ĐỂ REDIRECT VỀ (Cấu hình domain FE của bạn)
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/thanh-toan/ket-qua';

        if ($secureHash == $vnp_SecureHash) {
            // Tạo bản ghi lịch sử thanh toán vào bảng thanh_toan
            if ($donHang) {
                $thanhToan = new ThanhToan();
                $thanhToan->maDonHang = $donHang->maDonHang;
                $thanhToan->phuongThuc = 'VNPAY';
                $thanhToan->maGiaoDich = $inputData['vnp_TransactionNo'] ?? null;
                $thanhToan->soTien = $inputData['vnp_Amount'] ? ($inputData['vnp_Amount'] / 100) : 0;
                $thanhToan->ngayThanhToan = now();
                $thanhToan->noiDung = $inputData['vnp_OrderInfo'] ?? 'Thanh toan VNPay';
            }

            if ($inputData['vnp_ResponseCode'] == '00') {
                // Thanh toán thành công
                if ($donHang && $donHang->trangThai == 0) {
                    $donHang->trangThai = 1; // Đã thanh toán
                    $donHang->phuongThucThanhToan = 'VNPAY';
                    $donHang->save();

                    $thanhToan->trangThai = 'ThanhCong';
                    $thanhToan->save();

                    // LÝ TƯỞNG: Gọi API Duffel ở đây để Create Order chính thức
                    // $duffel = new DichVuDuffel();
                    // $duffel->taoDonHang($donHang->duffel_offer_id, ...);
                }

                // Redirect về FE kèm params thành công
                return redirect()->away($frontendUrl . "?status=success&maCode=" . $maCodeDonHang);
            } else {
                // Thanh toán thất bại
                if ($donHang) {
                    $donHang->trangThai = 2; // Thanh toán lỗi/hủy
                    $donHang->save();
                    
                    $thanhToan->trangThai = 'ThatBai';
                    $thanhToan->save();
                }
                return redirect()->away($frontendUrl . "?status=failed&maCode=" . $maCodeDonHang . "&reason=vnpay_error");
            }
        } else {
            // Chữ ký không hợp lệ
            return redirect()->away($frontendUrl . "?status=failed&maCode=" . $maCodeDonHang . "&reason=invalid_signature");
        }
    }
}
