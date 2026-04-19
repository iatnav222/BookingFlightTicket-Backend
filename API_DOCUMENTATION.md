# 📚 API Documentation - Flight Booking System

**Base URL:** `https://bookingflightticket-backend-new.onrender.com`  
**Version:** 1.0  
**Last Updated:** April 18, 2026

---

## 🔐 Authentication

Hầu hết các API Client không yêu cầu authentication. Các API Admin yêu cầu:
- Header: `Authorization: Bearer {token}`
- Middleware: `auth:sanctum` + `isAdmin`

---

## 📍 Client APIs

### 1. Tìm kiếm chuyến bay

**Endpoint:** `GET /api/client/chuyen-bay`

**Mô tả:** Tìm kiếm chuyến bay từ Duffel API, giá tự động quy đổi sang VND

**Query Parameters:**
| Tham số | Kiểu | Bắt buộc | Mô tả | Ví dụ |
|---------|------|----------|-------|-------|
| `maSanBayDi` | string | Có | Mã IATA sân bay đi | `HAN`, `SGN`, `LHR` |
| `maSanBayDen` | string | Có | Mã IATA sân bay đến | `SGN`, `HAN`, `JFK` |
| `ngayBay` | string | Có | Ngày bay (YYYY-MM-DD) | `2026-05-01` |
| `adults` | integer | Không | Số người lớn (mặc định: 1) | `1`, `2`, `3` |

**Request Example:**
```bash
GET /api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1
```

**Response Success (200):**
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
        "tenSanBay": "Hanoi",
        "hinhAnh": null
      },
      "san_bay_den": {
        "maCode": "SGN",
        "tenSanBay": "Ho Chi Minh City",
        "hinhAnh": null
      },
      "ngayGioCatCanh": "2026-05-01T08:00:00",
      "ngayGioHaCanh": "2026-05-01T10:00:00",
      "gia_thap_nhat": 2500000,
      "tien_te": "VND",
      "gia_goc": 100.00,
      "tien_te_goc": "USD",
      "chi_tiet_chuyen": [...],
      "soGheConLai": 999,
      "trangThai": 1,
      "duffel_offer_id": "off_0000AZJlYKzG4Y8NAjmfDr",
      "duffel_raw": {...}
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 50,
    "total": 50
  },
  "meta": {
    "offer_request_id": "orq_0000AZJlYKzG4Y8NAjmfDr"
  }
}
```

**Response Error (500):**
```json
{
  "success": false,
  "message": "Loi khi goi Duffel: ..."
}
```

---

### 2. Danh sách sân bay

**Endpoint:** `GET /api/client/san-bay`

**Mô tả:** Lấy danh sách sân bay từ Duffel API (dùng cho autocomplete)

**Query Parameters:**
| Tham số | Kiểu | Bắt buộc | Mô tả | Ví dụ |
|---------|------|----------|-------|-------|
| `search` | string | Không | Tìm kiếm theo tên/mã sân bay | `hanoi`, `HAN`, `saigon` |

**Request Example:**
```bash
GET /api/client/san-bay?search=hanoi
```

**Response Success (200):**
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
      "quocGia": "VN",
      "hinhAnh": null,
      "latitude": 21.221192,
      "longitude": 105.807178
    }
  ]
}
```

---

### 3. Danh sách hãng hàng không

**Endpoint:** `GET /api/client/hang-hang-khong`

**Mô tả:** Lấy danh sách hãng hàng không từ Duffel API

**Query Parameters:**
| Tham số | Kiểu | Bắt buộc | Mô tả | Ví dụ |
|---------|------|----------|-------|-------|
| `search` | string | Không | Tìm kiếm theo tên/mã hãng | `vietnam`, `VN` |

**Request Example:**
```bash
GET /api/client/hang-hang-khong?search=vietnam
```

**Response Success (200):**
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
      "logo": "https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/VN.svg",
      "logoLockup": "https://assets.duffel.com/img/airlines/for-light-background/full-color-lockup/VN.svg",
      "trangThai": 1
    }
  ],
  "total": 1
}
```

---

### 4. Tạo đơn hàng (Đặt vé)

**Endpoint:** `POST /api/client/dat-ve/tao-don-hang`

**Mô tả:** Tạo đơn hàng từ offer đã chọn

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {token} (optional)
```

