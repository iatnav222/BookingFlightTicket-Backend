# 🚀 API QUICK REFERENCE - CHO FRONTEND

**Base URL:** `https://bookingflightticket-backend-new.onrender.com`

---

## 📋 CLIENT APIs (Không cần đăng nhập)

| # | API | Method | Endpoint |
|---|-----|--------|----------|
| 1 | Tìm chuyến bay | GET | `/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1` |
| 2 | Danh sách sân bay | GET | `/api/client/san-bay?search=hanoi` |
| 3 | Danh sách hãng bay | GET | `/api/client/hang-hang-khong?search=vietnam` |
| 4 | Đặt vé | POST | `/api/client/dat-ve/tao-don-hang` |
| 5 | Danh sách đơn hàng | GET | `/api/client/dat-ve/don-hang` |
| 6 | Chi tiết đơn hàng | GET | `/api/client/dat-ve/don-hang/{id}` |
| 7 | Danh sách vé | GET | `/api/client/dat-ve/ve` |
| 8 | Hủy đơn hàng | PUT | `/api/client/dat-ve/don-hang/{id}/huy` |
| 9 | Danh sách khuyến mãi | GET | `/api/client/khuyen-mai` |

---

## 🔐 ADMIN APIs (Cần token)

| # | API | Method | Endpoint |
|---|-----|--------|----------|
| 10 | Đăng nhập | POST | `/api/login` |
| 11 | Quản lý đơn hàng | GET/PUT/DELETE | `/api/admin/don-hang` |
| 12 | Quản lý tài khoản | GET/POST/PUT/DELETE | `/api/admin/tai-khoan` |
| 13 | Quản lý hãng bay | GET/POST/PUT/DELETE | `/api/admin/hang-hang-khong` |
| 14 | Quản lý sân bay | GET/POST/PUT/DELETE | `/api/admin/san-bay` |
| 15 | Quản lý khuyến mãi | GET/POST/PUT/DELETE | `/api/admin/khuyen-mai` |

---

## 💡 COPY & PASTE - JAVASCRIPT EXAMPLES

### 1. Tìm chuyến bay
```javascript
const flights = await fetch(
  'https://bookingflightticket-backend-new.onrender.com/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1'
).then(r => r.json());

console.log(flights.data); // Array of flights
```

### 2. Đặt vé
```javascript
const booking = await fetch(
  'https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/tao-don-hang',
  {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      offer_data: selectedFlight, // Từ API tìm kiếm
      hanh_khach: [{
        hoTen: "Nguyen Van A",
        ngaySinh: "1990-01-01",
        gioiTinh: "Nam",
        loaiHanhKhach: "NguoiLon",
        email: "test@example.com",
        sdt: "0123456789"
      }],
      thongTinLienHe: {
        ten: "Nguyen Van A",
        email: "test@example.com",
        sdt: "0123456789"
      }
    })
  }
).then(r => r.json());

console.log(booking.data.maCodeDonHang); // Mã đơn hàng
```

### 3. Xem đơn hàng
```javascript
const orders = await fetch(
  'https://bookingflightticket-backend-new.onrender.com/api/client/dat-ve/don-hang'
).then(r => r.json());

console.log(orders.data); // Array of orders
```

---

## 📦 FILES GỬI CHO FRONTEND

1. ✅ `API_LIST_FOR_FRONTEND.md` - Tài liệu API đầy đủ
2. ✅ `API_QUICK_REFERENCE.md` - File này - Quick reference
3. ✅ `Postman_Collection.json` - Import vào Postman để test

---

## ⚡ QUICK TIPS

### Lưu offer khi user chọn:
```javascript
localStorage.setItem('selectedOffer', JSON.stringify(flight));
```

### Lấy offer khi đặt vé:
```javascript
const offerData = JSON.parse(localStorage.getItem('selectedOffer'));
```

### Check response:
```javascript
if (response.success) {
  // Success
} else {
  // Error: response.message
}
```

---

## 🎯 LUỒNG ĐẶT VÉ

```
1. Tìm chuyến bay → GET /api/client/chuyen-bay
2. Lưu offer → localStorage
3. Đặt vé → POST /api/client/dat-ve/tao-don-hang
4. Thanh toán → VNPay/Momo (chưa có)
5. Xem đơn hàng → GET /api/client/dat-ve/don-hang
```

---

**Base URL:** `https://bookingflightticket-backend-new.onrender.com`  
**Status:** ✅ All APIs Working  
**Last Updated:** April 18, 2026
