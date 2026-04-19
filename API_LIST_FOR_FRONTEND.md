# 📋 DANH SÁCH API - GỬI CHO FRONTEND

**Base URL Production:** `https://bookingflightticket-backend-new.onrender.com`  
**Base URL Local:** `http://localhost:8000`

---

## 🎯 CLIENT APIs (Public - Không cần đăng nhập)

### 1. 🔍 TÌM KIẾM CHUYẾN BAY
```
GET /api/client/chuyen-bay
```

**Query Parameters:**
- `maSanBayDi` (required): Mã IATA sân bay đi (VD: HAN, SGN, DAD)
- `maSanBayDen` (required): Mã IATA sân bay đến (VD: SGN, HAN, DAD)
- `ngayBay` (required): Ngày bay (YYYY-MM-DD)
- `adults` (optional): Số người lớn (mặc định: 1)

**Example:**
```
GET https://bookingflightticket-backend-new.onrender.com/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1
```

**Response:**
```json
{
  "success": true,
  "message": "Lay danh sach chuyen bay thanh cong",
  "nguon": "duffel",
  "data": [
    {
      "maChuyenBay": "off_0000AZJlYKzG4Y8NAjmfDr",
      "hang_hang_khong": {
        "tenHang": "Vietnam Airlines",
        "logo": "https://assets.duffel.com/img/airlines/...",
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
      "gia_goc": 100.00,
      "tien_te_goc": "USD",
      "chi_tiet_chuyen": [...],
      "duffel_offer_id": "off_0000AZJlYKzG4Y8NAjmfDr"
    }
  ]
}
```

---

### 2. ✈️ DANH SÁCH SÂN BAY
```
GET /api/client/san-bay
```

**Query Parameters:**
- `search` (optional): Tìm kiếm theo tên/mã sân bay

**Example:**
```
GET https://bookingflightticket-backend-new.onrender.com/api/client/san-bay?search=hanoi
```

**Response:**
```json
{
  "success": true,
  "message": "Lay danh sach san bay thanh cong",
  "nguon": "duffel",
  "data": [
    {
      "maSanBay": "arp_han_vn",
      "maCode": "HAN",
      "tenSanBay": "Noi Bai International Airport",
      "thanhPho": "Hanoi",
      "quocGia": "VN"
    }
  ]
}
```

---

### 3. 🏢 DANH SÁCH HÃNG HÀNG KHÔNG
```
GET /api/client/hang-hang-khong
```

**Query Parameters:**
- `search` (optional): Tìm kiếm theo tên/mã hãng

**Example:**
```
GET https://bookingflightticket-backend-new.onrender.com/api/client/hang-hang-khong?search=vietnam
```

**Response:**
```json
{
  "success": true,
  "message": "Lay danh sach hang hang khong thanh cong",
  "nguon": "duffel",
  "data": [
    {
      "maHang": "arl_00009VME7DAGCGnosEpGW",
      "tenHang": "Vietnam Airlines",
      "maCode": "VN",
      "logo": "https://assets.duffel.com/img/airlines/...",
      "trangThai": 1
    }
  ]
}
```

---

