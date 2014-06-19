-- phpMyAdmin SQL Dump
-- version 2.11.10.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2014 年 05 月 10 日 14:37
-- 服务器版本: 5.6.17
-- PHP 版本: 5.4.12

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- 数据库: `lazy`
--

-- --------------------------------------------------------

--
-- 表的结构 `admins`
--


CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) DEFAULT '0' COMMENT '0: ok, 1: locked',
  `email` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'md5(md5(password)+salt)',
  `salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `reg_time` datetime NOT NULL,
  `reg_ip` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `login_time` datetime DEFAULT NULL,
  `login_expire` datetime DEFAULT NULL,
  `login_ip` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_token` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- 表的结构 `merchants`
--

CREATE TABLE IF NOT EXISTS `merchants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logo` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `desc` mediumtext CHARACTER SET utf8,
  `link` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `mkey` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `cps_url` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `api_url` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------

--
-- 表的结构 `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_id` int(11) DEFAULT NULL,
  `pid` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `short_title` varchar(255) DEFAULT NULL,
  `img_url` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `wap_url` varchar(255) DEFAULT NULL,
  `desc` mediumtext,
  `total_amount` float(20,2) DEFAULT '0.00',
  `current_amount` float(20,2) DEFAULT '0.00',
  `annual_rate` float(20,2) DEFAULT NULL,
  `annual_rate_bottom` varchar(255) DEFAULT NULL,
  `annual_rate_top` varchar(255) DEFAULT NULL,
  `guaranteed` tinyint(1) DEFAULT NULL,
  `interest_guaranteed` tinyint(1) DEFAULT NULL,
  `assurance_provider` varchar(255) DEFAULT NULL,
  `pledge` tinyint(1) DEFAULT NULL,
  `pre_redeem` int(11) DEFAULT NULL,
  `start_price` float(20,2) DEFAULT '0.00',
  `step_price` float(20,2) DEFAULT '0.00',
  `start_time` datetime DEFAULT NULL,
  `close_time` datetime DEFAULT NULL,
  `due_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `sort_factor` int(11) DEFAULT '100',
  `corp_name` varchar(255) DEFAULT NULL,
  `display` tinyint(1) DEFAULT '1',
  `locked` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_projects_on_merchant_id_and_pid` (`merchant_id`,`pid`),
  KEY `total_amount` (`total_amount`),
  KEY `current_amount` (`current_amount`),
  KEY `start_time` (`start_time`),
  KEY `close_time` (`close_time`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `api_fetch_logs`
--

CREATE TABLE IF NOT EXISTS `api_fetch_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `merchant_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `api_pid` varchar(32) NOT NULL,
  `api_content` longtext NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant_id` (`merchant_id`,`api_pid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- 表的结构 `links`
--


CREATE TABLE IF NOT EXISTS `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `status` tinyint(4) DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `url` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

