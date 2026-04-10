<?php

namespace App\Http\Controllers\Admin;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Controllers\Controller;
use App\Models\MaGiamGia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KhuyenMaiController extends Controller
{
    // Lấy public_id từ URL Cloudinary để xóa ảnh
    private function getCloudinaryPublicId(string $url): string
    {
        // URL dạng: https://res.cloudinary.com/cloud/image/upload/v123/folder/abc.jpg
        $path = parse_url($url, PHP_URL_PATH);
        $parts = explode('/upload/', $path);
        if (count($parts) < 2) return ''; // Trả về rỗng nếu URL không hợp lệ
        $afterUpload = $parts[1];
        $afterUpload = preg_replace('/^v\d+\//', '', $afterUpload);
        return preg_replace('/\.[^.]+$/', '', $afterUpload);
    }

    // =============================================
    // API Lấy danh sách khuyến mãi (GET)
    // /api/admin/khuyen-mai
    // Hỗ trợ: tìm kiếm, lọc theo trạng thái, phân trang
    // =============================================
    public function index(Request $request)
    {
        $query = MaGiamGia::query();

        // 1. Tìm kiếm theo tên khuyến mãi
        if ($request->filled('search')) {
            $search = $request->search;
            // Model MaGiamGia có trường 'ten_km'
            $query->where('ten_km', 'like', "%{$search}%");
        }

        // 2. Lọc theo trạng thái (0: Ẩn/Hết hạn, 1: Đang hoạt động)
        if ($request->has('trangThai') && $request->trangThai !== null) {
            $query->where('trangThai', $request->trangThai);
        }

        // 3. Phân trang (mặc định 10 bản ghi / trang) giống AccountController
        $perPage = $request->get('perPage', 10);
        // Model MaGiamGia có khóa chính là 'maGiamGia'
        $danhSach = $query->withCount('ves')->orderBy('maGiamGia', 'desc')->paginate($perPage);

        // Thêm URL ảnh và số lượng đã dùng vào response
        $items = collect($danhSach->items())->map(function ($item) {
            $item->anh_url = $item->anh ?? null;
            $item->daSuDung = $item->ves_count; // ves_count được tạo bởi withCount('ves')
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách khuyến mãi thành công',
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
    // API Thêm mới khuyến mãi (POST)
    // /api/admin/khuyen-mai
    // =============================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ten_km'       => 'required|string|max:255',
            'type'         => 'required|in:phan_tram,tien_mat',
            'giamPhanTram' => 'required|numeric|min:0',
            'soLuongToiDa' => 'required|integer|min:1',
            'ngayBatDau'   => 'required|date',
            'ngayKetThuc'  => 'required|date|after_or_equal:ngayBatDau',
            'trangThai'    => 'nullable|boolean',
            'anh'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dieukien'     => 'nullable|string',
        ], [
            'ten_km.required'            => 'Vui lòng nhập tên khuyến mãi.',
            'type.in'                    => 'Loại giảm giá chỉ được là phan_tram hoặc tien_mat.',
            'ngayKetThuc.after_or_equal' => 'Ngày kết thúc phải diễn ra sau hoặc bằng ngày bắt đầu.',
            'soLuongToiDa.min'           => 'Số lượng phải lớn hơn 0.',
            'giamPhanTram.min'           => 'Giá trị giảm không được âm.',
            'anh.image'                  => 'File tải lên phải là hình ảnh.',
            'anh.mimes'                  => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'anh.max'                    => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('anh');
        $data['trangThai'] = $request->has('trangThai') ? $request->trangThai : 1;

        // Upload ảnh lên Cloudinary
        if ($request->hasFile('anh')) {
            $uploadResult = Cloudinary::uploadApi()->upload($request->file('anh')->getRealPath(), [
                'folder' => 'khuyen_mai'
            ]);
            $data['anh'] = $uploadResult['secure_url'];
        }

        $maGiamGia = MaGiamGia::create($data);
        $maGiamGia->anh_url = $maGiamGia->anh ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Thêm khuyến mãi thành công!',
            'data'    => $maGiamGia
        ], 201);
    }

    // =============================================
    // API Lấy chi tiết 1 khuyến mãi (GET)
    // /api/admin/khuyen-mai/{id}
    // =============================================
    public function show($id)
    {
        $maGiamGia = MaGiamGia::withCount('ves')->find($id);

        if (!$maGiamGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khuyến mãi này.'
            ], 404);
        }

        $maGiamGia->anh_url = $maGiamGia->anh ?? null;
        $maGiamGia->daSuDung = $maGiamGia->ves_count;

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin khuyến mãi thành công',
            'data'    => $maGiamGia
        ], 200);
    }

    // =============================================
    // API Cập nhật khuyến mãi (PUT)
    // /api/admin/khuyen-mai/{id}
    // =============================================
    public function update(Request $request, $id)
    {
        $maGiamGia = MaGiamGia::withCount('ves')->find($id);

        if (!$maGiamGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khuyến mãi này.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'ten_km'       => 'sometimes|required|string|max:255',
            'type'         => 'sometimes|required|in:phan_tram,tien_mat',
            'giamPhanTram' => 'sometimes|required|numeric|min:0',
            'soLuongToiDa' => 'sometimes|required|integer|min:0',
            'ngayBatDau'   => 'sometimes|required|date',
            'ngayKetThuc'  => 'sometimes|required|date|after_or_equal:ngayBatDau',
            'trangThai'    => 'sometimes|nullable|boolean',
            'anh'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'dieukien'     => 'nullable|string',
        ], [
            'type.in'                    => 'Loại giảm giá chỉ được là phan_tram hoặc tien_mat.',
            'ngayKetThuc.after_or_equal' => 'Ngày kết thúc phải diễn ra sau hoặc bằng ngày bắt đầu.',
            'anh.image'                  => 'File tải lên phải là hình ảnh.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $data = $request->except('anh');

        // Rào chắn: Không cho phép sửa giảm số lượng thấp hơn số lượng đã có người dùng
        if ($request->has('soLuongToiDa') && $request->soLuongToiDa < $maGiamGia->ves_count) {
            return response()->json([
                'success' => false,
                'message' => "Không thể giảm số lượng! Mã này đã có {$maGiamGia->ves_count} lượt sử dụng."
            ], 400);
        }

        // Nếu có ảnh mới: xóa ảnh cũ trên Cloudinary rồi upload ảnh mới
        if ($request->hasFile('anh')) {
            if ($maGiamGia->anh) {
                Cloudinary::uploadApi()->destroy($this->getCloudinaryPublicId($maGiamGia->anh));
            }
            $uploadResult = Cloudinary::uploadApi()->upload($request->file('anh')->getRealPath(), [
                'folder' => 'khuyen_mai'
            ]);
            $data['anh'] = $uploadResult['secure_url'];
        }

        $maGiamGia->update($data);
        $maGiamGia->anh_url = $maGiamGia->anh ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật khuyến mãi thành công!',
            'data'    => $maGiamGia
        ], 200);
    }

    // =============================================
    // API Xóa khuyến mãi (DELETE)
    // /api/admin/khuyen-mai/{id}
    // =============================================
    public function destroy($id)
    {
        $maGiamGia = MaGiamGia::find($id);

        if (!$maGiamGia) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy khuyến mãi để xóa.'
            ], 404);
        }

        // Rào chắn: Nếu mã khuyến mãi đã được sử dụng trong bất kỳ vé nào
        if ($maGiamGia->ves()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa! Mã khuyến mãi này đã có khách hàng sử dụng, chỉ có thể Tắt trạng thái.'
            ], 400);
        }

        // Xóa ảnh trên Cloudinary (nếu có)
        if ($maGiamGia->anh) {
            Cloudinary::uploadApi()->destroy($this->getCloudinaryPublicId($maGiamGia->anh));
        }

        $maGiamGia->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa khuyến mãi thành công!'
        ], 200);
    }
}