### 4. 🎫 TẠO ĐỐN HÀNG (ĐẶT VÉ)
```
POST /api/client/dat-ve/tao-don-hang
```

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {token} (optional - nếu user đã đăng nhập)
```

**Body:**
```json
{
  "offer_data": {
    "duffel_offer_id": "off_0000AZJlYKzG4Y8NAjmfDr",
    "gia_thap_nhat": 2500000,
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
    "ngayGioHaCanh": "2026-05-01T10:00:00"
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
  },
  "phuongThucThanhToan": "VNPAY"
}
```

**Response:**
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

### 5. 📋 DANH SÁCH ĐƠN HÀNG
```
GET /api/client/dat-ve/don-hang
```

**Query Parameters:**
- `trangThai` (optional): 0=Chờ thanh toán, 1=Đã thanh toán, 2=Đã xác nhận, 3=Đã hủy
- `perPage` (optional): Số bản ghi/trang (mặc định: 10)

**Example:**
```
GET https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/don-hang?trangThai=1&perPage=10
```

**Response:**
```json
{
  "success": true,
  "message": "Lay danh sach don hang thanh cong",
  "data": [
    {
      "maDonHang": 1,
      "maCodeDonHang": "DH20260418120000ABCD",
      "ngayDat": "2026-04-18 12:00:00",
      "tongTien": 2500000,
      "trangThai": "DaThanhToan",
      "soLuongVe": 1,
      "thongTinChuyenBay": {
        "hangHangKhong": "Vietnam Airlines",
        "sanBayDi": "Hanoi",
        "sanBayDen": "Ho Chi Minh City",
        "ngayGioBay": "2026-05-01T08:00:00"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

### 6. 📄 CHI TIẾT ĐƠN HÀNG
```
GET /api/client/dat-ve/don-hang/{id}
```

**Example:**
```
GET https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/don-hang/1
```

**Response:**
```json
{
  "success": true,
  "message": "Lay thong tin don hang thanh cong",
  "data": {
    "maDonHang": 1,
    "maCodeDonHang": "DH20260418120000ABCD",
    "ngayDat": "2026-04-18 12:00:00",
    "tongTien": 2500000,
    "trangThai": "DaThanhToan",
    "phuongThucThanhToan": "VNPAY",
    "thongTinLienHe": {
      "ten": "Nguyen Van A",
      "email": "test@example.com",
      "sdt": "0123456789"
    },
    "thongTinChuyenBay": {...},
    "danhSachVe": [
      {
        "maVe": 1,
        "hanhKhach": "Nguyen Van A",
        "loaiHanhKhach": "NguoiLon",
        "giaMuaThucTe": 2500000,
        "trangThaiVe": "DaDat",
        "maGhe": "0"
      }
    ]
  }
}
```

---

### 7. 🎟️ DANH SÁCH VÉ
```
GET /api/client/dat-ve/ve
```

**Query Parameters:**
- `trangThaiVe` (optional): DaDat, DaHuy
- `perPage` (optional): Số bản ghi/trang (mặc định: 10)

**Example:**
```
GET https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/ve?trangThaiVe=DaDat&perPage=10
```

**Response:**
```json
{
  "success": true,
  "message": "Lay danh sach ve thanh cong",
  "data": [
    {
      "maVe": 1,
      "maCodeDonHang": "DH20260418120000ABCD",
      "hanhKhach": "Nguyen Van A",
      "loaiHanhKhach": "NguoiLon",
      "giaMuaThucTe": 2500000,
      "trangThaiVe": "DaDat",
      "maGhe": "0",
      "thongTinChuyenBay": {
        "hangHangKhong": "Vietnam Airlines",
        "sanBayDi": "Hanoi",
        "sanBayDen": "Ho Chi Minh City",
        "ngayGioBay": "2026-05-01T08:00:00"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

---

### 8. ❌ HỦY ĐƠN HÀNG
```
PUT /api/client/dat-ve/don-hang/{id}/huy
```

**Example:**
```
PUT https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/don-hang/1/huy
```

**Response Success:**
```json
{
  "success": true,
  "message": "Huy don hang thanh cong"
}
```

**Response Error (Không thể hủy):**
```json
{
  "success": false,
  "message": "Chi co the huy don hang chua thanh toan"
}
```

---

### 9. 🎁 DANH SÁCH KHUYẾN MÃI
```
GET /api/client/khuyen-mai
```

**Example:**
```
GET https://bookingflightticket-backend-new.onrender.com/api/client/khuyen-mai
```

**Response:**
```json
{
  "success": true,
  "message": "Lay danh sach khuyen mai thanh cong",
  "data": [
    {
      "maGiamGia": 1,
      "ten_km": "12.12 SUPER SALE",
      "giamPhanTram": 10.00,
      "ngayBatDau": "2025-12-12",
      "ngayKetThuc": "2025-12-15",
      "trangThai": 1
    }
  ]
}
```

---

## 🔐 ADMIN APIs (Require Authentication)

**Headers Required:**
```
Authorization: Bearer {token}
```

### 10. 🔑 ĐĂNG NHẬP
```
POST /api/login
```

**Body:**
```json
{
  "username": "admin",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Dang nhap thanh cong",
  "token": "1|abc123xyz...",
  "user": {
    "maTK": 1,
    "username": "admin",
    "email": "admin@example.com",
    "quyen": "admin"
  }
}
```

---

### 11. 📦 QUẢN LÝ ĐƠN HÀNG (Admin)
```
GET /api/admin/don-hang
GET /api/admin/don-hang/{id}
PUT /api/admin/don-hang/{id}
DELETE /api/admin/don-hang/{id}
```

---

### 12. 👥 QUẢN LÝ TÀI KHOẢN (Admin)
```
GET /api/admin/tai-khoan
POST /api/admin/tai-khoan
GET /api/admin/tai-khoan/{id}
PUT /api/admin/tai-khoan/{id}
DELETE /api/admin/tai-khoan/{id}
```

---

### 13. ✈️ QUẢN LÝ HÃNG HÀNG KHÔNG (Admin)
```
GET /api/admin/hang-hang-khong
POST /api/admin/hang-hang-khong
GET /api/admin/hang-hang-khong/{id}
PUT /api/admin/hang-hang-khong/{id}
DELETE /api/admin/hang-hang-khong/{id}
```

---

### 14. 🏢 QUẢN LÝ SÂN BAY (Admin)
```
GET /api/admin/san-bay
POST /api/admin/san-bay
GET /api/admin/san-bay/{id}
PUT /api/admin/san-bay/{id}
DELETE /api/admin/san-bay/{id}
```

---

### 15. 🎁 QUẢN LÝ KHUYẾN MÃI (Admin)
```
GET /api/admin/khuyen-mai
POST /api/admin/khuyen-mai
GET /api/admin/khuyen-mai/{id}
PUT /api/admin/khuyen-mai/{id}
DELETE /api/admin/khuyen-mai/{id}
```

---

## 🎯 LUỒNG SỬ DỤNG CHO FRONTEND

### 1️⃣ Tìm kiếm chuyến bay
```javascript
const response = await fetch(
  'https://bookingflightticket-backend-new.onrender.com/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1'
);
const data = await response.json();
// Hiển thị data.data lên UI
```

### 2️⃣ Lưu offer khi user chọn
```javascript
const selectedFlight = data.data[0]; // User chọn chuyến bay đầu tiên
localStorage.setItem('selectedOffer', JSON.stringify(selectedFlight));
```

### 3️⃣ Đặt vé
```javascript
const offerData = JSON.parse(localStorage.getItem('selectedOffer'));

const response = await fetch(
  'https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/tao-don-hang',
  {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      offer_data: offerData,
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
  }
);

const result = await response.json();
console.log(result.data.maCodeDonHang); // Mã đơn hàng
```

### 4️⃣ Xem đơn hàng
```javascript
const response = await fetch(
  'https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/don-hang'
);
const data = await response.json();
// Hiển thị danh sách đơn hàng
```

---

## 📝 LƯU Ý QUAN TRỌNG

### 1. **Offer Expiration** ⏰
- Duffel offers chỉ tồn tại **20 phút**
- Frontend **PHẢI** lưu toàn bộ `offer_data` từ API tìm kiếm
- **KHÔNG** gọi lại API chi tiết sau khi tìm kiếm

### 2. **Giá vé** 💰
- Giá đã bao gồm thuế và phí
- Giá đã quy đổi sang VND tự động
- Response có cả `gia_thap_nhat` (VND) và `gia_goc` (currency gốc)

### 3. **Trạng thái đơn hàng** 📊
- `0`: Chờ thanh toán (có thể hủy)
- `1`: Đã thanh toán (không thể hủy)
- `2`: Đã xác nhận (không thể hủy)
- `3`: Đã hủy

### 4. **Authentication** 🔐
- API Client **KHÔNG** yêu cầu đăng nhập
- API Admin yêu cầu `Authorization: Bearer {token}`

---

## 🧪 TEST API

### Dùng Postman:
1. Import file `Postman_Collection.json`
2. Đổi base URL thành production
3. Test từng API

### Dùng Browser:
```
https://bookingflightticket-backend-new.onrender.com/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01
```

---

## 📞 LIÊN HỆ

- **Backend Developer:** [Your Name]
- **API Base URL:** https://bookingflightticket-backend-new.onrender.com
- **Documentation:** API_DOCUMENTATION.md
- **Postman Collection:** Postman_Collection.json

---

**Last Updated:** April 18, 2026  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
