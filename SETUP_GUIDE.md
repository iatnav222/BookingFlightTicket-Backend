# 🚀 Hướng dẫn Setup Backend - Flight Booking System

## 📋 Tóm tắt những gì đã làm

✅ **Tích hợp Duffel API** - Lấy dữ liệu chuyến bay thực từ Duffel  
✅ **Tự động quy đổi giá sang VND** - Hỗ trợ 7 loại tiền tệ  
✅ **API hoàn chỉnh** - 8 endpoints cho Frontend  
✅ **Lấy hãng bay & sân bay từ Duffel** - Không dùng database cũ  

---

## 🔧 Bước 1: Cập nhật Database

### Option 1: Chạy file SQL (Khuyến nghị)
1. Mở **phpMyAdmin** hoặc **MySQL Workbench**
2. Chọn database của bạn
3. Import file `DATABASE_UPDATE.sql`
4. Hoặc copy nội dung file và chạy từng câu lệnh

### Option 2: Chạy từng câu lệnh
```sql
-- Thêm cột vào bảng don_hang
ALTER TABLE `don_hang` 
ADD COLUMN `duffel_offer_id` VARCHAR(255) NULL,
ADD COLUMN `duffel_order_id` VARCHAR(255) NULL,
ADD COLUMN `duffel_booking_reference` VARCHAR(255) NULL,
ADD COLUMN `duffel_raw_data` LONGTEXT NULL;

-- Thêm cột vào bảng ve
ALTER TABLE `ve` 
ADD COLUMN `duffel_slice_id` VARCHAR(255) NULL,
ADD COLUMN `duffel_segment_id` VARCHAR(255) NULL,
ADD COLUMN `duffel_passenger_data` TEXT NULL;

-- Thêm cột vào bảng hanh_khach
ALTER TABLE `hanh_khach` 
ADD COLUMN `ho` VARCHAR(100) NULL,
ADD COLUMN `ten` VARCHAR(100) NULL,
ADD COLUMN `soHoChieu` VARCHAR(50) NULL,
ADD COLUMN `quocTich` VARCHAR(10) NULL;
```

### Kiểm tra đã thêm thành công chưa:
```sql
DESCRIBE don_hang;
DESCRIBE ve;
DESCRIBE hanh_khach;
```

---

## 🔑 Bước 2: Cấu hình Environment Variables

### Local (.env)
```env
# Duffel API Configuration
DUFFEL_ACCESS_TOKEN=duffel_test_your_actual_token_here
DUFFEL_API_VERSION=v2

# Exchange Rates (VND)
EXCHANGE_RATE_USD=25000
EXCHANGE_RATE_EUR=27000
EXCHANGE_RATE_GBP=31000
EXCHANGE_RATE_JPY=170
EXCHANGE_RATE_SGD=18500
EXCHANGE_RATE_THB=700
EXCHANGE_RATE_KRW=19
```

### Production (Render)
1. Vào **Render Dashboard**
2. Chọn service backend
3. Vào **Environment** tab
4. Thêm các biến:
   - `DUFFEL_ACCESS_TOKEN` = `duffel_test_...`
   - `DUFFEL_API_VERSION` = `v2`
   - `EXCHANGE_RATE_USD` = `25000`
   - `EXCHANGE_RATE_EUR` = `27000`
   - ... (các tỷ giá khác)

---

## 🧪 Bước 3: Test API

### Test Local
```bash
# Start server
php artisan serve

# Test tìm chuyến bay
curl "http://localhost:8000/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01&adults=1"

# Test danh sách sân bay
curl "http://localhost:8000/api/client/san-bay?search=hanoi"

# Test danh sách hãng bay
curl "http://localhost:8000/api/client/hang-hang-khong?search=vietnam"
```

### Test Production
```bash
# Test tìm chuyến bay
curl "https://bookingflightticket-backend-new.onrender.com/api/client/chuyen-bay?maSanBayDi=LHR&maSanBayDen=JFK&ngayBay=2026-05-01&adults=1"
```

