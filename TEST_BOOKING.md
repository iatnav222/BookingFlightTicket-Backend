# 🧪 Test API Đặt Vé - Hướng dẫn chi tiết

## ⚠️ Vấn đề đã fix:

### Lỗi trước đây:
1. ❌ Validation quá strict - yêu cầu `offer_data.duffel_offer_id` bắt buộc
2. ❌ Lỗi nếu database chưa có columns `duffel_*`
3. ❌ Không linh hoạt với format data từ FE

### Đã sửa:
1. ✅ Validation linh hoạt hơn - chấp nhận nhiều format
2. ✅ Tự động bỏ qua duffel fields nếu DB chưa có columns
3. ✅ Lấy giá từ nhiều nguồn: `gia_thap_nhat`, `gia`, `tongTien`
4. ✅ Lấy offer ID từ nhiều nguồn: `duffel_offer_id`, `maChuyenBay`, `id`

---

## 🚀 Cách test API đặt vé

### Bước 1: Tìm chuyến bay trước
```bash
curl "http://localhost:8000/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1"
```

**Lưu lại toàn bộ object của 1 chuyến bay**, ví dụ:
```json
{
  "maChuyenBay": "off_0000AZJlYKzG4Y8NAjmfDr",
  "hang_hang_khong": {
    "tenHang": "Vietnam Airlines",
    "logo": "https://...",
    "maCode": "VN"
  },
  "san_bay_di": {
    "maCode": "HAN",
    "tenSanBay": "Hanoi"
  },
  "san_bay_den": {
    "maCode": "SGN",
    "tenSanBay": "Ho Chi Minh City"
  },
  "ngayGioCatCanh": "2026-05-01T08:00:00",
  "ngayGioHaCanh": "2026-05-01T10:00:00",
  "gia_thap_nhat": 2500000,
  "tien_te": "VND",
  "duffel_offer_id": "off_0000AZJlYKzG4Y8NAjmfDr"
}
```

---

### Bước 2: Đặt vé với data vừa lấy

#### Option 1: Dùng curl (Terminal)
```bash
curl -X POST http://localhost:8000/api/client/dat-ve/tao-don-hang \
  -H "Content-Type: application/json" \
  -d '{
    "offer_data": {
      "duffel_offer_id": "off_0000AZJlYKzG4Y8NAjmfDr",
      "gia_thap_nhat": 2500000,
      "hang_hang_khong": {
        "tenHang": "Vietnam Airlines"
      },
      "san_bay_di": {
        "tenSanBay": "Hanoi"
      },
      "san_bay_den": {
        "tenSanBay": "Ho Chi Minh City"
      },
      "ngayGioCatCanh": "2026-05-01T08:00:00"
    },
    "hanh_khach": [
      {
        "hoTen": "Nguyen Van A",
        "ngaySinh": "1990-01-01",
        "gioiTinh": "Nam",
        "loaiHanhKhach": "NguoiLon",
        "email": "test@example.com",
        "sdt": "0123456789"
      }
    ],
    "thongTinLienHe": {
      "ten": "Nguyen Van A",
      "email": "test@example.com",
      "sdt": "0123456789"
    }
  }'
```

#### Option 2: Dùng Postman
1. Import file `Postman_Collection.json`
2. Chọn request "4. Tạo đơn hàng (Đặt vé)"
3. Sửa `offer_data` bằng data từ Bước 1
4. Click **Send**

#### Option 3: Dùng JavaScript (Frontend)
```javascript
// Lấy offer từ localStorage hoặc state
const selectedOffer = JSON.parse(localStorage.getItem('selectedOffer'));

// Gọi API đặt vé
const response = await fetch('http://localhost:8000/api/client/dat-ve/tao-don-hang', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    offer_data: selectedOffer, // Toàn bộ offer object
    hanh_khach: [
      {
        hoTen: "Nguyen Van A",
        ngaySinh: "1990-01-01",
        gioiTinh: "Nam",
        loaiHanhKhach: "NguoiLon",
        email: "test@example.com",
        sdt: "0123456789"
      }
    ],
    thongTinLienHe: {
      ten: "Nguyen Van A",
      email: "test@example.com",
      sdt: "0123456789"
    }
  })
});

const result = await response.json();
console.log(result);
```

---

## ✅ Response thành công

