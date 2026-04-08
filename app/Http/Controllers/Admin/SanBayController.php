<?php

namespace App\Http\Controllers\Admin;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Controllers\Controller;
use App\Models\SanBay;
use App\Models\ChuyenBay;
use Illuminate\Http\Request;

class SanBayController extends Controller
{
    // Lấy public_id từ URL Cloudinary để xóa ảnh
    private function getCloudinaryPublicId(string $url): string
    {
        $path        = parse_url($url, PHP_URL_PATH);
        $parts       = explode('/upload/', $path);
        $afterUpload = $parts[1];
        $afterUpload = preg_replace('/^v\d+\//', '', $afterUpload);
        return preg_replace('/\.[^.]+$/', '', $afterUpload);
    }

    // API Lấy danh sách sân bay
    public function index(Request $request)
    {
        $query = SanBay::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tenSanBay', 'like', "%{$search}%")
                  ->orWhere('maCode', 'like', "%{$search}%")
                  ->orWhere('thanhPho', 'like', "%{$search}%");
            });
        }

        if ($request->filled('thanhPho')) {
            $query->where('thanhPho', 'like', "%{$request->thanhPho}%");
        }

        $danhSach = $query->orderBy('maSanBay', 'asc')->get();

        $danhSach->transform(function ($item) {
            $item->hinh_anh_url = $item->hinhAnh ?? null;
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách sân bay thành công',
            'data'    => $danhSach
        ], 200);
    }

    // API Thêm mới sân bay
    public function store(Request $request)
    {
        $validated = $request->validate([
            'maCode'    => 'required|string|max:10|unique:san_bay,maCode',
            'tenSanBay' => 'required|string|max:150',
            'thanhPho'  => 'required|string|max:100',
            'hinhAnh'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'maCode.required'    => 'Vui lòng nhập mã IATA sân bay (VD: SGN, HAN).',
            'maCode.unique'      => 'Mã code này đã tồn tại.',
            'tenSanBay.required' => 'Vui lòng nhập tên sân bay.',
            'thanhPho.required'  => 'Vui lòng nhập thành phố.',
            'hinhAnh.image'      => 'File tải lên phải là hình ảnh.',
            'hinhAnh.mimes'      => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'hinhAnh.max'        => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        // Upload ảnh lên Cloudinary
        if ($request->hasFile('hinhAnh')) {
            $uploadResult = Cloudinary::upload($request->file('hinhAnh')->getRealPath(), [
                'folder' => 'san_bay'
            ]);
            $validated['hinhAnh'] = $uploadResult->getSecurePath();
        }

        $sanBay = SanBay::create($validated);
        $sanBay->hinh_anh_url = $sanBay->hinhAnh ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Thêm sân bay thành công!',
            'data'    => $sanBay
        ], 201);
    }

    // API Lấy chi tiết một sân bay
    public function show($id)
    {
        $sanBay = SanBay::find($id);

        if (!$sanBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sân bay này.'
            ], 404);
        }

        $sanBay->hinh_anh_url = $sanBay->hinhAnh ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin sân bay thành công',
            'data'    => $sanBay
        ], 200);
    }

    // API Cập nhật sân bay
    public function update(Request $request, $id)
    {
        $sanBay = SanBay::find($id);

        if (!$sanBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sân bay này.'
            ], 404);
        }

        $validated = $request->validate([
            'maCode'    => 'sometimes|required|string|max:10|unique:san_bay,maCode,' . $id . ',maSanBay',
            'tenSanBay' => 'sometimes|required|string|max:150',
            'thanhPho'  => 'sometimes|required|string|max:100',
            'hinhAnh'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'maCode.unique'  => 'Mã code này đã tồn tại.',
            'hinhAnh.image'  => 'File tải lên phải là hình ảnh.',
            'hinhAnh.mimes'  => 'Ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'hinhAnh.max'    => 'Dung lượng ảnh không được vượt quá 2MB.',
        ]);

        // Nếu có ảnh mới: xóa ảnh cũ trên Cloudinary rồi upload ảnh mới
        if ($request->hasFile('hinhAnh')) {
            if ($sanBay->hinhAnh) {
                Cloudinary::destroy($this->getCloudinaryPublicId($sanBay->hinhAnh));
            }
            $uploadResult = Cloudinary::upload($request->file('hinhAnh')->getRealPath(), [
                'folder' => 'san_bay'
            ]);
            $validated['hinhAnh'] = $uploadResult->getSecurePath();
        }

        $sanBay->update($validated);
        $sanBay->hinh_anh_url = $sanBay->hinhAnh ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sân bay thành công!',
            'data'    => $sanBay
        ], 200);
    }

    // API Xóa sân bay
    public function destroy($id)
    {
        $sanBay = SanBay::find($id);

        if (!$sanBay) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy sân bay để xóa.'
            ], 404);
        }

        $coChuyenBayDi  = ChuyenBay::where('maSanBayDi', $id)->exists();
        $coChuyenBayDen = ChuyenBay::where('maSanBayDen', $id)->exists();

        if ($coChuyenBayDi || $coChuyenBayDen) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa! Sân bay này đang có chuyến bay đi hoặc đến liên quan.'
            ], 400);
        }

        // Xóa ảnh trên Cloudinary (nếu có)
        if ($sanBay->hinhAnh) {
            Cloudinary::destroy($this->getCloudinaryPublicId($sanBay->hinhAnh));
        }

        $sanBay->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sân bay thành công!'
        ], 200);
    }
}