---

## 📁 Files quan trọng

| File | Mô tả |
|------|-------|
| `API_DOCUMENTATION.md` | Tài liệu API đầy đủ cho Frontend |
| `DATABASE_UPDATE.sql` | Script cập nhật database |
| `SETUP_GUIDE.md` | File này - hướng dẫn setup |
| `app/DichVu/DichVuDuffel.php` | Service gọi Duffel API |
| `app/Http/Controllers/Client/ChuyenBayController.php` | Controller tìm chuyến bay |
| `app/Http/Controllers/Client/DatVeController.php` | Controller đặt vé |
| `app/Http/Controllers/Client/DanhMucController.php` | Controller sân bay/hãng bay |
| `config/currency.php` | Cấu hình tỷ giá |
| `routes/api.php` | Định nghĩa routes |

---

## 🐛 Troubleshooting

### Lỗi: "Column 'duffel_offer_id' not found"
→ Chưa chạy `DATABASE_UPDATE.sql`  
→ Chạy lại Bước 1

### Lỗi: "Unauthorized" khi gọi Duffel API
→ Kiểm tra `DUFFEL_ACCESS_TOKEN` trong `.env`  
→ Token phải bắt đầu bằng `duffel_test_` hoặc `duffel_live_`

### Lỗi: "Loi khi goi Duffel: ..."
→ Kiểm tra internet connection  
→ Kiểm tra Duffel API status: https://status.duffel.com/

### API đặt hàng bị lỗi
→ Kiểm tra đã cập nhật database chưa (Bước 1)  
→ Kiểm tra request body có đúng format không (xem `API_DOCUMENTATION.md`)  
→ Kiểm tra logs: `tail -f storage/logs/laravel.log`

### Giá vé hiển thị sai
→ Kiểm tra tỷ giá trong `.env`  
→ Cập nhật `config/currency.php` nếu cần  
→ Clear cache: `php artisan config:clear`

---

## 📊 Luồng dữ liệu

```
Frontend → Backend API → Duffel API → Backend → Frontend
                ↓
            Database (lưu đơn hàng)
```

**Lưu ý:** 
- Dữ liệu chuyến bay, hãng bay, sân bay **KHÔNG** lưu vào database
- Chỉ lưu đơn hàng, vé, hành khách vào database
- Offer data được lưu dạng JSON trong `don_hang.duffel_raw_data`

---

## 🚀 Deploy lên Render

### Bước 1: Push code lên GitHub
```bash
git add .
git commit -m "feat: integrate Duffel API with currency conversion"
git push origin main
```

### Bước 2: Render tự động deploy
- Render sẽ tự động detect thay đổi và deploy
- Kiểm tra logs trong Render Dashboard

### Bước 3: Cấu hình Environment Variables
- Vào Render Dashboard → Environment
- Thêm tất cả biến trong `.env.example`

### Bước 4: Test Production API
```bash
curl "https://bookingflightticket-backend-new.onrender.com/api/client/chuyen-bay?maSanBayDi=HAN&maSanBayDen=SGN&ngayBay=2026-05-01"
```

---

## ✅ Checklist hoàn thành

- [ ] Đã chạy `DATABASE_UPDATE.sql`
- [ ] Đã cấu hình `.env` với Duffel token
- [ ] Đã test API local thành công
- [ ] Đã push code lên GitHub
- [ ] Đã cấu hình Environment Variables trên Render
- [ ] Đã test API production thành công
- [ ] Đã gửi `API_DOCUMENTATION.md` cho team Frontend

---

## 📞 Liên hệ

Nếu có vấn đề, liên hệ:
- Backend Developer: [Your Name]
- GitHub Issues: [Your Repo URL]
- Duffel Support: https://duffel.com/support

---

**Last Updated:** April 18, 2026  
**Version:** 1.0.0
