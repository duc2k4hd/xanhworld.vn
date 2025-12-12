-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1:3306
-- Thời gian đã tạo: Th12 04, 2025 lúc 01:04 PM
-- Phiên bản máy phục vụ: 9.1.0
-- Phiên bản PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `xanhworld.vn`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính tự tăng của tài khoản',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên hiển thị của người dùng',
  `email` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email đăng nhập duy nhất',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm xác thực email',
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mật khẩu đã băm',
  `role` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user' COMMENT 'Vai trò tài khoản',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token ghi nhớ đăng nhập',
  `last_password_changed_at` timestamp NULL DEFAULT NULL COMMENT 'Lần thay đổi mật khẩu gần nhất',
  `login_attempts` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lần đăng nhập thất bại',
  `status` enum('active','inactive','suspended','locked','banned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Trạng thái tài khoản',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `tags` json DEFAULT NULL,
  `security_flags` json DEFAULT NULL COMMENT 'Các cờ bảo mật bổ sung',
  `login_history` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm đăng nhập gần nhất',
  `logs` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú nội bộ liên quan tới tài khoản',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông tin tài khoản người dùng';

--
-- Đang đổ dữ liệu cho bảng `accounts`
--

INSERT INTO `accounts` (`id`, `name`, `email`, `phone`, `email_verified_at`, `password`, `role`, `remember_token`, `last_password_changed_at`, `login_attempts`, `status`, `admin_note`, `tags`, `security_flags`, `login_history`, `logs`, `created_at`, `updated_at`, `deleted_at`) VALUES
(8, 'Nguyễn Minh Đức ❤️', 'admin@gmail.com', '0827786198', '2025-11-29 07:28:00', '$2y$12$WFAjILjswd1XDSNs9gn49.41zF.5QKmdZ3/4FEK5QwzpjeayjovHa', 'admin', 'tFRXJfZ6dKOB6zdFowQYmxzIoFF3AiRncgJ1acGmdopwvWMDDwGQjqHlpKzE', '2025-11-30 01:15:20', 0, 'active', NULL, NULL, NULL, NULL, NULL, '2025-11-29 07:28:00', '2025-11-30 01:15:21', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account_email_verifications`
--

DROP TABLE IF EXISTS `account_email_verifications`;
CREATE TABLE IF NOT EXISTS `account_email_verifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính của phiên xác thực email',
  `account_id` bigint UNSIGNED NOT NULL,
  `token` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã token gửi qua email',
  `expires_at` timestamp NOT NULL COMMENT 'Thời điểm token hết hạn',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Thời điểm tạo token',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_email_verifications_token_unique` (`token`),
  KEY `account_email_verifications_account_id_foreign` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng quản lý token xác thực email';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `account_logs`
--

DROP TABLE IF EXISTS `account_logs`;
CREATE TABLE IF NOT EXISTS `account_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính log',
  `account_id` bigint UNSIGNED NOT NULL COMMENT 'ID tài khoản',
  `admin_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID quản trị',
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Loại hành động',
  `payload` json DEFAULT NULL COMMENT 'Dữ liệu chi tiết',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP thực hiện',
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'User-agent',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_logs_account_id_index` (`account_id`),
  KEY `account_logs_admin_id_index` (`admin_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'create, update, delete, etc.',
  `model_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Product, Order, Account, etc.',
  `model_id` bigint UNSIGNED DEFAULT NULL,
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Người thực hiện',
  `description` text COLLATE utf8mb4_unicode_ci,
  `old_data` json DEFAULT NULL COMMENT 'Dữ liệu cũ (trước khi thay đổi)',
  `new_data` json DEFAULT NULL COMMENT 'Dữ liệu mới (sau khi thay đổi)',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `activity_logs_account_id_index` (`account_id`),
  KEY `activity_logs_action_index` (`action`),
  KEY `activity_logs_created_at_index` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính địa chỉ',
  `account_id` bigint UNSIGNED NOT NULL COMMENT 'ID tài khoản',
  `full_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Họ tên người nhận',
  `phone_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SĐT',
  `detail_address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Địa chỉ chi tiết',
  `ward` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Phường',
  `district` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Quận',
  `province` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tỉnh',
  `province_code` bigint UNSIGNED DEFAULT NULL COMMENT 'Mã tỉnh',
  `district_code` bigint UNSIGNED DEFAULT NULL COMMENT 'Mã huyện',
  `ward_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã phường',
  `postal_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã bưu chính',
  `country` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Quốc gia',
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vĩ độ',
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Kinh độ',
  `address_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home' COMMENT 'Loại địa chỉ',
  `notes` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Địa chỉ mặc định',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_account_id_index` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `address_audits`
--

DROP TABLE IF EXISTS `address_audits`;
CREATE TABLE IF NOT EXISTS `address_audits` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `address_id` bigint UNSIGNED NOT NULL COMMENT 'ID địa chỉ',
  `performed_by` bigint UNSIGNED DEFAULT NULL COMMENT 'ID người thực hiện',
  `action` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hành động thực hiện',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả chi tiết',
  `changes` json DEFAULT NULL COMMENT 'Các thay đổi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `address_audits_address_id_index` (`address_id`),
  KEY `address_audits_performed_by_index` (`performed_by`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `affiliates`
--

DROP TABLE IF EXISTS `affiliates`;
CREATE TABLE IF NOT EXISTS `affiliates` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính affiliate',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID người dùng tạo mã giới thiệu',
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã giới thiệu duy nhất',
  `clicks` bigint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lượt click',
  `conversions` bigint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lượt chuyển đổi',
  `commission_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Phần trăm hoa hồng (0-100)',
  `total_commission` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Hoa hồng tích lũy',
  `referral_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL giới thiệu',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active' COMMENT 'Trạng thái',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  UNIQUE KEY `affiliates_code_unique` (`code`),
  KEY `affiliates_account_id_foreign` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `banners`
--

DROP TABLE IF EXISTS `banners`;
CREATE TABLE IF NOT EXISTS `banners` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính banner',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tiêu đề banner',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả banner',
  `image_desktop` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Đường dẫn hình ảnh',
  `image_mobile` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn hình ảnh mobile',
  `link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Liên kết khi click vào banner',
  `target` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_blank' COMMENT 'Target của link (_blank, _self)',
  `position` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Vị trí hiển thị trên trang',
  `order` int NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `start_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian bắt đầu hiển thị',
  `end_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian kết thúc hiển thị',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT 'Trạng thái hoạt động',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian cập nhật',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian xóa mềm',
  PRIMARY KEY (`id`),
  KEY `banners_start_at_index` (`start_at`),
  KEY `banners_end_at_index` (`end_at`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `banners`
--

INSERT INTO `banners` (`id`, `title`, `description`, `image_desktop`, `image_mobile`, `link`, `target`, `position`, `order`, `start_at`, `end_at`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Banner 1', NULL, 'banner-nobifashion.vn.png', NULL, NULL, '_blank', 'homepage_banner_parent', 0, NULL, NULL, 1, '2025-11-25 07:55:45', NULL, NULL),
(2, 'Banner 2', NULL, 'Banner-trang-chu-nobifashion.png', NULL, NULL, '_blank', 'homepage_banner_parent', 1, NULL, NULL, 1, '2025-11-25 07:55:45', NULL, NULL),
(3, 'Banner 3', NULL, 'banner-trang-home.png', NULL, NULL, '_blank', 'homepage_banner_parent', 2, NULL, NULL, 1, '2025-11-25 07:57:27', NULL, NULL),
(4, 'Banner con 1', NULL, 'banner-con-trang-chu.png', NULL, NULL, '_blank', 'homepage_banner_children', 0, NULL, NULL, 1, '2025-11-25 09:22:00', NULL, NULL),
(5, 'Banner con 2', NULL, 'banner-con-trang-home-nobifashion.png', NULL, NULL, '_blank', 'homepage_banner_children', 1, NULL, NULL, 1, '2025-11-25 09:22:00', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính giỏ hàng',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản (nếu đăng nhập)',
  `session_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'ID session khách vãng lai',
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID sản phẩm',
  `options` json DEFAULT NULL COMMENT 'Tùy chọn sản phẩm (màu, size...)',
  `status` enum('active','ordered','abandoned') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  KEY `carts_account_id_index` (`account_id`),
  KEY `carts_session_id_index` (`session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính mục giỏ hàng',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã định danh duy nhất của item',
  `cart_id` bigint UNSIGNED NOT NULL COMMENT 'ID giỏ hàng',
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID sản phẩm',
  `product_variant_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID biến thể sản phẩm (nếu có)',
  `quantity` int UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Số lượng',
  `price` decimal(15,2) NOT NULL COMMENT 'Giá tại thời điểm thêm vào giỏ',
  `total_price` decimal(10,0) NOT NULL DEFAULT '0' COMMENT 'Thành tiền = price * quantity',
  `options` json DEFAULT NULL COMMENT 'Thuộc tính bổ sung (size, màu...)',
  `status` enum('active','removed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đánh dấu sản phẩm flash sale',
  `flash_sale_item_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID flash sale item tương ứng (nếu có)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  KEY `cart_items_cart_id_index` (`cart_id`),
  KEY `cart_items_product_id_index` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính danh mục',
  `parent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Danh mục cha',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên danh mục',
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug duy nhất',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả danh mục',
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh đại diện danh mục',
  `order` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái hoạt động',
  `metadata` json DEFAULT NULL COMMENT 'Meta SEO JSON',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_index` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `image`, `order`, `is_active`, `metadata`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Cây Cảnh', 'cay-canh', NULL, NULL, 1, 1, NULL, '2025-11-25 04:02:52', '2025-11-25 04:02:52'),
(2, NULL, 'Cây Phong Thủy', 'cay-phong-thuy', NULL, NULL, 0, 1, NULL, '2025-11-25 04:02:52', '2025-11-25 04:02:52'),
(3, NULL, 'Cây Hoa Đẹp', 'cay-hoa-dep', NULL, NULL, 2, 1, NULL, '2025-11-25 04:02:52', '2025-11-25 04:02:52'),
(4, NULL, 'Sen Đá – Xương Rồng', 'sen-da-xuong-rong', NULL, NULL, 3, 1, NULL, '2025-11-25 04:02:52', '2025-11-25 04:02:52'),
(5, NULL, 'Chậu Cây – Phụ Kiện', 'chau-cay-phu-kien', NULL, NULL, 4, 1, NULL, '2025-11-25 04:02:52', '2025-11-25 04:02:52'),
(6, 1, 'Cây để bàn', 'cay-de-ban', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(7, 1, 'Cây trong nhà', 'cay-trong-nha', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(8, 1, 'Cây ngoài trời', 'cay-ngoai-troi', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(9, 1, 'Cây mini', 'cay-mini', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(10, 1, 'Cây bonsai', 'cay-bonsai', NULL, 'cay-bonsai-C99gR4bQ.webp', 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-30 15:24:36'),
(11, 1, 'Cây thủy sinh', 'cay-thuy-sinh', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(12, 2, 'Cây mang ý nghĩa tài lộc', 'cay-y-nghia-tai-loc', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(13, 2, 'Cây hút tài – chiêu lộc', 'cay-hut-tai-chieu-loc', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(14, 2, 'Cây hợp mệnh', 'cay-hop-menh', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(15, 2, 'Cây hợp tuổi', 'cay-hop-tuoi', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(16, 2, 'Cây để bàn phong thủy', 'cay-de-ban-phong-thuy', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(17, 2, 'Cây nội thất phong thủy', 'cay-noi-that-phong-thuy', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:08', '2025-11-25 04:03:08'),
(18, 14, 'Cây hợp mệnh Kim', 'cay-hop-menh-kim', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:45', '2025-11-25 04:03:45'),
(19, 14, 'Cây hợp mệnh Mộc', 'cay-hop-menh-moc', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:45', '2025-11-25 04:03:45'),
(20, 14, 'Cây hợp mệnh Thủy', 'cay-hop-menh-thuy', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:45', '2025-11-25 04:03:45'),
(21, 14, 'Cây hợp mệnh Hỏa', 'cay-hop-menh-hoa', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:45', '2025-11-25 04:03:45'),
(22, 14, 'Cây hợp mệnh Thổ', 'cay-hop-menh-tho', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:45', '2025-11-25 04:03:45'),
(23, 3, 'Hoa để bàn', 'hoa-de-ban', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(24, 3, 'Hoa trong nhà', 'hoa-trong-nha', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(25, 3, 'Hoa ngoài trời', 'hoa-ngoai-troi', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(26, 3, 'Hoa phong thủy', 'hoa-phong-thuy', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(27, 3, 'Hoa leo – hoa dây', 'hoa-leo', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(28, 4, 'Sen đá các loại', 'sen-da-cac-loai', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(29, 4, 'Xương rồng mini', 'xuong-rong-mini', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(30, 4, 'Combo sen đá', 'combo-sen-da', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(31, 5, 'Chậu cây', 'chau-cay', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(32, 5, 'Đất – giá thể', 'dat-gia-the', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(33, 5, 'Dụng cụ làm vườn', 'dung-cu-lam-vuon', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55'),
(34, 5, 'Bình tưới – phụ kiện', 'binh-tuoi-phu-kien', NULL, NULL, 0, 1, NULL, '2025-11-25 04:03:55', '2025-11-25 04:03:55');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính bình luận',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản bình luận',
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Session của khách vãng lai',
  `commentable_id` bigint UNSIGNED NOT NULL COMMENT 'ID đối tượng được bình luận',
  `commentable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Kiểu model (Post/Product/...)',
  `parent_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID bình luận cha',
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung bình luận',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên người bình luận (khách)',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email khách',
  `is_approved` tinyint NOT NULL DEFAULT '1' COMMENT 'Duyệt bình luận',
  `rating` int DEFAULT NULL COMMENT 'Đánh giá (1-5, chỉ áp dụng cho sản phẩm)',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP người bình luận',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'User agent của người bình luận',
  `is_reported` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đã báo cáo',
  `reports_count` int NOT NULL DEFAULT '0' COMMENT 'Số lần báo cáo',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  KEY `comments_commentable_id_index` (`commentable_id`),
  KEY `comments_commentable_type_index` (`commentable_type`),
  KEY `comments_parent_id_index` (`parent_id`),
  KEY `comments_account_id_index` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính liên hệ',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Tài khoản liên quan (nếu có)',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên người gửi',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email người gửi',
  `phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại',
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tiêu đề',
  `message` text COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung liên hệ',
  `attachment_path` text COLLATE utf8mb4_unicode_ci COMMENT 'Đường dẫn tệp đính kèm của khách hàng',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Địa chỉ IP',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new' COMMENT 'Trạng thái xử lý: new, processing, done, spam',
  `source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nguồn liên hệ: contact_form, landing_page, popup, ...',
  `admin_note` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú nội bộ của admin',
  `last_replied_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian trả lời cuối cùng',
  `reply_count` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lần đã trả lời khách',
  `is_read` tinyint NOT NULL DEFAULT '0' COMMENT 'Đã đọc hay chưa',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm gửi',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contacts_status_index` (`status`),
  KEY `contacts_source_index` (`source`),
  KEY `contacts_is_read_index` (`is_read`),
  KEY `contacts_created_at_index` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `contact_replies`
--

DROP TABLE IF EXISTS `contact_replies`;
CREATE TABLE IF NOT EXISTS `contact_replies` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `contact_id` bigint UNSIGNED NOT NULL COMMENT 'Liên hệ gốc',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Admin gửi phản hồi',
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung trả lời (HTML)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `contact_replies_contact_id_index` (`contact_id`),
  KEY `contact_replies_account_id_index` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `emails`
--

DROP TABLE IF EXISTS `emails`;
CREATE TABLE IF NOT EXISTS `emails` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính tài khoản email gửi đi',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Địa chỉ email gửi',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên hiển thị',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả thêm',
  `is_default` tinyint NOT NULL DEFAULT '0' COMMENT 'Đánh dấu làm tài khoản mặc định',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT 'Trạng thái hoạt động',
  `order` int NOT NULL DEFAULT '0' COMMENT 'Thứ tự ưu tiên',
  `mail_host` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SMTP Host',
  `mail_port` int DEFAULT NULL COMMENT 'SMTP Port',
  `mail_username` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'SMTP Username',
  `mail_password` text COLLATE utf8mb4_unicode_ci COMMENT 'SMTP Password',
  `mail_encryption` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Kiểu mã hóa (ssl/tls)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Xóa mềm',
  PRIMARY KEY (`id`),
  UNIQUE KEY `emails_email_unique` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `email_templates`
--

DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE IF NOT EXISTS `email_templates` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique key: order_confirmation, password_reset, etc.',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên template',
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Subject email',
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung email (HTML)',
  `variables` text COLLATE utf8mb4_unicode_ci COMMENT 'JSON: Danh sách biến có thể dùng',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_templates_key_unique` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `favorites`
--

DROP TABLE IF EXISTS `favorites`;
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính yêu thích',
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID sản phẩm yêu thích',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản (nếu có)',
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Session khách vãng lai',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_unique_owner` (`product_id`,`account_id`,`session_id`),
  KEY `favorites_account_id_session_id_index` (`account_id`,`session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `flash_sales`
--

DROP TABLE IF EXISTS `flash_sales`;
CREATE TABLE IF NOT EXISTS `flash_sales` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính chương trình flash sale',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên chương trình',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả',
  `banner` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh banner',
  `tag` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tag hiển thị',
  `start_time` timestamp NOT NULL COMMENT 'Bắt đầu',
  `end_time` timestamp NOT NULL COMMENT 'Kết thúc',
  `status` enum('draft','active','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'Trạng thái',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT 'Kích hoạt?',
  `created_by` bigint UNSIGNED DEFAULT NULL COMMENT 'Người tạo',
  `max_per_user` int UNSIGNED DEFAULT NULL COMMENT 'Giới hạn mỗi người',
  `display_limit` int UNSIGNED NOT NULL DEFAULT '20' COMMENT 'Giới hạn hiển thị',
  `product_add_mode` enum('auto_by_category','manual') COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cách thêm sản phẩm',
  `views` bigint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Lượt xem',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Cập nhật',
  `deleted_at` timestamp NULL DEFAULT NULL COMMENT 'Xóa mềm',
  PRIMARY KEY (`id`),
  KEY `flash_sales_created_by_foreign` (`created_by`),
  KEY `flash_sales_status_index` (`status`),
  KEY `flash_sales_start_time_index` (`start_time`),
  KEY `flash_sales_end_time_index` (`end_time`),
  KEY `flash_sales_status_is_active_start_time_end_time_index` (`status`,`is_active`,`start_time`,`end_time`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `flash_sale_items`
--

DROP TABLE IF EXISTS `flash_sale_items`;
CREATE TABLE IF NOT EXISTS `flash_sale_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính mục flash sale',
  `flash_sale_id` bigint UNSIGNED NOT NULL COMMENT 'ID flash sale',
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID sản phẩm',
  `original_price` decimal(15,2) DEFAULT NULL COMMENT 'Giá gốc',
  `sale_price` decimal(15,2) NOT NULL COMMENT 'Giá sale',
  `unified_price` decimal(15,2) DEFAULT NULL COMMENT 'Giá đồng nhất',
  `original_variant_price` decimal(15,2) DEFAULT NULL COMMENT 'Giá gốc biến thể',
  `stock` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Kho',
  `sold` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Đã bán',
  `max_per_user` int UNSIGNED DEFAULT NULL COMMENT 'Giới hạn mỗi người',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT 'Kích hoạt',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thứ tự',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Cập nhật',
  PRIMARY KEY (`id`),
  KEY `flash_sale_items_product_id_index` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `flash_sale_price_logs`
--

DROP TABLE IF EXISTS `flash_sale_price_logs`;
CREATE TABLE IF NOT EXISTS `flash_sale_price_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính',
  `flash_sale_item_id` bigint UNSIGNED NOT NULL COMMENT 'ID flash sale item',
  `old_price` decimal(15,2) NOT NULL COMMENT 'Giá cũ',
  `new_price` decimal(15,2) NOT NULL COMMENT 'Giá mới',
  `changed_by` bigint UNSIGNED DEFAULT NULL COMMENT 'Người thay đổi',
  `changed_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian thay đổi',
  `reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Lý do thay đổi',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `flash_sale_price_logs_item_id_index` (`flash_sale_item_id`),
  KEY `flash_sale_price_logs_changed_by_index` (`changed_by`),
  KEY `flash_sale_price_logs_changed_at_index` (`changed_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `images`
--

DROP TABLE IF EXISTS `images`;
CREATE TABLE IF NOT EXISTS `images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` text COLLATE utf8mb4_unicode_ci,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tiêu đề ảnh',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú thêm cho ảnh',
  `alt` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Alt text cho SEO',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Ảnh chính của sản phẩm',
  `order` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `inventory_movements`
--

DROP TABLE IF EXISTS `inventory_movements`;
CREATE TABLE IF NOT EXISTS `inventory_movements` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity_change` int NOT NULL COMMENT 'Số lượng +/- thay đổi tồn kho',
  `stock_before` int DEFAULT NULL COMMENT 'Tồn kho trước khi cập nhật',
  `stock_after` int DEFAULT NULL COMMENT 'Tồn kho sau khi cập nhật',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'order, order_cancel, import, export, adjust, system',
  `reference_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Model liên quan: Order, ImportReceipt...',
  `reference_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID bản ghi liên quan',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Người thao tác (admin)',
  `note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventory_movements_product_id_created_at_index` (`product_id`,`created_at`),
  KEY `inventory_movements_reference_type_reference_id_index` (`reference_type`,`reference_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính job queue',
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên queue',
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Payload job',
  `attempts` tinyint UNSIGNED NOT NULL COMMENT 'Số lần thử',
  `reserved_at` int UNSIGNED DEFAULT NULL COMMENT 'Thời điểm reserve',
  `available_at` int UNSIGNED NOT NULL COMMENT 'Thời điểm có thể chạy',
  `created_at` int UNSIGNED NOT NULL COMMENT 'Thời điểm tạo job',
  PRIMARY KEY (`id`),
  KEY `jobs_queue_reserved_at_index` (`queue`,`reserved_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ID batch',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên batch',
  `total_jobs` int NOT NULL COMMENT 'Tổng số job',
  `pending_jobs` int NOT NULL COMMENT 'Số job đang chờ',
  `failed_jobs` int NOT NULL COMMENT 'Số job lỗi',
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Danh sách job lỗi',
  `options` mediumtext COLLATE utf8mb4_unicode_ci COMMENT 'Tùy chọn',
  `cancelled_at` int DEFAULT NULL COMMENT 'Thời điểm hủy',
  `created_at` int NOT NULL COMMENT 'Thời điểm tạo',
  `finished_at` int DEFAULT NULL COMMENT 'Thời điểm hoàn tất',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `newsletters`
--

DROP TABLE IF EXISTS `newsletters`;
CREATE TABLE IF NOT EXISTS `newsletters` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính đăng ký nhận tin',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email đăng ký',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP đăng ký',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Địa chỉ IP đăng ký (chuẩn hóa)',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'User agent khi đăng ký',
  `note` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú nội bộ admin',
  `is_verified` tinyint NOT NULL DEFAULT '0' COMMENT 'Đã xác thực?',
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái: pending, subscribed, unsubscribed',
  `source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nguồn đăng ký: homepage_form, popup, checkout, ...',
  `verify_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token xác nhận / hủy đăng ký',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian xác nhận đăng ký',
  `unsubscribed_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian hủy đăng ký',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời gian cập nhật',
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletters_email_unique` (`email`),
  UNIQUE KEY `newsletters_verify_token_unique` (`verify_token`),
  KEY `newsletters_status_index` (`status`),
  KEY `newsletters_source_index` (`source`),
  KEY `newsletters_created_at_index` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `newsletter_campaigns`
--

DROP TABLE IF EXISTS `newsletter_campaigns`;
CREATE TABLE IF NOT EXISTS `newsletter_campaigns` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `cta_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cta_text` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer` longtext COLLATE utf8mb4_unicode_ci,
  `filter_status` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filter_source` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `filter_date_from` date DEFAULT NULL,
  `filter_date_to` date DEFAULT NULL,
  `total_target` int UNSIGNED NOT NULL DEFAULT '0',
  `sent_success` int UNSIGNED NOT NULL DEFAULT '0',
  `sent_failed` int UNSIGNED NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `newsletter_campaigns_created_by_index` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính thông báo',
  `account_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Loại thông báo: order, comment, contact, voucher, flash_sale, etc.',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề thông báo',
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nội dung thông báo',
  `data` json DEFAULT NULL COMMENT 'Dữ liệu bổ sung (JSON)',
  `link` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL liên quan đến thông báo',
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icon hiển thị (fa-bell, fa-shopping-cart, etc.)',
  `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT 'Mức độ ưu tiên',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đã đọc chưa',
  `read_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm đọc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_account_id_is_read_index` (`account_id`,`is_read`),
  KEY `notifications_type_created_at_index` (`type`,`created_at`),
  KEY `notifications_created_at_index` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Bảng lưu thông báo cho người dùng và admin';

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính đơn hàng',
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã đơn hàng',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản đặt hàng',
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Session khách vãng lai',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Tổng tiền hàng',
  `shipping_address_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID địa chỉ giao hàng',
  `billing_address_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID địa chỉ thanh toán',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tổng tiền hàng',
  `discount` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Giảm giá',
  `voucher_discount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Giảm giá từ voucher',
  `voucher_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã voucher áp dụng',
  `final_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Tổng thanh toán cuối cùng',
  `receiver_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên người nhận',
  `receiver_phone` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại người nhận',
  `receiver_email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email người nhận',
  `shipping_address` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Địa chỉ giao hàng',
  `shipping_province_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Mã tỉnh (shipping)',
  `shipping_district_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Mã quận (shipping)',
  `shipping_ward_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Mã phường (shipping)',
  `shipping_fee` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Phí vận chuyển',
  `tax` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Thuế áp dụng',
  `total` decimal(15,2) NOT NULL DEFAULT '0.00' COMMENT 'Tổng thanh toán',
  `payment_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Phương thức thanh toán',
  `payment_status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái thanh toán',
  `transaction_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã giao dịch từ cổng thanh toán',
  `shipping_partner` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đối tác vận chuyển',
  `shipping_tracking_code` text COLLATE utf8mb4_unicode_ci COMMENT 'Mã vận đơn',
  `shipping_raw_response` json DEFAULT NULL COMMENT 'Phản hồi gốc từ đối tác vận chuyển',
  `delivery_status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái giao hàng',
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đơn có sản phẩm flash sale hay không',
  `customer_note` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú từ khách hàng',
  `admin_note` text COLLATE utf8mb4_unicode_ci COMMENT 'Ghi chú nội bộ',
  `voucher_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID voucher áp dụng',
  `shipping_method` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Phương thức giao hàng',
  `status` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'Trạng thái đơn hàng',
  `note` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ghi chú',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Địa chỉ IP đặt hàng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_code_unique` (`code`),
  KEY `orders_account_id_index` (`account_id`),
  KEY `orders_session_id_index` (`session_id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính sản phẩm trong đơn',
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã định danh item',
  `order_id` bigint UNSIGNED NOT NULL COMMENT 'ID đơn hàng',
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID sản phẩm',
  `is_flash_sale` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Đánh dấu sản phẩm flash sale',
  `flash_sale_item_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID flash sale item (nếu có)',
  `product_variant_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID biến thể sản phẩm (nếu có)',
  `quantity` int UNSIGNED NOT NULL COMMENT 'Số lượng',
  `price` decimal(15,2) NOT NULL COMMENT 'Giá tại thời điểm mua',
  `total` decimal(15,2) NOT NULL COMMENT 'Thành tiền',
  `options` json DEFAULT NULL COMMENT 'Thuộc tính sản phẩm',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `account_id` bigint UNSIGNED DEFAULT NULL,
  `method` enum('cod','bank_transfer','qr','momo','zalopay','vnpay','credit_card','payos') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `gateway` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_code` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `raw_response` json DEFAULT NULL,
  `card_brand` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_four` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `receipt_url` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','success','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_foreign` (`order_id`),
  KEY `payments_account_id_foreign` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính token cá nhân',
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên token',
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Giá trị token',
  `abilities` text COLLATE utf8mb4_unicode_ci COMMENT 'Quyền hạn',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cuối cùng dùng token',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm hết hạn token',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính bài viết',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề bài viết',
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug thân thiện URL',
  `meta_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Meta title SEO',
  `meta_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Meta description SEO',
  `meta_keywords` text COLLATE utf8mb4_unicode_ci COMMENT 'Meta keywords SEO',
  `meta_canonical` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Canonical URL',
  `tag_ids` json DEFAULT NULL COMMENT 'Danh sách tag (JSON)',
  `excerpt` text COLLATE utf8mb4_unicode_ci COMMENT 'Tóm tắt bài viết',
  `content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung chi tiết',
  `image_ids` json DEFAULT NULL COMMENT 'Danh sách ảnh (JSON)',
  `status` enum('draft','pending','published','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft' COMMENT 'Trạng thái xuất bản',
  `is_featured` tinyint NOT NULL DEFAULT '0' COMMENT 'Đánh dấu bài viết nổi bật',
  `views` bigint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Lượt xem',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản sở hữu',
  `category_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID danh mục bài viết',
  `published_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm xuất bản',
  `created_by` bigint UNSIGNED NOT NULL COMMENT 'ID người tạo',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_account_id_foreign` (`account_id`),
  KEY `posts_category_id_foreign` (`category_id`),
  KEY `posts_created_by_foreign` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `post_revisions`
--

DROP TABLE IF EXISTS `post_revisions`;
CREATE TABLE IF NOT EXISTS `post_revisions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính bản nháp bài viết',
  `post_id` bigint UNSIGNED NOT NULL COMMENT 'ID bài viết gốc',
  `edited_by` bigint UNSIGNED DEFAULT NULL COMMENT 'ID người chỉnh sửa',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tiêu đề phiên bản',
  `content` longtext COLLATE utf8mb4_unicode_ci COMMENT 'Nội dung phiên bản',
  `excerpt` text COLLATE utf8mb4_unicode_ci COMMENT 'Tóm tắt phiên bản',
  `meta` json DEFAULT NULL COMMENT 'Dữ liệu meta (JSON)',
  `is_autosave` tinyint NOT NULL DEFAULT '0' COMMENT 'Đánh dấu autosave',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_revisions_post_id_foreign` (`post_id`),
  KEY `post_revisions_edited_by_foreign` (`edited_by`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính sản phẩm',
  `sku` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã SKU (không biến thể)',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên sản phẩm',
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug sản phẩm',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả chi tiết',
  `short_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả ngắn',
  `price` decimal(10,2) NOT NULL COMMENT 'Giá bán niêm yết',
  `sale_price` decimal(10,2) DEFAULT NULL COMMENT 'Giá khuyến mãi',
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT 'Giá vốn',
  `stock_quantity` int NOT NULL DEFAULT '0' COMMENT 'Tồn kho hiện tại',
  `meta_title` text COLLATE utf8mb4_unicode_ci COMMENT 'Meta title SEO',
  `meta_description` text COLLATE utf8mb4_unicode_ci COMMENT 'Meta description SEO',
  `meta_keywords` json DEFAULT NULL COMMENT 'Meta keywords (JSON)',
  `meta_canonical` text COLLATE utf8mb4_unicode_ci COMMENT 'Canonical URL',
  `primary_category_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Danh mục chính',
  `category_included_ids` json DEFAULT NULL COMMENT 'Danh sách danh mục dùng để gợi ý sản phẩm đi kèm',
  `category_ids` json DEFAULT NULL COMMENT 'Danh sách danh mục (JSON)',
  `tag_ids` json DEFAULT NULL COMMENT 'Danh sách tag (JSON)',
  `image_ids` json DEFAULT NULL COMMENT 'Danh sách ảnh (JSON)',
  `is_featured` tinyint NOT NULL DEFAULT '0' COMMENT 'Sản phẩm nổi bật',
  `locked_by` bigint UNSIGNED DEFAULT NULL COMMENT 'ID người đang khóa chỉnh sửa',
  `locked_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm khóa',
  `created_by` bigint UNSIGNED NOT NULL COMMENT 'ID người tạo sản phẩm',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT 'Trạng thái hiển thị',
  `category_ids_backup` json DEFAULT NULL COMMENT 'Backup danh mục (nếu cần)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_created_by_index` (`created_by`),
  KEY `products_locked_by_foreign` (`locked_by`),
  KEY `products_primary_category_id_index` (`primary_category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_faqs`
--

DROP TABLE IF EXISTS `product_faqs`;
CREATE TABLE IF NOT EXISTS `product_faqs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính FAQ sản phẩm',
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID sản phẩm',
  `question` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Câu hỏi của khách hàng',
  `answer` text COLLATE utf8mb4_unicode_ci COMMENT 'Câu trả lời',
  `order` int NOT NULL DEFAULT '0' COMMENT 'Thứ tự',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_faqs_product_id_foreign` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_how_tos`
--

DROP TABLE IF EXISTS `product_how_tos`;
CREATE TABLE IF NOT EXISTS `product_how_tos` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính hướng dẫn sử dụng',
  `product_id` bigint UNSIGNED NOT NULL COMMENT 'ID sản phẩm',
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề hướng dẫn',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả tổng quan',
  `steps` json DEFAULT NULL COMMENT 'Danh sách bước (JSON)',
  `supplies` json DEFAULT NULL COMMENT 'Dụng cụ cần thiết (JSON)',
  `is_active` tinyint NOT NULL DEFAULT '1' COMMENT 'Trạng thái hiển thị',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_how_tos_product_id_foreign` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `product_views`
--

DROP TABLE IF EXISTS `product_views`;
CREATE TABLE IF NOT EXISTS `product_views` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'User đã đăng nhập',
  `session_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Session ID cho user chưa đăng nhập',
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `viewed_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_views_product_id_viewed_at_index` (`product_id`,`viewed_at`),
  KEY `product_views_account_id_viewed_at_index` (`account_id`,`viewed_at`),
  KEY `product_views_session_id_viewed_at_index` (`session_id`,`viewed_at`),
  KEY `product_views_viewed_at_index` (`viewed_at`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `profiles`
--

DROP TABLE IF EXISTS `profiles`;
CREATE TABLE IF NOT EXISTS `profiles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính hồ sơ tài khoản',
  `account_id` bigint UNSIGNED NOT NULL COMMENT 'ID tài khoản',
  `fullname` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Họ và tên',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại',
  `avatar` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh đại diện',
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Giới tính',
  `birthday` date DEFAULT NULL COMMENT 'Ngày sinh',
  `extra` json DEFAULT NULL COMMENT 'Dữ liệu mở rộng (JSON)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  UNIQUE KEY `profiles_account_id_unique` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Session ID',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP đăng nhập',
  `user_agent` text COLLATE utf8mb4_unicode_ci COMMENT 'Thông tin trình duyệt',
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Dữ liệu session',
  `last_activity` int NOT NULL COMMENT 'Hoạt động cuối (timestamp)',
  PRIMARY KEY (`id`),
  KEY `sessions_account_id_index` (`account_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính cấu hình',
  `key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Khóa cấu hình (duy nhất)',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Giá trị cấu hình',
  `type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string' COMMENT 'Kiểu dữ liệu (string, boolean, image, ...)',
  `group` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nhóm cấu hình',
  `label` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Nhãn hiển thị',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả chi tiết',
  `is_public` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Có hiển thị công khai hay không',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm tạo',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm cập nhật',
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=MyISAM AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `label`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(37, 'google_tag_body', '', 'text', 'general', 'Thẻ Google Body', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 09:04:34'),
(30, 'dmca_logo', 'https://images.dmca.com/Badges/DMCA_logo-grn-btn120w.png?ID=2fe593a9-a802-4d1b-9501-c3654a4771ed', 'text', 'general', 'Chống sao chép (IMG)', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 09:05:06'),
(31, 'bo_cong_thuong', 'http://online.gov.vn/Home/WebDetails/137357', 'image', 'general', 'Ảnh bộ công thương', 'setting-bo_cong_thuong-1757497818.webp', 1, '2025-06-10 16:10:59', '2025-09-09 19:50:18'),
(32, 'contact_zalo', '0398951396', 'text', 'general', 'Zalo Đức Nobi 💖', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 20:11:52'),
(33, 'telegram_link', 'https://discord.gg/nobifashion', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(34, 'discord_link', 'https://discord.gg/nobifashion', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(35, 'site_image', '', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(36, 'google_tag_header', '<!-- Google Tag Manager -->\r\n<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':\r\nnew Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],\r\nj=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=\r\n\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);\r\n})(window,document,\'script\',\'dataLayer\',\'GTM-KPTC863L\');</script>\r\n<!-- End Google Tag Manager -->\r\n<!-- Google tag (gtag.js) -->\r\n<script async src=\"https://www.googletagmanager.com/gtag/js?id=AW-17013569193\"></script>\r\n<script>\r\n  window.dataLayer = window.dataLayer || [];\r\n  function gtag(){dataLayer.push(arguments);}\r\n  gtag(\'js\', new Date());\r\n\r\n  gtag(\'config\', \'AW-17013569193\');\r\n</script>\r\n<!-- Event snippet for Lượt xem trang conversion page -->\r\n<script>\r\n  gtag(\'event\', \'conversion\', {\r\n      \'send_to\': \'AW-17013569193/3VCDCPK1zrkaEKnt2bA_\',\r\n      \'value\': 1.0,\r\n      \'currency\': \'VND\'\r\n  });\r\n</script>', 'text', 'general', 'Google Tag Header', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 09:06:05'),
(29, 'dmca', '', 'text', 'general', 'Chống sao chép', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 09:04:00'),
(28, 'instagram_link', 'https://www.facebook.com/ducnobi2004', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(27, 'google_search_console', '', 'text', 'general', 'Google Search Console', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 09:05:49'),
(26, 'site_title', 'NOBI FASHION – Shop Quần Áo Nam Nữ Đẹp, Mix & Match Chuẩn Gu', 'text', 'general', 'Meta title - Trang chủ', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 20:05:42'),
(25, 'site_slug', '/', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(24, 'site_url', 'http://127.0.0.1:8000', 'url', 'general', 'Domain', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 19:54:44'),
(23, 'allow_file_uploads', '1', 'boolean', 'general', 'Cho phép tải lên FILE', 'Cho phép tải lên FILE', 1, '2025-06-10 16:10:59', '2025-09-09 19:43:04'),
(22, 'contact_form_recipient', 'xanhworldvietnam@gmail.com', 'email', 'general', 'Email nhận thông tin', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 08:55:55'),
(21, 'copyright', '<p>Copyright &copy; {{ ((2025 != now()->year & 2025 < now()->year) ? \'2025 - \' : \'\'). now()->year }} <a style=\"color: green;\" href=\"{{ $settings->site_url }}\">{{ $settings->site_name }}</a>. All Rights Reserved.</p>', 'textarea', 'general', 'Copyright Footer', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 20:09:08'),
(20, 'maintenance_mode', '0', 'boolean', 'general', 'Chế độ bảo trì', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 20:07:44'),
(19, 'enable_ssl', 'true', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(17, 'site_timezone', 'Asia/Ho_Chi_Minh', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(18, 'site_language', 'vi', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(16, 'enable_newsletter', 'true', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(15, 'enable_comments', 'true', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(13, 'google_analytics', '', 'text', 'general', 'Google Analytics', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 09:05:31'),
(14, 'enable_registration', 'true', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(12, 'twitter_link', 'https://twitter.com/', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(11, 'facebook_link', 'https://www.facebook.com/ducnobi2004', 'url', 'general', 'Facebook Đức Nobi', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 19:51:01'),
(9, 'seo_keywords', 'cây xanh, cây cảnh, cây văn phòng, cây phong thủy, cây nội thất, cây để bàn, cây trang trí, chậu cây cảnh, chậu xi măng, phụ kiện decor, setup góc làm việc, setup ban công, cây cảnh mini, cây cảnh để bàn, cây cảnh trong nhà, cây lọc không khí, cây hợp mệnh, cây thủy sinh, shop cây cảnh, xanhworld, xanh world', 'text', 'general', 'Keywords chính - Trang chủ', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 08:44:27'),
(10, 'seo_author', 'Nguyễn Minh Đức (Đức Nobi)', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(6, 'contact_email', 'xanhworldvietnam@gmail.com', 'email', 'general', 'Email hỗ trợ', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 20:00:38'),
(7, 'contact_phone', '0827786198', 'text', 'general', 'Số điện thoại Đức Nobi 💖', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 20:12:31'),
(8, 'contact_address', 'Xóm 3 - Xã Hà Đông - Thành Phố Hải Phòng', 'text', 'general', 'Địa chỉ bán hàng', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 09:13:17'),
(5, 'site_favicon', 'favicon-xanhworld.vn.svg', 'image', 'general', 'Favicon chính', NULL, 1, '2025-06-10 16:10:59', '2025-09-15 18:03:08'),
(4, 'site_announcement', 'MIỄN PHÍ VẬN CHUYỂN...', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(3, 'site_logo', 'logo-xanhworld.vn.svg', 'image', 'general', 'Ảnh Logo', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 19:40:05'),
(2, 'site_description', 'THẾ GIỚI CÂY XANH XWORLD – cây phong thủy, cây để bàn, cây lọc không khí, chậu cảnh đẹp. Decor không gian sống xanh – đẹp – bền. Giao cây tận nơi.', 'text', 'general', 'Mô tả SEO', NULL, 1, '2025-06-10 16:10:59', '2025-12-03 08:46:31'),
(1, 'site_name', 'THẾ GIỚI CÂY XANH XWORLD', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', '2025-06-10 16:10:59'),
(38, 'site_pinterest', '', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', NULL),
(39, 'site_banner', 'banner.webp', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', NULL),
(40, 'url_banner', 'setting-url_banner-1757498296.png', 'image', 'general', 'Banner chính', NULL, 1, '2025-06-10 16:10:59', '2025-09-09 19:58:16'),
(41, 'bing_tag_header', '', 'text', 'general', '', NULL, 1, '2025-06-10 16:10:59', NULL),
(45, 'city', 'Hải Phòng', 'text', 'general', 'Thành phố', NULL, 1, '2025-07-16 02:46:33', '2025-12-03 08:56:10'),
(43, 'postalCode', '180000', 'text', 'general', '', NULL, 1, '2025-06-11 00:31:16', NULL),
(44, 'subname', 'XWORLD', 'text', 'general', '', NULL, 1, '2025-06-16 00:23:46', NULL),
(46, 'district', 'Hải Phòng', 'text', 'general', 'Quận/Huyện', NULL, 1, '2025-07-16 02:47:28', '2025-12-03 08:57:02'),
(47, 'ward', 'PHƯỜNG LÃM HÀ', 'text', 'general', '', NULL, 1, '2025-07-16 02:47:28', NULL),
(48, 'detail_address', 'Xóm 3 - Xã Hà Đông - Thành Phố Hải Phòng', 'text', 'general', 'Địa chỉ bán hàng', NULL, 1, '2025-07-16 02:47:28', '2025-12-03 08:55:15'),
(49, 'latitude', '20.83943', 'text', 'general', '', NULL, 1, '2025-07-20 01:55:34', NULL),
(50, 'longitude', '106.65338', 'text', 'general', '', NULL, 1, '2025-07-20 01:55:34', NULL),
(51, 'enable_cart', 'true', 'text', 'general', '', NULL, 1, '2025-07-21 13:54:49', NULL),
(52, 'enable_order', 'true', 'text', 'general', '', NULL, 1, '2025-07-21 13:54:49', NULL),
(53, 'enable_payment', 'true', 'text', 'general', '', NULL, 1, '2025-07-21 13:57:43', NULL),
(54, 'is_demo', 'true', 'boolean', 'general', '', NULL, 1, '2025-07-21 13:57:43', NULL),
(55, 'site_tax_code', '030204013643', 'string', 'general', 'Mã số thuế', NULL, 1, '2025-10-31 00:15:00', '2025-11-17 00:45:39'),
(56, 'source_map', 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15676.49041047659!2d106.688084!3d10.780834!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f2bc3a8979f%3A0xe5a3028117c2f383!2sHo%20Chi%20Minh%20City!5e0!3m2!1svi!2s!4v1764171111111!5m2!1svi!2s', 'string', 'general', 'Map XWORLD', NULL, 1, '2025-11-28 12:13:35', '2025-12-02 02:56:41');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sitemap_configs`
--

DROP TABLE IF EXISTS `sitemap_configs`;
CREATE TABLE IF NOT EXISTS `sitemap_configs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính cấu hình sitemap',
  `config_key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `config_value` text COLLATE utf8mb4_unicode_ci,
  `value_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sitemap_configs_config_key_unique` (`config_key`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sitemap_configs`
--

INSERT INTO `sitemap_configs` (`id`, `config_key`, `config_value`, `value_type`, `created_at`, `updated_at`) VALUES
(1, 'enabled', '1', 'boolean', NULL, NULL),
(2, 'posts_enabled', '1', 'boolean', NULL, NULL),
(3, 'products_enabled', '1', 'boolean', NULL, NULL),
(4, 'categories_enabled', '1', 'boolean', NULL, NULL),
(5, 'tags_enabled', '1', 'boolean', NULL, NULL),
(6, 'pages_enabled', '1', 'boolean', NULL, NULL),
(7, 'images_enabled', '1', 'boolean', NULL, '2025-12-02 08:26:20'),
(8, 'ping_google_enabled', '1', 'boolean', NULL, NULL),
(9, 'ping_bing_enabled', '1', 'boolean', NULL, NULL),
(10, 'urls_per_file', '10000', 'integer', NULL, NULL),
(11, 'last_generated_at', '2025-12-02 15:26:05', 'datetime', NULL, '2025-12-02 08:26:05');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sitemap_excludes`
--

DROP TABLE IF EXISTS `sitemap_excludes`;
CREATE TABLE IF NOT EXISTS `sitemap_excludes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính loại trừ sitemap',
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'url',
  `value` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sitemap_excludes_type_value_unique` (`type`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tags`
--

DROP TABLE IF EXISTS `tags`;
CREATE TABLE IF NOT EXISTS `tags` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính tag',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên tag',
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug tag',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả tag',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Kích hoạt',
  `usage_count` bigint UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Số lần dùng',
  `entity_id` bigint UNSIGNED NOT NULL COMMENT 'ID của entity',
  `entity_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Loại entity: product/post/...',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tags_slug_unique` (`slug`),
  KEY `tags_entity_index` (`entity_id`,`entity_type`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vouchers`
--

DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính voucher',
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã voucher',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tên voucher',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả voucher',
  `image` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ảnh hiển thị',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'Người tạo',
  `updated_by` bigint UNSIGNED DEFAULT NULL COMMENT 'Người cập nhật',
  `type` enum('percent','fixed','free_shipping') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent' COMMENT 'Loại voucher',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Giá trị voucher',
  `max_discount` decimal(10,2) DEFAULT NULL COMMENT 'Giảm tối đa',
  `min_order_value` int UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Giá trị đơn tối thiểu',
  `usage_limit` int DEFAULT NULL COMMENT 'Giới hạn dùng tối đa',
  `usage_limit_per_user` int DEFAULT NULL COMMENT 'Giới hạn mỗi người',
  `start_time` datetime DEFAULT NULL COMMENT 'Thời gian bắt đầu',
  `end_time` datetime DEFAULT NULL COMMENT 'Thời gian kết thúc',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái kích hoạt',
  `apply_for` json DEFAULT NULL COMMENT 'Điều kiện áp dụng (JSON)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vouchers_code_unique` (`code`),
  KEY `vouchers_account_id_index` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `vouchers`
--

INSERT INTO `vouchers` (`id`, `code`, `name`, `description`, `image`, `account_id`, `updated_by`, `type`, `value`, `max_discount`, `min_order_value`, `usage_limit`, `usage_limit_per_user`, `start_time`, `end_time`, `is_active`, `apply_for`, `created_at`, `updated_at`) VALUES
(1, 'SALE10', 'Giảm 10% đơn hàng', 'Giảm 10% cho tất cả sản phẩm, tối đa 50.000đ.', 'clients/assets/img/vouchers/1764642888_wQZpJWqiKl.webp', NULL, NULL, 'percent', 10.00, 50000.00, 0, 500, 1, '2025-01-01 00:00:00', '2025-12-31 23:59:00', 1, NULL, '2025-11-25 09:50:46', '2025-12-02 02:34:49'),
(2, 'GIAM30K', 'Giảm 30.000đ', 'Giảm trực tiếp 30.000đ cho mọi đơn từ 199.000đ.', NULL, NULL, NULL, 'fixed', 30000.00, NULL, 199000, 300, 2, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, '{\"type\": \"all\"}', '2025-11-25 09:50:46', '2025-11-25 09:50:46'),
(3, 'FREESHIP25K', 'Miễn phí vận chuyển', 'Miễn phí vận chuyển tối đa 25.000đ cho đơn từ 99.000đ.', NULL, NULL, NULL, 'free_shipping', 25000.00, 25000.00, 99000, 1000, 2, '2025-01-01 00:00:00', '2025-12-31 23:59:59', 1, '{\"type\": \"shipping\"}', '2025-11-25 09:50:46', '2025-11-25 09:50:46'),
(4, 'AOSOMI15', 'Giảm 15% Áo sơ mi', 'Giảm 15% cho các sản phẩm thuộc danh mục Áo sơ mi.', NULL, NULL, NULL, 'percent', 15.00, 60000.00, 0, 200, 1, '2025-01-01 00:00:00', '2025-06-30 23:59:59', 0, '{\"type\": \"category\", \"category_ids\": [10]}', '2025-11-25 09:50:46', '2025-12-02 02:08:47'),
(7, 'SALE10-FQ1W', 'Giảm 10% đơn hàng', 'Giảm 10% cho tất cả sản phẩm, tối đa 50.000đ.', NULL, NULL, NULL, 'percent', 10.00, 50000.00, 0, 500, 1, '2025-12-03 09:13:00', '2026-01-07 23:59:00', 0, NULL, '2025-12-02 02:13:10', '2025-12-02 02:13:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `voucher_histories`
--

DROP TABLE IF EXISTS `voucher_histories`;
CREATE TABLE IF NOT EXISTS `voucher_histories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính lịch sử dùng voucher',
  `voucher_id` bigint UNSIGNED NOT NULL COMMENT 'ID voucher',
  `order_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID đơn hàng',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT 'Số tiền giảm',
  `ip` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IP dùng voucher',
  `session_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Session khách',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `voucher_histories_voucher_id_index` (`voucher_id`),
  KEY `voucher_histories_order_id_index` (`order_id`),
  KEY `voucher_histories_account_id_index` (`account_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `voucher_user_usages`
--

DROP TABLE IF EXISTS `voucher_user_usages`;
CREATE TABLE IF NOT EXISTS `voucher_user_usages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Khóa chính thống kê sử dụng voucher',
  `voucher_id` bigint UNSIGNED NOT NULL COMMENT 'ID voucher',
  `account_id` bigint UNSIGNED DEFAULT NULL COMMENT 'ID tài khoản',
  `session_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Session khách vãng lai',
  `usage_count` int NOT NULL DEFAULT '0' COMMENT 'Số lần dùng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucher_user_usages_unique` (`voucher_id`,`account_id`,`session_id`),
  KEY `voucher_user_usages_voucher_id_index` (`voucher_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
