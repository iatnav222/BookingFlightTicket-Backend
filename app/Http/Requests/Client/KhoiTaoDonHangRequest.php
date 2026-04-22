<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class KhoiTaoDonHangRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có quyền thực hiện request này không.
     */
    public function authorize(): bool
    {
        return true; // Cho phép tất cả mọi người (kể cả khách vãng lai)
    }

    /**
     * Các quy tắc validation áp dụng cho request.
     */
    public function rules(): array
    {
        return [
            'duffel_offer_id' => 'required|string',
            'tong_tien' => 'required|numeric|min:0',
            
            // Thông tin liên hệ
            'thong_tin_lien_he' => 'required|array',
            'thong_tin_lien_he.email' => 'required|email',
            'thong_tin_lien_he.soDienThoai' => 'required|string|min:9|max:15',

            // Danh sách hành khách
            'hanh_khach' => 'required|array|min:1',
            'hanh_khach.*.ho' => 'required|string|max:50',
            'hanh_khach.*.ten' => 'required|string|max:50',
            'hanh_khach.*.ngaySinh' => 'required|date_format:Y-m-d',
            'hanh_khach.*.gioiTinh' => 'required|string|in:Nam,Nu,Nữ,Khac,Khác',
            'hanh_khach.*.loaiHanhKhach' => 'required|string|in:adult,child,infant',
            'hanh_khach.*.soCMND' => 'nullable|string|max:20',
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi (Tiếng Việt) để hiển thị cho FE dễ hiểu.
     */
    public function messages(): array
    {
        return [
            'duffel_offer_id.required' => 'Mã chuyến bay (Offer ID) là bắt buộc.',
            'tong_tien.required' => 'Tổng tiền là bắt buộc.',
            'tong_tien.numeric' => 'Tổng tiền phải là dạng số.',
            
            'thong_tin_lien_he.required' => 'Vui lòng cung cấp thông tin liên hệ.',
            'thong_tin_lien_he.email.required' => 'Email liên hệ là bắt buộc.',
            'thong_tin_lien_he.email.email' => 'Email liên hệ không đúng định dạng.',
            'thong_tin_lien_he.soDienThoai.required' => 'Số điện thoại liên hệ là bắt buộc.',
            'thong_tin_lien_he.soDienThoai.min' => 'Số điện thoại không hợp lệ.',
            
            'hanh_khach.required' => 'Danh sách hành khách không được để trống.',
            'hanh_khach.min' => 'Phải có ít nhất 1 hành khách.',
            
            'hanh_khach.*.ho.required' => 'Họ của hành khách là bắt buộc.',
            'hanh_khach.*.ten.required' => 'Tên của hành khách là bắt buộc.',
            'hanh_khach.*.ngaySinh.required' => 'Ngày sinh của hành khách là bắt buộc.',
            'hanh_khach.*.ngaySinh.date_format' => 'Ngày sinh phải theo định dạng YYYY-MM-DD.',
            'hanh_khach.*.gioiTinh.required' => 'Giới tính của hành khách là bắt buộc.',
            'hanh_khach.*.loaiHanhKhach.required' => 'Loại hành khách (người lớn/trẻ em) là bắt buộc.',
        ];
    }

    /**
     * Xử lý trả về lỗi định dạng JSON thay vì redirect về trang trước đó (dành cho API)
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Dữ liệu đầu vào không hợp lệ.',
            'errors' => $validator->errors()
        ], 422));
    }
}
