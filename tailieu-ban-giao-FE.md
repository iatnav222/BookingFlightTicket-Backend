# TÀI LIỆU BÀN GIAO API ĐẶT VÉ VÀ THANH TOÁN CHO FRONTEND

Tài liệu này mô tả luồng thực hiện đặt vé (booking flow) dành cho frontend, sử dụng các endpoint mới được tạo. Các bạn Frontend (FE) vui lòng đọc kỹ luồng này để tích hợp.

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
      "ngaySinh": "1990-01-01",
      "gioiTinh": "Nam",
      "loaiHanhKhach": "adult",
      "soCMND": "0123456789",
      "duffel_passenger_id": "pas_0000B5W9dqUe7qRT33wEwP"
    }
  ]
}
```
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

*Lưu ý cho Backend/FE:* Nếu chưa đăng nhập, truyền lên token thì API sẽ tự động lưu `maTK` vào đơn hàng. Nếu khách vãng lai thì `maTK` sẽ null.

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

**Thao tác của FE:** Nhận được `payment_url` này, Frontend chuyển hướng người dùng bằng lệnh:
```javascript
window.location.href = response.data.payment_url;
```

---

### Bước 3: Nhận kết quả trả về từ VNPAY

Sau khi người dùng thanh toán xong trên giao diện VNPAY (nhập thẻ test Sandbox), VNPAY sẽ tự động redirect về đường dẫn Backend API `GET /api/client/dat-ve/vnpay-return`.

Backend sau khi kiểm tra mã bảo mật, sẽ **tự động chuyển hướng (redirect) ngược lại về FRONTEND**.

**Giao diện FE cần chuẩn bị:**
Frontend cần tạo một route ví dụ `/thanh-toan/ket-qua`. Backend sẽ redirect về route này với các tham số trên URL:
- Thành công: `http://localhost:5173/thanh-toan/ket-qua?status=success&maCode=XY8A1ZOP`
- Thất bại: `http://localhost:5173/thanh-toan/ket-qua?status=failed&maCode=XY8A1ZOP&reason=vnpay_error`

**FE lấy thông số từ URL** (ví dụ dùng `new URLSearchParams(window.location.search)`) để hiển thị ra thông báo "Đặt vé thành công" hoặc "Giao dịch thất bại" cho khách hàng.

---

## Các vấn đề Backend cần cấu hình (Bàn giao cho BE/Admin)

1. **Cấu hình VNPAY ở file `.env`:**
Để VNPay hoạt động, bạn cần cấu hình các Key test Sandbox vào file `.env` của thư mục backend:
```env
VNP_TMN_CODE=Mã_TMN_Của_Bạn
VNP_HASH_SECRET=Chuỗi_Secret_Của_Bạn
FRONTEND_URL=http://localhost:5173
```
*(Nếu làm bằng React/Vue ở port khác, nhớ sửa `FRONTEND_URL` cho đúng để BE redirect lại đúng trang FE).*

2. **Database (Bảng Ve và ChuyenBay):**
Vì chuyển sang xài API ngoài, các bảng cũ có dính khoá ngoại như `maChuyenBay` hay `maGiaVe` sẽ bị xung đột. 
- BE phải dùng phần mềm quản lý Database (HeidiSQL/phpMyAdmin) để `ALTER TABLE ve MODIFY COLUMN maChuyenBay int NULL`, và `maGiaVe int NULL`.
- Hoặc hiện tại BE đang set cứng = `0` để chữa cháy trong controller.

3. **Chức năng xuất vé chính thức trên Duffel (Mở rộng):**
Hiện tại khi thanh toán xong, DB cập nhật trạng thái đơn hàng = 1 (Đã thanh toán). Để xuất vé thật trên Duffel, Backend cần viết thêm hàm `taoDonHang` trong `DichVuDuffel.php` và gọi nó sau khi nhận được trạng thái thanh toán thành công ở hàm `vnpayReturn`. (Đã take note trong code).
