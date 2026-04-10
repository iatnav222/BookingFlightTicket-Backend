<?php

namespace App\Http\Controllers\Admin;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Controllers\Controller;
use App\Models\HangHangKhong;
use App\Models\MayBay;
use App\Models\ChuyenBay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HangHangKhongController extends Controller
{
    // Lấy public_id từ URL Cloudinary để xóa ảnh
    private function getCloudinaryPublicId(string $url): string
    {
        // URL dạng: https://res.cloudinary.com/cloud/image/upload/v123/hang_hang_khong/abc.jpg
        // Lấy phần từ folder trở đi, bỏ đuôi file
        $path = parse_url($url, PHP_URL_PATH); // /cloud/image/upload/v123/hang_hang_khong/abc.jpg
        // Bỏ phần /cloud/image/upload/vXXX/
        $parts = explode('/upload/', $path);
        if (count($parts) < 2) return ''; // Trả về rỗng nếu URL không hợp lệ
        $afterUpload = $parts[1]; // v123/hang_hang_khong/abc.jpg
        // Bỏ version nếu có (bắt đầu bằng v + số)
        $afterUpload = preg_replace('/^v\d+\//', '', $afterUpload);
        // Bỏ đuôi file (.jpg, .png, ...)
        return preg_replace('/\.[^.]+$/', '', $afterUpload);
    }

    // API Lấy danh sách hãng hàng không
    public function index(Request $request)
    {
        $query = HangHangKhong::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tenHang', 'like', "%{$search}%")
                  ->orWhere('maCode', 'like', "%{$search}%");
            });
        }

        if ($request->has('trangThai') && $request->trangThai !== null) {
            $query->where('trangThai', $request->trangThai);
        }

        $danhSach = $query->orderBy('maHang', 'desc')->get();

        // logo trong DB đã là URL Cloudinary đầy đủ, chỉ cần map sang logo_url cho FE
        $danhSach->transform(function ($item) {
            $item->logo_url = $item->logo ?? null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách hãng hàng không thành công',
            'data'    => $danhSach
        ], 200);
    }

    // API Thêm mới hãng hàng không
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenHang'   => 'required|string|max:100',
            'maCode'    => 'required|string|max:10|unique:hang_hang_khong,maCode',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ghiChu'    => 'nullable|string',
            'trangThai' => 'nullable|boolean',
        ], [
            'tenHang.required' => 'Vui lòng nhập tên hãng hàng không.',
            'maCode.required'  => 'Vui lòng nhập mã code hãng.',
            'maCode.unique'    => 'Mã code này đã tồn tại.',
            'logo.image'       => 'File tải lên phải là hình ảnh.',
            'logo.mimes'       => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'logo.max'         => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('logo');
        $data['trangThai'] = $data['trangThai'] ?? 1;

        // Upload logo lên Cloudinary
        if ($request->hasFile('logo')) {
            $uploadResult = Cloudinary::upload($request->file('logo')->getRealPath(), [
                'folder' => 'hang_hang_khong'
            ]);
            // Lưu URL đầy đủ vào DB
            $data['logo'] = $uploadResult->getSecurePath();
        }

        $hangHangKhong = HangHangKhong::create($data);
        $hangHangKhong->logo_url = $hangHangKhong->logo ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Thêm hãng hàng không thành công',
            'data'    => $hangHangKhong
        ], 201);
    }

    // API Lấy chi tiết một hãng hàng không
    public function show($id)
    {
        $hangHangKhong = HangHangKhong::find($id);

        if (!$hangHangKhong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hãng hàng không này.'
            ], 404);
        }

        $hangHangKhong->logo_url = $hangHangKhong->logo ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin chi tiết thành công',
            'data'    => $hangHangKhong
        ], 200);
    }

    // API Cập nhật hãng hàng không
    public function update(Request $request, $id)
    {
        $hangHangKhong = HangHangKhong::find($id);

        if (!$hangHangKhong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hãng hàng không này.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tenHang'   => 'sometimes|required|string|max:100',
            'maCode'    => 'sometimes|required|string|max:10|unique:hang_hang_khong,maCode,' . $id . ',maHang',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'ghiChu'    => 'nullable|string',
            'trangThai' => 'nullable|boolean',
        ], [
            'tenHang.required' => 'Vui lòng nhập tên hãng.',
            'maCode.required'  => 'Vui lòng nhập mã code.',
            'maCode.unique'    => 'Mã code này đã tồn tại.',
            'logo.image'       => 'File tải lên phải là hình ảnh.',
            'logo.mimes'       => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'logo.max'         => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('logo');

        // Nếu có ảnh mới: xóa ảnh cũ trên Cloudinary rồi upload ảnh mới
        if ($request->hasFile('logo')) {
            if ($hangHangKhong->logo) {
                Cloudinary::destroy($this->getCloudinaryPublicId($hangHangKhong->logo));
            }
            $uploadResult = Cloudinary::upload($request->file('logo')->getRealPath(), [
                'folder' => 'hang_hang_khong'
            ]);
            $data['logo'] = $uploadResult->getSecurePath();
        }

        $hangHangKhong->update($data);
        $hangHangKhong->logo_url = $hangHangKhong->logo ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hãng hàng không thành công',
            'data'    => $hangHangKhong
        ], 200);
    }

    // API Xóa hãng hàng không
    public function destroy($id)
    {
        $hangHangKhong = HangHangKhong::find($id);

        if (!$hangHangKhong) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hãng hàng không để xóa.'
            ], 404);
        }

        // Kiểm tra ràng buộc
        $coMayBay    = MayBay::where('maHang', $id)->exists();
        $coChuyenBay = ChuyenBay::where('maHang', $id)->exists();

        if ($coMayBay || $coChuyenBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa! Hãng hàng không này đang có máy bay hoặc chuyến bay hoạt động.'
            ], 400);
        }

        // Xóa ảnh trên Cloudinary (nếu có)
        if ($hangHangKhong->logo) {
            Cloudinary::destroy($this->getCloudinaryPublicId($hangHangKhong->logo));
        }

        $hangHangKhong->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa hãng hàng không thành công.'
        ], 200);
    }
}