**Request Body:**
```json
{
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
      "ho": "Nguyen",
      "ten": "Van A",
      "ngaySinh": "1990-01-01",
      "gioiTinh": "Nam",
      "loaiHanhKhach": "NguoiLon",
      "soHoChieu": "ABC123456",
      "soCMND": "123456789",
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

**Response Success (201):**
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

**Response Error (500):**
```json
{
  "success": false,
  "message": "Loi khi tao don hang: ..."
}
```

---

### 5. Danh sách đơn hàng

**Endpoint:** `GET /api/client/dat-ve/don-hang`

**Mô tả:** Xem danh sách đơn hàng (của user nếu đã đăng nhập)

**Query Parameters:**
| Tham số | Kiểu | Bắt buộc | Mô tả | Ví dụ |
|---------|------|----------|-------|-------|
| `trangThai` | integer | Không | Lọc theo trạng thái | `0`, `1`, `2`, `3` |
| `perPage` | integer | Không | Số bản ghi/trang (mặc định: 10) | `10`, `20` |

**Trạng thái đơn hàng:**
- `0`: Chờ thanh toán
- `1`: Đã thanh toán
- `2`: Đã xác nhận
- `3`: Đã hủy

**Request Example:**
```bash
GET /api/client/dat-ve/don-hang?trangThai=1&perPage=10
```

**Response Success (200):**
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

### 6. Chi tiết đơn hàng

**Endpoint:** `GET /api/client/dat-ve/don-hang/{id}`

**Mô tả:** Xem chi tiết đơn hàng theo ID

**Path Parameters:**
| Tham số | Kiểu | Mô tả |
|---------|------|-------|
| `id` | integer | ID đơn hàng |

**Request Example:**
```bash
GET /api/client/dat-ve/don-hang/1
```

**Response Success (200):**
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

**Response Error (404):**
```json
{
  "success": false,
  "message": "Khong tim thay don hang"
}
```

---

### 7. Danh sách vé

**Endpoint:** `GET /api/client/dat-ve/ve`

**Mô tả:** Xem danh sách vé đã đặt (của user nếu đã đăng nhập)

**Query Parameters:**
| Tham số | Kiểu | Bắt buộc | Mô tả | Ví dụ |
|---------|------|----------|-------|-------|
| `trangThaiVe` | string | Không | Lọc theo trạng thái vé | `DaDat`, `DaHuy` |
| `perPage` | integer | Không | Số bản ghi/trang (mặc định: 10) | `10`, `20` |

**Request Example:**
```bash
GET /api/client/dat-ve/ve?trangThaiVe=DaDat&perPage=10
```

**Response Success (200):**
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

### 8. Hủy đơn hàng

**Endpoint:** `PUT /api/client/dat-ve/don-hang/{id}/huy`

**Mô tả:** Hủy đơn hàng (chỉ được hủy đơn chưa thanh toán)

**Path Parameters:**
| Tham số | Kiểu | Mô tả |
|---------|------|-------|
| `id` | integer | ID đơn hàng |

**Request Example:**
```bash
PUT /api/client/dat-ve/don-hang/1/huy
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Huy don hang thanh cong"
}
```

**Response Error (400):**
```json
{
  "success": false,
  "message": "Chi co the huy don hang chua thanh toan"
}
```

**Response Error (403):**
```json
{
  "success": false,
  "message": "Ban khong co quyen huy don hang nay"
}
```

**Response Error (404):**
```json
{
  "success": false,
  "message": "Khong tim thay don hang"
}
```

---

### 9. Danh sách khuyến mãi

**Endpoint:** `GET /api/client/khuyen-mai`

**Mô tả:** Xem danh sách khuyến mãi đang hoạt động

**Request Example:**
```bash
GET /api/client/khuyen-mai
```

**Response Success (200):**
```json
{
  "success": true,
  "message": "Lay danh sach khuyen mai thanh cong",
  "data": [...]
}
```

---

## 🔄 Luồng hoạt động Frontend

### Bước 1: Tìm kiếm chuyến bay
```javascript
// Gọi API tìm kiếm
const response = await fetch(
  '/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1'
);
const data = await response.json();

