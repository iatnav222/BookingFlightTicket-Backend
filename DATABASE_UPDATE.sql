-- =====================================================
-- CẬP NHẬT DATABASE CHO DUFFEL INTEGRATION
-- =====================================================
-- Chạy các câu lệnh SQL này trong phpMyAdmin hoặc MySQL Workbench
-- để thêm các cột cần thiết cho tích hợp Duffel API

-- =====================================================
-- 1. Bảng DON_HANG - Thêm cột lưu thông tin Duffel
-- =====================================================

-- Kiểm tra và thêm cột duffel_offer_id
ALTER TABLE `don_hang` 
ADD COLUMN IF NOT EXISTS `duffel_offer_id` VARCHAR(255) NULL COMMENT 'ID của offer từ Duffel' AFTER `thongTinLienHe`;

-- Kiểm tra và thêm cột duffel_order_id
ALTER TABLE `don_hang` 
ADD COLUMN IF NOT EXISTS `duffel_order_id` VARCHAR(255) NULL COMMENT 'ID của order từ Duffel sau khi booking' AFTER `duffel_offer_id`;

-- Kiểm tra và thêm cột duffel_booking_reference
ALTER TABLE `don_hang` 
ADD COLUMN IF NOT EXISTS `duffel_booking_reference` VARCHAR(255) NULL COMMENT 'Mã booking reference từ Duffel' AFTER `duffel_order_id`;

-- Kiểm tra và thêm cột duffel_raw_data
ALTER TABLE `don_hang` 
ADD COLUMN IF NOT EXISTS `duffel_raw_data` LONGTEXT NULL COMMENT 'Dữ liệu JSON gốc từ Duffel offer' AFTER `duffel_booking_reference`;

-- =====================================================
-- 2. Bảng VE - Thêm cột lưu thông tin Duffel
-- =====================================================

-- Kiểm tra và thêm cột duffel_slice_id
ALTER TABLE `ve` 
ADD COLUMN IF NOT EXISTS `duffel_slice_id` VARCHAR(255) NULL COMMENT 'ID của slice (chặng bay) từ Duffel' AFTER `maGhe`;

-- Kiểm tra và thêm cột duffel_segment_id
ALTER TABLE `ve` 
ADD COLUMN IF NOT EXISTS `duffel_segment_id` VARCHAR(255) NULL COMMENT 'ID của segment từ Duffel' AFTER `duffel_slice_id`;

-- Kiểm tra và thêm cột duffel_passenger_data
ALTER TABLE `ve` 
ADD COLUMN IF NOT EXISTS `duffel_passenger_data` TEXT NULL COMMENT 'Dữ liệu JSON hành khách từ Duffel' AFTER `duffel_segment_id`;

-- =====================================================
-- 3. Bảng HANH_KHACH - Thêm cột bổ sung
-- =====================================================

-- Kiểm tra và thêm cột ho (họ riêng)
ALTER TABLE `hanh_khach` 
ADD COLUMN IF NOT EXISTS `ho` VARCHAR(100) NULL COMMENT 'Họ của hành khách' AFTER `maTK`;

-- Kiểm tra và thêm cột ten (tên riêng)
ALTER TABLE `hanh_khach` 
ADD COLUMN IF NOT EXISTS `ten` VARCHAR(100) NULL COMMENT 'Tên của hành khách' AFTER `ho`;

-- Kiểm tra và thêm cột soHoChieu
ALTER TABLE `hanh_khach` 
ADD COLUMN IF NOT EXISTS `soHoChieu` VARCHAR(50) NULL COMMENT 'Số hộ chiếu' AFTER `soCMND`;

-- Kiểm tra và thêm cột quocTich
ALTER TABLE `hanh_khach` 
ADD COLUMN IF NOT EXISTS `quocTich` VARCHAR(10) NULL COMMENT 'Quốc tịch (VD: VN, US)' AFTER `soHoChieu`;

-- Kiểm tra và thêm cột soDienThoai (alias cho sdt)
ALTER TABLE `hanh_khach` 
ADD COLUMN IF NOT EXISTS `soDienThoai` VARCHAR(20) NULL COMMENT 'Số điện thoại' AFTER `sdt`;

-- =====================================================
-- 4. CẬP NHẬT KIỂU DỮ LIỆU
-- =====================================================

-- Đảm bảo cột trangThai trong don_hang là INT (0,1,2,3)
ALTER TABLE `don_hang` 
MODIFY COLUMN `trangThai` TINYINT(1) DEFAULT 0 COMMENT '0=Chờ thanh toán, 1=Đã thanh toán, 2=Đã xác nhận, 3=Đã hủy';

-- Đảm bảo cột thongTinLienHe là TEXT để lưu JSON
ALTER TABLE `don_hang` 
MODIFY COLUMN `thongTinLienHe` TEXT NULL COMMENT 'Thông tin liên hệ dạng JSON';

-- =====================================================
-- 5. TẠO INDEX ĐỂ TỐI ƯU HIỆU SUẤT
-- =====================================================

-- Index cho duffel_offer_id để tìm kiếm nhanh
CREATE INDEX IF NOT EXISTS `idx_duffel_offer_id` ON `don_hang` (`duffel_offer_id`);

-- Index cho duffel_order_id
CREATE INDEX IF NOT EXISTS `idx_duffel_order_id` ON `don_hang` (`duffel_order_id`);

-- Index cho maCodeDonHang
CREATE INDEX IF NOT EXISTS `idx_ma_code_don_hang` ON `don_hang` (`maCodeDonHang`);

-- Index cho trangThai để lọc nhanh
CREATE INDEX IF NOT EXISTS `idx_trang_thai` ON `don_hang` (`trangThai`);

-- =====================================================
-- HOÀN TẤT!
-- =====================================================
-- Sau khi chạy xong, kiểm tra lại bằng câu lệnh:
-- DESCRIBE don_hang;
-- DESCRIBE ve;
-- DESCRIBE hanh_khach;
