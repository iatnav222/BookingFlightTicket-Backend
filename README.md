# ✈️ Flight Booking System - Backend API

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://php.net)
[![Duffel API](https://img.shields.io/badge/Duffel-v2-green.svg)](https://duffel.com)
[![Status](https://img.shields.io/badge/Status-Production-success.svg)](https://bookingflightticket-backend-new.onrender.com)

Backend API cho hệ thống đặt vé máy bay, tích hợp với Duffel API để lấy dữ liệu chuyến bay thực.

## 🚀 Features

- ✅ Tìm kiếm chuyến bay thực từ Duffel API
- ✅ Tự động quy đổi giá sang VND (7 loại tiền tệ)
- ✅ Quản lý đơn hàng, vé, hành khách
- ✅ API RESTful đầy đủ cho Frontend
- ✅ Fallback strategy cho reliability
- ✅ Error handling toàn diện

## 📚 Documentation

| File | Mô tả |
|------|-------|
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | 📖 Tài liệu API đầy đủ cho Frontend |
| [SETUP_GUIDE.md](SETUP_GUIDE.md) | 🔧 Hướng dẫn setup từng bước |
| [TEST_RESULTS.md](TEST_RESULTS.md) | ✅ Kết quả test và performance |
| [TEST_BOOKING.md](TEST_BOOKING.md) | 🧪 Hướng dẫn test API đặt vé |
| [DATABASE_UPDATE.sql](DATABASE_UPDATE.sql) | 💾 Script cập nhật database |
| [Postman_Collection.json](Postman_Collection.json) | 📮 Collection test API |
| [SUMMARY.md](SUMMARY.md) | 📝 Tóm tắt dự án |

## 🎯 Quick Start

### 1. Clone & Install
```bash
git clone <your-repo>
cd BookingFlightTicket-Backend
composer install
```

### 2. Configure Environment
```bash
cp .env.example .env
# Sửa .env với Duffel token của bạn
```

### 3. Update Database
```bash
# Chạy DATABASE_UPDATE.sql trong phpMyAdmin
```

### 4. Start Server
```bash
php artisan serve
```

### 5. Test API
```bash
curl "http://localhost:8000/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01"
```

## 🌐 Production

**Base URL:** https://bookingflightticket-backend-new.onrender.com

**Status:** ✅ All APIs working (100% test coverage)

## 📊 API Endpoints

### Client APIs (Public)
```
GET  /api/client/chuyen-bay              - Tìm chuyến bay
GET  /api/client/san-bay                 - Danh sách sân bay
GET  /api/client/hang-hang-khong         - Danh sách hãng bay
POST /api/client/dat-ve/tao-don-hang    - Đặt vé
GET  /api/client/dat-ve/don-hang        - Danh sách đơn hàng
GET  /api/client/dat-ve/don-hang/{id}   - Chi tiết đơn hàng
GET  /api/client/dat-ve/ve              - Danh sách vé
PUT  /api/client/dat-ve/don-hang/{id}/huy - Hủy đơn hàng
```

### Admin APIs (Require Auth)
```
GET  /api/admin/don-hang                - Quản lý đơn hàng
GET  /api/admin/tai-khoan               - Quản lý tài khoản
GET  /api/admin/khuyen-mai              - Quản lý khuyến mãi
```

## 🔧 Tech Stack

- **Framework:** Laravel 12.x
- **PHP:** 8.2
- **Database:** MySQL
- **External API:** Duffel API v2
- **Deployment:** Render
- **Authentication:** Laravel Sanctum

## 💰 Currency Support

Tự động quy đổi sang VND:
- USD → VND (25,000)
- EUR → VND (27,000)
- GBP → VND (31,000)
- JPY → VND (170)
- SGD → VND (18,500)
- THB → VND (700)
- KRW → VND (19)

## 🧪 Testing

```bash
# Import Postman Collection
# File: Postman_Collection.json

# Hoặc dùng curl
curl "http://localhost:8000/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01"
```

**Test Results:** 8/8 APIs working ✅ (100%)

## 📦 Project Structure

```
BookingFlightTicket-Backend/
├── app/
│   ├── DichVu/
│   │   └── DichVuDuffel.php          # Duffel API service
│   ├── Http/Controllers/
│   │   ├── Client/
│   │   │   ├── ChuyenBayController.php
│   │   │   ├── DatVeController.php
│   │   │   └── DanhMucController.php
│   │   └── Admin/
│   └── Models/
├── config/
│   ├── currency.php                   # Tỷ giá quy đổi
│   └── services.php                   # Duffel config
├── routes/
│   └── api.php                        # API routes
├── database/
│   └── migrations/
├── API_DOCUMENTATION.md               # 📖 Tài liệu API
├── SETUP_GUIDE.md                     # 🔧 Hướng dẫn setup
├── TEST_RESULTS.md                    # ✅ Kết quả test
└── README.md                          # File này
```

## 🐛 Troubleshooting

### Lỗi: "Unauthorized" khi gọi Duffel
→ Kiểm tra `DUFFEL_ACCESS_TOKEN` trong `.env`

### Lỗi: "Column not found"
→ Chạy `DATABASE_UPDATE.sql`

### API trả về mảng rỗng
→ Đã fix với fallback strategy

Xem thêm: [SETUP_GUIDE.md](SETUP_GUIDE.md)

## 📞 Support

- **Documentation:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Duffel Docs:** https://duffel.com/docs/api
- **Duffel Status:** https://status.duffel.com/

## 📄 License

MIT License

---

**Last Updated:** April 18, 2026  
**Version:** 1.0.0  
**Status:** ✅ Production Ready
