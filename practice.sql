-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 23, 2018 at 09:52 AM
-- Server version: 5.7.21
-- PHP Version: 5.6.35

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `practice`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_log`
--

DROP TABLE IF EXISTS `account_log`;
CREATE TABLE IF NOT EXISTS `account_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `user_money` int(11) NOT NULL,
  `frozen_money` int(11) NOT NULL,
  `rank_points` int(11) NOT NULL,
  `pay_points` int(11) NOT NULL,
  `change_desc` varchar(40) NOT NULL,
  `change_type` tinyint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `attribute`
--

DROP TABLE IF EXISTS `attribute`;
CREATE TABLE IF NOT EXISTS `attribute` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `attribute_name` varchar(20) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `input_value_type` tinyint(1) NOT NULL DEFAULT '1',
  `input_type_id` int(11) NOT NULL DEFAULT '1',
  `input_type_values` varchar(40) NOT NULL DEFAULT '',
  `attr_index` int(11) NOT NULL DEFAULT '0',
  `attr_group` varchar(7) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute`
--

INSERT INTO `attribute` (`id`, `type_id`, `attribute_name`, `status`, `input_value_type`, `input_type_id`, `input_type_values`, `attr_index`, `attr_group`) VALUES
(1, 1, '作者', 1, 1, 1, '', 1, '1'),
(2, 1, '出版社', 1, 1, 1, '', 1, ''),
(3, 1, '图书书号/ISBN', 1, 1, 2, '', 0, ''),
(4, 1, '出版日期', 1, 1, 1, '', 0, ''),
(5, 1, '开本', 1, 1, 1, '', 0, ''),
(6, 1, '图书页数', 1, 1, 1, '', 0, ''),
(7, 1, '图书装订', 1, 1, 1, '', 0, ''),
(8, 1, '图书规格', 1, 1, 1, '', 0, ''),
(9, 1, '版次', 1, 1, 1, '', 0, ''),
(10, 1, '印张', 1, 1, 1, '', 0, ''),
(11, 1, '字数', 1, 1, 1, '', 0, ''),
(12, 1, '所属分类', 1, 1, 1, '', 0, ''),
(13, 4, '网络制式', 1, 2, 2, '2g\r\n3g\r\n4g\r\n5g', 0, ''),
(14, 4, '支持频率/网络频率', 1, 1, 1, '', 0, ''),
(15, 4, '尺寸体积', 1, 1, 2, '小体积\r\n中体积\r\n大体积', 0, ''),
(16, 4, '外观样式/手机类型', 1, 1, 1, '', 0, ''),
(17, 4, '主屏参数/内屏参数', 1, 1, 1, '', 0, ''),
(18, 4, '副屏参数/外屏参数', 1, 1, 1, '', 0, ''),
(19, 4, '清晰度', 1, 1, 1, '', 0, ''),
(20, 4, '色数/灰度', 1, 1, 1, '', 0, ''),
(21, 4, '长度', 1, 1, 1, '', 0, ''),
(22, 4, '宽度', 1, 1, 1, '', 0, ''),
(23, 4, '厚度', 1, 1, 1, '', 0, ''),
(24, 4, '屏幕材质', 1, 1, 1, '', 0, ''),
(29, 4, '内存容量', 1, 1, 1, '', 0, ''),
(30, 4, '操作系统', 1, 1, 1, '', 0, ''),
(31, 4, '通话时间', 1, 1, 1, '', 0, ''),
(32, 4, '待机时间', 1, 1, 1, '', 0, ''),
(33, 4, '标准配置', 1, 1, 1, '', 0, ''),
(34, 4, 'WAP上网', 1, 1, 1, '', 0, ''),
(35, 4, '数据业务', 1, 1, 1, '', 0, ''),
(36, 4, '天线位置', 1, 1, 1, '', 0, ''),
(37, 4, '随机配件', 1, 1, 1, '', 0, ''),
(38, 4, '铃声', 1, 1, 1, '', 0, ''),
(39, 4, '摄像头', 1, 1, 1, '', 0, ''),
(40, 4, '彩信/彩e', 1, 1, 1, '', 0, ''),
(41, 4, '红外/蓝牙', 1, 1, 1, '', 0, ''),
(42, 4, '价格等级', 1, 1, 1, '', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `bonus`
--

DROP TABLE IF EXISTS `bonus`;
CREATE TABLE IF NOT EXISTS `bonus` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `bonus_name` varchar(30) NOT NULL,
  `bonus_money` decimal(10,2) NOT NULL,
  `min_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `send_type` tinyint(3) NOT NULL,
  `min_goods_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `bonus`
--

INSERT INTO `bonus` (`id`, `bonus_name`, `bonus_money`, `min_amount`, `send_type`, `min_goods_amount`) VALUES
(1, '线下红包', '5.00', '360.00', 3, '0.00'),
(2, '订单红包', '20.00', '800.00', 2, '1500.00');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_name` varchar(30) NOT NULL,
  `brand_desc` varchar(150) NOT NULL DEFAULT '',
  `brand_url` varchar(80) NOT NULL DEFAULT '',
  `sort_order` int(11) NOT NULL DEFAULT '100',
  `if_show` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `brand_name`, `brand_desc`, `brand_url`, `sort_order`, `if_show`) VALUES
