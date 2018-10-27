-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 27, 2018 at 10:17 AM
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
  `input_type_id` int(11) NOT NULL DEFAULT '1',
  `input_type_values` varchar(40) NOT NULL DEFAULT '''''',
  `attr_index` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `attribute`
--

INSERT INTO `attribute` (`id`, `type_id`, `attribute_name`, `status`, `input_type_id`, `input_type_values`, `attr_index`) VALUES
(1, 1, '作者', 1, 1, '王八,故事', 1),
(2, 1, '出版社', 1, 1, '', 1),
(3, 1, '图书书号/ISBN', 1, 2, '', 0),
(4, 1, '出版日期', 1, 1, '', 0),
(5, 1, '开本', 1, 1, '', 0),
(6, 1, '图书页数', 1, 1, '', 0),
(7, 1, '图书装订', 1, 1, '', 0),
(8, 1, '图书规格', 1, 1, '', 0),
(9, 1, '版次', 1, 1, '', 0),
(10, 1, '印张', 1, 1, '', 0),
(11, 1, '字数', 1, 1, '', 0),
(12, 1, '所属分类', 1, 1, '', 0),
(13, 4, '网络制式', 1, 1, '', 0),
(14, 4, '支持频率/网络频率', 1, 1, '', 0),
(15, 4, '尺寸体积', 1, 2, '小体积,中体积,大体积,,', 0),
(16, 4, '外观样式/手机类型', 1, 1, '', 0),
(17, 4, '主屏参数/内屏参数', 1, 1, '', 0),
(18, 4, '副屏参数/外屏参数', 1, 1, '', 0),
(19, 4, '清晰度', 1, 1, '', 0),
(20, 4, '色数/灰度', 1, 1, '', 0),
(21, 4, '长度', 1, 1, '', 0),
(22, 4, '宽度', 1, 1, '', 0),
(23, 4, '厚度', 1, 1, '', 0),
(24, 4, '屏幕材质', 1, 1, '', 0),
(29, 4, '内存容量', 1, 1, '', 0),
(30, 4, '操作系统', 1, 1, '', 0),
(31, 4, '通话时间', 1, 1, '', 0),
(32, 4, '待机时间', 1, 1, '', 0),
(33, 4, '标准配置', 1, 1, '', 0),
(34, 4, 'WAP上网', 1, 1, '', 0),
(35, 4, '数据业务', 1, 1, '', 0),
(36, 4, '天线位置', 1, 1, '', 0),
(37, 4, '随机配件', 1, 1, '', 0),
(38, 4, '铃声', 1, 1, '', 0),
(39, 4, '摄像头', 1, 1, '', 0),
(40, 4, '彩信/彩e', 1, 1, '', 0),
(41, 4, '红外/蓝牙', 1, 1, '', 0),
(42, 4, '价格等级', 1, 1, '', 0);

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
  `price` decimal(10,0) NOT NULL,
  `promotion_price` decimal(10,0) NOT NULL,
  `promotion_start` int(11) NOT NULL,
  `promotion_end` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `goods`
--

INSERT INTO `goods` (`id`, `good_name`, `good_sn`, `cat_id`, `status`, `sort`, `type_id`, `keywords`, `is_hot`, `is_new`, `is_best`, `number`, `warn_number`, `weight`, `price`, `promotion_price`, `promotion_start`, `promotion_end`) VALUES
(1, 'Holy Bible', '', 0, 1, 50, 1, '', 0, 0, 0, 0, 0, '0', '0', '0', 0, 0),
(2, 'iphone Max(512G)', 'gn201810225817613', 0, 1, 50, 4, '', 0, 0, 0, 0, 0, '7', '0', '0', 0, 0),
(12, 'iphoneMax512', 'gn20181022682392', 0, 1, 50, 4, '烤饼|插电', 0, 1, 0, 3000, 0, '12', '0', '0', 0, 0);

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `good_attrs`
--

INSERT INTO `good_attrs` (`id`, `good_id`, `attr_id`, `attr_value`) VALUES
(118, 12, 39, 'sony'),
(117, 12, 21, '800'),
(116, 12, 33, 'A12'),
(115, 12, 30, 'IOS'),
(113, 12, 24, '玻璃'),
(114, 12, 29, '3G'),
(112, 12, 14, '3000MHZ'),
(85, 12, 13, 'N/A');

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `good_attr_types`
--

INSERT INTO `good_attr_types` (`id`, `type_name`, `status`, `sort`) VALUES
(1, '书', 1, 50),
(4, '手机', 1, 50);

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
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `my_users`
--

INSERT INTO `my_users` (`id`, `username`, `email`, `phone`, `status`, `user_money`, `frozen_moeny`, `rank_points`, `pay_points`, `affiliate_id`, `password`, `user_salt`, `salt`, `sex`, `birthday`, `qq`, `pwd_question`, `office_phone`, `home_phone`, `pwd_question_answer`, `reg_time`, `last_login_time`, `last_login_ip`) VALUES
(1, 'yuanwei', 'yuanwei@yuanwei.com', '', 1, 0, 0, 0, 0, 0, 'yuanwei888', '', 0, 0, '0', '', '', '', '', '', 1, 0, ''),
(3, 'xxxxxx', 'xxxxx@xxxxxxx.com', '', 1, 0, 0, 0, 0, 0, '3a27140dd96c3a005b42365408df13f0', '87243', 0, 0, '', '', '', '', '', '', 1540627148, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `navs`
--

DROP TABLE IF EXISTS `navs`;
CREATE TABLE IF NOT EXISTS `navs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nav_name` varchar(20) NOT NULL,
  `status` enum('1','0') NOT NULL DEFAULT '1',
  `sort` int(11) NOT NULL DEFAULT '50',
  `pid` int(11) NOT NULL,
  `url` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `navs`
--

INSERT INTO `navs` (`id`, `nav_name`, `status`, `sort`, `pid`, `url`) VALUES
(1, '文章', '1', 50, 0, 'document/article'),
(2, '下载', '1', 50, 0, 'document/download');

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
(7, 'pwd_questions', '密码找回问题', 1, 1, '您的爸爸姓名?\r\n您的妈妈姓名?\r\n您的家庭住址?');

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
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`id`, `config_name`, `config_title`, `config_value`, `enabled`, `sort`, `groups`, `type`) VALUES
(1, 'keywords', '网页关键字', '我的网站', 1, 10, 0, 1),
(2, 'site_closed', '网站维护', '0', 1, 10, 0, 1),
(3, 'affiliate', '邀请设置', 'a:3:{s:17:\"invitation_points\";i:2;s:17:\"affiliate_enabled\";i:1;s:20:\"invitation_points_up\";i:100;}', 1, 10, 0, 1);

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
