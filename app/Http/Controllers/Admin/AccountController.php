<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Taikhoan;
use App\Models\LichSuDangNhap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    // =============================================
    // API Lấy danh sách tài khoản (GET)
    // /api/admin/tai-khoan
    // Hỗ trợ: tìm kiếm theo keyword, lọc theo quyen, phân trang
    // =============================================
    public function index(Request $request)
    {
        $query = Taikhoan::query();

        // 1. Tìm kiếm theo username, email hoặc họ tên
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('email',    'like', "%{$search}%")
                  ->orWhere('hoten',    'like', "%{$search}%");
            });
        }

        // 2. Lọc theo quyền (admin / user)
        if ($request->filled('quyen')) {
            $query->where('quyen', $request->quyen);
        }

        // 3. Lọc theo trạng thái (1: Hoạt động, 0: Bị khóa)
        if ($request->has('trangThai') && $request->trangThai !== null) {
            $query->where('trangThai', $request->trangThai);
        }

        // 4. Phân trang (mặc định 10 bản ghi / trang)
        $perPage = $request->get('perPage', 10);
        $danhSach = $query->orderBy('maTK', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách tài khoản thành công',
            'data'    => $danhSach->items(),
            'pagination' => [
                'current_page' => $danhSach->currentPage(),
                'last_page'    => $danhSach->lastPage(),
                'per_page'     => $danhSach->perPage(),
                'total'        => $danhSach->total(),
            ]
        ], 200);
    }

    // =============================================
    // API Lấy chi tiết 1 tài khoản + lịch sử đăng nhập (GET)
    // /api/admin/tai-khoan/{id}
    // =============================================
    public function show($id)
    {
        $taikhoan = Taikhoan::find($id);

        if (!$taikhoan) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản này.'
            ], 404);
        }

        // Lấy lịch sử đăng nhập, mới nhất lên đầu, phân trang 10
        $lichSu = LichSuDangNhap::where('maTK', $id)
                    ->orderBy('ngayDangNhap', 'desc')
                    ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin tài khoản thành công',
            'data'    => [
                'taikhoan'        => $taikhoan,
                'lich_su_dang_nhap' => $lichSu->items(),
                'pagination'      => [
                    'current_page' => $lichSu->currentPage(),
                    'last_page'    => $lichSu->lastPage(),
                    'per_page'     => $lichSu->perPage(),
                    'total'        => $lichSu->total(),
                ]
            ]
        ], 200);
    }

    // =============================================
    // API Thêm mới tài khoản (POST)
    // /api/admin/tai-khoan
    // =============================================
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:taikhoan,username',
            'email'    => 'required|email|max:100|unique:taikhoan,email',
            'password' => 'required|string|min:6',
            'hoten'    => 'nullable|string|max:100',
            'quyen'    => 'required|in:admin,user',
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.unique'   => 'Tên đăng nhập này đã tồn tại.',
            'email.required'    => 'Vui lòng nhập email.',
            'email.unique'      => 'Email này đã được sử dụng.',
            'email.email'       => 'Email không đúng định dạng.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'quyen.required'    => 'Vui lòng chọn quyền tài khoản.',
            'quyen.in'          => 'Quyền không hợp lệ (chỉ được là admin hoặc user).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        $taikhoan = Taikhoan::create([
            'username'   => $request->username,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'hoten'      => $request->hoten,
            'quyen'      => $request->quyen,
            'trangThai'  => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thêm tài khoản thành công!',
            'data'    => $taikhoan
        ], 201);
    }

    // =============================================
    // API Cập nhật tài khoản (PUT)
    // /api/admin/tai-khoan/{id}
    // =============================================
    public function update(Request $request, $id)
    {
        $taikhoan = Taikhoan::find($id);

        if (!$taikhoan) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản này.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'username'  => 'sometimes|required|string|max:50|unique:taikhoan,username,' . $id . ',maTK',
            'email'     => 'sometimes|required|email|max:100|unique:taikhoan,email,' . $id . ',maTK',
            'password'  => 'nullable|string|min:6',
            'hoten'     => 'nullable|string|max:100',
            'quyen'     => 'sometimes|required|in:admin,user',
            'trangThai' => 'nullable|boolean',
        ], [
            'username.unique' => 'Tên đăng nhập này đã tồn tại.',
            'email.unique'    => 'Email này đã được sử dụng.',
            'email.email'     => 'Email không đúng định dạng.',
            'password.min'    => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'quyen.in'        => 'Quyền không hợp lệ.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Cập nhật các trường được gửi lên
        if ($request->filled('username'))  $taikhoan->username  = $request->username;
        if ($request->filled('email'))     $taikhoan->email     = $request->email;
        if ($request->filled('hoten'))     $taikhoan->hoten     = $request->hoten;
        if ($request->filled('quyen'))     $taikhoan->quyen     = $request->quyen;
        if ($request->has('trangThai'))    $taikhoan->trangThai = $request->trangThai;

        // Chỉ đổi mật khẩu nếu admin có nhập vào
        if ($request->filled('password')) {
            $taikhoan->password = Hash::make($request->password);
        }

        $taikhoan->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật tài khoản thành công!',
            'data'    => $taikhoan
        ], 200);
    }

    // =============================================
    // API Khóa/Mở khóa tài khoản (DELETE)
    // /api/admin/tai-khoan/{id}
    // - Đảo trangThai (khóa/mở khóa), không xóa vĩnh viễn để tránh vướng FK dữ liệu
    // =============================================
    public function destroy($id)
    {
        $taikhoan = Taikhoan::find($id);

        if (!$taikhoan) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy tài khoản này.'
            ], 404);
        }

        // Không cho phép tự khóa chính mình (nếu hệ thống có Auth)
        if (Auth::id() == $taikhoan->maTK && $taikhoan->trangThai == 1) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tự khóa tài khoản của chính mình!'
            ], 400);
        }

        // Khóa/Mở khóa (áp dụng cho cả admin và user)
        $taikhoan->trangThai = ($taikhoan->trangThai == 1) ? 0 : 1;
        $taikhoan->save();

        $message = ($taikhoan->trangThai == 0)
            ? 'Đã KHÓA tài khoản thành công.'
            : 'Đã MỞ KHÓA tài khoản thành công.';

        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $taikhoan
        ], 200);
    }
}