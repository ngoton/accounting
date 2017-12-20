-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Client :  127.0.0.1
-- Généré le :  Jeu 23 Novembre 2017 à 07:13
-- Version du serveur :  5.6.16
-- Version de PHP :  5.5.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `accounting`
--

-- --------------------------------------------------------

--
-- Structure de la table `account`
--

CREATE TABLE IF NOT EXISTS `account` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_number` varchar(20) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `account_parent` int(11) DEFAULT NULL,
  `account_type` int(11) DEFAULT NULL,
  `account_code` varchar(10) DEFAULT NULL,
  `account_debit_dauky` decimal(14,2) DEFAULT NULL,
  `account_credit_dauky` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=121 ;

--
-- Contenu de la table `account`
--

INSERT INTO `account` (`account_id`, `account_number`, `account_name`, `account_parent`, `account_type`, `account_code`, `account_debit_dauky`, `account_credit_dauky`) VALUES
(1, '111', 'Tiền mặt', NULL, 1, '110', NULL, NULL),
(2, '1111', 'Tiền Việt Nam', 1, 0, '110', '2000000.00', '1000000.00'),
(3, '1112', 'Ngoại tệ', 1, NULL, '110', NULL, NULL),
(4, '112', 'Tiền gửi Ngân hàng', NULL, 1, '110', NULL, NULL),
(5, '1121', 'Tiền Việt Nam', 4, NULL, '110', NULL, NULL),
(6, '1122', 'Ngoại tệ', 4, NULL, '110', NULL, NULL),
(7, '121', 'Chứng khoán kinh doanh', NULL, 1, '121', NULL, NULL),
(8, '128', 'Đầu tư nắm giữ đến ngày đáo hạn', NULL, 1, '122', NULL, NULL),
(9, '1281', 'Tiền gửi có kỳ hạn', 8, NULL, '122', NULL, NULL),
(10, '1288', 'Các khoản đầu tư khác nắm giữ đến ngày đáo hạn', 8, NULL, '122', NULL, NULL),
(11, '131', 'Phải thu của khách hàng', NULL, 1, '131', NULL, NULL),
(12, '133', 'Thuế GTGT được khấu trừ', NULL, 1, '181', NULL, NULL),
(13, '1331', 'Thuế GTGT được khấu trừ của hàng hóa, dịch vụ', 12, 0, '181', '99999999.99', '0.00'),
(14, '1332', 'Thuế GTGT được khấu trừ của TSCĐ', 12, NULL, '181', NULL, NULL),
(15, '136', 'Phải thu nội bộ', NULL, 1, '134', NULL, NULL),
(16, '1361', 'Vốn kinh doanh ở đơn vị trực thuộc', 15, NULL, '133', NULL, NULL),
(17, '1368', 'Phải thu nội bộ khác', 15, NULL, '134', NULL, NULL),
(18, '138', 'Phải thu khác', NULL, 1, '134', NULL, NULL),
(19, '1381', 'Tài sản thiếu chờ xử lý', 18, NULL, '135', NULL, NULL),
(20, '1386', 'Cầm cố, thế chấp, ký quỹ, ký cược', 18, NULL, '134', NULL, NULL),
(21, '1388', 'Phải thu khác', 18, NULL, '134', NULL, NULL),
(22, '141', 'Tạm ứng', NULL, 1, '134', NULL, NULL),
(23, '151', 'Hàng mua đang đi đường', NULL, 1, '141', NULL, NULL),
(24, '152', 'Nguyên liệu, vật liệu', NULL, 1, '141', NULL, NULL),
(25, '153', 'Công cụ, dụng cụ', NULL, 1, '141', NULL, NULL),
(26, '154', 'Chi phí sản xuất, kinh doanh dở dang', NULL, 1, '141', NULL, NULL),
(27, '155', 'Thành phẩm', NULL, 1, '141', NULL, NULL),
(28, '156', 'Hàng hóa', NULL, 1, '141', NULL, NULL),
(29, '157', 'Hàng gửi đi bán', NULL, 1, '141', NULL, NULL),
(30, '211', 'Tài sản cố định', NULL, 1, '151', NULL, NULL),
(31, '2111', 'TSCĐ hữu hình', 30, NULL, '151', NULL, NULL),
(32, '2112', 'TSCĐ thuê tài chính', 30, NULL, '151', NULL, NULL),
(33, '2113', 'TSCĐ vô hình', 30, NULL, '151', NULL, NULL),
(34, '214', 'Hao mòn tài sản cố định', NULL, 1, '152', NULL, NULL),
(35, '2141', 'Hao mòn TSCĐ hữu hình', 34, NULL, '152', NULL, NULL),
(36, '2142', 'Hao mòn TSCĐ thuê tài chính', 34, NULL, '152', NULL, NULL),
(37, '2147', 'Hao mòn bất động sản đầu tư', 34, NULL, '162', NULL, NULL),
(38, '217', 'Bất động sản đầu tư', NULL, 1, '161', NULL, NULL),
(39, '228', 'Đầu tư góp vốn vào đơn vị khác', NULL, 1, '123', NULL, NULL),
(40, '2281', 'Đầu tư vào công ty liên doanh, liên kết', 39, NULL, '123', NULL, NULL),
(41, '2288', 'Đầu tư khác', 39, NULL, '123', NULL, NULL),
(42, '229', 'Dự phòng tổn thất tài sản', NULL, 1, NULL, NULL, NULL),
(43, '2291', 'Dự phòng giảm giá chứng khoán kinh doanh', 42, NULL, '124', NULL, NULL),
(44, '2292', 'Dự phòng tổn thất đầu tư vào đơn vị khác', 42, NULL, '124', NULL, NULL),
(45, '2293', 'Dự phòng phải thu khó đòi', 42, NULL, '136', NULL, NULL),
(46, '2294', 'Dự phòng giảm giá hàng tồn kho', 42, NULL, '142', NULL, NULL),
(47, '241', 'Xây dựng cơ bản dở dang', NULL, 1, '170', NULL, NULL),
(48, '2411', 'Mua sắm TSCĐ', 47, NULL, '170', NULL, NULL),
(49, '2412', 'Xây dựng cơ bản', 47, NULL, '170', NULL, NULL),
(50, '2413', 'Sửa chữa lớn TSCĐ', 47, NULL, '170', NULL, NULL),
(51, '242', 'Chi phí trả trước', NULL, 1, '182', NULL, NULL),
(52, '331', 'Phải trả cho người bán', NULL, 2, '311', NULL, NULL),
(53, '333', 'Thuế và các khoản phải nộp Nhà nước', NULL, 2, '313', NULL, NULL),
(54, '3331', 'Thuế giá trị gia tăng phải nộp', 53, NULL, '313', NULL, NULL),
(55, '33311', 'Thuế GTGT đầu ra', 53, NULL, '313', NULL, NULL),
(56, '33312', 'Thuế GTGT hàng nhập khẩu', 53, NULL, '313', NULL, NULL),
(57, '3332', 'Thuế tiêu thụ đặc biệt', 53, NULL, '313', NULL, NULL),
(58, '3333', 'Thuế xuất, nhập khẩu', 53, NULL, '313', NULL, NULL),
(59, '3334', 'Thuế thu nhập doanh nghiệp', 53, NULL, '313', NULL, NULL),
(60, '3335', 'Thuế thu nhập cá nhân', 53, NULL, '313', NULL, NULL),
(61, '3336', 'Thuế tài nguyên', 53, NULL, '313', NULL, NULL),
(62, '3337', 'Thuế nhà đất, tiền thuê đất', 53, NULL, '313', NULL, NULL),
(63, '3338', 'Thuế bảo vệ môi trường và các loại thuế khác', 53, NULL, '313', NULL, NULL),
(64, '33381', 'Thuế bảo vệ môi trường', 53, NULL, '313', NULL, NULL),
(65, '33382', 'Các loại thuế khác', 53, NULL, '313', NULL, NULL),
(66, '3339', 'Phí, lệ phí và các khoản phải nộp khác', 53, NULL, '313', NULL, NULL),
(67, '334', 'Phải trả người lao động', NULL, 2, '314', NULL, NULL),
(68, '335', 'Chi phí phải trả', NULL, 2, '315', NULL, NULL),
(69, '336', 'Phải trả nội bộ', NULL, 2, NULL, NULL, NULL),
(70, '3361', 'Phải trả nội bộ về vốn kinh doanh', 69, NULL, '317', NULL, NULL),
(71, '3368', 'Phải trả nội bộ khác', 69, NULL, '315', NULL, NULL),
(72, '338', 'Phải trả, phải nộp khác', NULL, 2, '315', NULL, NULL),
(73, '3381', 'Tài sản thừa chờ giải quyết', 72, NULL, '315', NULL, NULL),
(74, '3382', 'Kinh phí công đoàn', 72, NULL, '315', NULL, NULL),
(75, '3383', 'Bảo hiểm xã hội', 72, NULL, '315', NULL, NULL),
(76, '3384', 'Bảo hiểm y tế', 72, NULL, '315', NULL, NULL),
(77, '3385', 'Bảo hiểm thất nghiệp', 72, NULL, '315', NULL, NULL),
(78, '3386', 'Nhận ký quỹ, ký cược', 72, NULL, '315', NULL, NULL),
(79, '3387', 'Doanh thu chưa thực hiện', 72, NULL, '315', NULL, NULL),
(80, '3388', 'Phải trả, phải nộp khác', 72, NULL, '315', NULL, NULL),
(81, '341', 'Vay và nợ thuê tài chính', NULL, 2, '316', NULL, NULL),
(82, '3411', 'Các khoản đi vay', 81, NULL, '316', NULL, NULL),
(83, '3412', 'Nợ thuê tài chính', 81, NULL, '316', NULL, NULL),
(84, '352', 'Dự phòng phải trả', NULL, 2, '318', NULL, NULL),
(85, '3521', 'Dự phòng bảo hành sản phẩm hàng hóa', 84, NULL, '318', NULL, NULL),
(86, '3522', 'Dự phòng bảo hành công trình xây dựng', 84, NULL, '318', NULL, NULL),
(87, '3524', 'Dự phòng phải trả khác', 84, NULL, '318', NULL, NULL),
(88, '353', 'Quỹ khen thưởng phúc lợi', NULL, 2, '319', NULL, NULL),
(89, '3531', 'Quỹ khen thưởng', 88, NULL, '319', NULL, NULL),
(90, '3532', 'Quỹ phúc lợi', 88, NULL, '319', NULL, NULL),
(91, '3533', 'Quỹ phúc lợi đã hình thành TSCĐ', 88, NULL, '319', NULL, NULL),
(92, '3534', 'Quỹ thưởng ban quản lý điều hành công ty', 88, NULL, '319', NULL, NULL),
(93, '356', 'Quỹ phát triển khoa học và công nghệ', NULL, 2, '320', NULL, NULL),
(94, '3561', 'Quỹ phát triển khoa học và công nghệ', 93, NULL, '320', NULL, NULL),
(95, '3562', 'Quỹ phát triển khoa học và công nghệ đã hình thành', 93, NULL, '320', NULL, NULL),
(96, '411', 'Vốn đầu tư của chủ sở hữu', NULL, 3, '411', NULL, NULL),
(97, '4111', 'Thặng dư vốn cổ phần', 96, NULL, '412', NULL, NULL),
(98, '4118', 'Vốn khác', 96, NULL, '413', NULL, NULL),
(99, '413', 'Chênh lệch tỷ giá hối đoái', NULL, 3, '415', NULL, NULL),
(100, '418', 'Các quỹ thuộc vốn chủ sở hữu', NULL, 3, '416', NULL, NULL),
(101, '419', 'Cổ phiếu quỹ', NULL, 3, '414', NULL, NULL),
(102, '421', 'Lợi nhuận sau thuế chưa phân phối', NULL, 3, '417', NULL, NULL),
(103, '4211', 'Lợi nhuận sau thuế chưa phân phối năm trước', 102, NULL, '417', NULL, NULL),
(104, '4212', 'Lợi nhuận sau thuế chưa phân phối năm nay', 102, NULL, '417', NULL, NULL),
(105, '511', 'Doanh thu bán hàng và cung cấp dịch vụ', NULL, 4, '', NULL, NULL),
(106, '5111', 'Doanh thu cung cấp dịch vụ', 105, NULL, '', NULL, NULL),
(107, '5112', 'Doanh thu bán thành phẩm', 105, NULL, '', NULL, NULL),
(108, '5118', 'Doanh thu khác', 105, NULL, '', NULL, NULL),
(109, '515', 'Doanh thu hoạt động tài chính', NULL, 4, '', NULL, NULL),
(110, '611', 'Mua hàng', NULL, 5, '', NULL, NULL),
(111, '631', 'Giá thành sản xuất', NULL, 5, '', NULL, NULL),
(112, '632', 'Giá vốn hàng bán', NULL, 5, '', NULL, NULL),
(113, '635', 'Chi phí tài chính', NULL, 5, '', NULL, NULL),
(114, '642', 'Chi phí quản lý kinh doanh', NULL, 5, '', NULL, NULL),
(115, '6421', 'Chi phí bán hàng', 114, NULL, '', NULL, NULL),
(116, '6422', 'Chi phí quản lý doanh nghiệp', 114, NULL, '', NULL, NULL),
(117, '711', 'Thu nhập khác', NULL, 6, '', NULL, NULL),
(118, '811', 'Chi phí khác', NULL, 7, '', NULL, NULL),
(119, '821', 'Chi phí thuế thu nhập doanh nghiệp', NULL, 7, '', NULL, NULL),
(120, '911', 'Xác định kết quả kinh doanh', NULL, 8, '', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `account_type`
--

CREATE TABLE IF NOT EXISTS `account_type` (
  `account_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`account_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Contenu de la table `account_type`
--

INSERT INTO `account_type` (`account_type_id`, `account_type_name`) VALUES
(1, 'Tài sản'),
(2, 'Nợ phải trả'),
(3, 'Vốn chủ sở hữu'),
(4, 'Doanh thu'),
(5, 'Chi phí sản xuất, kinh doanh'),
(6, 'Thu nhập khác'),
(7, 'Chi phí khác'),
(8, 'Kết quả kinh doanh');

-- --------------------------------------------------------

--
-- Structure de la table `additional`
--

CREATE TABLE IF NOT EXISTS `additional` (
  `additional_id` int(11) NOT NULL AUTO_INCREMENT,
  `document_number` varchar(20) DEFAULT NULL,
  `document_date` int(11) DEFAULT NULL,
  `additional_date` int(11) DEFAULT NULL,
  `additional_comment` text,
  `debit` int(11) DEFAULT NULL,
  `credit` int(11) DEFAULT NULL,
  `money` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item` int(11) DEFAULT NULL,
  `service_buy_item` int(11) DEFAULT NULL,
  `invoice_sell_item` int(11) DEFAULT NULL,
  `invoice_purchase_item` int(11) DEFAULT NULL,
  `payment_item` int(11) DEFAULT NULL,
  `payment_cost` int(11) DEFAULT NULL,
  `internal_transfer_item` int(11) DEFAULT NULL,
  `additional_other` int(11) DEFAULT NULL,
  `additional_customer` int(11) DEFAULT NULL,
  `account` int(11) DEFAULT NULL,
  `items` int(11) DEFAULT NULL,
  `customer` int(11) DEFAULT NULL,
  `bank` int(11) DEFAULT NULL,
  PRIMARY KEY (`additional_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `additional`
--


-- --------------------------------------------------------

--
-- Structure de la table `additional_other`
--

CREATE TABLE IF NOT EXISTS `additional_other` (
  `additional_other_id` int(11) NOT NULL AUTO_INCREMENT,
  `additional_other_document_number` varchar(50) DEFAULT NULL,
  `additional_other_document_date` int(11) DEFAULT NULL,
  `additional_other_additional_date` int(11) DEFAULT NULL,
  `additional_other_customer` int(11) DEFAULT NULL,
  `additional_other_money` decimal(14,2) DEFAULT NULL,
  `additional_other_comment` varchar(255) DEFAULT NULL,
  `additional_other_debit` int(11) DEFAULT NULL,
  `additional_other_credit` int(11) DEFAULT NULL,
  `additional_other_bank` int(11) DEFAULT NULL,
  `additional_other_bank_check` int(11) DEFAULT NULL,
  `additional_other_tax_percent` DECIMAL(14,2) NULL ,
  `additional_other_tax` DECIMAL(14,2) NULL , 
  `additional_other_tax_debit` INT NULL , 
  `additional_other_tax_credit` INT NULL , 
  `additional_other_invoice_number` VARCHAR(20) NULL , 
  `additional_other_invoice_date` INT NULL , 
  `additional_other_invoice_symbol` VARCHAR(10) NULL ,
  PRIMARY KEY (`additional_other_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `additional_other`
--


-- --------------------------------------------------------

--
-- Structure de la table `asset`
--

CREATE TABLE IF NOT EXISTS `asset` (
  `asset_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(20) DEFAULT NULL,
  `asset_name` varchar(50) DEFAULT NULL,
  `asset_desc` varchar(200) DEFAULT NULL,
  `asset_symbol` varchar(20) DEFAULT NULL,
  `asset_date` int(11) DEFAULT NULL,
  `asset_country` varchar(20) DEFAULT NULL,
  `asset_warranty` int(11) DEFAULT NULL,
  `asset_accessory` varchar(200) DEFAULT NULL,
  `asset_type` int(11) DEFAULT NULL COMMENT '1:Nhà cửa, vật kiến trúc | 2:Máy móc, thiết bị | 3:Phương tiện vận tải, thiết bị truyền dẫn | 4:Thiết bị, dụng cụ quản lý | 5:Vườn cây lâu năm, súc vật làm việc và/hoặc cho sản phẩm | 6:Các loại tài sản cố định khác',
  `asset_manufacture` varchar(50) DEFAULT NULL,
  `asset_department` int(11) DEFAULT NULL,
  `asset_customer` int(11) DEFAULT NULL,
  `asset_document_number` varchar(20) DEFAULT NULL,
  `asset_document_date` int(11) DEFAULT NULL,
  `asset_status` int(11) DEFAULT NULL,
  `asset_buy_status` int(11) DEFAULT NULL COMMENT '1:Mới | 2:Cũ',
  `asset_start_date` int(11) DEFAULT NULL,
  `asset_debit` int(11) DEFAULT NULL,
  `asset_credit` int(11) DEFAULT NULL,
  `asset_cost_debit` int(11) DEFAULT NULL,
  `asset_origin_money` decimal(14,2) DEFAULT NULL,
  `asset_money` decimal(14,2) DEFAULT NULL,
  `asset_use_time` decimal(14,2) DEFAULT NULL,
  `asset_year_percent` decimal(14,2) DEFAULT NULL,
  `asset_month_percent` decimal(14,2) DEFAULT NULL,
  `asset_month_money` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `bank`
--

CREATE TABLE IF NOT EXISTS `bank` (
  `bank_id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_code` varchar(20) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `account_bank` varchar(50) DEFAULT NULL,
  `account_bank_branch` varchar(100) DEFAULT NULL,
  `bank_debit_dauky` decimal(14,2) NULL,
  PRIMARY KEY (`bank_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `bank`
--

-- --------------------------------------------------------

--
-- Structure de la table `bank_balance`
--

CREATE TABLE IF NOT EXISTS `bank_balance` (
  `bank_balance_id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_balance_date` int(11) DEFAULT NULL,
  `bank` int(11) DEFAULT NULL,
  `bank_balance_money` decimal(14,2) DEFAULT NULL,
  `payment` int(11) DEFAULT NULL,
  `internal_transfer_item` int(11) DEFAULT NULL,
  `additional_other` int(11) DEFAULT NULL,
  PRIMARY KEY (`bank_balance_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `bank_balance`
--



-- --------------------------------------------------------

--
-- Structure de la table `contact_person`
--

CREATE TABLE IF NOT EXISTS `contact_person` (
  `contact_person_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_person_name` varchar(50) DEFAULT NULL,
  `contact_person_address` varchar(100) DEFAULT NULL,
  `contact_person_phone` varchar(20) DEFAULT NULL,
  `contact_person_mobile` varchar(20) DEFAULT NULL,
  `contact_person_email` varchar(50) DEFAULT NULL,
  `contact_person_position` varchar(20) DEFAULT NULL,
  `contact_person_department` varchar(20) DEFAULT NULL,
  `customer` int(11) DEFAULT NULL,
  PRIMARY KEY (`contact_person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `customer`
--

CREATE TABLE IF NOT EXISTS `customer` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_company` varchar(200) DEFAULT NULL,
  `customer_mst` varchar(20) DEFAULT NULL,
  `customer_address` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_mobile` varchar(20) DEFAULT NULL,
  `customer_email` varchar(50) DEFAULT NULL,
  `customer_bank_account` int(11) DEFAULT NULL,
  `customer_bank_name` varchar(50) DEFAULT NULL,
  `customer_bank_branch` varchar(50) DEFAULT NULL,
  `customer_sub` varchar(20) DEFAULT NULL,
  `type_customer` int(11) DEFAULT NULL COMMENT '1:Khách hàng | 2:Đối tác',
  `customer_debit_dauky` decimal(14,2) DEFAULT NULL,
  `customer_credit_dauky` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `customer`
--


-- --------------------------------------------------------

--
-- Structure de la table `customer_sub`
--

CREATE TABLE IF NOT EXISTS `customer_sub` (
  `customer_sub_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_sub_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`customer_sub_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `debit`
--

CREATE TABLE IF NOT EXISTS `debit` (
  `debit_id` int(11) NOT NULL AUTO_INCREMENT,
  `debit_date` int(11) DEFAULT NULL,
  `debit_customer` int(11) DEFAULT NULL,
  `debit_money` decimal(14,2) DEFAULT NULL,
  `debit_money_foreign` decimal(14,2) DEFAULT NULL,
  `debit_comment` varchar(255) DEFAULT NULL,
  `service_buy` int(11) DEFAULT NULL,
  `invoice_buy` int(11) DEFAULT NULL,
  `invoice_sell` int(11) DEFAULT NULL,
  `invoice_purchase` int(11) DEFAULT NULL,
  `payment_item` int(11) DEFAULT NULL,
  `additional_other` int(11) DEFAULT NULL,
  `customer` int(11) DEFAULT NULL,
  PRIMARY KEY (`debit_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `debit`
--


-- --------------------------------------------------------

--
-- Structure de la table `department`
--

CREATE TABLE IF NOT EXISTS `department` (
  `department_id` int(11) NOT NULL AUTO_INCREMENT,
  `department_code` varchar(10) DEFAULT NULL,
  `department_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `house`
--

CREATE TABLE IF NOT EXISTS `house` (
  `house_id` int(11) NOT NULL AUTO_INCREMENT,
  `house_code` varchar(10) DEFAULT NULL,
  `house_name` varchar(20) DEFAULT NULL,
  `house_place` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`house_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `house`
--

INSERT INTO `house` (`house_id`, `house_code`, `house_name`, `house_place`) VALUES
(1, 'HH', 'Kho hàng', 'Trụ sở công ty');

-- --------------------------------------------------------

--
-- Structure de la table `info`
--

CREATE TABLE IF NOT EXISTS `info` (
  `info_id` int(11) NOT NULL AUTO_INCREMENT,
  `info_company` varchar(100) DEFAULT NULL,
  `info_mst` int(11) DEFAULT NULL,
  `info_address` varchar(200) DEFAULT NULL,
  `info_phone` varchar(20) DEFAULT NULL,
  `info_email` varchar(50) DEFAULT NULL,
  `info_director` varchar(50) DEFAULT NULL,
  `info_general_accountant` varchar(50) DEFAULT NULL,
  `info_accountant` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`info_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `info`
--

INSERT INTO `info` (`info_id`, `info_company`, `info_mst`, `info_address`, `info_phone`, `info_email`, `info_director`, `info_general_accountant`, `info_accountant`) VALUES
(1, 'CÔNG TY TNHH VIỆT TRA DE', 2147483647, 'Số 545, Tổ 10, Ấp Hương Phước, Xã Phước Tân, TP. Biên Hòa, Tỉnh Đồng Nai', '025 193 7677', 'it@viet-trade.org', 'Nguyễn Hoàng Minh Long', 'Phạm Hoài Thương Ly', 'Hoàng Minh Vy');

-- --------------------------------------------------------

--
-- Structure de la table `internal_transfer`
--

CREATE TABLE IF NOT EXISTS `internal_transfer` (
  `internal_transfer_id` int(11) NOT NULL AUTO_INCREMENT,
  `internal_transfer_document_date` int(11) DEFAULT NULL,
  `internal_transfer_document_number` varchar(20) DEFAULT NULL,
  `internal_transfer_additional_date` int(11) DEFAULT NULL,
  `internal_transfer_comment` varchar(255) DEFAULT NULL,
  `internal_transfer_money` decimal(14,2) DEFAULT NULL,
  `internal_transfer_create_user` int(11) DEFAULT NULL,
  `internal_transfer_create_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`internal_transfer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `internal_transfer_item`
--

CREATE TABLE IF NOT EXISTS `internal_transfer_item` (
  `internal_transfer_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `internal_transfer` int(11) DEFAULT NULL,
  `internal_transfer_item_out` int(11) DEFAULT NULL,
  `internal_transfer_item_in` int(11) DEFAULT NULL,
  `internal_transfer_item_money` decimal(14,2) DEFAULT NULL,
  `internal_transfer_item_debit` int(11) DEFAULT NULL,
  `internal_transfer_item_credit` int(11) DEFAULT NULL,
  PRIMARY KEY (`internal_transfer_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE IF NOT EXISTS `invoice` (
  `invoice_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_symbol` varchar(10) DEFAULT NULL,
  `invoice_date` int(11) DEFAULT NULL,
  `invoice_number` varchar(10) DEFAULT NULL,
  `invoice_customer` int(11) DEFAULT NULL,
  `invoice_money` decimal(14,2) DEFAULT NULL,
  `invoice_tax` decimal(14,2) DEFAULT NULL,
  `invoice_comment` varchar(255) DEFAULT NULL,
  `invoice_buy` int(11) DEFAULT NULL,
  `service_buy` int(11) DEFAULT NULL,
  `invoice_sell` int(11) DEFAULT NULL,
  `invoice_purchase` int(11) DEFAULT NULL,
  `invoice_type` int(11) DEFAULT NULL,
  `payment_cost` int(11) DEFAULT NULL,
  `account` int(11) DEFAULT NULL,
  `payment_item` int(11) DEFAULT NULL,
  `additional_other` int(11) DEFAULT NULL,
  PRIMARY KEY (`invoice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `invoice`
--

-- --------------------------------------------------------

--
-- Structure de la table `invoice_buy`
--

CREATE TABLE IF NOT EXISTS `invoice_buy` (
  `invoice_buy_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_buy_document_date` int(11) DEFAULT NULL,
  `invoice_buy_document_number` varchar(20) DEFAULT NULL,
  `invoice_buy_additional_date` int(11) DEFAULT NULL,
  `invoice_buy_customer` int(11) DEFAULT NULL,
  `invoice_buy_number` varchar(20) DEFAULT NULL,
  `invoice_buy_date` int(11) DEFAULT NULL,
  `invoice_buy_symbol` varchar(10) DEFAULT NULL,
  `invoice_buy_bill_number` varchar(20) DEFAULT NULL,
  `invoice_buy_contract_number` varchar(20) DEFAULT NULL,
  `invoice_buy_money_type` int(11) DEFAULT NULL COMMENT '1:VND | 2:USD',
  `invoice_buy_money_rate` decimal(14,2) DEFAULT NULL,
  `invoice_buy_origin_doc` varchar(20) DEFAULT NULL,
  `invoice_buy_comment` varchar(255) DEFAULT NULL,
  `invoice_buy_number_total` int(11) DEFAULT NULL,
  `invoice_buy_money` decimal(14,2) DEFAULT NULL,
  `invoice_buy_money_foreign` decimal(14,2) DEFAULT NULL,
  `invoice_buy_discount` decimal(14,2) DEFAULT NULL,
  `invoice_buy_tax_vat` decimal(14,2) DEFAULT NULL,
  `invoice_buy_tax_import` decimal(14,2) DEFAULT NULL,
  `invoice_buy_total` decimal(14,2) DEFAULT NULL,
  `invoice_buy_custom_cost` decimal(14,2) DEFAULT NULL,
  `invoice_buy_other_cost` decimal(14,2) DEFAULT NULL,
  `invoice_buy_create_user` int(11) DEFAULT NULL,
  `invoice_buy_create_date` int(11) DEFAULT NULL,
  `invoice_buy_allocation` int(11) DEFAULT NULL,
  `invoice_buy_allocation2` int(11) DEFAULT NULL,
  PRIMARY KEY (`invoice_buy_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `invoice_buy`
--

-- --------------------------------------------------------

--
-- Structure de la table `invoice_buy_item`
--

CREATE TABLE IF NOT EXISTS `invoice_buy_item` (
  `invoice_buy_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_buy` int(11) DEFAULT NULL,
  `invoice_buy_item` int(11) DEFAULT NULL,
  `invoice_buy_item_unit` varchar(10) DEFAULT NULL,
  `invoice_buy_item_number` int(11) DEFAULT NULL,
  `invoice_buy_item_price` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_money` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_price_discount` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_house` int(11) DEFAULT NULL,
  `invoice_buy_item_debit` int(11) DEFAULT NULL,
  `invoice_buy_item_credit` int(11) DEFAULT NULL,
  `invoice_buy_item_tax_vat` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_tax_import` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_tax_vat_percent` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_tax_import_percent` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_tax_vat_debit` int(11) DEFAULT NULL,
  `invoice_buy_item_tax_vat_credit` int(11) DEFAULT NULL,
  `invoice_buy_item_tax_import_debit` int(11) DEFAULT NULL,
  `invoice_buy_item_custom_rate` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_custom_cost_debit` int(11) DEFAULT NULL,
  `invoice_buy_item_custom_cost` decimal(14,6) DEFAULT NULL,
  `invoice_buy_item_custom_cost_money` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_other_cost` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_total` decimal(14,2) DEFAULT NULL,
  `invoice_buy_item_tax_total` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`invoice_buy_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `invoice_buy_item`
--

-- --------------------------------------------------------

--
-- Structure de la table `invoice_purchase`
--

CREATE TABLE IF NOT EXISTS `invoice_purchase` (
  `invoice_purchase_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_purchase_document_date` int(11) DEFAULT NULL,
  `invoice_purchase_document_number` varchar(20) DEFAULT NULL,
  `invoice_purchase_additional_date` int(11) DEFAULT NULL,
  `invoice_purchase_customer` int(11) DEFAULT NULL,
  `invoice_purchase_number` varchar(20) DEFAULT NULL,
  `invoice_purchase_date` int(11) DEFAULT NULL,
  `invoice_purchase_symbol` varchar(10) DEFAULT NULL,
  `invoice_purchase_bill_number` varchar(20) DEFAULT NULL,
  `invoice_purchase_contract_number` varchar(20) DEFAULT NULL,
  `invoice_purchase_money_type` int(11) DEFAULT NULL,
  `invoice_purchase_money_rate` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_origin_doc` varchar(20) DEFAULT NULL,
  `invoice_purchase_comment` varchar(255) DEFAULT NULL,
  `invoice_purchase_number_total` int(11) DEFAULT NULL,
  `invoice_purchase_money` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_money_foreign` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_discount` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_cost` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_tax_vat` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_total` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_create_user` int(11) DEFAULT NULL,
  `invoice_purchase_create_date` int(11) DEFAULT NULL,
  `invoice_purchase_allocation` int(11) DEFAULT NULL,
  PRIMARY KEY (`invoice_purchase_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `invoice_purchase`
--

-- --------------------------------------------------------

--
-- Structure de la table `invoice_purchase_item`
--

CREATE TABLE IF NOT EXISTS `invoice_purchase_item` (
  `invoice_purchase_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_purchase` int(11) DEFAULT NULL,
  `invoice_purchase_item` int(11) DEFAULT NULL,
  `invoice_purchase_item_unit` varchar(10) DEFAULT NULL,
  `invoice_purchase_item_number` int(11) DEFAULT NULL,
  `invoice_purchase_item_price` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_item_money` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_item_price_discount` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_item_debit` int(11) DEFAULT NULL,
  `invoice_purchase_item_credit` int(11) DEFAULT NULL,
  `invoice_purchase_item_tax_vat_percent` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_item_tax_vat` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_item_tax_vat_debit` int(11) DEFAULT NULL,
  `invoice_purchase_item_total` decimal(14,2) DEFAULT NULL,
  `invoice_purchase_item_house` int(11) DEFAULT NULL,
  `invoice_purchase_item_cost` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`invoice_purchase_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `invoice_purchase_item`
--

-- --------------------------------------------------------

--
-- Structure de la table `invoice_sell`
--

CREATE TABLE IF NOT EXISTS `invoice_sell` (
  `invoice_sell_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_sell_document_date` int(11) DEFAULT NULL,
  `invoice_sell_document_number` varchar(20) DEFAULT NULL,
  `invoice_sell_additional_date` int(11) DEFAULT NULL,
  `invoice_sell_customer` int(11) DEFAULT NULL,
  `invoice_sell_number` varchar(20) DEFAULT NULL,
  `invoice_sell_date` int(11) DEFAULT NULL,
  `invoice_sell_symbol` varchar(10) DEFAULT NULL,
  `invoice_sell_bill_number` varchar(20) DEFAULT NULL,
  `invoice_sell_contract_number` varchar(20) DEFAULT NULL,
  `invoice_sell_money_type` int(11) DEFAULT NULL,
  `invoice_sell_money_rate` decimal(14,2) DEFAULT NULL,
  `invoice_sell_origin_doc` varchar(20) DEFAULT NULL,
  `invoice_sell_comment` varchar(255) DEFAULT NULL,
  `invoice_sell_number_total` int(11) DEFAULT NULL,
  `invoice_sell_money` decimal(14,2) DEFAULT NULL,
  `invoice_sell_money_foreign` decimal(14,2) DEFAULT NULL,
  `invoice_sell_discount` decimal(14,2) DEFAULT NULL,
  `invoice_sell_tax_vat` decimal(14,2) DEFAULT NULL,
  `invoice_sell_total` decimal(14,2) DEFAULT NULL,
  `invoice_sell_create_user` int(11) DEFAULT NULL,
  `invoice_sell_create_date` int(11) DEFAULT NULL,
  PRIMARY KEY (`invoice_sell_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `invoice_sell`
--

-- --------------------------------------------------------

--
-- Structure de la table `invoice_sell_item`
--

CREATE TABLE IF NOT EXISTS `invoice_sell_item` (
  `invoice_sell_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_sell` int(11) DEFAULT NULL,
  `invoice_sell_item` int(11) DEFAULT NULL,
  `invoice_sell_item_unit` varchar(10) DEFAULT NULL,
  `invoice_sell_item_number` int(11) DEFAULT NULL,
  `invoice_sell_item_price` decimal(14,2) DEFAULT NULL,
  `invoice_sell_item_money` decimal(14,2) DEFAULT NULL,
  `invoice_sell_item_price_discount` decimal(14,2) DEFAULT NULL,
  `invoice_sell_item_debit` int(11) DEFAULT NULL,
  `invoice_sell_item_credit` int(11) DEFAULT NULL,
  `invoice_sell_item_tax_vat_percent` decimal(14,2) DEFAULT NULL,
  `invoice_sell_item_tax_vat` decimal(14,2) DEFAULT NULL,
  `invoice_sell_item_tax_vat_debit` int(11) DEFAULT NULL,
  `invoice_sell_item_total` decimal(14,2) DEFAULT NULL,
  `invoice_sell_item_house` int(11) DEFAULT NULL,
  `invoice_sell_item_house_debit` int(11) DEFAULT NULL,
  `invoice_sell_item_house_credit` int(11) DEFAULT NULL,
  `invoice_sell_item_house_money` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`invoice_sell_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `invoice_sell_item`
--

-- --------------------------------------------------------

--
-- Structure de la table `invoice_service_buy`
--

CREATE TABLE IF NOT EXISTS `invoice_service_buy` (
  `invoice_service_buy_id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_buy` int(11) DEFAULT NULL,
  `service_buy` int(11) DEFAULT NULL,
  `invoice_service_buy_money` decimal(14,2) DEFAULT NULL,
  `invoice_service_buy_money_foreign` decimal(14,2) DEFAULT NULL,
  `invoice_purchase` int(11) DEFAULT NULL,
  `invoice_buy_type` int(11) DEFAULT NULL COMMENT '1:Trước HQ | 2:Về kho',
  PRIMARY KEY (`invoice_service_buy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `items_id` int(11) NOT NULL AUTO_INCREMENT,
  `items_code` varchar(20) DEFAULT NULL,
  `items_name` varchar(50) DEFAULT NULL,
  `items_unit` varchar(10) DEFAULT NULL,
  `items_type` int(11) DEFAULT NULL COMMENT '1:Vật tư hàng hóa | 2:Dịch vụ | 3:Diễn giải',
  `items_tax` decimal(14,2) DEFAULT NULL,
  `items_stuff` decimal(14,2) DEFAULT NULL,
  `items_number_dauky` decimal(14,2) DEFAULT NULL,
  `items_price_dauky` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`items_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `items`
--

-- --------------------------------------------------------

--
-- Structure de la table `payment`
--

CREATE TABLE IF NOT EXISTS `payment` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_document_date` int(11) DEFAULT NULL,
  `payment_additional_date` int(11) DEFAULT NULL,
  `payment_document_number` varchar(20) DEFAULT NULL,
  `payment_bank` int(11) DEFAULT NULL,
  `payment_comment` varchar(255) DEFAULT NULL,
  `payment_person` varchar(50) DEFAULT NULL,
  `payment_origin_doc` varchar(20) DEFAULT NULL,
  `payment_money_type` int(11) DEFAULT '1',
  `payment_money_rate` decimal(14,2) DEFAULT NULL,
  `payment_money` decimal(14,2) DEFAULT NULL,
  `payment_money_foreign` decimal(14,2) DEFAULT NULL,
  `payment_bank_type` int(11) DEFAULT NULL COMMENT '1:Cash | 2:Bank',
  `payment_type` int(11) DEFAULT NULL COMMENT '1:Receipt | 2:Pay',
  `payment_check` int(11) DEFAULT NULL COMMENT '1:KH-NCC |  2:Thuế |3:Khác',
  `payment_create_user` int(11) DEFAULT NULL,
  `payment_create_date` int(11) DEFAULT NULL,
  `payment_KBNN` varchar(100) DEFAULT NULL,
  `payment_in_ex` int(11) DEFAULT NULL COMMENT '1:Intern | 2:Extern',
  `payment_money_cost` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `payment`
--

-- --------------------------------------------------------

--
-- Structure de la table `payment_cost`
--

CREATE TABLE IF NOT EXISTS `payment_cost` (
  `payment_cost_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment` int(11) DEFAULT NULL,
  `payment_cost_comment` varchar(255) DEFAULT NULL,
  `payment_cost_money` decimal(14,2) DEFAULT NULL,
  `payment_cost_debit` int(11) DEFAULT NULL,
  `payment_cost_credit` int(11) DEFAULT NULL,
  `payment_cost_customer` int(11) DEFAULT NULL,
  `payment_cost_vat` decimal(14,2) DEFAULT NULL,
  `payment_cost_debit_vat` int(11) DEFAULT NULL,
  PRIMARY KEY (`payment_cost_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `payment_item`
--

CREATE TABLE IF NOT EXISTS `payment_item` (
  `payment_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment` int(11) DEFAULT NULL,
  `payment_item_invoice` int(11) DEFAULT NULL,
  `payment_item_invoice_2` int(11) DEFAULT NULL,
  `payment_item_comment` varchar(255) DEFAULT NULL,
  `payment_item_receivable_money` decimal(14,2) DEFAULT NULL,
  `payment_item_pay_money` decimal(14,2) DEFAULT NULL,
  `payment_item_receivable_money_foreign` decimal(14,2) DEFAULT NULL,
  `payment_item_pay_money_foreign` decimal(14,2) DEFAULT NULL,
  `payment_item_price` decimal(14,2) DEFAULT NULL,
  `payment_item_money` decimal(14,2) DEFAULT NULL,
  `payment_item_money_2` decimal(14,2) DEFAULT NULL,
  `payment_item_debit` int(11) DEFAULT NULL,
  `payment_item_debit_2` int(11) DEFAULT NULL,
  `payment_item_credit` int(11) DEFAULT NULL,
  `payment_item_customer` int(11) DEFAULT NULL,
  `payment_item_tax_percent` DECIMAL(14,2) NULL ,
  `payment_item_tax` DECIMAL(14,2) NULL , 
  `payment_item_tax_debit` INT NULL , 
  `payment_item_tax_credit` INT NULL , 
  `payment_item_invoice_number` VARCHAR(20) NULL , 
  `payment_item_invoice_date` INT NULL , 
  `payment_item_invoice_symbol` VARCHAR(10) NULL ,
  PRIMARY KEY (`payment_item_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `payment_item`
--

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) DEFAULT NULL,
  `role_status` int(1) NOT NULL DEFAULT '1' COMMENT '1:active|0:inactive',
  `role_permission` text,
  `role_permission_action` text,
  PRIMARY KEY (`role_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=22 ;

--
-- Contenu de la table `role`
--

INSERT INTO `role` (`role_id`, `role_name`, `role_status`, `role_permission`, `role_permission_action`) VALUES
(1, 'Quản trị cấp cao', 1, '["backup","update","permission","info","user","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(2, 'Tổng giám đốc', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(3, 'Giám đốc tài chính', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(4, 'Kế toán trưởng', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(5, 'Kế toán thuế', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(6, 'Kế toán thanh toán', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(7, 'Kế toán công nợ', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(8, 'Kế toán kho', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}'),
(9, 'Thủ quỹ', 1, '["info","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}');

-- --------------------------------------------------------

--
-- Structure de la table `service_buy`
--

CREATE TABLE IF NOT EXISTS `service_buy` (
  `service_buy_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_buy_document_date` int(11) DEFAULT NULL,
  `service_buy_document_number` varchar(20) DEFAULT NULL,
  `service_buy_additional_date` int(11) DEFAULT NULL,
  `service_buy_customer` int(11) DEFAULT NULL,
  `service_buy_number` varchar(20) DEFAULT NULL,
  `service_buy_date` int(11) DEFAULT NULL,
  `service_buy_symbol` varchar(10) DEFAULT NULL,
  `service_buy_bill_number` varchar(20) DEFAULT NULL,
  `service_buy_contract_number` varchar(20) DEFAULT NULL,
  `service_buy_money_type` int(11) DEFAULT NULL,
  `service_buy_money_rate` decimal(14,2) DEFAULT NULL,
  `service_buy_origin_doc` varchar(20) DEFAULT NULL,
  `service_buy_comment` varchar(255) DEFAULT NULL,
  `service_buy_money` decimal(14,2) DEFAULT NULL,
  `service_buy_money_foreign` decimal(14,2) DEFAULT NULL,
  `service_buy_discount` decimal(14,2) DEFAULT NULL,
  `service_buy_tax_vat` decimal(14,2) DEFAULT NULL,
  `service_buy_total` decimal(14,2) DEFAULT NULL,
  `service_buy_create_user` int(11) DEFAULT NULL,
  `service_buy_create_date` int(11) DEFAULT NULL,
  `service_buy_type` int(11) DEFAULT NULL COMMENT '1:Mua hàng | 2:Khác',
  PRIMARY KEY (`service_buy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `service_buy_item`
--

CREATE TABLE IF NOT EXISTS `service_buy_item` (
  `service_buy_item_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_buy` int(11) DEFAULT NULL,
  `service_buy_item` int(11) DEFAULT NULL,
  `service_buy_item_unit` varchar(10) DEFAULT NULL,
  `service_buy_item_number` int(11) DEFAULT NULL,
  `service_buy_item_price` decimal(14,2) DEFAULT NULL,
  `service_buy_item_money` decimal(14,2) DEFAULT NULL,
  `service_buy_item_price_discount` decimal(14,2) DEFAULT NULL,
  `service_buy_item_debit` int(11) DEFAULT NULL,
  `service_buy_item_credit` int(11) DEFAULT NULL,
  `service_buy_item_tax_vat_percent` decimal(14,2) DEFAULT NULL,
  `service_buy_item_tax_vat` decimal(14,2) DEFAULT NULL,
  `service_buy_item_tax_vat_debit` int(11) DEFAULT NULL,
  `service_buy_item_total` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`service_buy_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE IF NOT EXISTS `stock` (
  `stock_id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_date` int(11) DEFAULT NULL,
  `stock_item` int(11) DEFAULT NULL,
  `stock_number` int(11) DEFAULT NULL,
  `stock_price` decimal(14,2) DEFAULT NULL,
  `stock_house` int(11) DEFAULT NULL,
  `invoice_buy_item` int(11) DEFAULT NULL,
  `invoice_sell_item` int(11) DEFAULT NULL,
  `invoice_purchase_item` int(11) DEFAULT NULL,
  `items` int(11) DEFAULT NULL,
  PRIMARY KEY (`stock_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `stock`
--

-- --------------------------------------------------------

--
-- Structure de la table `tax`
--

CREATE TABLE IF NOT EXISTS `tax` (
  `tax_id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_date` int(11) DEFAULT NULL,
  `tax_money` decimal(14,2) DEFAULT NULL,
  `tax_comment` varchar(255) DEFAULT NULL,
  `tax_type` int(11) DEFAULT NULL COMMENT '1:NK | 2:GTGT',
  `invoice_buy` int(11) DEFAULT NULL,
  `payment_item` int(11) DEFAULT NULL,
  PRIMARY KEY (`tax_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contenu de la table `tax`
--

-- --------------------------------------------------------

--
-- Structure de la table `tire_brand`
--

CREATE TABLE IF NOT EXISTS `tire_brand` (
  `tire_brand_id` int(11) NOT NULL AUTO_INCREMENT,
  `tire_brand_name` varchar(50) DEFAULT NULL,
  `tire_brand_group` int(11) DEFAULT NULL,
  `tire_brand_region` int(11) DEFAULT NULL,
  PRIMARY KEY (`tire_brand_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tire_brand_group`
--

CREATE TABLE IF NOT EXISTS `tire_brand_group` (
  `tire_brand_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `tire_brand_group_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`tire_brand_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tire_brand_region`
--

CREATE TABLE IF NOT EXISTS `tire_brand_region` (
  `tire_brand_region_id` int(11) NOT NULL AUTO_INCREMENT,
  `tire_brand_region_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`tire_brand_region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tire_pattern`
--

CREATE TABLE IF NOT EXISTS `tire_pattern` (
  `tire_pattern_id` int(11) NOT NULL AUTO_INCREMENT,
  `tire_pattern_name` varchar(20) DEFAULT NULL,
  `tire_pattern_type` varchar(20) DEFAULT NULL COMMENT '1:DC01 | 2:DC02 | 3:DC03 | 4:NC01 | 5:BC01 | 6:BC02 | 7:DK01 | 8:DK02 | 9:NK01 | 10:NK02',
  PRIMARY KEY (`tire_pattern_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `tire_size`
--

CREATE TABLE IF NOT EXISTS `tire_size` (
  `tire_size_id` int(11) NOT NULL AUTO_INCREMENT,
  `tire_size_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`tire_size_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(200) NOT NULL,
  `create_time` int(11) NOT NULL,
  `role` int(11) NOT NULL,
  `user_lock` int(11) DEFAULT NULL,
  `user_group` int(11) DEFAULT NULL,
  `user_dept` int(11) DEFAULT NULL,
  `permission` text,
  `permission_action` text,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Contenu de la table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `create_time`, `role`, `user_lock`, `user_group`, `user_dept`, `permission`, `permission_action`) VALUES
(1, 'root', '81dc9bdb52d04dc20036dbd8313ed055', 0, 1, NULL, NULL, NULL, '["backup","update","permission","info","user","account","bank","invoicebuy","house","items","customer","supplier","servicebuy","invoicesell","additional","receiptcash","receiptbank","paycash","paybank","paytaxbank","payexbank","internaltransfer","invoicepurchase","stock","stockdetail","balanceadditional","additionalother","invoicein","invoiceout","debitreceive","debitpay","balanceaccounting","balanceresult","detailcash","detailbank","detailaccount","invoicetax","forward","debitreceivedetail","debitpaydetail","detailsell","detailbuy","department","asset"]', '{"backup":"backup","update":"update","permission":"permission","info":"info","user":"user","account":"account","bank":"bank","invoicebuy":"invoicebuy","house":"house","items":"items","customer":"customer","supplier":"supplier","servicebuy":"servicebuy","invoicesell":"invoicesell","additional":"additional","payment":"payment","receiptcash":"receiptcash","receiptbank":"receiptbank","paycash":"paycash","paybank":"paybank","paytaxbank":"paytaxbank","payexbank":"payexbank","internaltransfer":"internaltransfer","invoicepurchase":"invoicepurchase","stock":"stock","stockdetail":"stockdetail","balanceadditional":"balanceadditional","additionalother":"additionalother","invoicein":"invoicein","invoiceout":"invoiceout","debitreceive":"debitreceive","debitpay":"debitpay","balanceaccounting":"balanceaccounting","balanceresult":"balanceresult","detailcash":"detailcash","detailbank":"detailbank","detailaccount":"detailaccount","invoicetax":"invoicetax","forward":"forward","debitreceivedetail":"debitreceivedetail","debitpaydetail":"debitpaydetail","detailsell":"detailsell","detailbuy":"detailbuy","department":"department","asset":"asset"}');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