```json
{
  "success": true,
  "message": "Tao don hang thanh cong",
  "data": {
    "maDonHang": 1,
    "maCodeDonHang": "DH20260418120000ABCD",
    "tongTien": 2500000,
    "trangThai": "ChoThanhToan",
    "danhSachVe": [
      {
        "maVe": 1,
        "hanhKhach": "Nguyen Van A",
        "giaMuaThucTe": 2500000
      }
    ],
    "thongTinChuyenBay": {
      "hangHangKhong": "Vietnam Airlines",
      "sanBayDi": "Hanoi",
      "sanBayDen": "Ho Chi Minh City",
      "ngayGioBay": "2026-05-01T08:00:00"
    }
  }
}
```

---

## ❌ Các lỗi có thể gặp

### 1. Lỗi: "The offer_data field is required"
**Nguyên nhân:** Thiếu `offer_data` trong request body

**Giải pháp:** Đảm bảo gửi đầy đủ:
```json
{
  "offer_data": {...},
  "hanh_khach": [...],
  "thongTinLienHe": {...}
}
```

---

### 2. Lỗi: "Gia ve khong hop le"
**Nguyên nhân:** Không tìm thấy giá trong `offer_data`

**Giải pháp:** Đảm bảo `offer_data` có 1 trong các field:
- `gia_thap_nhat`
- `gia`
- `tongTien`

---

### 3. Lỗi: "Column 'duffel_offer_id' not found"
**Nguyên nhân:** Database chưa có columns mới

**Giải pháp:** 
1. Chạy file `DATABASE_UPDATE.sql`
2. Hoặc code đã tự động bỏ qua lỗi này (sau khi fix)

---

### 4. Lỗi: "SQLSTATE[23000]: Integrity constraint violation"
**Nguyên nhân:** Thiếu foreign key hoặc data không hợp lệ

**Giải pháp:**
- Kiểm tra `maTK` có tồn tại không (nếu đăng nhập)
- Kiểm tra các field bắt buộc trong `hanh_khach`

---

## 🔍 Debug

### Xem logs Laravel:
```bash
tail -f storage/logs/laravel.log
```

### Xem query SQL:
Thêm vào `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Support\Facades\DB;

public function boot()
{
    DB::listen(function($query) {
        \Log::info($query->sql);
        \Log::info($query->bindings);
    });
}
```

### Test trực tiếp trong Tinker:
```bash
php artisan tinker

# Test tạo đơn hàng
$donHang = \App\Models\DonHang::create([
    'maCodeDonHang' => 'TEST123',
    'tongTien' => 1000000,
    'trangThai' => 0,
    'thongTinLienHe' => '{"email":"test@test.com"}'
]);

# Kiểm tra
$donHang->maDonHang;
```

---

## 📊 Test cases

### Test 1: Đặt vé 1 người lớn ✅
```json
{
  "offer_data": {...},
  "hanh_khach": [
    {"hoTen": "Nguyen Van A", "loaiHanhKhach": "NguoiLon", ...}
  ],
  "thongTinLienHe": {...}
}
```

### Test 2: Đặt vé nhiều người ✅
```json
{
  "offer_data": {...},
  "hanh_khach": [
    {"hoTen": "Nguyen Van A", "loaiHanhKhach": "NguoiLon", ...},
    {"hoTen": "Nguyen Thi B", "loaiHanhKhach": "NguoiLon", ...},
    {"hoTen": "Nguyen Van C", "loaiHanhKhach": "TreEm", ...}
  ],
  "thongTinLienHe": {...}
}
```

### Test 3: Đặt vé không đăng nhập ✅
- Không cần header `Authorization`
- `maTK` sẽ là `null`

### Test 4: Đặt vé có đăng nhập ✅
```bash
curl -X POST http://localhost:8000/api/client/dat-ve/tao-don-hang \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{...}'
```

---

## ✅ Checklist test

- [ ] API tìm chuyến bay hoạt động
- [ ] Lưu được offer_data từ API tìm kiếm
- [ ] API đặt vé trả về success
- [ ] Kiểm tra database có record mới trong `don_hang`
- [ ] Kiểm tra database có record mới trong `hanh_khach`
- [ ] Kiểm tra database có record mới trong `ve`
- [ ] API xem đơn hàng hoạt động
- [ ] API danh sách đơn hàng hoạt động

---

## 🎯 Kết luận

**API đặt vé bây giờ:**
- ✅ Linh hoạt với nhiều format data
- ✅ Không bị lỗi nếu DB chưa update
- ✅ Validation hợp lý hơn
- ✅ Dễ debug hơn

**Nếu vẫn lỗi:**
1. Kiểm tra logs: `storage/logs/laravel.log`
2. Chạy `DATABASE_UPDATE.sql`
3. Test từng bước theo hướng dẫn trên
4. Gửi error message để debug

---

**Last Updated:** April 18, 2026  
**Status:** ✅ Đã fix validation và error handling
