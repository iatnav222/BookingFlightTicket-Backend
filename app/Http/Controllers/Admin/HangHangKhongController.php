<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HangHangKhong;
use App\Models\MayBay;
use App\Models\ChuyenBay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class HangHangKhongController extends Controller
{
    /**
     * Lấy danh sách Hãng hàng không
     */
    /**
     * Lấy danh sách Hãng hàng không (Có link ảnh đầy đủ)
     */
    public function index(Request $request)
    {
        $query = HangHangKhong::query();

        // 1. Tìm kiếm và Lọc (giữ nguyên logic cũ)
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('tenHang', 'LIKE', "%{$search}%")
                  ->orWhere('maCode', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('trangThai') && $request->trangThai !== null) {
            $query->where('trangThai', $request->input('trangThai'));
        }

        // 2. Lấy danh sách
        $danhSach = $query->orderBy('maHang', 'desc')->get();

        // 3. BIẾN ĐỔI DỮ LIỆU: Thêm link ảnh đầy đủ cho từng phần tử
        $danhSach->transform(function ($item) {
            // Tạo thêm một thuộc tính ảo logo_url cho mỗi hãng
            $item->logo_url = $item->logo ? asset('storage/' . $item->logo) : null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách hãng hàng không thành công',
            'data'    => $danhSach
        ], 200);
    }
    /**
     * Thêm mới Hãng hàng không (Có xử lý upload Logo)
     */
    public function store(Request $request)
    {
        // 1. Kiểm tra dữ liệu (Nâng cấp rule cho biến 'logo' thành dạng file ảnh)
        $validator = Validator::make($request->all(), [
            'tenHang'   => 'required|string|max:100',
            'maCode'    => 'required|string|max:10|unique:hang_hang_khong,maCode',
            'quocGia'   => 'required|string|max:100',
            // Chỉ chấp nhận file ảnh, dung lượng tối đa 2MB (2048 KB)
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'trangThai' => 'nullable|boolean'
        ], [
            'tenHang.required' => 'Vui lòng nhập tên hãng hàng không.',
            'maCode.required'  => 'Vui lòng nhập mã code hãng.',
            'maCode.unique'    => 'Mã code này đã tồn tại.',
            'quocGia.required' => 'Vui lòng nhập quốc gia.',
            'logo.image'       => 'File tải lên phải là hình ảnh.',
            'logo.mimes'       => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'logo.max'         => 'Dung lượng ảnh không được vượt quá 2MB.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('logo'); // Lấy hết dữ liệu, trừ cái file logo ra để xử lý riêng
        
        if (!isset($data['trangThai'])) {
            $data['trangThai'] = 1;
        }

        // 2. Xử lý Upload file Logo
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            // Đổi tên file để không bị trùng (Ví dụ: 1698765432_VN.png)
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Lưu file vào thư mục: storage/app/public/logos
            $path = $file->storeAs('logos', $filename, 'public');
            
            // Gán đường dẫn vào mảng data để lưu xuống Database (chỉ lưu: logos/ten_file.png)
            $data['logo'] = $path;
        }

        // 3. Lưu vào Database
        $hangHangKhong = HangHangKhong::create($data);

        // 4. Trả về kết quả (Kèm theo link ảnh đầy đủ cho FE hiển thị)
        // Nếu có logo, tạo ra một link dạng http://localhost:8000/storage/logos/...
        $hangHangKhong->logo_url = $hangHangKhong->logo ? asset('storage/' . $hangHangKhong->logo) : null;

        return response()->json([
            'success' => true,
            'message' => 'Thêm hãng hàng không thành công',
            'data'    => $hangHangKhong
        ], 201);
    }
    /**
     * Lấy chi tiết một Hãng hàng không
     */
    public function show($id)
    {
        // 1. Tìm hãng hàng không theo khóa chính (maHang)
        $hangHangKhong = HangHangKhong::find($id);

        // 2. Nếu không tìm thấy, trả về lỗi 404 (Not Found)
        if (!$hangHangKhong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hãng hàng không này.'
            ], 404);
        }

        // 3. Gắn thêm link ảnh đầy đủ tương tự như hàm index và store
        $hangHangKhong->logo_url = $hangHangKhong->logo ? asset('storage/' . $hangHangKhong->logo) : null;

        // 4. Trả về kết quả
        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin chi tiết thành công',
            'data'    => $hangHangKhong
        ], 200);
    }
    public function update(Request $request, $id)
    {
        // 1. Tìm hãng hàng không
        $hangHangKhong = HangHangKhong::find($id);

        if (!$hangHangKhong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hãng hàng không này.'
            ], 404);
        }

        // 2. Kiểm tra dữ liệu
        $validator = Validator::make($request->all(), [
            'tenHang'   => 'required|string|max:100',
            // Lưu ý chỗ này: Bỏ qua kiểm tra trùng lặp với chính maHang hiện tại
            'maCode'    => 'required|string|max:10|unique:hang_hang_khong,maCode,' . $id . ',maHang',
            'quocGia'   => 'required|string|max:100',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'trangThai' => 'nullable|boolean'
        ], [
            'tenHang.required' => 'Vui lòng nhập tên hãng.',
            'maCode.required'  => 'Vui lòng nhập mã code.',
            'maCode.unique'    => 'Mã code này đã tồn tại.',
            'quocGia.required' => 'Vui lòng nhập quốc gia.',
            'logo.image'       => 'File tải lên phải là hình ảnh.',
            'logo.mimes'       => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'logo.max'         => 'Dung lượng ảnh không được vượt quá 2MB.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('logo');

        // 3. Xử lý Ảnh (Nếu người dùng có chọn ảnh mới)
        if ($request->hasFile('logo')) {
            // Xóa ảnh cũ đi (nếu có) để đỡ tốn dung lượng server
            if ($hangHangKhong->logo) {
                Storage::disk('public')->delete($hangHangKhong->logo);
            }

            // Lưu ảnh mới
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('logos', $filename, 'public');
            
            $data['logo'] = $path;
        }

        // 4. Cập nhật vào Database
        $hangHangKhong->update($data);

        // 5. Trả về kết quả cho FE
        $hangHangKhong->logo_url = $hangHangKhong->logo ? asset('storage/' . $hangHangKhong->logo) : null;

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hãng hàng không thành công',
            'data'    => $hangHangKhong
        ], 200);
    }
    public function destroy($id)
    {
        $hangHangKhong = HangHangKhong::find($id);

        if (!$hangHangKhong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hãng hàng không để xóa.'
            ], 404);
        }

        // KIỂM TRA ĐIỀU KIỆN RÀNG BUỘC
        $coMayBay = MayBay::where('maHang', $id)->exists();
        
        // Lưu ý: Đoạn này giả sử bảng chuyen_bay của bạn có chứa cột maHang.
        // Nếu cấu trúc DB của bạn khác (ví dụ chuyến bay chỉ liên kết với máy bay) thì hãy điều chỉnh lại tên cột cho khớp nhé.
        $coChuyenBay = ChuyenBay::where('maHang', $id)->exists();

        // Nếu có máy bay HOẶC có chuyến bay thì chặn lại ngay
        if ($coMayBay || $coChuyenBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa! Hãng hàng không này đang có máy bay hoặc chuyến bay hoạt động.'
            ], 400); 
        }

        // XỬ LÝ XÓA FILE ẢNH TRÊN SERVER
        if ($hangHangKhong->logo) {
            if (Storage::disk('public')->exists($hangHangKhong->logo)) {
                Storage::disk('public')->delete($hangHangKhong->logo);
            }
        }

        // Xóa bản ghi trong Database
        $hangHangKhong->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa hãng hàng không và logo liên quan thành công.'
        ], 200);
    }
}
