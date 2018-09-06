-- phpMyAdmin SQL Dump
-- version 3.3.7
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2018 年 08 月 31 日 10:54
-- 服务器版本: 5.5.49
-- PHP 版本: 5.6.21

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `test`
--

-- --------------------------------------------------------

--
-- 表的结构 `suliss_admin`
--

CREATE TABLE IF NOT EXISTS `suliss_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(30) NOT NULL,
  `password` varchar(200) NOT NULL,
  `salt` varchar(10) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `token` varchar(30) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `joindate` int(10) unsigned NOT NULL,
  `joinip` varchar(15) NOT NULL,
  `lastvisit` int(10) unsigned NOT NULL,
  `lastip` varchar(15) NOT NULL,
  `remark` varchar(500) NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

INSERT INTO `suliss_admin` (`id`, `avatar`, `username`, `password`, `salt`, `type`, `token`, `status`, `joindate`, `joinip`, `lastvisit`, `lastip`, `remark`, `starttime`, `endtime`) VALUES
(1, '/public/attachment/images/20180711/89bea514e1d302d006fac4d8ba69d22f.jpg', 'admin', 'f374baf63f70a5c2c4d172a0a6e37897', 'U66yPU04', 0, '48012b5254b17983308b13dda53807', 1, 1532331947, '182.245.71.7', 1535681170, '116.52.235.217', 'wqe12', 0, 0),
(3, '/public/attachment/images/20180719/fd38d78f8dfe912b836e4ff320bbebcf.jpg', 'test', '438b6acb8aca7eea3295ffb62cbc238a', 'yoHo3WGS', 0, 'ead29ada1a8c5cfc982f65383fd5b0', 1, 1532612737, '14.204.0.220', 1535596524, '182.245.71.15', '', 0, 0),
(2, '/public/attachment/images/20180530/0847d00bfcc965c68a7ac014715270aa.jpg', 'administrator', 'e70e25fa46ca7b582c2ef46a3a573326', 'lVRVVp9g', 0, '6439a7b9cf9b36bafc97c4c49bc0d3', 1, 1532408484, '218.63.141.87', 1533888306, '182.245.71.229', 'ces121312', 0, 0),
(4, '/public/attachment/images/20180716/a4f5aeab52c188a099d99c037650288f.png', 'doncheng', '45ef1cdcde019d8f73baf65e493ec9d6', 'fYufupRr', 0, 'c9a3ab10825e39e8a1e05f13a1869d', 1, 1533277779, '218.63.141.110', 1535599463, '182.245.71.15', '', 0, 0);


-- --------------------------------------------------------

--
-- 表的结构 `suliss_admin_log`
--

CREATE TABLE IF NOT EXISTS `suliss_admin_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminid` int(11) DEFAULT '0',
  `type` varchar(255) DEFAULT '',
  `op` text,
  `createtime` int(11) DEFAULT '0',
  `ip` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_adminid` (`adminid`),
  KEY `idx_createtime` (`createtime`),
  FULLTEXT KEY `idx_type` (`type`),
  FULLTEXT KEY `idx_op` (`op`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=235 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_attachment_group`
--

CREATE TABLE IF NOT EXISTS `suliss_attachment_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_auth_group`
--

CREATE TABLE IF NOT EXISTS `suliss_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_auth_group_access`
--

CREATE TABLE IF NOT EXISTS `suliss_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_auth_rule`
--

CREATE TABLE IF NOT EXISTS `suliss_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_citywide_banner`
--

CREATE TABLE IF NOT EXISTS `suliss_citywide_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannername` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  `shopid` int(11) DEFAULT '0',
  `iswxapp` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_citywide_life_banner`
--

CREATE TABLE IF NOT EXISTS `suliss_citywide_life_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannername` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_citywide_life_store`
--

CREATE TABLE IF NOT EXISTS `suliss_citywide_life_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate` int(11) NOT NULL DEFAULT '0',
  `storename` varchar(255) DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `tel` varchar(255) DEFAULT '',
  `lat` varchar(255) DEFAULT '',
  `lng` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `contacts` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  `logo` varchar(255) DEFAULT '',
  `saletime` varchar(255) DEFAULT '',
  `desc` text,
  `displayorder` int(11) DEFAULT '0',
  `banner` text,
  `label` varchar(255) NOT NULL DEFAULT '',
  `province` varchar(30) NOT NULL DEFAULT '',
  `city` varchar(30) NOT NULL DEFAULT '',
  `area` varchar(30) NOT NULL DEFAULT '',
  `provincecode` varchar(30) NOT NULL DEFAULT '',
  `citycode` varchar(20) DEFAULT '',
  `areacode` varchar(30) NOT NULL DEFAULT '',
  `collectcount` int(11) NOT NULL DEFAULT '0',
  `clickcount` int(11) NOT NULL DEFAULT '0',
  `isrecommand` tinyint(3) NOT NULL DEFAULT '0',
  `deleted` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_citywide_life_store_category`
--

CREATE TABLE IF NOT EXISTS `suliss_citywide_life_store_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catename` varchar(255) DEFAULT '',
  `color` varchar(15) NOT NULL DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `thumb` varchar(500) DEFAULT '',
  `isrecommand` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_citywide_life_store_collect`
--

CREATE TABLE IF NOT EXISTS `suliss_citywide_life_store_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `storeid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_storeid` (`storeid`),
  KEY `idx_mid` (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_citywide_secondgoods`
--

CREATE TABLE IF NOT EXISTS `suliss_citywide_secondgoods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `cate` int(11) DEFAULT '0',
  `title` varchar(100) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `thumb_url` text,
  `productprice` decimal(10,2) DEFAULT '0.00',
  `marketprice` decimal(10,2) DEFAULT '0.00',
  `description` varchar(1000) DEFAULT '',
  `degree` varchar(255) DEFAULT '',
  `content` text,
  `mobile` varchar(11) DEFAULT '',
  `buytime` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(3) NOT NULL DEFAULT '0',
  `checked` tinyint(1) NOT NULL DEFAULT '1',
  `province` varchar(255) NOT NULL DEFAULT '',
  `city` varchar(255) NOT NULL DEFAULT '',
  `area` varchar(255) NOT NULL DEFAULT '',
  `failedreason` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_cate` (`cate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_citywide_secondgoods_category`
--

CREATE TABLE IF NOT EXISTS `suliss_citywide_secondgoods_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `parentid` int(11) DEFAULT '0',
  `isrecommand` int(10) DEFAULT '0',
  `description` varchar(500) DEFAULT '',
  `displayorder` tinyint(3) unsigned DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `level` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_parentid` (`parentid`),
  KEY `idx_isrecommand` (`isrecommand`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community`
--

CREATE TABLE IF NOT EXISTS `suliss_community` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `communityname` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `mobile` varchar(255) DEFAULT '',
  `tel` varchar(255) DEFAULT '',
  `logo` varchar(255) DEFAULT '',
  `desc` text,
  `province` varchar(30) NOT NULL DEFAULT '',
  `city` varchar(30) NOT NULL DEFAULT '',
  `area` varchar(30) NOT NULL DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `provincecode` varchar(30) NOT NULL DEFAULT '',
  `citycode` varchar(20) DEFAULT '',
  `areacode` varchar(30) NOT NULL DEFAULT '',
  `lat` varchar(255) DEFAULT '',
  `lng` varchar(255) DEFAULT '',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=29 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_apply_payment`
--

CREATE TABLE IF NOT EXISTS `suliss_community_apply_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `houseid` int(11) DEFAULT '0',
  `applysn` varchar(30) DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `paytype` tinyint(3) NOT NULL DEFAULT '1',
  `transid` varchar(30) NOT NULL DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `watermoney` decimal(10,2) DEFAULT '0.00',
  `electricitymoney` decimal(10,2) DEFAULT '0.00',
  `propertymoney` decimal(10,2) DEFAULT '0.00',
  `poundage` decimal(10,2) DEFAULT '0.00' COMMENT '手续费',
  `orderids` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(3) DEFAULT '0' COMMENT '-1-已关闭 0-未支付 1-已支付 2-已完成',
  `refundstate` tinyint(3) DEFAULT '0' COMMENT '退款状态',
  `refundtime` int(11) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  `userdeleted` tinyint(3) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `paytime` int(11) DEFAULT '0',
  `finishtime` int(11) DEFAULT '0',
  `canceltime` int(11) NOT NULL DEFAULT '0',
  `cancelpaytime` int(11) DEFAULT '0',
  `adminremark` text NOT NULL,
  `remark` text,
  `remarkclose` text,
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_houseid` (`houseid`),
  KEY `idx_status` (`status`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_paytime` (`paytime`),
  KEY `idx_finishtime` (`finishtime`),
  KEY `idx_applysn` (`applysn`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=111 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_apply_repair`
--

CREATE TABLE IF NOT EXISTS `suliss_community_apply_repair` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `houseid` int(11) DEFAULT '0',
  `repairsn` varchar(30) NOT NULL DEFAULT '',
  `description` text,
  `thumb_url` text COMMENT '多图',
  `createtime` int(11) DEFAULT '0',
  `bookingtime` int(11) DEFAULT '0',
  `maketime` int(11) NOT NULL DEFAULT '0',
  `canceltime` int(11) NOT NULL DEFAULT '0',
  `finishtime` int(11) NOT NULL DEFAULT '0',
  `mobile` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0' COMMENT '-1-已关闭 1-待处理 2-已完成',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_houseid` (`houseid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1234567907 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_banner`
--

CREATE TABLE IF NOT EXISTS `suliss_community_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannername` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_building`
--

CREATE TABLE IF NOT EXISTS `suliss_community_building` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `communityid` int(11) DEFAULT '0',
  `buildingname` varchar(255) NOT NULL DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `desc` text,
  `status` tinyint(3) DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_communityid` (`communityid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_house`
--

CREATE TABLE IF NOT EXISTS `suliss_community_house` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `communityid` int(11) DEFAULT '0',
  `buildingid` int(11) DEFAULT '0',
  `housesn` varchar(30) DEFAULT '',
  `housename` varchar(255) NOT NULL DEFAULT '',
  `ownername` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `desc` text,
  `status` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_communityid` (`communityid`),
  KEY `idx_buildingid` (`buildingid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_house_electricity_order`
--

CREATE TABLE IF NOT EXISTS `suliss_community_house_electricity_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `houseid` int(11) NOT NULL DEFAULT '0',
  `electricitymoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `electricity` decimal(10,2) NOT NULL DEFAULT '0.00',
  `electricity_o` decimal(10,2) NOT NULL DEFAULT '0.00',
  `timestart` int(11) NOT NULL DEFAULT '0',
  `timeend` int(11) NOT NULL DEFAULT '0',
  `paymenttime` int(11) NOT NULL DEFAULT '0',
  `completiontime` int(11) NOT NULL DEFAULT '0',
  `desc` text NOT NULL,
  `way` tinyint(3) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_house_property_order`
--

CREATE TABLE IF NOT EXISTS `suliss_community_house_property_order` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `houseid` int(11) NOT NULL,
  `timestart` int(11) NOT NULL DEFAULT '0',
  `timeend` int(11) NOT NULL DEFAULT '0',
  `propertymoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `basicmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `othermoney` varchar(255) NOT NULL DEFAULT '',
  `paymenttime` int(11) NOT NULL DEFAULT '0',
  `desc` text NOT NULL,
  `way` tinyint(3) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_house_water_order`
--

CREATE TABLE IF NOT EXISTS `suliss_community_house_water_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `houseid` int(11) DEFAULT '0',
  `watermoney` decimal(10,2) DEFAULT '0.00',
  `water` decimal(10,2) NOT NULL DEFAULT '0.00',
  `water_m` decimal(10,2) NOT NULL DEFAULT '0.00',
  `timestart` int(11) DEFAULT '0',
  `timeend` int(11) DEFAULT '0',
  `completiontime` int(11) DEFAULT '0',
  `paymenttime` int(12) NOT NULL DEFAULT '0',
  `desc` text,
  `way` tinyint(3) NOT NULL DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_houseid` (`houseid`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_housing`
--

CREATE TABLE IF NOT EXISTS `suliss_community_housing` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `communityid` int(11) DEFAULT '0' COMMENT '小区',
  `buildingid` int(11) DEFAULT '0' COMMENT '楼栋',
  `title` varchar(255) DEFAULT '',
  `housingtype` tinyint(3) DEFAULT '0' COMMENT '1-整租 2-合租 3-短租 4-二手房',
  `thumb` varchar(255) DEFAULT '' COMMENT '缩略图',
  `thumb_url` text COMMENT '多图',
  `area` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(1000) DEFAULT '',
  `allocation` text COMMENT '房屋配置 包含WIFI，床，衣柜等',
  `housenum` varchar(50) NOT NULL DEFAULT '' COMMENT '户号',
  `acreage` int(11) DEFAULT '0' COMMENT '面积',
  `decorating` tinyint(3) DEFAULT '0' COMMENT '装修 1-精装 2-一般 3-毛坯',
  `hall` int(11) DEFAULT '0' COMMENT '厅',
  `room` int(11) DEFAULT '0' COMMENT '室',
  `toilet` int(11) DEFAULT '0' COMMENT '卫',
  `orientations` varchar(30) DEFAULT '' COMMENT '朝向',
  `floor` int(11) DEFAULT '0' COMMENT '楼层',
  `totalfloor` int(11) DEFAULT '0' COMMENT '总楼层',
  `hasparking` tinyint(3) DEFAULT '0' COMMENT '车位',
  `haselevator` tinyint(3) DEFAULT '0' COMMENT '电梯',
  `rent` decimal(10,2) DEFAULT '0.00' COMMENT '月租金',
  `rentstyle` tinyint(3) DEFAULT '0' COMMENT '租金方式 1-押一付一 2-押一付三 3-半年付 4-年付',
  `rentdetail` text COMMENT '租金详情 包含水电，燃气，宽带物业等',
  `agencyfee` decimal(10,2) DEFAULT '0.00' COMMENT '中介费',
  `contacts` varchar(255) DEFAULT '' COMMENT '联系人',
  `contactssex` tinyint(3) DEFAULT '0' COMMENT '联系人性别',
  `contactsidentity` int(10) DEFAULT '0' COMMENT '联系人身份 1-房东 2-转租 3-经纪人',
  `contactsnumber` varchar(255) DEFAULT '' COMMENT '联系人电话',
  `detail` text,
  `displayorder` int(11) DEFAULT '0',
  `clickcount` int(11) DEFAULT '0',
  `ishome` tinyint(3) DEFAULT '0' COMMENT '首页展示',
  `createtime` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_housing_collect`
--

CREATE TABLE IF NOT EXISTS `suliss_community_housing_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `housingid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_housingid` (`housingid`),
  KEY `idx_mid` (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=24 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_housing_param`
--

CREATE TABLE IF NOT EXISTS `suliss_community_housing_param` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `housingid` int(10) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `value` text,
  `displayorder` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_housingid` (`housingid`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=33 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_community_notice`
--

CREATE TABLE IF NOT EXISTS `suliss_community_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT '',
  `subtitle` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `content` text,
  `createtime` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `cate` tinyint(3) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_core_attachment`
--

CREATE TABLE IF NOT EXISTS `suliss_core_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `type` tinyint(3) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `module_upload_dir` varchar(100) NOT NULL,
  `group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=337 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member`
--

CREATE TABLE IF NOT EXISTS `suliss_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupid` int(11) DEFAULT '0',
  `level` int(11) DEFAULT '0',
  `realname` varchar(20) DEFAULT '',
  `mobile` varchar(11) DEFAULT '',
  `content` text,
  `createtime` int(10) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `nickname` varchar(255) DEFAULT '',
  `carrier_mobile` varchar(11) NOT NULL DEFAULT '',
  `carrier_realname` varchar(255) NOT NULL DEFAULT '',
  `credit1` decimal(10,2) DEFAULT '0.00',
  `credit2` decimal(10,2) DEFAULT '0.00',
  `birthyear` varchar(255) DEFAULT '',
  `birthmonth` varchar(255) DEFAULT '',
  `birthday` varchar(255) DEFAULT '',
  `gender` tinyint(3) DEFAULT '0',
  `avatar` varchar(255) DEFAULT '',
  `province` varchar(255) DEFAULT '',
  `city` varchar(255) DEFAULT '',
  `area` varchar(255) DEFAULT '',
  `isblack` int(11) DEFAULT '0',
  `username` varchar(255) DEFAULT '',
  `salt` varchar(32) DEFAULT '',
  `password` varchar(50) DEFAULT '',
  `mobileverify` tinyint(3) DEFAULT '0',
  `token` varchar(50) DEFAULT '',
  `expirestime` int(10) DEFAULT '0',
  `diymaxcredit` tinyint(3) NOT NULL DEFAULT '0',
  `maxcredit` int(10) NOT NULL DEFAULT '0',
  `regId` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_level` (`level`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_groupid` (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_credits_record`
--

CREATE TABLE IF NOT EXISTS `suliss_member_credits_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(10) unsigned NOT NULL,
  `credittype` varchar(10) NOT NULL,
  `num` decimal(10,2) NOT NULL,
  `operator` int(10) unsigned NOT NULL,
  `module` varchar(30) NOT NULL,
  `clerk_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  `clerk_type` tinyint(3) unsigned NOT NULL,
  `createtime` int(10) unsigned NOT NULL,
  `remark` varchar(200) NOT NULL,
  `real_uniacid` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_failed_login`
--

CREATE TABLE IF NOT EXISTS `suliss_member_failed_login` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(15) NOT NULL,
  `username` varchar(32) NOT NULL,
  `count` tinyint(1) unsigned NOT NULL,
  `lastupdate` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip_username` (`ip`,`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=97 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_group`
--

CREATE TABLE IF NOT EXISTS `suliss_member_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(255) DEFAULT '',
  `description` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_level`
--

CREATE TABLE IF NOT EXISTS `suliss_member_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` int(11) DEFAULT '0',
  `levelname` varchar(50) DEFAULT '',
  `ordermoney` decimal(10,2) DEFAULT '0.00',
  `ordercount` int(10) DEFAULT '0',
  `discount` decimal(10,2) DEFAULT '0.00',
  `enabled` tinyint(3) DEFAULT '0',
  `enabledadd` tinyint(1) DEFAULT '0',
  `buygoods` tinyint(1) NOT NULL DEFAULT '0',
  `goodsids` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_message`
--

CREATE TABLE IF NOT EXISTS `suliss_member_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `remark` text NOT NULL,
  `datas` text NOT NULL,
  `messagethumb` varchar(255) NOT NULL,
  `messagetype` varchar(30) DEFAULT '',
  `businessid` int(11) NOT NULL DEFAULT '0',
  `messagetid` varchar(30) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `sendtime` int(11) DEFAULT '0',
  `sendcount` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=845 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_adv`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_adv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `advname` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  `shopid` int(11) DEFAULT '0',
  `iswxapp` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_area_config`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_area_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `new_area` tinyint(3) NOT NULL DEFAULT '0',
  `address_street` tinyint(3) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_article`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_article` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_title` varchar(255) NOT NULL DEFAULT '',
  `resp_desc` text NOT NULL,
  `resp_img` text NOT NULL,
  `article_content` longtext,
  `article_category` int(11) NOT NULL DEFAULT '0',
  `article_date` varchar(20) NOT NULL DEFAULT '',
  `article_mp` varchar(50) NOT NULL DEFAULT '',
  `article_author` varchar(20) NOT NULL DEFAULT '',
  `article_readnum_v` int(11) NOT NULL DEFAULT '0',
  `article_readnum` int(11) NOT NULL DEFAULT '0',
  `article_likenum_v` int(11) NOT NULL DEFAULT '0',
  `article_likenum` int(11) NOT NULL DEFAULT '0',
  `page_set_option_nocopy` int(1) NOT NULL DEFAULT '0',
  `page_set_option_noshare_tl` int(1) NOT NULL DEFAULT '0',
  `page_set_option_noshare_msg` int(1) NOT NULL DEFAULT '0',
  `article_state` int(1) NOT NULL DEFAULT '0',
  `article_endtime` int(11) DEFAULT '0',
  `article_hasendtime` tinyint(3) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_article_title` (`article_title`),
  KEY `idx_article_content` (`article_content`(10))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='营销文章' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_article_category`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_article_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL DEFAULT '',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `isshow` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_category_name` (`category_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='营销表单分类' AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_banner`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_auction_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannername` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_category`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_auction_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) unsigned DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_banner`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannername` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_bargain_actor`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_bargain_actor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `now_price` decimal(9,2) NOT NULL,
  `created_time` datetime NOT NULL,
  `update_time` datetime NOT NULL,
  `bargain_times` int(10) NOT NULL,
  `nickname` varchar(20) NOT NULL,
  `head_image` varchar(200) NOT NULL,
  `bargain_price` decimal(9,2) NOT NULL,
  `status` tinyint(2) NOT NULL,
  `account_id` int(11) NOT NULL,
  `initiate` tinyint(4) NOT NULL DEFAULT '0',
  `order` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_core_paylog`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_core_paylog` (
  `plid` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `mid` int(10) NOT NULL,
  `uniontid` varchar(64) NOT NULL,
  `tid` varchar(128) NOT NULL,
  `credit` int(10) NOT NULL DEFAULT '0',
  `creditmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fee` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `module` varchar(50) NOT NULL,
  `tag` varchar(2000) NOT NULL,
  `is_usecard` tinyint(3) unsigned NOT NULL,
  `card_type` tinyint(3) unsigned NOT NULL,
  `card_id` varchar(50) NOT NULL,
  `card_fee` decimal(10,2) unsigned NOT NULL,
  `encrypt_code` varchar(100) NOT NULL,
  `createtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`plid`),
  KEY `idx_tid` (`tid`),
  KEY `idx_mid` (`mid`),
  KEY `uniontid` (`uniontid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=684 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_data`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_coupon_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `gettype` tinyint(3) DEFAULT '0',
  `used` int(11) DEFAULT '0',
  `usetime` int(11) DEFAULT '0',
  `gettime` int(11) DEFAULT '0',
  `senduid` int(11) DEFAULT '0',
  `ordersn` varchar(255) DEFAULT '',
  `back` tinyint(3) DEFAULT '0',
  `backtime` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `isnew` tinyint(1) DEFAULT '1',
  `nocount` tinyint(1) DEFAULT '1',
  `shareident` varchar(50) DEFAULT NULL,
  `textkey` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_couponid` (`couponid`),
  KEY `idx_gettype` (`gettype`),
  KEY `idx_used` (`used`),
  KEY `idx_gettime` (`gettime`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_goodsendtask`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_coupon_goodsendtask` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `starttime` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '1',
  `num` int(11) DEFAULT '0',
  `sendpoint` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_log`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_coupon_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `logno` varchar(255) DEFAULT '',
  `couponid` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `paystatus` tinyint(3) DEFAULT '0',
  `creditstatus` tinyint(3) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `paytype` tinyint(3) DEFAULT '0',
  `getfrom` tinyint(3) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_couponid` (`couponid`),
  KEY `idx_status` (`status`),
  KEY `idx_paystatus` (`paystatus`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_getfrom` (`getfrom`),
  KEY `idx_logno` (`logno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_sendshow`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_coupon_sendshow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `showkey` varchar(20) NOT NULL,
  `mid` int(11) NOT NULL,
  `coupondataid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_sendtasks`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_coupon_sendtasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enough` decimal(10,2) DEFAULT '0.00',
  `couponid` int(11) DEFAULT '0',
  `starttime` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '1',
  `num` int(11) DEFAULT '0',
  `sendpoint` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_taskdata`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_coupon_taskdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT NULL,
  `taskid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '0',
  `tasktype` tinyint(1) DEFAULT '0',
  `orderid` int(11) DEFAULT '0',
  `parentorderid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `sendpoint` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_usesendtasks`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_coupon_usesendtasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usecouponid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `starttime` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '1',
  `num` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_banner`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannername` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_comment`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `logid` int(11) NOT NULL DEFAULT '0',
  `logno` varchar(50) NOT NULL DEFAULT '',
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `nickname` varchar(50) DEFAULT NULL,
  `headimg` varchar(255) DEFAULT NULL,
  `level` tinyint(3) NOT NULL DEFAULT '0',
  `content` varchar(255) DEFAULT NULL,
  `images` text,
  `time` int(11) NOT NULL DEFAULT '0',
  `reply_content` varchar(255) DEFAULT NULL,
  `reply_images` text,
  `reply_time` int(11) NOT NULL DEFAULT '0',
  `append_content` varchar(255) DEFAULT NULL,
  `append_images` text,
  `append_time` int(11) NOT NULL DEFAULT '0',
  `append_reply_content` varchar(255) DEFAULT NULL,
  `append_reply_images` text,
  `append_reply_time` int(11) NOT NULL DEFAULT '0',
  `istop` tinyint(3) NOT NULL DEFAULT '0',
  `checked` tinyint(3) NOT NULL DEFAULT '0',
  `append_checked` tinyint(3) NOT NULL DEFAULT '0',
  `virtual` tinyint(3) NOT NULL DEFAULT '0',
  `deleted` tinyint(3) NOT NULL DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `displayorder` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `cate` int(11) DEFAULT '0',
  `thumb` varchar(255) DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `type` tinyint(3) DEFAULT '0',
  `credit` int(11) DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00',
  `total` int(11) DEFAULT '0',
  `totalday` int(11) DEFAULT '0',
  `chance` int(11) DEFAULT '0',
  `chanceday` int(11) DEFAULT '0',
  `detail` text,
  `rate1` int(11) DEFAULT '0',
  `rate2` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `joins` int(11) DEFAULT '0',
  `views` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  `showlevels` text,
  `buylevels` text,
  `showgroups` text,
  `buygroups` text,
  `vip` tinyint(3) DEFAULT '0',
  `istop` tinyint(3) DEFAULT '0',
  `isrecommand` tinyint(3) DEFAULT '0',
  `istime` tinyint(3) DEFAULT '0',
  `timestart` int(11) DEFAULT '0',
  `timeend` int(11) DEFAULT '0',
  `share_title` varchar(255) DEFAULT '',
  `share_icon` varchar(255) DEFAULT '',
  `share_desc` varchar(500) DEFAULT '',
  `followneed` tinyint(3) DEFAULT '0',
  `followtext` varchar(255) DEFAULT '',
  `subtitle` varchar(255) DEFAULT '',
  `subdetail` text,
  `noticedetail` text,
  `usedetail` varchar(255) DEFAULT '',
  `goodsdetail` text,
  `isendtime` tinyint(3) DEFAULT '0',
  `usecredit2` tinyint(3) DEFAULT '0',
  `area` varchar(255) DEFAULT '',
  `dispatch` decimal(10,2) DEFAULT '0.00',
  `storeids` text,
  `noticeopenid` varchar(255) DEFAULT '',
  `noticetype` tinyint(3) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `goodstype` tinyint(3) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  `productprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `mincredit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `minmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `maxcredit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `maxmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `dispatchtype` tinyint(3) NOT NULL DEFAULT '0',
  `dispatchid` int(11) NOT NULL DEFAULT '0',
  `verifytype` tinyint(3) NOT NULL DEFAULT '0',
  `verifynum` int(11) NOT NULL DEFAULT '0',
  `grant1` decimal(10,2) NOT NULL DEFAULT '0.00',
  `grant2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goodssn` varchar(255) NOT NULL,
  `productsn` varchar(255) NOT NULL,
  `weight` int(11) NOT NULL,
  `showtotal` tinyint(3) NOT NULL,
  `totalcnf` tinyint(3) NOT NULL DEFAULT '0',
  `usetime` int(11) NOT NULL DEFAULT '0',
  `hasoption` tinyint(3) NOT NULL DEFAULT '0',
  `noticedetailshow` tinyint(3) NOT NULL DEFAULT '0',
  `detailshow` tinyint(3) NOT NULL DEFAULT '0',
  `packetmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `surplusmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `packetlimit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `packettype` tinyint(3) NOT NULL DEFAULT '0',
  `minpacketmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `packettotal` int(11) NOT NULL DEFAULT '0',
  `packetsurplus` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`),
  KEY `idx_endtime` (`endtime`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_status` (`status`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_deleted` (`deleted`),
  KEY `idx_istop` (`istop`),
  KEY `idx_isrecommand` (`isrecommand`),
  KEY `idx_istime` (`istime`),
  KEY `idx_timestart` (`timestart`),
  KEY `idx_timeend` (`timeend`),
  KEY `idx_goodstype` (`goodstype`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_category`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_goods_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) unsigned DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_option`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_goods_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(10) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `thumb` varchar(60) DEFAULT '',
  `credit` int(10) NOT NULL DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00',
  `total` int(11) DEFAULT '0',
  `weight` decimal(10,2) DEFAULT '0.00',
  `displayorder` int(11) DEFAULT '0',
  `specs` text,
  `skuId` varchar(255) DEFAULT '',
  `goodssn` varchar(255) DEFAULT '',
  `productsn` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0',
  `exchange_stock` int(11) NOT NULL DEFAULT '-1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_spec`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_goods_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(11) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `displaytype` tinyint(3) DEFAULT '0',
  `content` text,
  `displayorder` int(11) DEFAULT '0',
  `propId` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_spec_item`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_goods_spec_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `specid` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `show` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `valueId` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_log`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `logno` varchar(255) DEFAULT '',
  `eno` varchar(255) DEFAULT '',
  `goodsid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `paystatus` tinyint(3) DEFAULT '0',
  `paytype` tinyint(3) DEFAULT '-1',
  `dispatchstatus` tinyint(3) DEFAULT '0',
  `creditpay` tinyint(3) DEFAULT '0',
  `addressid` int(11) DEFAULT '0',
  `dispatchno` varchar(255) DEFAULT '',
  `usetime` int(11) DEFAULT '0',
  `express` varchar(255) DEFAULT '',
  `expresssn` varchar(255) DEFAULT '',
  `expresscom` varchar(255) DEFAULT '',
  `verifyopenid` varchar(255) DEFAULT '',
  `storeid` int(11) DEFAULT '0',
  `realname` varchar(255) DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  `couponid` int(11) DEFAULT '0',
  `dupdate1` tinyint(3) DEFAULT '0',
  `transid` varchar(255) DEFAULT '',
  `dispatchtransid` varchar(255) DEFAULT '',
  `address` text,
  `optionid` int(11) NOT NULL DEFAULT '0',
  `time_send` int(11) NOT NULL DEFAULT '0',
  `time_finish` int(11) NOT NULL DEFAULT '0',
  `iscomment` tinyint(3) NOT NULL DEFAULT '0',
  `dispatchtime` int(11) NOT NULL DEFAULT '0',
  `verifynum` int(11) NOT NULL DEFAULT '1',
  `verifytime` int(11) NOT NULL DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_verify`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_creditshop_verify` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `logid` int(11) DEFAULT '0',
  `verifycode` varchar(45) DEFAULT NULL,
  `storeid` int(11) DEFAULT '0',
  `verifier` varchar(45) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `verifytime` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_dispatch`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_dispatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatchname` varchar(50) DEFAULT '',
  `dispatchtype` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `firstprice` decimal(10,2) DEFAULT '0.00',
  `secondprice` decimal(10,2) DEFAULT '0.00',
  `firstweight` int(11) DEFAULT '0',
  `secondweight` int(11) DEFAULT '0',
  `express` varchar(250) DEFAULT '',
  `areas` longtext,
  `carriers` text,
  `enabled` int(11) DEFAULT '0',
  `calculatetype` tinyint(1) DEFAULT '0',
  `firstnum` int(11) DEFAULT '0',
  `secondnum` int(11) DEFAULT '0',
  `firstnumprice` decimal(10,2) DEFAULT '0.00',
  `secondnumprice` decimal(10,2) DEFAULT '0.00',
  `isdefault` tinyint(1) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `nodispatchareas` text,
  `nodispatchareas_code` longtext,
  `isdispatcharea` tinyint(3) NOT NULL DEFAULT '0',
  `freeprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_express`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_express` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '',
  `express` varchar(50) DEFAULT '',
  `status` tinyint(1) DEFAULT '1',
  `displayorder` tinyint(3) unsigned DEFAULT '0',
  `code` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=92 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_express_cache`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_express_cache` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expresssn` varchar(50) DEFAULT NULL,
  `express` varchar(50) DEFAULT NULL,
  `lasttime` int(11) NOT NULL,
  `datas` text,
  PRIMARY KEY (`id`),
  KEY `idx_expresssn` (`expresssn`) USING BTREE,
  KEY `idx_express` (`express`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_fullback_goods`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_fullback_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL DEFAULT '0',
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `titles` varchar(255) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `marketprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `minallfullbackallprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `maxallfullbackallprice` decimal(10,2) NOT NULL,
  `minallfullbackallratio` decimal(10,2) DEFAULT NULL,
  `maxallfullbackallratio` decimal(10,2) DEFAULT NULL,
  `day` int(11) NOT NULL DEFAULT '0',
  `fullbackprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullbackratio` decimal(10,2) DEFAULT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `hasoption` tinyint(3) NOT NULL DEFAULT '0',
  `optionid` text NOT NULL,
  `startday` int(11) NOT NULL DEFAULT '0',
  `refund` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_fullback_log`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_fullback_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `orderid` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `priceevery` decimal(10,2) NOT NULL DEFAULT '0.00',
  `day` int(10) NOT NULL DEFAULT '0',
  `fullbackday` int(10) NOT NULL DEFAULT '0',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `fullbacktime` int(10) NOT NULL DEFAULT '0',
  `isfullback` tinyint(3) NOT NULL DEFAULT '0',
  `goodsid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_gift`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_gift` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `activity` tinyint(3) NOT NULL DEFAULT '1',
  `orderprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goodsid` varchar(255) NOT NULL,
  `giftgoodsid` varchar(255) NOT NULL,
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `share_title` varchar(255) NOT NULL,
  `share_icon` varchar(255) NOT NULL,
  `share_desc` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pcate` int(11) DEFAULT '0',
  `ccate` int(11) DEFAULT '0',
  `title` varchar(100) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `type` tinyint(10) DEFAULT '1',
  `status` tinyint(1) DEFAULT '1',
  `displayorder` int(11) DEFAULT '0',
  `unit` varchar(5) DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `content` text,
  `goodssn` varchar(50) DEFAULT '',
  `productsn` varchar(50) DEFAULT '',
  `productprice` decimal(10,2) DEFAULT '0.00',
  `marketprice` decimal(10,2) DEFAULT '0.00',
  `costprice` decimal(10,2) DEFAULT '0.00',
  `originalprice` decimal(10,2) DEFAULT '0.00',
  `total` int(10) DEFAULT '0',
  `totalcnf` int(11) DEFAULT '0',
  `sales` int(11) DEFAULT '0',
  `salesreal` int(11) DEFAULT '0',
  `spec` varchar(5000) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `weight` decimal(10,2) DEFAULT '0.00',
  `credit` varchar(255) DEFAULT '',
  `maxbuy` int(11) DEFAULT '0',
  `usermaxbuy` int(11) DEFAULT '0',
  `hasoption` int(11) DEFAULT '0',
  `dispatch` int(11) DEFAULT '0',
  `thumb_url` text,
  `isnew` tinyint(1) DEFAULT '0',
  `ishot` tinyint(1) DEFAULT '0',
  `isdiscount` tinyint(1) DEFAULT '0',
  `isrecommand` tinyint(1) DEFAULT '0',
  `issendfree` tinyint(1) DEFAULT '0',
  `istime` tinyint(1) DEFAULT '0',
  `iscomment` tinyint(1) DEFAULT '0',
  `timestart` int(11) DEFAULT '0',
  `timeend` int(11) DEFAULT '0',
  `viewcount` int(11) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  `score` decimal(10,2) DEFAULT '0.00',
  `updatetime` int(11) DEFAULT '0',
  `share_title` varchar(255) DEFAULT '',
  `share_icon` varchar(255) DEFAULT '',
  `cash` tinyint(3) DEFAULT '0',
  `isnodiscount` tinyint(3) DEFAULT '0',
  `showlevels` text,
  `buylevels` text,
  `showgroups` text,
  `buygroups` text,
  `isverify` tinyint(3) DEFAULT '0',
  `storeids` text,
  `noticemid` varchar(255) NOT NULL DEFAULT '',
  `tcate` int(11) DEFAULT '0',
  `noticetype` text,
  `needfollow` tinyint(3) DEFAULT '0',
  `followtip` varchar(255) DEFAULT '',
  `followurl` varchar(255) DEFAULT '',
  `deduct` decimal(10,2) DEFAULT '0.00',
  `virtual` int(11) DEFAULT '0',
  `ccates` text,
  `discounts` text,
  `pcates` text,
  `tcates` text,
  `cates` text,
  `artid` int(11) DEFAULT '0',
  `detail_logo` varchar(255) DEFAULT '',
  `detail_shopname` varchar(255) DEFAULT '',
  `detail_btntext1` varchar(255) DEFAULT '',
  `detail_btnurl1` varchar(255) DEFAULT '',
  `detail_btntext2` varchar(255) DEFAULT '',
  `detail_btnurl2` varchar(255) DEFAULT '',
  `detail_totaltitle` varchar(255) DEFAULT '',
  `saleupdate42392` tinyint(3) DEFAULT '0',
  `deduct2` decimal(10,2) DEFAULT '0.00',
  `ednum` int(11) DEFAULT '0',
  `saleupdate` tinyint(3) DEFAULT '0',
  `edmoney` decimal(10,2) DEFAULT '0.00',
  `edareas` text,
  `diyformtype` tinyint(1) DEFAULT '0',
  `diyformid` int(11) DEFAULT '0',
  `diymode` tinyint(1) DEFAULT '0',
  `dispatchtype` tinyint(1) DEFAULT '0',
  `dispatchid` int(11) DEFAULT '0',
  `dispatchprice` decimal(10,2) DEFAULT '0.00',
  `manydeduct` tinyint(1) DEFAULT '0',
  `shorttitle` varchar(255) DEFAULT '',
  `isdiscount_title` varchar(255) DEFAULT '',
  `isdiscount_time` int(11) DEFAULT '0',
  `isdiscount_discounts` text,
  `saleupdate37975` tinyint(3) DEFAULT '0',
  `shopid` int(11) DEFAULT '0',
  `allcates` text,
  `minbuy` int(11) DEFAULT '0',
  `invoice` tinyint(3) DEFAULT '0',
  `repair` tinyint(3) DEFAULT '0',
  `seven` tinyint(3) DEFAULT '0',
  `money` varchar(255) DEFAULT '',
  `minprice` decimal(10,2) DEFAULT '0.00',
  `maxprice` decimal(10,2) DEFAULT '0.00',
  `province` varchar(255) DEFAULT '',
  `city` varchar(255) DEFAULT '',
  `buyshow` tinyint(1) DEFAULT '0',
  `buycontent` text,
  `saleupdate51117` tinyint(3) DEFAULT '0',
  `virtualsend` tinyint(1) DEFAULT '0',
  `virtualsendcontent` text,
  `verifytype` tinyint(1) DEFAULT '0',
  `diyfields` text,
  `diysaveid` int(11) DEFAULT '0',
  `diysave` tinyint(1) DEFAULT '0',
  `quality` tinyint(3) DEFAULT '0',
  `groupstype` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `showtotal` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `subtitle` varchar(255) DEFAULT '',
  `minpriceupdated` tinyint(1) DEFAULT '0',
  `newgoods` tinyint(3) NOT NULL DEFAULT '0',
  `video` varchar(512) DEFAULT '',
  `sharebtn` tinyint(1) NOT NULL DEFAULT '0',
  `catesinit3` text,
  `showtotaladd` tinyint(1) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `merchcates` text CHARACTER SET armscii8,
  `merchccates` text CHARACTER SET armscii8,
  `merchccate` int(10) NOT NULL DEFAULT '0',
  `merchtcates` text CHARACTER SET armscii8,
  `merchtcate` int(10) NOT NULL DEFAULT '0',
  `merchpcates` text CHARACTER SET armscii8,
  `merchpcate` int(10) NOT NULL DEFAULT '0',
  `checked` tinyint(3) DEFAULT '0',
  `thumb_first` tinyint(3) DEFAULT '0',
  `merchsale` tinyint(1) DEFAULT '0',
  `keywords` varchar(255) DEFAULT '',
  `catch_id` varchar(255) DEFAULT '',
  `catch_url` varchar(255) DEFAULT '',
  `catch_source` varchar(255) DEFAULT '',
  `saleupdate40170` tinyint(3) DEFAULT '0',
  `saleupdate35843` tinyint(3) DEFAULT '0',
  `labelname` text,
  `autoreceive` int(11) DEFAULT '0',
  `cannotrefund` tinyint(3) DEFAULT '0',
  `saleupdate33219` tinyint(3) DEFAULT '0',
  `bargain` int(11) DEFAULT '0',
  `buyagain` decimal(10,2) DEFAULT '0.00',
  `buyagain_islong` tinyint(1) DEFAULT '0',
  `buyagain_condition` tinyint(1) DEFAULT '0',
  `buyagain_sale` tinyint(1) DEFAULT '0',
  `saleupdate32484` tinyint(3) DEFAULT '0',
  `saleupdate36586` tinyint(3) DEFAULT '0',
  `diypage` int(11) DEFAULT '0',
  `cashier` tinyint(1) DEFAULT '0',
  `saleupdate53481` tinyint(3) DEFAULT '0',
  `saleupdate30424` tinyint(3) DEFAULT '0',
  `isendtime` tinyint(3) NOT NULL DEFAULT '0',
  `usetime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `merchdisplayorder` int(11) NOT NULL DEFAULT '0',
  `exchange_stock` int(11) DEFAULT '0',
  `exchange_postage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ispresell` tinyint(3) NOT NULL DEFAULT '0',
  `presellprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `presellover` tinyint(3) NOT NULL DEFAULT '0',
  `presellovertime` int(11) NOT NULL DEFAULT '0',
  `presellstart` tinyint(3) NOT NULL DEFAULT '0',
  `preselltimestart` int(11) NOT NULL DEFAULT '0',
  `presellend` tinyint(3) NOT NULL DEFAULT '0',
  `preselltimeend` int(11) NOT NULL DEFAULT '0',
  `presellsendtype` tinyint(3) NOT NULL DEFAULT '0',
  `presellsendstatrttime` int(11) NOT NULL DEFAULT '0',
  `presellsendtime` int(11) NOT NULL DEFAULT '0',
  `edareas_code` text NOT NULL,
  `unite_total` tinyint(3) NOT NULL DEFAULT '0',
  `buyagain_price` decimal(10,2) DEFAULT '0.00',
  `threen` varchar(255) DEFAULT '',
  `intervalfloor` tinyint(1) DEFAULT '0',
  `intervalprice` varchar(512) DEFAULT '',
  `isfullback` tinyint(3) NOT NULL DEFAULT '0',
  `isstatustime` tinyint(3) NOT NULL DEFAULT '0',
  `statustimestart` int(10) NOT NULL DEFAULT '0',
  `statustimeend` int(10) NOT NULL DEFAULT '0',
  `nosearch` tinyint(1) NOT NULL DEFAULT '0',
  `showsales` tinyint(3) NOT NULL DEFAULT '1',
  `islive` int(11) NOT NULL DEFAULT '0',
  `liveprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `opencard` tinyint(1) DEFAULT '0',
  `cardid` varchar(255) DEFAULT '',
  `verifygoodsnum` int(11) DEFAULT '1',
  `verifygoodsdays` int(11) DEFAULT '1',
  `verifygoodslimittype` tinyint(1) DEFAULT '0',
  `verifygoodslimitdate` int(11) DEFAULT '0',
  `minliveprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `maxliveprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `dowpayment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tempid` int(11) NOT NULL DEFAULT '0',
  `isstoreprice` tinyint(11) NOT NULL DEFAULT '0',
  `beforehours` int(11) NOT NULL DEFAULT '0',
  `isgroups` tinyint(1) NOT NULL DEFAULT '0' COMMENT '团购',
  `category` int(11) NOT NULL DEFAULT '0',
  `gid` int(11) NOT NULL DEFAULT '0',
  `groupsprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `single` tinyint(2) NOT NULL DEFAULT '0',
  `singleprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goodsnum` int(11) NOT NULL DEFAULT '0',
  `purchaselimit` int(11) NOT NULL DEFAULT '0',
  `teamnum` int(11) NOT NULL DEFAULT '0',
  `isindex` tinyint(3) NOT NULL DEFAULT '0',
  `groupnum` int(10) NOT NULL DEFAULT '0',
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `discount` tinyint(3) NOT NULL DEFAULT '0',
  `headstype` tinyint(3) NOT NULL DEFAULT '0',
  `headsmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `headsdiscount` int(11) NOT NULL DEFAULT '0',
  `isauction` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_pcate` (`pcate`),
  KEY `idx_ccate` (`ccate`),
  KEY `idx_isnew` (`isnew`),
  KEY `idx_ishot` (`ishot`),
  KEY `idx_isdiscount` (`isdiscount`),
  KEY `idx_isrecommand` (`isrecommand`),
  KEY `idx_iscomment` (`iscomment`),
  KEY `idx_issendfree` (`issendfree`),
  KEY `idx_istime` (`istime`),
  KEY `idx_deleted` (`deleted`),
  KEY `idx_tcate` (`tcate`),
  KEY `idx_scate` (`tcate`),
  KEY `idx_merchid` (`merchid`),
  KEY `idx_checked` (`checked`),
  KEY `idx_productsn` (`productsn`),
  FULLTEXT KEY `idx_buylevels` (`buylevels`),
  FULLTEXT KEY `idx_showgroups` (`showgroups`),
  FULLTEXT KEY `idx_buygroups` (`buygroups`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_category`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `parentid` int(11) DEFAULT '0',
  `isrecommand` int(10) DEFAULT '0',
  `description` varchar(500) DEFAULT '',
  `displayorder` tinyint(3) unsigned DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `ishome` tinyint(3) DEFAULT '0',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `level` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_parentid` (`parentid`),
  KEY `idx_isrecommand` (`isrecommand`),
  KEY `idx_ishome` (`ishome`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=56 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_favorite`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(10) DEFAULT '0',
  `mid` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `type` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_mid` (`mid`),
  KEY `idx_deleted` (`deleted`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=44 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_group`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `goodsids` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '50',
  `merchid` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_label`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_label` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) NOT NULL DEFAULT '',
  `labelname` text NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `merchid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_option`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(10) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `thumb` varchar(60) DEFAULT '',
  `productprice` decimal(10,2) DEFAULT '0.00',
  `marketprice` decimal(10,2) DEFAULT '0.00',
  `costprice` decimal(10,2) DEFAULT '0.00',
  `stock` int(11) DEFAULT '0',
  `weight` decimal(10,2) DEFAULT '0.00',
  `displayorder` int(11) DEFAULT '0',
  `specs` text,
  `skuId` varchar(255) DEFAULT '',
  `goodssn` varchar(255) DEFAULT '',
  `productsn` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0',
  `exchange_stock` int(11) DEFAULT '0',
  `exchange_postage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `presellprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `day` int(3) NOT NULL DEFAULT '0',
  `allfullbackprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullbackprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `allfullbackratio` decimal(10,2) DEFAULT '0.00',
  `fullbackratio` decimal(10,2) DEFAULT '0.00',
  `isfullback` tinyint(3) NOT NULL,
  `islive` int(11) NOT NULL,
  `liveprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_productsn` (`productsn`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=129 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_param`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_param` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(10) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `value` text,
  `displayorder` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=47 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_spec`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_spec` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(11) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `displaytype` tinyint(3) DEFAULT '0',
  `content` text,
  `displayorder` int(11) DEFAULT '0',
  `propId` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=21 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_spec_item`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_goods_spec_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `specid` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `show` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `valueId` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_specid` (`specid`),
  KEY `idx_show` (`show`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=52 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_banner`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_groups_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannername` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_enabled` (`enabled`),
  KEY `idx_displayorder` (`displayorder`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_category`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_groups_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) unsigned DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_displayorder` (`displayorder`),
  KEY `idx_enabled` (`enabled`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_order`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_groups_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `orderno` varchar(45) NOT NULL,
  `groupnum` int(11) NOT NULL,
  `paytime` int(11) NOT NULL,
  `price` decimal(11,2) DEFAULT '0.00',
  `freight` decimal(11,2) DEFAULT '0.00',
  `status` int(9) NOT NULL,
  `pay_type` varchar(45) DEFAULT NULL,
  `goodid` int(11) NOT NULL,
  `teamid` int(11) NOT NULL,
  `is_team` int(2) NOT NULL,
  `heads` int(11) DEFAULT '0',
  `starttime` int(11) NOT NULL,
  `endtime` int(45) NOT NULL,
  `createtime` int(11) NOT NULL,
  `success` int(2) NOT NULL DEFAULT '0',
  `delete` int(2) NOT NULL DEFAULT '0',
  `dispatchid` int(11) DEFAULT NULL,
  `addressid` int(11) NOT NULL DEFAULT '0',
  `address` varchar(1000) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `canceltime` int(11) NOT NULL DEFAULT '0',
  `finishtime` int(11) NOT NULL DEFAULT '0',
  `refundid` int(11) NOT NULL DEFAULT '0',
  `refundstate` tinyint(2) NOT NULL DEFAULT '0',
  `refundtime` int(11) NOT NULL DEFAULT '0',
  `express` varchar(45) DEFAULT NULL,
  `expresscom` varchar(100) DEFAULT NULL,
  `expresssn` varchar(45) DEFAULT NULL,
  `sendtime` int(45) DEFAULT '0',
  `remark` varchar(255) DEFAULT NULL,
  `remarkclose` text,
  `remarksend` text,
  `message` varchar(255) DEFAULT NULL,
  `deleted` int(2) NOT NULL DEFAULT '0',
  `realname` varchar(20) DEFAULT NULL,
  `mobile` varchar(11) DEFAULT NULL,
  `isverify` tinyint(3) DEFAULT '0',
  `verifytype` tinyint(3) DEFAULT '0',
  `verifycode` varchar(45) DEFAULT '0',
  `verifynum` int(11) DEFAULT '0',
  `printstate` int(11) NOT NULL DEFAULT '0',
  `printstate2` int(11) NOT NULL DEFAULT '0',
  `apppay` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_order_refund`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_groups_order_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `orderid` int(11) NOT NULL DEFAULT '0',
  `refundno` varchar(45) NOT NULL DEFAULT '0',
  `refundstatus` tinyint(3) NOT NULL DEFAULT '0',
  `refundaddressid` int(11) NOT NULL DEFAULT '0',
  `refundaddress` varchar(1000) NOT NULL DEFAULT '0',
  `content` varchar(255) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `images` varchar(255) DEFAULT NULL,
  `applytime` varchar(45) NOT NULL DEFAULT '0',
  `applycredit` int(11) NOT NULL DEFAULT '0',
  `applyprice` decimal(11,2) NOT NULL DEFAULT '0.00',
  `reply` text,
  `refundtype` varchar(45) DEFAULT NULL,
  `rtype` int(3) NOT NULL DEFAULT '0',
  `refundtime` varchar(45) NOT NULL,
  `endtime` varchar(45) NOT NULL DEFAULT '0',
  `message` varchar(255) DEFAULT NULL,
  `operatetime` varchar(45) NOT NULL DEFAULT '0',
  `realcredit` int(11) NOT NULL,
  `realmoney` decimal(11,2) NOT NULL,
  `express` varchar(45) DEFAULT NULL,
  `expresscom` varchar(100) DEFAULT NULL,
  `expresssn` varchar(45) DEFAULT NULL,
  `sendtime` varchar(45) NOT NULL DEFAULT '0',
  `returntime` int(11) NOT NULL DEFAULT '0',
  `rexpress` varchar(45) DEFAULT NULL,
  `rexpresscom` varchar(100) DEFAULT NULL,
  `rexpresssn` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_paylog`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_groups_paylog` (
  `plid` bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `acid` int(11) unsigned NOT NULL,
  `mid` int(11) unsigned NOT NULL,
  `tid` varchar(64) NOT NULL,
  `credit` int(10) NOT NULL DEFAULT '0',
  `creditmoney` decimal(10,2) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `module` varchar(50) NOT NULL,
  `tag` varchar(2000) NOT NULL,
  `is_usecard` tinyint(3) unsigned NOT NULL,
  `card_type` tinyint(3) unsigned NOT NULL,
  `card_id` varchar(50) NOT NULL,
  `card_fee` decimal(10,2) unsigned NOT NULL,
  `encrypt_code` varchar(100) NOT NULL,
  `uniontid` varchar(50) NOT NULL,
  PRIMARY KEY (`plid`),
  KEY `idx_mid` (`mid`),
  KEY `idx_tid` (`tid`),
  KEY `uniontid` (`uniontid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_set`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_groups_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groups` int(2) NOT NULL DEFAULT '0',
  `groupsurl` varchar(255) DEFAULT NULL,
  `groups_description` text,
  `description` int(2) NOT NULL DEFAULT '0',
  `opengroups` tinyint(3) DEFAULT '0',
  `creditdeduct` tinyint(2) NOT NULL DEFAULT '0',
  `groupsdeduct` tinyint(2) NOT NULL DEFAULT '0',
  `credit` int(11) NOT NULL DEFAULT '1',
  `groupsmoney` decimal(11,2) NOT NULL DEFAULT '0.00',
  `refund` int(11) NOT NULL DEFAULT '0',
  `refundday` int(11) NOT NULL DEFAULT '0',
  `goodsid` text NOT NULL,
  `rules` text,
  `receive` int(11) DEFAULT '0',
  `discount` tinyint(3) DEFAULT '0',
  `headstype` tinyint(3) DEFAULT '0',
  `headsmoney` decimal(10,2) DEFAULT '0.00',
  `headsdiscount` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_member_address`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_member_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `realname` varchar(20) DEFAULT '',
  `mobile` varchar(11) DEFAULT '',
  `province` varchar(30) DEFAULT '',
  `city` varchar(30) DEFAULT '',
  `area` varchar(30) DEFAULT '',
  `street` varchar(50) NOT NULL DEFAULT '',
  `address` varchar(300) DEFAULT '',
  `zipcode` varchar(255) DEFAULT '',
  `isdefault` tinyint(1) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_isdefault` (`isdefault`),
  KEY `idx_deleted` (`deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=62 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_member_cart`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_member_cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `total` int(11) DEFAULT '0',
  `marketprice` decimal(10,2) DEFAULT '0.00',
  `deleted` tinyint(1) DEFAULT '0',
  `optionid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `selected` tinyint(1) DEFAULT '1',
  `selectedadd` tinyint(1) DEFAULT '1',
  `merchid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_deleted` (`deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=297 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_member_history`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_member_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `goodsid` int(10) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `times` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_deleted` (`deleted`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=120 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_nav`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_nav` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `navname` varchar(255) DEFAULT '',
  `icon` varchar(255) DEFAULT '',
  `url` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `iswxapp` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_notice`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `displayorder` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `cate` varchar(20) NOT NULL DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `detail` text,
  `status` tinyint(3) DEFAULT '0',
  `createtime` int(11) DEFAULT NULL,
  `merchid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `ordersn` varchar(30) DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `goodsprice` decimal(10,2) DEFAULT '0.00',
  `discountprice` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(3) DEFAULT '0',
  `paytype` tinyint(1) DEFAULT '0',
  `transid` varchar(30) DEFAULT '0',
  `remark` varchar(1000) DEFAULT '',
  `addressid` int(11) DEFAULT '0',
  `dispatchprice` decimal(10,2) DEFAULT '0.00',
  `dispatchid` int(10) DEFAULT '0',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `dispatchtype` tinyint(3) DEFAULT '0',
  `carrier` text,
  `refundid` int(11) DEFAULT '0',
  `iscomment` tinyint(3) DEFAULT '0',
  `creditadd` tinyint(3) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  `userdeleted` tinyint(3) DEFAULT '0',
  `finishtime` int(11) DEFAULT '0',
  `paytime` int(11) DEFAULT '0',
  `expresscom` varchar(30) NOT NULL DEFAULT '',
  `expresssn` varchar(50) NOT NULL DEFAULT '',
  `express` varchar(255) DEFAULT '',
  `sendtime` int(11) DEFAULT '0',
  `fetchtime` int(11) DEFAULT '0',
  `cash` tinyint(3) DEFAULT '0',
  `canceltime` int(11) NOT NULL DEFAULT '0',
  `cancelpaytime` int(11) DEFAULT '0',
  `refundtime` int(11) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `verified` tinyint(3) DEFAULT '0',
  `verifyopenid` int(11) NOT NULL DEFAULT '0',
  `verifycode` varchar(255) DEFAULT '',
  `verifytime` int(11) DEFAULT '0',
  `verifystoreid` int(11) DEFAULT '0',
  `deductprice` decimal(10,2) DEFAULT '0.00',
  `deductcredit` int(10) DEFAULT '0',
  `deductcredit2` decimal(10,2) DEFAULT '0.00',
  `deductenough` decimal(10,2) DEFAULT '0.00',
  `virtual` int(11) DEFAULT '0',
  `virtual_info` text,
  `virtual_str` text,
  `address` text,
  `sysdeleted` tinyint(3) DEFAULT '0',
  `ordersn2` int(11) DEFAULT '0',
  `changeprice` decimal(10,2) DEFAULT '0.00',
  `changedispatchprice` decimal(10,2) DEFAULT '0.00',
  `oldprice` decimal(10,2) DEFAULT '0.00',
  `olddispatchprice` decimal(10,2) DEFAULT '0.00',
  `isvirtual` tinyint(3) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `couponprice` decimal(10,2) DEFAULT '0.00',
  `storeid` int(11) DEFAULT '0',
  `printstate` tinyint(1) DEFAULT '0',
  `printstate2` tinyint(1) DEFAULT '0',
  `address_send` text,
  `refundstate` tinyint(3) DEFAULT '0',
  `closereason` text,
  `remarksaler` text,
  `remarkclose` text,
  `remarksend` text,
  `ismr` int(1) NOT NULL DEFAULT '0',
  `isdiscountprice` decimal(10,2) DEFAULT '0.00',
  `isvirtualsend` tinyint(1) DEFAULT '0',
  `virtualsend_info` text,
  `verifyinfo` text,
  `verifytype` tinyint(1) DEFAULT '0',
  `verifycodes` text,
  `invoicename` varchar(255) DEFAULT '',
  `merchid` int(11) DEFAULT '0',
  `ismerch` tinyint(1) DEFAULT '0',
  `parentid` int(11) NOT NULL DEFAULT '0',
  `isparent` tinyint(1) NOT NULL DEFAULT '0',
  `grprice` decimal(10,2) DEFAULT '0.00',
  `merchshow` tinyint(1) DEFAULT '0',
  `merchdeductenough` decimal(10,2) DEFAULT '0.00',
  `couponmerchid` int(11) DEFAULT '0',
  `isglobonus` tinyint(3) DEFAULT '0',
  `merchapply` tinyint(1) DEFAULT '0',
  `isabonus` tinyint(3) DEFAULT '0',
  `merchisdiscountprice` decimal(10,2) DEFAULT '0.00',
  `apppay` tinyint(3) NOT NULL DEFAULT '0',
  `coupongoodprice` decimal(10,2) DEFAULT '1.00',
  `buyagainprice` decimal(10,2) DEFAULT '0.00',
  `authorid` int(11) DEFAULT '0',
  `isauthor` tinyint(1) DEFAULT '0',
  `ispackage` tinyint(3) DEFAULT '0',
  `packageid` int(11) DEFAULT '0',
  `taskdiscountprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `seckilldiscountprice` decimal(10,2) DEFAULT '0.00',
  `verifyendtime` int(11) NOT NULL DEFAULT '0',
  `willcancelmessage` tinyint(1) DEFAULT '0',
  `sendtype` tinyint(3) NOT NULL DEFAULT '0',
  `lotterydiscountprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `contype` tinyint(1) DEFAULT '0',
  `wxid` int(11) DEFAULT '0',
  `wxcardid` varchar(50) DEFAULT '',
  `wxcode` varchar(50) DEFAULT '',
  `dispatchkey` varchar(30) NOT NULL DEFAULT '',
  `quickid` int(11) NOT NULL DEFAULT '0',
  `istrade` tinyint(3) NOT NULL DEFAULT '0',
  `isnewstore` tinyint(3) NOT NULL DEFAULT '0',
  `liveid` int(11) NOT NULL DEFAULT '0',
  `ordersn_trade` varchar(32) NOT NULL DEFAULT '',
  `tradestatus` tinyint(1) DEFAULT '0',
  `tradepaytype` tinyint(1) NOT NULL DEFAULT '0',
  `tradepaytime` int(11) DEFAULT '0',
  `dowpayment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `betweenprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isshare` int(11) NOT NULL DEFAULT '0',
  `officcode` varchar(50) NOT NULL DEFAULT '',
  `wxapp_prepay_id` varchar(100) NOT NULL DEFAULT '',
  `iswxappcreate` tinyint(1) DEFAULT '0',
  `cashtime` int(11) DEFAULT '0',
  `random_code` varchar(4) NOT NULL DEFAULT '',
  `print_template` text,
  `city_express_state` tinyint(1) NOT NULL DEFAULT '0',
  `isgroups` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否团购',
  `teamid` int(11) NOT NULL DEFAULT '0',
  `is_team` int(2) NOT NULL DEFAULT '0' COMMENT '团购',
  `freight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `success` tinyint(3) NOT NULL DEFAULT '0',
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_status` (`status`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_refundid` (`refundid`),
  KEY `idx_paytime` (`paytime`),
  KEY `idx_finishtime` (`finishtime`),
  KEY `idx_merchid` (`merchid`),
  KEY `idx_ordersn` (`ordersn`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=323 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_comment`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_order_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `optionid` int(11) NOT NULL DEFAULT '0',
  `mid` int(11) DEFAULT '0',
  `nickname` varchar(50) DEFAULT '',
  `headimgurl` varchar(255) DEFAULT '',
  `level` tinyint(3) DEFAULT '0',
  `content` varchar(255) DEFAULT '',
  `images` text,
  `createtime` int(11) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  `append_content` varchar(255) DEFAULT '',
  `append_images` text,
  `reply_content` varchar(255) DEFAULT '',
  `reply_images` text,
  `append_reply_content` varchar(255) DEFAULT '',
  `append_reply_images` text,
  `istop` tinyint(3) DEFAULT '0',
  `checked` tinyint(3) NOT NULL DEFAULT '0',
  `replychecked` tinyint(3) NOT NULL DEFAULT '0',
  `isanonymous` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_mid` (`mid`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_orderid` (`orderid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=96 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_goods`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `price` decimal(10,2) DEFAULT '0.00',
  `total` int(11) DEFAULT '1',
  `optionid` int(10) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `optionname` text,
  `applytime1` int(11) DEFAULT '0',
  `checktime1` int(10) DEFAULT '0',
  `paytime1` int(11) DEFAULT '0',
  `invalidtime1` int(11) DEFAULT '0',
  `deletetime1` int(11) DEFAULT '0',
  `status1` tinyint(3) DEFAULT '0',
  `content1` text,
  `applytime2` int(11) DEFAULT '0',
  `checktime2` int(10) DEFAULT '0',
  `paytime2` int(11) DEFAULT '0',
  `invalidtime2` int(11) DEFAULT '0',
  `deletetime2` int(11) DEFAULT '0',
  `status2` tinyint(3) DEFAULT '0',
  `content2` text,
  `applytime3` int(11) DEFAULT '0',
  `checktime3` int(10) DEFAULT '0',
  `paytime3` int(11) DEFAULT '0',
  `invalidtime3` int(11) DEFAULT '0',
  `deletetime3` int(11) DEFAULT '0',
  `status3` tinyint(3) DEFAULT '0',
  `content3` text,
  `realprice` decimal(10,2) DEFAULT '0.00',
  `goodssn` varchar(255) DEFAULT '',
  `productsn` varchar(255) DEFAULT '',
  `changeprice` decimal(10,2) DEFAULT '0.00',
  `oldprice` decimal(10,2) DEFAULT '0.00',
  `mid` int(11) NOT NULL DEFAULT '0',
  `printstate` int(11) NOT NULL DEFAULT '0',
  `printstate2` int(11) NOT NULL DEFAULT '0',
  `refundid` int(11) NOT NULL DEFAULT '0',
  `rstate` tinyint(3) DEFAULT '0',
  `refundtime` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `parentorderid` int(11) DEFAULT '0',
  `merchsale` tinyint(3) NOT NULL DEFAULT '0',
  `isdiscountprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `canbuyagain` tinyint(1) DEFAULT '0',
  `seckill` tinyint(3) DEFAULT '0',
  `seckill_taskid` int(11) DEFAULT '0',
  `seckill_roomid` int(11) DEFAULT '0',
  `seckill_timeid` int(11) DEFAULT '0',
  `is_make` tinyint(1) DEFAULT '0',
  `sendtype` tinyint(3) NOT NULL DEFAULT '0',
  `expresscom` varchar(30) NOT NULL DEFAULT '',
  `expresssn` varchar(50) NOT NULL DEFAULT '',
  `express` varchar(255) NOT NULL DEFAULT '',
  `sendtime` int(11) NOT NULL DEFAULT '0',
  `finishtime` int(11) NOT NULL DEFAULT '0',
  `remarksend` text NOT NULL,
  `prohibitrefund` tinyint(3) NOT NULL DEFAULT '0',
  `storeid` varchar(255) NOT NULL DEFAULT '',
  `trade_time` int(11) NOT NULL DEFAULT '0',
  `optime` varchar(30) NOT NULL DEFAULT '',
  `tdate_time` int(11) NOT NULL DEFAULT '0',
  `dowpayment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `peopleid` int(11) NOT NULL DEFAULT '0',
  `esheetprintnum` int(11) NOT NULL DEFAULT '0',
  `ordercode` varchar(30) NOT NULL DEFAULT '',
  `iscomment` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_orderid` (`orderid`),
  KEY `idx_goodsid` (`goodsid`),
  KEY `idx_createtime` (`createtime`),
  KEY `idx_applytime1` (`applytime1`),
  KEY `idx_checktime1` (`checktime1`),
  KEY `idx_status1` (`status1`),
  KEY `idx_applytime2` (`applytime2`),
  KEY `idx_checktime2` (`checktime2`),
  KEY `idx_status2` (`status2`),
  KEY `idx_applytime3` (`applytime3`),
  KEY `idx_invalidtime1` (`invalidtime1`),
  KEY `idx_checktime3` (`checktime3`),
  KEY `idx_invalidtime2` (`invalidtime2`),
  KEY `idx_invalidtime3` (`invalidtime3`),
  KEY `idx_status3` (`status3`),
  KEY `idx_paytime1` (`paytime1`),
  KEY `idx_paytime2` (`paytime2`),
  KEY `idx_paytime3` (`paytime3`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=531 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_refund`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_order_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `goodsids` varchar(50) NOT NULL DEFAULT '',
  `orderid` int(11) DEFAULT '0',
  `refundno` varchar(255) DEFAULT '',
  `price` varchar(255) DEFAULT '',
  `reason` varchar(255) DEFAULT '',
  `images` text,
  `content` text,
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `reply` text,
  `refundtype` tinyint(3) DEFAULT '0',
  `orderprice` decimal(10,2) DEFAULT '0.00',
  `applyprice` decimal(10,2) DEFAULT '0.00',
  `imgs` text,
  `rtype` tinyint(3) DEFAULT '0',
  `refundaddress` text,
  `message` text,
  `express` varchar(100) DEFAULT '',
  `expresscom` varchar(100) DEFAULT '',
  `expresssn` varchar(100) DEFAULT '',
  `operatetime` int(11) DEFAULT '0',
  `sendtime` int(11) DEFAULT '0',
  `returntime` int(11) DEFAULT '0',
  `refundtime` int(11) DEFAULT '0',
  `rexpress` varchar(100) DEFAULT '',
  `rexpresscom` varchar(100) DEFAULT '',
  `rexpresssn` varchar(100) DEFAULT '',
  `refundaddressid` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `realprice` decimal(10,2) DEFAULT '0.00',
  `merchid` int(11) DEFAULT '0',
  `lastupdate` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=82 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_refund_log`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_order_refund_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refundid` int(11) DEFAULT '0',
  `operator` varchar(255) DEFAULT '',
  `msgtype` tinyint(3) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `content` text,
  `link` varchar(255) NOT NULL DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `issend` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_refundid` (`refundid`),
  KEY `idx_createtime` (`createtime`),
  FULLTEXT KEY `idx_content` (`content`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=175 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_package`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `freight` decimal(10,2) NOT NULL DEFAULT '0.00',
  `thumb` varchar(255) NOT NULL,
  `starttime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `goodsid` varchar(255) NOT NULL,
  `cash` tinyint(3) NOT NULL DEFAULT '0',
  `share_title` varchar(255) NOT NULL,
  `share_icon` varchar(255) NOT NULL,
  `share_desc` varchar(500) NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `deleted` tinyint(3) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_package_goods`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_package_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `goodsid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `thumb` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `option` varchar(255) NOT NULL,
  `goodssn` varchar(255) NOT NULL,
  `productsn` varchar(255) NOT NULL,
  `hasoption` tinyint(3) NOT NULL DEFAULT '0',
  `marketprice` decimal(10,2) DEFAULT '0.00',
  `packageprice` decimal(10,2) DEFAULT '0.00',
  `commission1` decimal(10,2) DEFAULT '0.00',
  `commission2` decimal(10,2) DEFAULT '0.00',
  `commission3` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_package_goods_option`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_package_goods_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `optionid` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `packageprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `marketprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission1` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission3` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_payment`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(2) NOT NULL DEFAULT '0',
  `appid` varchar(255) DEFAULT '',
  `mch_id` varchar(50) NOT NULL DEFAULT '',
  `apikey` varchar(50) NOT NULL DEFAULT '',
  `sub_appid` varchar(50) DEFAULT '',
  `sub_appsecret` varchar(50) DEFAULT '',
  `sub_mch_id` varchar(50) DEFAULT '',
  `cert_file` text,
  `key_file` text,
  `root_file` text,
  `is_raw` tinyint(1) DEFAULT '0',
  `createtime` int(10) unsigned DEFAULT '0',
  `paytype` tinyint(3) NOT NULL DEFAULT '0',
  `alitype` tinyint(3) NOT NULL DEFAULT '0',
  `alipay_sec` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_type` (`type`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_refund_address`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_refund_address` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `title` varchar(20) DEFAULT '',
  `name` varchar(20) DEFAULT '',
  `tel` varchar(20) DEFAULT '',
  `mobile` varchar(11) DEFAULT '',
  `province` varchar(30) DEFAULT '',
  `city` varchar(30) DEFAULT '',
  `area` varchar(30) DEFAULT '',
  `address` varchar(300) DEFAULT '',
  `isdefault` tinyint(1) DEFAULT '0',
  `zipcode` varchar(255) DEFAULT '',
  `content` text,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_isdefault` (`isdefault`),
  KEY `idx_deleted` (`deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_saler`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_saler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `storeid` int(11) DEFAULT '0',
  `mid` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `salername` varchar(255) DEFAULT '',
  `username` varchar(50) DEFAULT '',
  `pwd` varchar(255) DEFAULT '',
  `salt` varchar(255) DEFAULT '',
  `lastvisit` varchar(255) DEFAULT '',
  `lastip` varchar(255) DEFAULT '',
  `isfounder` tinyint(3) DEFAULT '0',
  `mobile` varchar(255) DEFAULT '',
  `getmessage` tinyint(1) DEFAULT '0',
  `getnotice` tinyint(1) DEFAULT '0',
  `roleid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_storeid` (`storeid`),
  KEY `idx_mid` (`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_sms_set`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_sms_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `juhe` tinyint(3) NOT NULL DEFAULT '0',
  `juhe_key` varchar(255) NOT NULL DEFAULT '',
  `emay` tinyint(3) NOT NULL DEFAULT '0',
  `emay_url` varchar(255) NOT NULL DEFAULT '',
  `emay_appid` varchar(255) NOT NULL DEFAULT '',
  `emay_pw` varchar(255) NOT NULL DEFAULT '',
  `emay_sk` varchar(255) NOT NULL DEFAULT '',
  `emay_phost` varchar(255) NOT NULL DEFAULT '',
  `emay_pport` int(11) NOT NULL DEFAULT '0',
  `emay_puser` varchar(255) NOT NULL DEFAULT '',
  `emay_ppw` varchar(255) NOT NULL DEFAULT '',
  `emay_out` int(11) NOT NULL DEFAULT '0',
  `emay_outresp` int(11) NOT NULL DEFAULT '30',
  `emay_warn` decimal(10,2) NOT NULL DEFAULT '0.00',
  `emay_mobile` varchar(11) NOT NULL DEFAULT '',
  `emay_warn_time` int(11) NOT NULL DEFAULT '0',
  `aliyun_new` tinyint(3) NOT NULL DEFAULT '0',
  `aliyun_new_keyid` varchar(255) NOT NULL DEFAULT '',
  `aliyun_new_keysecret` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regid` int(11) DEFAULT '0',
  `mid` int(11) DEFAULT '0',
  `groupid` int(11) DEFAULT '0',
  `merchno` varchar(255) NOT NULL DEFAULT '',
  `merchname` varchar(255) NOT NULL DEFAULT '',
  `salecate` varchar(255) NOT NULL DEFAULT '',
  `desc` varchar(500) NOT NULL DEFAULT '',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `score` decimal(10,2) NOT NULL DEFAULT '4.50',
  `status` tinyint(3) DEFAULT '0',
  `accounttime` int(11) DEFAULT '0',
  `applytime` int(11) DEFAULT '0',
  `accounttotal` int(11) DEFAULT '0',
  `remark` text,
  `jointime` int(11) DEFAULT '0',
  `accountid` int(11) DEFAULT '0',
  `sets` text,
  `logo` varchar(255) NOT NULL DEFAULT '',
  `banner` text,
  `paymid` int(11) NOT NULL DEFAULT '0',
  `payrate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isrecommand` tinyint(1) DEFAULT '0',
  `cateid` int(11) DEFAULT '0',
  `address` varchar(255) DEFAULT '',
  `tel` varchar(255) DEFAULT '',
  `lat` varchar(255) DEFAULT '',
  `lng` varchar(255) DEFAULT '',
  `collectcount` int(11) NOT NULL DEFAULT '0',
  `uname` varchar(50) NOT NULL DEFAULT '',
  `upass` varchar(255) NOT NULL DEFAULT '',
  `deleted` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_groupid` (`groupid`),
  KEY `idx_regid` (`regid`),
  KEY `idx_cateid` (`cateid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store_account`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `username` varchar(255) DEFAULT '',
  `pwd` varchar(255) DEFAULT '',
  `salt` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `perms` text,
  `isfounder` tinyint(3) DEFAULT '0',
  `lastip` varchar(255) DEFAULT '',
  `lastvisit` varchar(255) DEFAULT '',
  `roleid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_merchid` (`merchid`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store_bill`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store_bill` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `applyno` varchar(255) NOT NULL DEFAULT '',
  `merchid` int(11) NOT NULL DEFAULT '0',
  `orderids` text NOT NULL,
  `realprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `realpricerate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `finalprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payrateprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payrate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00',
  `applytime` int(11) NOT NULL DEFAULT '0',
  `checktime` int(11) NOT NULL DEFAULT '0',
  `paytime` int(11) NOT NULL DEFAULT '0',
  `invalidtime` int(11) NOT NULL DEFAULT '0',
  `refusetime` int(11) NOT NULL DEFAULT '0',
  `remark` text NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `ordernum` int(11) NOT NULL DEFAULT '0',
  `orderprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `passrealprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `passrealpricerate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `passorderids` text NOT NULL,
  `passordernum` int(11) NOT NULL DEFAULT '0',
  `passorderprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `alipay` varchar(50) NOT NULL DEFAULT '',
  `bankname` varchar(50) NOT NULL DEFAULT '',
  `bankcard` varchar(50) NOT NULL DEFAULT '',
  `applyrealname` varchar(50) NOT NULL DEFAULT '',
  `applytype` tinyint(3) NOT NULL DEFAULT '0',
  `handpay` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_merchid` (`merchid`),
  KEY `idx_status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store_category`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catename` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `thumb` varchar(500) DEFAULT '',
  `isrecommand` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store_collect`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `storeid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_storeid` (`storeid`),
  KEY `idx_mid` (`mid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store_goods_category`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store_goods_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchid` int(11) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL DEFAULT '',
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `parentid` int(11) NOT NULL,
  `displayorder` int(10) NOT NULL DEFAULT '0',
  `isrecommand` int(11) NOT NULL DEFAULT '0',
  `description` varchar(32) NOT NULL,
  `ishome` int(11) NOT NULL,
  `advimg` varchar(255) NOT NULL,
  `advurl` varchar(500) NOT NULL,
  `enabled` int(11) NOT NULL DEFAULT '0',
  `level` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=20 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store_group`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupname` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `isdefault` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store_reg`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_store_reg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` varchar(255) DEFAULT '',
  `merchname` varchar(255) DEFAULT '',
  `salecate` varchar(255) DEFAULT '',
  `desc` varchar(500) DEFAULT '',
  `realname` varchar(255) DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `applytime` int(11) DEFAULT '0',
  `reason` text,
  `uname` varchar(50) NOT NULL DEFAULT '',
  `upass` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_sysset`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_sysset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sets` longtext,
  `plugins` longtext,
  `sec` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_system_copyright`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_system_copyright` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bgcolor` varchar(255) DEFAULT '',
  `ismanage` tinyint(3) DEFAULT '0',
  `logo` varchar(255) DEFAULT '',
  `title` varchar(255) DEFAULT '',
  `copyright` text,
  `agreement` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_task_extension_join`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_task_extension_join` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `taskid` int(11) NOT NULL,
  `require_data` text NOT NULL,
  `progress_data` text NOT NULL,
  `reward_data` text NOT NULL,
  `completetime` int(11) NOT NULL DEFAULT '0',
  `pickuptime` int(11) NOT NULL,
  `endtime` int(11) NOT NULL,
  `dotime` int(11) NOT NULL DEFAULT '0',
  `rewarded` text NOT NULL,
  `logo` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_verifygoods`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_verifygoods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT NULL,
  `orderid` int(11) DEFAULT NULL,
  `ordergoodsid` int(11) DEFAULT NULL,
  `storeid` int(11) DEFAULT NULL,
  `starttime` int(11) DEFAULT NULL,
  `limitdays` int(11) DEFAULT NULL,
  `limitnum` int(11) DEFAULT NULL,
  `used` tinyint(1) DEFAULT '0',
  `verifycode` varchar(20) DEFAULT NULL,
  `codeinvalidtime` int(11) DEFAULT NULL,
  `invalid` tinyint(1) DEFAULT '0',
  `getcard` tinyint(1) DEFAULT '0',
  `activecard` tinyint(1) DEFAULT '0',
  `cardcode` varchar(255) DEFAULT '',
  `limittype` tinyint(1) DEFAULT '0',
  `limitdate` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `verifycode` (`verifycode`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_virtual_data`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_virtual_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) NOT NULL DEFAULT '0',
  `typeid` int(11) NOT NULL DEFAULT '0',
  `pvalue` varchar(255) DEFAULT '',
  `fields` text NOT NULL,
  `usetime` int(11) NOT NULL DEFAULT '0',
  `orderid` int(11) DEFAULT '0',
  `ordersn` varchar(255) DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `merchid` int(11) DEFAULT '0',
  `createtime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_typeid` (`typeid`),
  KEY `idx_usetime` (`usetime`),
  KEY `idx_orderid` (`orderid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_virtual_type`
--

CREATE TABLE IF NOT EXISTS `suliss_shop_virtual_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate` int(11) DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `fields` text NOT NULL,
  `usedata` int(11) NOT NULL DEFAULT '0',
  `alldata` int(11) NOT NULL DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `linktext` varchar(50) DEFAULT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `recycled` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_cate` (`cate`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_sms_log`
--

CREATE TABLE IF NOT EXISTS `suliss_sms_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `type` varchar(25) NOT NULL DEFAULT '',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '验证码',
  `createtime` int(32) NOT NULL DEFAULT '0' COMMENT '发送时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=67 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_system_bank`
--

CREATE TABLE IF NOT EXISTS `suliss_system_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bankname` varchar(255) NOT NULL DEFAULT '',
  `content` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_system_feedback`
--

CREATE TABLE IF NOT EXISTS `suliss_system_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mid` int(11) DEFAULT '0',
  `desc` varchar(1000) NOT NULL DEFAULT '',
  `thumbs_url` text NOT NULL,
  `createtime` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_mid` (`mid`),
  KEY `idx_status` (`status`),
  KEY `idx_createtime` (`createtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;
