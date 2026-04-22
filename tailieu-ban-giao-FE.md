
## Quy trình Đặt vé (Booking Flow)

### Bước 1: Chọn vé và nhập thông tin hành khách

**Giao diện FE:**
- Người dùng chọn 1 chuyến bay (Offer) từ danh sách đã lấy từ Duffel API.
- Người dùng nhập thông tin hành khách (tên, ngày sinh, giới tính,...).
- Chọn tiếp tục để sang trang Thanh toán.

**Gọi API khởi tạo đơn hàng (Giữ chỗ):**
Lúc người dùng bấm "Tiếp tục" (hoặc "Đặt vé"), gọi API này để lưu đơn hàng vào Database và lấy `maDonHang`.

- **Endpoint:** `POST /api/client/dat-ve/khoi-tao`
- **Body Request:**
```json
{
  "duffel_offer_id": "off_0000B5W9dwQUWjTC5hvbFp",
  "tong_tien": 1122930,
  "duffel_raw_data": { "...": "toàn bộ object offer của Duffel nếu cần lưu lại" },
  "thong_tin_lien_he": {
    "email": "nguyenvana@gmail.com",
    "soDienThoai": "0912345678"
  },
  "hanh_khach": [
    {
      "ho": "Nguyen",
      "ten": "Van A",
      "ngaySinh": "1995-10-25",
      "gioiTinh": "Nam",
      "loaiHanhKhach": "adult",
      "soCMND": "0123456789",
      "duffel_passenger_id": "pas_0000B5W9dqUe7qRT33wEwP"
    }
  ]
}
```

> **[LƯU Ý CỰC QUAN TRỌNG VỀ VALIDATION]**
> API này đã được Backend rào bằng hệ thống Validation rất khắt khe để đảm bảo chuẩn của Duffel:
> 1. `ngaySinh` bắt buộc phải đúng chuẩn `YYYY-MM-DD` (Ví dụ: 1995-10-25).
> 2. Mảng `hanh_khach` phải có ít nhất 1 phần tử, bắt buộc điền các trường `ho`, `ten`, `ngaySinh`, `gioiTinh` (Nam/Nữ/Khác), `loaiHanhKhach` (adult/child/infant).
> 3. Cụm `thong_tin_lien_he` bắt buộc phải có `email` (chuẩn định dạng) và `soDienThoai`.
> Nếu gửi sai, API sẽ tự động văng ra mã lỗi `422` kèm một cục JSON báo lỗi bằng tiếng Việt (VD: "Email liên hệ không đúng định dạng"). FE nên bắt lỗi 422 này để hiển thị Toast báo lỗi cho người dùng.

- **Response trả về (Thành công):**
```json
{
  "success": true,
  "message": "Khởi tạo đơn hàng thành công, vui lòng tiếp tục thanh toán",
  "data": {
    "maDonHang": 123,
    "maCodeDonHang": "XY8A1ZOP",
    "tongTien": 1122930
  }
}
```

---

### Bước 2: Chọn phương thức thanh toán VNPay và Redirect

**Giao diện FE:**
- Ở trang thanh toán, hiển thị tổng tiền và các tuỳ chọn (VD: Thanh toán qua VNPAY).
- Khi người dùng click "Thanh toán", dùng `maDonHang` lấy được ở Bước 1 để gọi API tạo URL VNPay.

- **Endpoint:** `POST /api/client/thanh-toan/vnpay`
- **Body Request:**
```json
{
  "maDonHang": 123,
  "bank_code": "" // (Không bắt buộc, để trống sẽ hiển thị cổng VNPAY mặc định)
}
```
- **Response trả về:**
```json
{
  "success": true,
  "message": "Tạo link VNPay thành công",
  "data": {
    "payment_url": "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html?vnp_Amount=112293000&vnp_Command=pay&..."
  }
}
```

---

### Bước 3: Nhận kết quả trả về từ VNPAY

Sau khi người dùng thanh toán xong trên giao diện VNPAY (nhập thẻ test Sandbox), VNPAY sẽ tự động redirect về đường dẫn Backend API `GET /api/client/dat-ve/vnpay-return`.

Backend sau khi kiểm tra mã bảo mật và lưu kết quả vào bảng `thanh_toan`, sẽ **tự động chuyển hướng (redirect) ngược lại về FRONTEND**.

**Giao diện FE cần chuẩn bị:**
Frontend cần tạo một route Component (ví dụ `/thanh-toan/ket-qua`). Backend sẽ redirect về trang này với các tham số trên URL:
- Thành công: `https://booking-flight-ticket-frontend.vercel.app/thanh-toan/ket-qua?status=success&maCode=XY8A1ZOP`
- Thất bại: `https://booking-flight-ticket-frontend.vercel.app/thanh-toan/ket-qua?status=failed&maCode=XY8A1ZOP&reason=vnpay_error`

**FE lấy thông số từ URL** (ví dụ dùng `new URLSearchParams(window.location.search)` trong React/Vue) để hiển thị ra thông báo "Đặt vé thành công" hoặc "Giao dịch thất bại" cho khách hàng.

---