(1, '苹果', '苹果公司是一家大型跨国公司', 'https://www.apple.com', 100, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cat_name` varchar(20) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT '0',
  `if_show` tinyint(1) NOT NULL DEFAULT '1',
  `view_order` int(11) NOT NULL DEFAULT '100',
  `filter_attr` varchar(50) NOT NULL DEFAULT '',
  `unit` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `cat_name`, `pid`, `if_show`, `view_order`, `filter_attr`, `unit`) VALUES
(1, '数码设备', -1, 1, 100, '19,24', ''),
(2, '服装', 0, 1, 100, '', ''),
(3, '手机', 1, 1, 100, '', '个'),
(4, '书籍', 0, 1, 100, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reply_id` int(11) NOT NULL,
  `pid` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `content` varchar(200) NOT NULL,
  `comment_type` tinyint(4) NOT NULL,
  `ip_address` varchar(15) NOT NULL,
  `email` varchar(50) NOT NULL,
  `username` varchar(30) NOT NULL,
  `comment_rank` tinyint(1) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `add_time` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `reply_id`, `pid`, `uid`, `content`, `comment_type`, `ip_address`, `email`, `username`, `comment_rank`, `status`, `add_time`) VALUES
(11, 14, 0, 0, '好評！', 0, '0.0.0.0', 'aaaa@bbb.com', '', 5, 0, 1542543789);

-- --------------------------------------------------------

--
-- Table structure for table `document`
--

DROP TABLE IF EXISTS `document`;
CREATE TABLE IF NOT EXISTS `document` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `document_name` varchar(20) NOT NULL,
  `model` varchar(10) NOT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '1',
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `document_article`
--