// Lưu offer_data để đặt vé sau
const selectedOffer = data.data[0];
localStorage.setItem('selectedOffer', JSON.stringify(selectedOffer));
```

### Bước 2: Hiển thị form nhập thông tin hành khách
```javascript
// Lấy offer đã chọn
const offerData = JSON.parse(localStorage.getItem('selectedOffer'));

// Hiển thị thông tin chuyến bay
console.log(offerData.hang_hang_khong.tenHang); // "Vietnam Airlines"
console.log(offerData.gia_thap_nhat); // 2500000 (VND)
```

### Bước 3: Đặt vé
```javascript
const bookingData = {
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
};

const response = await fetch('/api/client/dat-ve/tao-don-hang', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(bookingData)
});

const result = await response.json();
console.log(result.data.maCodeDonHang); // "DH20260418120000ABCD"
```

### Bước 4: Xem đơn hàng
```javascript
// Xem danh sách đơn hàng
const response = await fetch('/api/client/dat-ve/don-hang');
const data = await response.json();

// Xem chi tiết đơn hàng
const detailResponse = await fetch(`/api/client/dat-ve/don-hang/${orderId}`);
const detailData = await detailResponse.json();
```

---

## 💰 Quy đổi tiền tệ

Hệ thống tự động quy đổi giá từ Duffel sang VND theo tỷ giá:

| Tiền tệ | Tỷ giá (VND) | Cấu hình |
|---------|--------------|----------|
| USD | 25,000 | `EXCHANGE_RATE_USD` |
| EUR | 27,000 | `EXCHANGE_RATE_EUR` |
| GBP | 31,000 | `EXCHANGE_RATE_GBP` |
| JPY | 170 | `EXCHANGE_RATE_JPY` |
| SGD | 18,500 | `EXCHANGE_RATE_SGD` |
| THB | 700 | `EXCHANGE_RATE_THB` |
| KRW | 19 | `EXCHANGE_RATE_KRW` |

**Cập nhật tỷ giá:** Sửa file `.env` hoặc `config/currency.php`

---

## ⚠️ Lưu ý quan trọng

### 1. Offer Expiration
- Duffel offers chỉ tồn tại trong **20 phút**
- Frontend **PHẢI lưu** toàn bộ `offer_data` từ API tìm kiếm
- **KHÔNG** gọi lại API chi tiết chuyến bay sau khi tìm kiếm

### 2. Giá vé
- Giá đã bao gồm thuế và phí
- Giá được quy đổi sang VND tự động
- Response trả về cả `gia_thap_nhat` (VND) và `gia_goc` (currency gốc)

### 3. Trạng thái đơn hàng
- `0`: Chờ thanh toán (có thể hủy)
- `1`: Đã thanh toán (không thể hủy)
- `2`: Đã xác nhận (không thể hủy)
- `3`: Đã hủy

### 4. Authentication
- Hiện tại các API Client **KHÔNG** yêu cầu đăng nhập
- Nếu có token, truyền vào header: `Authorization: Bearer {token}`
- Nếu không có token, hệ thống vẫn hoạt động bình thường

---

## 🐛 Xử lý lỗi

### Lỗi thường gặp:

**1. Duffel API Error (500)**
```json
{
  "success": false,
  "message": "Loi khi goi Duffel: Unauthorized"
}
```
→ Kiểm tra `DUFFEL_ACCESS_TOKEN` trong `.env`

**2. Validation Error (422)**
```json
{
  "success": false,
  "message": "The offer_data field is required."
}
```
→ Kiểm tra request body có đầy đủ fields không

**3. Not Found (404)**
```json
{
  "success": false,
  "message": "Khong tim thay don hang"
}
```
→ Kiểm tra ID đơn hàng có tồn tại không

---

## 📞 Support

Nếu có vấn đề, liên hệ:
- Backend Developer: [Your Contact]
- API Issues: Check server logs on Render
- Duffel API Docs: https://duffel.com/docs/api

---

**Generated:** April 18, 2026  
**Version:** 1.0.0