DROP TABLE IF EXISTS `document_article`;
CREATE TABLE IF NOT EXISTS `document_article` (
  `article_id` int(11) NOT NULL,
  `content` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `goods`
--

DROP TABLE IF EXISTS `goods`;
CREATE TABLE IF NOT EXISTS `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `good_name` varchar(20) NOT NULL,
  `good_name_style` varchar(70) NOT NULL DEFAULT '',
  `good_sn` varchar(20) NOT NULL,
  `cat_id` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '50',
  `type_id` int(11) NOT NULL,
  `keywords` varchar(60) NOT NULL DEFAULT '''''',
  `is_hot` int(11) NOT NULL DEFAULT '0',
  `is_new` int(11) NOT NULL DEFAULT '0',
  `is_best` int(11) NOT NULL DEFAULT '0',
  `number` int(11) NOT NULL,
  `warn_number` int(11) NOT NULL,
  `weight` decimal(10,0) NOT NULL,
  `market_price` decimal(10,2) NOT NULL,
  `shop_price` decimal(10,0) NOT NULL,
  `promotion_price` decimal(10,0) NOT NULL,
  `promotion_start` int(11) NOT NULL,
  `promotion_end` int(11) NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `is_on_sale` tinyint(1) NOT NULL DEFAULT '1',
  `is_alone_sale` tinyint(1) NOT NULL DEFAULT '1',
  `brand_id` int(11) NOT NULL DEFAULT '0',
  `last_update` int(11) NOT NULL,
  `give_integral` int(11) NOT NULL DEFAULT '0',
  `rank_integral` int(11) NOT NULL DEFAULT '0',
  `integral` int(11) NOT NULL DEFAULT '0',
  `is_shipping` tinyint(1) NOT NULL DEFAULT '1',
  `good_desc` text,
  `good_img` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `goods`
--

INSERT INTO `goods` (`id`, `good_name`, `good_name_style`, `good_sn`, `cat_id`, `status`, `sort`, `type_id`, `keywords`, `is_hot`, `is_new`, `is_best`, `number`, `warn_number`, `weight`, `market_price`, `shop_price`, `promotion_price`, `promotion_start`, `promotion_end`, `deleted`, `is_on_sale`, `is_alone_sale`, `brand_id`, `last_update`, `give_integral`, `rank_integral`, `integral`, `is_shipping`, `good_desc`, `good_img`) VALUES
(1, 'Holy Bible', '#f00|em', 'gn201811106869915', 4, 1, 50, 1, '', 0, 0, 0, 0, 0, '0', '0.00', '0', '0', 0, 0, 0, 1, 1, 0, 1541838405, 0, 0, 0, 1, NULL, ''),
(2, 'iphone Max(512G)', '|', 'gn201811103322215', 3, 1, 50, 4, '', 0, 0, 0, 0, 0, '7', '0.00', '9688', '0', 0, 0, 0, 1, 1, 1, 1541830344, 0, 0, 0, 1, NULL, ''),
(13, '三星Galaxy s9', '|', 'gn20181031400623', 3, 1, 50, 0, '', 0, 0, 0, 22, 0, '0', '0.00', '5800', '0', 0, 0, 0, 1, 1, 0, 1541830000, 0, 0, 0, 1, NULL, ''),
(14, '华为 Mate20', '|', 'gn201811235910715', 3, 1, 50, 4, '', 0, 0, 0, 0, 0, '0', '5000.00', '4666', '0', 0, 0, 0, 1, 1, 0, 1542966120, 100, 0, 0, 0, NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `good_attrs`
--

DROP TABLE IF EXISTS `good_attrs`;
CREATE TABLE IF NOT EXISTS `good_attrs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `good_id` int(11) NOT NULL,
  `attr_id` int(11) NOT NULL,
  `attr_value` varchar(20) NOT NULL,
  `attr_price` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `good_attrs`
--

INSERT INTO `good_attrs` (`id`, `good_id`, `attr_id`, `attr_value`, `attr_price`) VALUES
(1, 1, 2, '基督教', ''),
(2, 1, 6, '324', ''),
(3, 13, 2, '基督教', ''),
(4, 13, 6, '326', ''),
(12, 14, 13, '5g', '500'),
(21, 14, 29, '8g', ''),
(22, 14, 19, '2020x1240', ''),
(23, 14, 24, '玻璃', ''),
(24, 14, 30, 'emui', ''),
(25, 14, 13, '3g', '-200');

-- --------------------------------------------------------

--
-- Table structure for table `good_attr_types`
--

DROP TABLE IF EXISTS `good_attr_types`;
CREATE TABLE IF NOT EXISTS `good_attr_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(20) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '50',
  `attr_groups` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `good_attr_types`
--

INSERT INTO `good_attr_types` (`id`, `type_name`, `status`, `sort`, `attr_groups`) VALUES
(1, '书', 1, 50, '1\r\n2\r\n3'),
(2, '音乐', 1, 50, ''),
(3, '电影', 1, 50, ''),
(4, '手机', 1, 50, ''),
(5, '笔记本电脑', 1, 50, ''),
(6, '数码相机', 1, 50, ''),
(7, '数码摄像机', 1, 50, ''),
(8, '化妆品', 1, 50, ''),
(9, '精品手机', 1, 50, '');

-- --------------------------------------------------------

--
-- Table structure for table `good_extended_cats`
--

DROP TABLE IF EXISTS `good_extended_cats`;
CREATE TABLE IF NOT EXISTS `good_extended_cats` (
  `good_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `good_extended_cats`
--

INSERT INTO `good_extended_cats` (`good_id`, `cat_id`) VALUES
(1, 4),
(13, 0);

-- --------------------------------------------------------

--
-- Table structure for table `member_price`
--

DROP TABLE IF EXISTS `member_price`;
CREATE TABLE IF NOT EXISTS `member_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `good_id` int(11) NOT NULL,
  `user_rank` int(11) NOT NULL,
  `member_price` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `member_price`
--

INSERT INTO `member_price` (`id`, `good_id`, `user_rank`, `member_price`) VALUES
(1, 14, 2, '4567'),
(2, 14, 3, '4400');

-- --------------------------------------------------------

--
-- Table structure for table `my_users`
--

DROP TABLE IF EXISTS `my_users`;
CREATE TABLE IF NOT EXISTS `my_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(11) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `user_money` int(11) NOT NULL DEFAULT '0',
  `frozen_moeny` int(11) NOT NULL DEFAULT '0',
  `rank_points` int(11) NOT NULL DEFAULT '0',
  `pay_points` int(11) NOT NULL DEFAULT '0',
  `affiliate_id` int(11) NOT NULL DEFAULT '0',
  `password` char(32) NOT NULL,
  `user_salt` char(5) NOT NULL DEFAULT '',
  `salt` int(11) NOT NULL DEFAULT '0',
  `sex` tinyint(1) NOT NULL DEFAULT '0',
  `birthday` varchar(10) NOT NULL DEFAULT '',
  `qq` varchar(15) NOT NULL DEFAULT '',
  `pwd_question` varchar(50) NOT NULL DEFAULT '',
  `office_phone` varchar(15) NOT NULL DEFAULT '',
  `home_phone` varchar(15) NOT NULL DEFAULT '',
  `pwd_question_answer` varchar(50) NOT NULL DEFAULT '',
  `reg_time` int(11) NOT NULL DEFAULT '0',
  `last_login_time` int(11) NOT NULL DEFAULT '0',
  `last_login_ip` varchar(15) NOT NULL DEFAULT '',
  `rank_id` tinyint(4) NOT NULL DEFAULT '0',
  `visit_counts` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `my_users`
--

INSERT INTO `my_users` (`id`, `username`, `email`, `phone`, `status`, `user_money`, `frozen_moeny`, `rank_points`, `pay_points`, `affiliate_id`, `password`, `user_salt`, `salt`, `sex`, `birthday`, `qq`, `pwd_question`, `office_phone`, `home_phone`, `pwd_question_answer`, `reg_time`, `last_login_time`, `last_login_ip`, `rank_id`, `visit_counts`) VALUES
(1, 'yuanwei', 'yuanwei@yuanwei.com', '', 1, 0, 0, 0, 0, 0, '7545e36acb87b3020a78ebe5dbc365c6', '83248', 0, 0, '0', '', '您的爸爸姓名?', '', '', '袁德坤', 1, 1542591997, '0.0.0.0', 0, 7);

-- --------------------------------------------------------

--
-- Table structure for table `navs`
--

DROP TABLE IF EXISTS `navs`;
CREATE TABLE IF NOT EXISTS `navs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nav_name` varchar(20) NOT NULL,
  `if_show` tinyint(1) NOT NULL DEFAULT '1',
  `view_order` smallint(11) NOT NULL DEFAULT '50',
  `url` varchar(20) NOT NULL,
  `open_new` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `navs`
--

INSERT INTO `navs` (`id`, `nav_name`, `if_show`, `view_order`, `url`, `open_new`) VALUES
(3, '数码设备', 1, 50, '', 0),
(4, '服装', 1, 50, '', 0),
(5, '食品', 1, 50, '', 1),
(6, '玩具', 1, 50, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `register_fields`
--

DROP TABLE IF EXISTS `register_fields`;
CREATE TABLE IF NOT EXISTS `register_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_name` varchar(20) NOT NULL,
  `field_title` varchar(20) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `field_values` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `register_fields`
--

INSERT INTO `register_fields` (`id`, `field_name`, `field_title`, `type`, `enabled`, `field_values`) VALUES
(1, 'qq', 'qq', 1, 1, ''),
(2, 'home_phone', '家庭电话', 1, 1, ''),
(3, 'office_phone', '办公电话', 1, 1, ''),
(4, 'pwd_questions', '密码找回问题', 1, 1, '您的爸爸姓名?\r\n您的妈妈姓名?\r\n您的家庭住址?');

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

DROP TABLE IF EXISTS `system_config`;
CREATE TABLE IF NOT EXISTS `system_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_name` varchar(20) NOT NULL,
  `config_title` varchar(30) NOT NULL,
  `config_value` varchar(150) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `sort` smallint(6) NOT NULL DEFAULT '10',
  `groups` tinyint(4) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`id`, `config_name`, `config_title`, `config_value`, `enabled`, `sort`, `groups`, `type`) VALUES
(1, 'keywords', '网页关键字', '我的网站', 1, 10, 0, 1),
(2, 'site_closed', '网站维护', '0', 1, 10, 0, 1),
(3, 'affiliate', '邀请设置', 'a:3:{s:17:\"invitation_points\";i:2;s:17:\"affiliate_enabled\";i:1;s:20:\"invitation_points_up\";i:100;}', 1, 10, 0, 1),
(4, 'register_captcha', '注册验证码开启码', '1', 1, 10, 0, 1),
(5, 'register_closed', '关闭注册', '0', 1, 10, 0, 1),
(10, 'captcha', '验证码', '704', 1, 10, 0, 1),
(7, 'comment_factor', '评论开启条件', '0', 1, 10, 0, 1),
(11, 'comment_check', '评论审核', '1', 1, 10, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_bonus`
--

DROP TABLE IF EXISTS `user_bonus`;
CREATE TABLE IF NOT EXISTS `user_bonus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bonus_type` smallint(6) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `bonus_sn` bigint(20) NOT NULL DEFAULT '0',
  `used` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user_rank`
--

DROP TABLE IF EXISTS `user_rank`;
CREATE TABLE IF NOT EXISTS `user_rank` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `rank_name` varchar(10) NOT NULL,
  `discount` tinyint(4) NOT NULL,
  `min_points` int(11) NOT NULL,
  `max_points` int(11) NOT NULL,
  `show_price` tinyint(1) NOT NULL,
  `is_special` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `user_rank`
--

INSERT INTO `user_rank` (`id`, `rank_name`, `discount`, `min_points`, `max_points`, `show_price`, `is_special`) VALUES
(1, '注册会员', 100, 0, 10000, 1, 0),
(2, 'VIP会员', 95, 10000, 10000000, 1, 0),
(3, '代销用户', 90, 0, 0, 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `volume_price`
--

DROP TABLE IF EXISTS `volume_price`;
CREATE TABLE IF NOT EXISTS `volume_price` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price_type` tinyint(1) NOT NULL,
  `volume_number` smallint(6) NOT NULL,
  `volume_price` decimal(10,0) NOT NULL,
  `good_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `volume_price`
--

INSERT INTO `volume_price` (`id`, `price_type`, `volume_number`, `volume_price`, `good_id`) VALUES
(14, 1, 2, '4600', 14),
(13, 1, 3, '4580', 14);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
