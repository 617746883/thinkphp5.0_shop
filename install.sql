-- phpMyAdmin SQL Dump
-- version 4.7.9
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2019-08-19 10:14:06
-- 服务器版本： 10.1.39-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mall_sul1ss_onli`
--

-- --------------------------------------------------------

--
-- 表的结构 `suliss_admin`
--

CREATE TABLE `suliss_admin` (
  `id` int(10) UNSIGNED NOT NULL,
  `avatar` varchar(255) NOT NULL DEFAULT '',
  `username` varchar(30) NOT NULL,
  `password` varchar(200) NOT NULL,
  `salt` varchar(10) NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `token` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL,
  `joindate` int(10) UNSIGNED NOT NULL,
  `joinip` varchar(15) NOT NULL,
  `lastvisit` int(10) UNSIGNED NOT NULL,
  `lastip` varchar(15) NOT NULL,
  `remark` varchar(500) NOT NULL,
  `starttime` int(10) UNSIGNED NOT NULL,
  `endtime` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_admin`
--

TRUNCATE TABLE `suliss_admin`;
--
-- 转存表中的数据 `suliss_admin`
--

INSERT INTO `suliss_admin` (`id`, `avatar`, `username`, `password`, `salt`, `type`, `token`, `status`, `joindate`, `joinip`, `lastvisit`, `lastip`, `remark`, `starttime`, `endtime`) VALUES
(1, '/public/attachment/images/20180711/89bea514e1d302d006fac4d8ba69d22f.jpg', 'admin', 'f374baf63f70a5c2c4d172a0a6e37897', 'U66yPU04', 0, 'd1a63a50fad4332a8dea5383c4586361d4bd96ed', 1, 1532331947, '182.245.71.7', 1566177224, '116.52.235.156', 'wqe12', 0, 0),
(3, '/public/attachment/images/20180719/fd38d78f8dfe912b836e4ff320bbebcf.jpg', 'test', '438b6acb8aca7eea3295ffb62cbc238a', 'yoHo3WGS', 0, 'ead29ada1a8c5cfc982f65383fd5b0', 1, 1532612737, '14.204.0.220', 1535596524, '182.245.71.15', '', 0, 0),
(2, '/public/attachment/images/20180530/0847d00bfcc965c68a7ac014715270aa.jpg', 'administrator', '5d1b9a1c47060c0a16c723b7471bba74', 'lVRVVp9g', 0, 'afabe677ee5a06b80afb2616802c92474ef39fed', 1, 1532408484, '218.63.141.87', 1564378770, '39.128.20.82', 'ces121312', 1544492160, 1613612160);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_admin_log`
--

CREATE TABLE `suliss_admin_log` (
  `id` int(11) NOT NULL,
  `adminid` int(11) DEFAULT '0',
  `type` varchar(255) DEFAULT '',
  `op` text,
  `createtime` int(11) DEFAULT '0',
  `ip` varchar(255) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_admin_log`
--

TRUNCATE TABLE `suliss_admin_log`;
--
-- 转存表中的数据 `suliss_admin_log`
--

INSERT INTO `suliss_admin_log` (`id`, `adminid`, `type`, `op`, `createtime`, `ip`) VALUES
(723, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1562654760, '116.52.120.135'),
(724, 1, 'sysset.shop.edit', '修改系统设置-商城设置', 1562655321, '116.52.120.135'),
(725, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1563413121, '116.52.235.98'),
(726, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1563758378, '116.52.34.204'),
(727, 1, 'shop.category.edit', '修改分类 ID: 60', 1563861616, '182.245.71.172'),
(728, 1, 'shop.category.edit', '修改分类 ID: 61', 1563861635, '182.245.71.172'),
(729, 1, 'shop.category.edit', '修改分类 ID: 62', 1563861681, '182.245.71.172'),
(730, 1, 'sysset.shop.edit', '修改系统设置-商城设置', 1563863717, '182.245.71.172'),
(731, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1563895012, '222.172.249.164'),
(732, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1563930593, '116.52.98.162'),
(733, 1, 'livemall.set.edit', '修改星店基本设置', 1564035573, '182.245.71.3'),
(734, 1, 'livemall.set.edit', '修改星店基本设置', 1564035681, '182.245.71.3'),
(735, 1, 'livemall.set.edit', '修改星店基本设置', 1564035735, '182.245.71.3'),
(736, 1, 'livemall.set.edit', '修改星店基本设置', 1564036460, '182.245.71.3'),
(737, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564038434, '182.245.71.3'),
(738, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564038466, '182.245.71.3'),
(739, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564038477, '182.245.71.3'),
(740, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564038487, '182.245.71.3'),
(741, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564038566, '182.245.71.3'),
(742, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564038569, '182.245.71.3'),
(743, 1, 'goods.edit', '编辑商品 ID: 3', 1564039678, '182.245.71.3'),
(744, 1, 'goods.edit', '编辑商品 ID: 3', 1564039688, '182.245.71.3'),
(745, 1, 'goods.edit', '编辑商品 ID: 3', 1564040087, '182.245.71.3'),
(746, 1, 'goods.edit', '编辑商品 ID: 3', 1564040223, '182.245.71.3'),
(747, 1, 'goods.edit', '编辑商品 ID: 3', 1564040382, '182.245.71.3'),
(748, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564044957, '182.245.71.3'),
(749, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1564045043, '182.245.71.3'),
(750, 1, 'livemall.set.edit', '修改星店基本设置', 1564049424, '182.245.71.3'),
(751, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1564378533, '116.52.45.25'),
(752, 1, 'system.admin.delete', '删除操作员 ID: 4 操作员名称: doncheng ', 1564378700, '116.52.45.25'),
(753, 1, 'system.admin.delete', '删除操作员 ID: 5 操作员名称: zhuxietong ', 1564378700, '116.52.45.25'),
(754, 1, 'system.admin.delete', '删除操作员 ID: 6 操作员名称: xiaoxiao ', 1564378700, '116.52.45.25'),
(755, 1, 'system.admin.delete', '删除操作员 ID: 7 操作员名称: zhutong ', 1564378700, '116.52.45.25'),
(756, 1, 'system.admin.edit', '编辑操作员 ID: 2 用户名: administrator ', 1564378726, '116.52.45.25'),
(757, 2, 'admin.login', '管理员 : administrator悄悄地登陆了后台', 1564378770, '39.128.20.82'),
(758, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1564539733, '116.52.120.6'),
(759, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1564849679, '222.221.182.245'),
(760, 1, 'system.database.optimize', '数据库优化', 1564851573, '222.221.182.245'),
(761, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1565145925, '116.52.235.145'),
(762, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1565616722, '222.172.249.17'),
(763, 1, 'sysset.shop.edit', '修改系统设置-商城设置', 1565616734, '222.172.249.17'),
(764, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1565616786, '222.172.249.17'),
(765, 1, 'merch.user.edit', '编辑主播代理商品 ID: ', 1565617791, '222.172.249.17'),
(766, 1, 'system.admin.edit', '编辑个人信息 ID: 1 用户名: admin ', 1565618782, '222.172.249.17'),
(767, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1565619041, '222.172.249.17'),
(768, 1, 'system.admin.edit', '编辑个人信息 ID: 1 用户名:  ', 1565619515, '222.172.249.17'),
(769, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1565619554, '222.172.249.17'),
(770, 1, 'admin.login', '管理员 : admin悄悄地登陆了后台', 1566177224, '116.52.235.156'),
(771, 1, 'goods.edit', '编辑商品 ID: 3', 1566180323, '116.52.235.156');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_attachment_group`
--

CREATE TABLE `suliss_attachment_group` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `uid` int(11) DEFAULT NULL,
  `type` tinyint(1) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_attachment_group`
--

TRUNCATE TABLE `suliss_attachment_group`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_auth_group`
--

CREATE TABLE `suliss_auth_group` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(80) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_auth_group`
--

TRUNCATE TABLE `suliss_auth_group`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_auth_group_access`
--

CREATE TABLE `suliss_auth_group_access` (
  `uid` mediumint(8) UNSIGNED NOT NULL,
  `group_id` mediumint(8) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_auth_group_access`
--

TRUNCATE TABLE `suliss_auth_group_access`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_auth_rule`
--

CREATE TABLE `suliss_auth_rule` (
  `id` mediumint(8) UNSIGNED NOT NULL,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `pid` int(11) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_auth_rule`
--

TRUNCATE TABLE `suliss_auth_rule`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_core_attachment`
--

CREATE TABLE `suliss_core_attachment` (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `createtime` int(10) UNSIGNED NOT NULL,
  `module_upload_dir` varchar(100) NOT NULL,
  `group_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_core_attachment`
--

TRUNCATE TABLE `suliss_core_attachment`;
--
-- 转存表中的数据 `suliss_core_attachment`
--

INSERT INTO `suliss_core_attachment` (`id`, `uid`, `filename`, `attachment`, `type`, `createtime`, `module_upload_dir`, `group_id`) VALUES
(1, 1, '20190723/c32cb2659ec75fb1f6ecaef4ec20cfc8.png', '/public/attachment/images/20190723/c32cb2659ec75fb1f6ecaef4ec20cfc8.png', 1, 1563861612, '', -1),
(2, 1, '20190723/1233bce264e0c2503623b0a28e8b797f.png', '/public/attachment/images/20190723/1233bce264e0c2503623b0a28e8b797f.png', 1, 1563861630, '', -1),
(3, 1, '20190723/d0478e0852281c0b7fa48461e46cf118.png', '/public/attachment/images/20190723/d0478e0852281c0b7fa48461e46cf118.png', 1, 1563861678, '', -1),
(4, 1, '20190723/5955aa9b90526d89169663701456b20d.jpg', '/public/attachment/images/20190723/5955aa9b90526d89169663701456b20d.jpg', 1, 1563863713, '', -1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_livemall_goods_agent`
--

CREATE TABLE `suliss_livemall_goods_agent` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `regid` int(11) DEFAULT '0',
  `slogans` varchar(500) DEFAULT '',
  `hascommission` tinyint(3) NOT NULL DEFAULT '0',
  `commission1_rate` decimal(10,2) DEFAULT '0.00',
  `commission1_pay` decimal(10,2) DEFAULT '0.00',
  `commission2_rate` decimal(10,2) DEFAULT '0.00',
  `commission2_pay` decimal(10,2) DEFAULT '0.00',
  `commission3_rate` decimal(10,2) DEFAULT '0.00',
  `commission3_pay` decimal(10,2) DEFAULT '0.00',
  `commission` text,
  `salers` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_livemall_goods_agent`
--

TRUNCATE TABLE `suliss_livemall_goods_agent`;
--
-- 转存表中的数据 `suliss_livemall_goods_agent`
--

INSERT INTO `suliss_livemall_goods_agent` (`id`, `mid`, `goodsid`, `regid`, `slogans`, `hascommission`, `commission1_rate`, `commission1_pay`, `commission2_rate`, `commission2_pay`, `commission3_rate`, `commission3_pay`, `commission`, `salers`, `createtime`, `status`, `deleted`) VALUES
(1, 21, 2, 1, '我来卖', 1, '8.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, 0, 1564038434, 1, 0),
(2, 21, 1, 2, '我来卖', 1, '15.00', '1.00', '0.00', '0.00', '0.00', '0.00', NULL, 0, 1564045043, 1, 0),
(3, 21, 3, 3, '我来卖', 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, 0, 0, -1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_livemall_reg`
--

CREATE TABLE `suliss_livemall_reg` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `slogans` varchar(500) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `applytime` int(11) DEFAULT '0',
  `reason` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_livemall_reg`
--

TRUNCATE TABLE `suliss_livemall_reg`;
--
-- 转存表中的数据 `suliss_livemall_reg`
--

INSERT INTO `suliss_livemall_reg` (`id`, `mid`, `goodsid`, `slogans`, `status`, `applytime`, `reason`) VALUES
(2, 21, 1, '我来卖', 1, 1564045026, ''),
(3, 21, 3, '我来卖', 0, 1566180325, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member`
--

CREATE TABLE `suliss_member` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL DEFAULT '0',
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
  `regId` varchar(50) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_member`
--

TRUNCATE TABLE `suliss_member`;
--
-- 转存表中的数据 `suliss_member`
--

INSERT INTO `suliss_member` (`id`, `uid`, `groupid`, `level`, `realname`, `mobile`, `content`, `createtime`, `status`, `nickname`, `carrier_mobile`, `carrier_realname`, `credit1`, `credit2`, `birthyear`, `birthmonth`, `birthday`, `gender`, `avatar`, `province`, `city`, `area`, `isblack`, `username`, `salt`, `password`, `mobileverify`, `token`, `expirestime`, `diymaxcredit`, `maxcredit`, `regId`) VALUES
(21, 8, 0, 0, '', '13099907747', NULL, 1544455229, 1, 'SUL1SS', '13099907747', '敖敖', '0.00', '0.00', '2018', '01', '01', 1, 'http://mall.sul1ss.online/public/attachment/app/20190722/b1aa960244dde3645ae6bcab7eb215e0.jpg', '云南省', '昆明市', '西山区', 0, 'a123456', 'vP9DTrfi7T1rpD0r', 'd4ac47dc8bc86a493fa920d8d082dcc3', 1, '29003bb389060d2b51a75bb4fa132016', 1566785125, 0, 0, '1104a89792d25faf618'),
(22, 0, 0, 0, '', '18687510604', NULL, 1544602652, 1, '', '', '', '0.00', '0.00', '', '', '', 0, 'http://aoao.doncheng.cn/public/attachment/app/20190114/74b7742286c5c9e036b607c7366dcaca.png', '云南省', '昆明市', '五华区', 0, 'zhuxietong', 'J2UNn2k72j2d4j2m', 'ea4ded31f6ed1e04911a91ca15c5a07e', 1, '3be01d0fb2a035eb5fd9da1f43cf76ab', 1549414683, 0, 0, '171976fa8ac79f03c33'),
(23, 0, 0, 0, '不包', '15559952836', NULL, 1546573646, 1, '', '18487165037', '不包', '0.00', '0.00', '', '', '', 0, 'http://aoao.doncheng.cn/public/attachment/app/20190129/659ea593c83eb7196c4d22464d17933c.jpg', '安徽省', '合肥市', '瑶海区', 0, 'jimmy', 'MyQlzHPS3G65ZPZG', 'e5fcedbe26a6a56b4d2725c25044f9fc', 1, 'c152b7ad9d0996dc8fc698234be96284', 1549361352, 0, 0, '1a0018970af07939261'),
(24, 0, 0, 0, 'ID', '18687510603', NULL, 1546925054, 1, '', '18687510603', 'ID', '0.00', '0.00', '', '', '', 0, 'http://aoao.doncheng.cn/public/attachment/app/20190114/af0d75d690a172b55f12f5c35d37d038.png', '北京市', '北京市', '东城区', 0, 'tong', 'g8DSgSPGSM3mBZsT', '098b7b84ad60c702fc53d26a0b575cdd', 1, '6300679493d7ed243c71fa108760a577', 1548740024, 0, 0, ''),
(25, 0, 0, 0, 'gg', '13988982118', NULL, 1547629841, 1, '', '15484152485', 'gg', '0.00', '0.00', '', '', '', 0, 'http://aoao.doncheng.cn/public/attachment/app/20190117/33593248514a1f320a91987116e0acb6.jpg', '云南省', '昆明市', '西山区', 0, 'ztind', 'EXxHeXC17N3h1Nc7', 'ef049045902e4285505822d13de61966', 1, '0a04a9cb3915c3f094c541736647c0e0', 1549360831, 0, 0, ''),
(27, 0, 0, 0, '', '13466260815', NULL, 1563897852, 1, '马翔', '', '', '0.00', '0.00', '', '', '', 1, './public/attachment/201904/8/1555267161936.png', '火星', '', '', 0, '', '', '', 0, 'ec6045e388b9f4d4891921a2ecc0e1f5', 1566783816, 0, 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_credits_record`
--

CREATE TABLE `suliss_member_credits_record` (
  `id` int(11) NOT NULL,
  `mid` int(10) UNSIGNED NOT NULL,
  `credittype` varchar(10) NOT NULL,
  `num` decimal(10,2) NOT NULL,
  `operator` int(10) UNSIGNED NOT NULL,
  `module` varchar(30) NOT NULL,
  `clerk_id` int(10) UNSIGNED NOT NULL,
  `store_id` int(10) UNSIGNED NOT NULL,
  `clerk_type` tinyint(3) UNSIGNED NOT NULL,
  `createtime` int(10) UNSIGNED NOT NULL,
  `remark` varchar(200) NOT NULL,
  `real_uniacid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_member_credits_record`
--

TRUNCATE TABLE `suliss_member_credits_record`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_failed_login`
--

CREATE TABLE `suliss_member_failed_login` (
  `id` int(10) UNSIGNED NOT NULL,
  `ip` varchar(15) NOT NULL,
  `username` varchar(32) NOT NULL,
  `count` tinyint(1) UNSIGNED NOT NULL,
  `lastupdate` int(11) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_member_failed_login`
--

TRUNCATE TABLE `suliss_member_failed_login`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_group`
--

CREATE TABLE `suliss_member_group` (
  `id` int(11) NOT NULL,
  `groupname` varchar(255) DEFAULT '',
  `description` varchar(255) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_member_group`
--

TRUNCATE TABLE `suliss_member_group`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_level`
--

CREATE TABLE `suliss_member_level` (
  `id` int(11) NOT NULL,
  `level` int(11) DEFAULT '0',
  `levelname` varchar(50) DEFAULT '',
  `ordermoney` decimal(10,2) DEFAULT '0.00',
  `ordercount` int(10) DEFAULT '0',
  `discount` decimal(10,2) DEFAULT '0.00',
  `enabled` tinyint(3) DEFAULT '0',
  `enabledadd` tinyint(1) DEFAULT '0',
  `buygoods` tinyint(1) NOT NULL DEFAULT '0',
  `goodsids` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_member_level`
--

TRUNCATE TABLE `suliss_member_level`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_member_message`
--

CREATE TABLE `suliss_member_message` (
  `id` int(11) NOT NULL,
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
  `sendcount` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `suliss_prefix_jobs`
--

CREATE TABLE `suliss_prefix_jobs` (
  `id` int(11) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_prefix_jobs`
--

TRUNCATE TABLE `suliss_prefix_jobs`;
--
-- 转存表中的数据 `suliss_prefix_jobs`
--

INSERT INTO `suliss_prefix_jobs` (`id`, `queue`, `payload`, `attempts`, `reserved`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, '拍卖队列23', '{\"job\":\"application\\\\apiv1\\\\job\\\\Hello\",\"data\":\"{\\\"mid\\\":21,\\\"nickname\\\":\\\"SUL1SS\\\",\\\"goodsid\\\":1,\\\"ordersn\\\":\\\"AU20181217144647606552\\\",\\\"price\\\":1150,\\\"addprice\\\":500,\\\"bond\\\":150,\\\"paytype\\\":1,\\\"createtime\\\":1545029207}\"}', 0, 0, NULL, 1545029207, 1545029207),
(2, '拍卖队列24', '{\"job\":\"application\\\\apiv1\\\\job\\\\Hello\",\"data\":\"{\\\"mid\\\":21,\\\"nickname\\\":\\\"SUL1SS\\\",\\\"goodsid\\\":1,\\\"ordersn\\\":\\\"AU20181218091513474196\\\",\\\"price\\\":1150,\\\"addprice\\\":500,\\\"bond\\\":150,\\\"paytype\\\":1,\\\"createtime\\\":1545095713}\"}', 0, 0, NULL, 1545095713, 1545095713),
(3, '拍卖队列25', '{\"job\":\"application\\\\apiv1\\\\job\\\\Hello\",\"data\":\"{\\\"mid\\\":21,\\\"nickname\\\":\\\"SUL1SS\\\",\\\"goodsid\\\":1,\\\"ordersn\\\":\\\"AU20181218151237880746\\\",\\\"price\\\":1150,\\\"addprice\\\":500,\\\"bond\\\":150,\\\"paytype\\\":1,\\\"createtime\\\":1545117157}\"}', 0, 0, NULL, 1545117157, 1545117157);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_adv`
--

CREATE TABLE `suliss_shop_adv` (
  `id` int(11) NOT NULL,
  `advname` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  `shopid` int(11) DEFAULT '0',
  `iswxapp` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_adv`
--

TRUNCATE TABLE `suliss_shop_adv`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_area_config`
--

CREATE TABLE `suliss_shop_area_config` (
  `id` int(11) NOT NULL,
  `new_area` tinyint(3) NOT NULL DEFAULT '0',
  `address_street` tinyint(3) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

--
-- 插入之前先把表清空（truncate） `suliss_shop_area_config`
--

TRUNCATE TABLE `suliss_shop_area_config`;
--
-- 转存表中的数据 `suliss_shop_area_config`
--

INSERT INTO `suliss_shop_area_config` (`id`, `new_area`, `address_street`, `createtime`) VALUES
(2, 1, 1, 1545882026);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_article`
--

CREATE TABLE `suliss_shop_article` (
  `id` int(11) NOT NULL,
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
  `displayorder` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='营销文章';

--
-- 插入之前先把表清空（truncate） `suliss_shop_article`
--

TRUNCATE TABLE `suliss_shop_article`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_article_category`
--

CREATE TABLE `suliss_shop_article_category` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL DEFAULT '',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `isshow` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='营销表单分类';

--
-- 插入之前先把表清空（truncate） `suliss_shop_article_category`
--

TRUNCATE TABLE `suliss_shop_article_category`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_banner`
--

CREATE TABLE `suliss_shop_auction_banner` (
  `id` int(11) NOT NULL,
  `bannername` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_auction_banner`
--

TRUNCATE TABLE `suliss_shop_auction_banner`;
--
-- 转存表中的数据 `suliss_shop_auction_banner`
--

INSERT INTO `suliss_shop_auction_banner` (`id`, `bannername`, `link`, `thumb`, `displayorder`, `enabled`) VALUES
(3, '幻灯片一', '', '/public/attachment/images/20181221/7153e21983f045827077f228335a4b4a.jpg', 50, 1),
(4, '幻灯片二', '', '/public/attachment/images/20190102/efe89f51520e92ec7b552fabf6129729.jpg', 50, 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_bondorder`
--

CREATE TABLE `suliss_shop_auction_bondorder` (
  `id` int(11) NOT NULL,
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `mid` int(11) DEFAULT '0',
  `ordersn` varchar(30) DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `status` tinyint(3) DEFAULT '0',
  `paytype` tinyint(1) DEFAULT '0',
  `transid` varchar(30) DEFAULT '0',
  `createtime` int(10) DEFAULT NULL,
  `deleted` tinyint(3) DEFAULT '0',
  `userdeleted` tinyint(3) DEFAULT '0',
  `finishtime` int(11) DEFAULT '0',
  `paytime` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_auction_bondorder`
--

TRUNCATE TABLE `suliss_shop_auction_bondorder`;
--
-- 转存表中的数据 `suliss_shop_auction_bondorder`
--

INSERT INTO `suliss_shop_auction_bondorder` (`id`, `goodsid`, `mid`, `ordersn`, `price`, `status`, `paytype`, `transid`, `createtime`, `deleted`, `userdeleted`, `finishtime`, `paytime`) VALUES
(57, 1, 21, 'AUB20190103180443850825', '150.00', 1, 1, '0', 1546509883, 0, 0, 1546509883, 1546509883),
(54, 5, 21, 'AUB20190103175940714469', '60.00', 1, 1, '0', 1546509580, 0, 0, 1546509883, 1546509883),
(58, 2, 21, 'AUB20190104133531920242', '100.00', 0, 0, '0', 1546580131, 0, 0, 0, 0),
(59, 4, 21, 'AUB20190104163706774330', '888.00', 1, 0, '0', 1546591026, 0, 0, 0, 1546509883),
(83, 5, 22, 'AUB20190124175252891628', '60.00', 0, 0, '0', 1548323572, 0, 0, 0, 0),
(75, 4, 22, 'AUB20190124160744844280', '888.00', 0, 0, '0', 1548317264, 0, 0, 0, 0),
(81, 7, 22, 'AUB20190124163011653254', '0.00', 0, 0, '0', 1548318611, 0, 0, 0, 0),
(82, 7, 21, 'AUB20190124175137688685', '0.00', 0, 0, '0', 1548323497, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_goods`
--

CREATE TABLE `suliss_shop_auction_goods` (
  `id` int(10) UNSIGNED NOT NULL COMMENT '主键',
  `title` varchar(100) DEFAULT '' COMMENT '商品标题',
  `category` int(10) DEFAULT '0' COMMENT '分类id',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `shprice` int(10) DEFAULT '0' COMMENT '起拍金额',
  `addprice` int(10) DEFAULT '0' COMMENT '默认加价金额',
  `stprice` int(10) DEFAULT '0' COMMENT '成交金额',
  `bond` int(10) DEFAULT '0' COMMENT '保证金',
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `thumb_url` text,
  `content` text NOT NULL COMMENT '商品详情',
  `starttime` int(11) UNSIGNED DEFAULT '0' COMMENT '开始时间',
  `endtime` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '结束时间',
  `createtime` int(11) UNSIGNED DEFAULT '0' COMMENT '创建时间',
  `pos` tinyint(4) UNSIGNED DEFAULT '0' COMMENT '出价次数',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1:已付余款',
  `dealmid` int(11) NOT NULL DEFAULT '0' COMMENT '成交人id',
  `deleted` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_auction_goods`
--

TRUNCATE TABLE `suliss_shop_auction_goods`;
--
-- 转存表中的数据 `suliss_shop_auction_goods`
--

INSERT INTO `suliss_shop_auction_goods` (`id`, `title`, `category`, `displayorder`, `shprice`, `addprice`, `stprice`, `bond`, `thumb`, `thumb_url`, `content`, `starttime`, `endtime`, `createtime`, `pos`, `status`, `dealmid`, `deleted`) VALUES
(1, '茅台老酒(1949年陈酿茅台)', 2, 0, 500, 50, 599, 150, '/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg', 'a:2:{i:0;s:71:\"/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg\";i:1;s:71:\"/public/attachment/images/20181225/43555cfcb33fdacb7971645e9fc70b66.jpg\";}', '<p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/bbecd1426cbd2594a0f94ffd7be806bd.png\" width=\"100%\" alt=\"20181211/bbecd1426cbd2594a0f94ffd7be806bd.png\"/></p>', 1545025200, 1546585500, 1544672372, 2, 1, 21, 0),
(2, '玻璃女鞋', 2, 0, 100, 30, 0, 100, '/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg', 'a:1:{i:0;s:71:\"/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg\";}', '<p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\"><span style=\"font-size: 1em; font-weight: 700;\">女鞋品牌前十一大排名（2013年）</span></p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">1<span style=\"font-size: 1em; font-weight: 700;\">达芙妮Daphne</span>(于1987年在香港创立,一个以鞋业研发、生产、加工及销售为主的<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=7546983&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">多元化经营</a>集团,<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=121772&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">达芙妮国际控股有限公司</a>)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">2<span style=\"font-size: 1em; font-weight: 700;\">ROXE诺晞</span>（<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=155899&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">中国驰名商标</a>,中国名牌,中国<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=10333830&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">真皮标志</a>品牌,行业著名品牌,十大女鞋品牌,<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=133821795&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">广州诺晞鞋业有限公司</a>）</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">3百丽<span style=\"font-size: 1em; font-weight: 700;\">BeLLE</span>(中国驰名商标,中国名牌,中国真皮标志品牌,上市公司,行业著名品牌,十大女鞋品牌,<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=103428835&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">百丽国际</a>控股有限公司)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">4<span style=\"font-size: 1em; font-weight: 700;\">他她TATA</span>(专业致力于各类<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=61356943&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">女性鞋</a>设计生产的专业女鞋制造企业,行业著名品牌,女鞋十大品牌女鞋,百丽国际控股有限公司)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">5<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=64292980&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">红蜻蜓</a>(中国名牌,中国驰名商标,全国<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=10184853&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">民企500强</a>,最具价值品牌500强,行业影响力品牌之一,<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=66327517&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">红蜻蜓集团有限公司</a>)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">6<span style=\"font-size: 1em; font-weight: 700;\">星期六ST&SAT</span>(广东省著名商标,中国真皮标志,女鞋十大品牌女鞋,中国大陆领先的鞋业品牌运营商,<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=10833786&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">佛山星期六鞋业股份有限公司</a>)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">7<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=65052988&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">千百度</a><span style=\"font-size: 1em; font-weight: 700;\">C.banner</span>(中国驰名商标,专业致力于女性产品设计生产的企业,<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=169113651&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">鸿国实业集团有限公司</a>旗下东莞美丽华鞋业有限公司)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">8<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=7636938&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">天美意</a><span style=\"font-size: 1em; font-weight: 700;\">Teenmix</span>(原由香港<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=70818410&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">美丽宝</a>集团所创后被百丽所收购,行业著名女鞋品牌,大型专业女鞋制造企业,百丽国际控股有限公司)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">9<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=59241965&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">戈美其</a><span style=\"font-size: 1em; font-weight: 700;\">GEMEIQI</span>(中国驰名商标,国内知名女鞋品牌,集研发/生产/销售/服务于一体的现代化企业,<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=60712799&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">浙江戈美其鞋业有限公司</a>)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">10<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=74576594&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">接吻猫</a><span style=\"font-size: 1em; font-weight: 700;\">Kisscat</span>(中国女鞋行业中最具代表性的中高端品牌之一,中国十大女鞋品牌,行业著名品牌,广州市天创鞋业有限公司)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">11<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=121874&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">思加图</a><span style=\"font-size: 1em; font-weight: 700;\">Staccato</span>(源自意大利,香港和中国大陆著名的高档女鞋品牌,十大女鞋品牌,专业的女鞋制造商,百丽国际控股有限公司)</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">12<span style=\"font-size: 1em; font-weight: 700;\">人和春天</span>（中国女鞋行业时尚代表中高端品牌，中国女鞋品牌，行业著名时尚品牌人和春天女鞋有限公司）</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">13<span style=\"font-size: 1em; font-weight: 700;\">自由漫步</span><span style=\"font-size: 1em; font-weight: 700;\">free-bummel</span>(源于美国，是美式鞋业的领导品牌，中国名牌,中国驰名商标，是<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=133963270&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">广州市圣奇步鞋业有限公司</a>旗下品牌）</p><p><br/></p>', 1545112740, 1547625120, 1545026619, 0, 1, 0, 0),
(3, '进口德国男士皮鞋', 2, 0, 199, 50, 0, 100, '/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg', 'a:1:{i:0;s:71:\"/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg\";}', '<p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">男正装鞋是一种常见鞋类，一般与西服等正统服装相搭配。男正装鞋也称为绅士鞋，指外观造型庄重、大方，无过多装饰的男鞋（彩图71）。最为典型的传统男正装鞋是内耳式三接头皮鞋（<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=250860&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">牛津鞋</a>）和外耳式三接头皮鞋，拌带耳扣式鞋（也称僧侣鞋Monk）也属于正装鞋类。男正装鞋已不仅仅局限于以上几种式样，一些造型简洁大方的素头鞋、舌式鞋、前开口式鞋和“<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=63024516&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">包子</a>”鞋（莫卡辛Moccsin）等都可以作为正装鞋来穿。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">随着时代发展和<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=476097&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">雅皮士</a>的产生，造型有个性的准正装鞋随之出现，这种鞋在秉承传统正装鞋基础上，在造型式样上追求一种个性及其独特品位。一般楦型不过分怪异，有时紧跟正装<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=7757156&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">鞋楦</a>造型的流行，如铲头式、方头式、斜头式等，但在结构式样、材质选择搭配、配件装饰等方面进行一些独特的有个性的设计。男性穿正装鞋一方面是为特定场合需要，要与西服、礼服相搭配，另一方面男性选择穿正装鞋也是为显示自己的修养和地位。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">因此，男正装鞋设计必须是在高贵、典雅、大方的总体造型风格下进行，并在产品中充分表现出一种精致的工艺美感，即必须有精湛的工艺。男正装鞋设计特点是造型要素变化微妙、幅度较小，注重各造型要素之间的谐调性。高档正装鞋设计特别注意对高档材料的选用，包括<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=68308508&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">鞋面材料</a>、鞋底材料、配件和各种辅料。另外，正装鞋设计受流行时尚的影响较大（主要是楦型和材料使用上）。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">传统式样三节头正装鞋的造型款式基本是固定的、程式化的，一般不对其进行太大的设计变化，像包头长度、中帮长度和鞋身的长度之间都有固定的比例，中帮拖脚也有固定的位置等。当然，以上固定的各部件造型、比例、位置不是绝对不可以改变的，略微的设计变化也是允许的，如鞋型（楦型）稍微加长、变薄等。但传统式样几近完美，人们对它的审美也已形成格式化，为满足这一部分消费者的需求，传统式样三节头正装鞋在造型设计上可以基本保持不变。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">（一）<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=63382788&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">形态设计</a></p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">男正装鞋形态设计包括鞋头部立体形态的造型设计（<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=41790815&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">楦头</a>式造型设计）和帮部件平面<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=72800289&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">廓形</a>造型设计。男正装鞋形态设计一般将传统式样三节头鞋去除在外，前面已说过，这种鞋有其固定的程式化的形式，改变了它的造型特有格式，常常弄巧成拙。总体上说，无论哪种结构式样的正装鞋，它们的形态一方面注意控制变化的幅度，遵从和追寻正装鞋总体造型风格，另一方面注意正装鞋当时在形态方面的流行时尚（主要是楦型）。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">横条舌式男正装鞋形态设计重点在对横条的变化设计和头式造型设计上，横条也可以作为配件来看待。横条的设计变化一般是通过镂空、串花、编花、镶嵌等装饰工艺来完成。如果是高档男正装鞋，横条除要在造型上设计得优雅、新颖，同时在完成这种造型的装饰工艺上也要使人感到精致。正装鞋头式造型设计既要进行一些微妙造型变化，又要注意与流行时尚相结合。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">拌带耳扣式正装鞋（僧侣鞋）形态设计的重点在头式造型设计和鞋钎造型设计上，头式造型设计要大方、新颖但不要过分夸张。拌带造型要注意与鞋的头式造型风格特点相谐调，如果头式造型是优雅修长型的，那么拌带造型也应该是略窄、修长形的。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">（二）色彩设计</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">男正装鞋色彩一般都为黑色和棕色，并且通常用一色配色，也可以使用棕红色、棕黄色、白色、米色、咖啡色等颜色。男正装鞋配色设计受时尚性影响较大。男正装鞋由于其穿用的目的和性质，使得配色设计总体上要求沉稳、含蓄，不能用纯度过高及鲜艳的颜色。如果用二色配色，要用接近的<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=7810538&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">同类色</a>搭配。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">（三）材质设计</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">男正装鞋的材质一般用粒纹细致、手感柔软、滑爽、丰满的<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=304940&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">胎牛皮</a>和小牛皮比较理想。对于高档男正装鞋来说，稀有高档的鞋面材料是必不可少的，如<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=47506663&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">鳄鱼皮</a>、<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=55234481&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">鸵鸟皮</a>、<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=64435334&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">鲨鱼皮</a>等，鞋底材料往往也选用天然革，使鞋具更好的透气性。高档男正装鞋的鞋面材料设计运用，非常注重用高档材料的特殊肌理与其它肌理的较好普通鞋面材料搭配使用，这样设计既可以节省高档鞋材，同时还可以使鞋具有一种高贵感。另外由于肌理不同，还可以形成一种对比的美感。不同材质的组合运用，其设计的关键是不同材料部件造型和位置的安排要有新意。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">（四）配件设计</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">正装鞋上的配件设计依结构式样而定，一般情况下，耳式三节头正装鞋和前开口正装鞋不加装任何配件；舌式正装鞋通常要加装一个小的标牌配件，也可不加；在舌式鞋的跗背处加上横条配件，变成横条舌式鞋，横条舌式也有多种式样变化；拌带耳扣式正装鞋一般要加装既有实用价值又有装饰功能的鞋钎配件，也有用尼龙粘扣代替鞋钎的。配件在正装鞋上往往起到画龙点晴的作用，因此，正装鞋配件设计原则是既要与鞋的整体造型风格相谐调，又要有较高艺术性，真正起到点缀、美化、标识的作用。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">正装鞋上加装的标牌除具有点缀、美化的功能外，它还具有品牌标识和宣传的实用功能。制鞋企业自己品牌标志设计得是否新颖、独特、美观，直接决定了标牌在正装鞋上所发挥的功能效果。正装鞋上标牌在体积上要小巧、纤秀；在造型轮廓上有规矩廓形形态的，即标牌图案在一个完整轮廓造型中，也有自由形态的，即标牌没有一个规矩的廓形，如有的标牌廓形造型用的就是品牌标志的字母体或具象的图案轮廓。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">正装鞋标牌色彩一般有金色、银色和<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=7950178&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">古铜色</a>三种。金色和古铜色适合于各种颜色的正装鞋，银色除与棕色、咖啡色搭配不<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=74348729&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">太适合</a>外，与其它颜色的鞋面材料搭配都适合。正装鞋标牌材质应与鞋材相谐调，普通正装鞋用金属或仿金属效果比较好，高档鞋材及名牌正装鞋可以选用镀金、<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=382645&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">18K金</a>、纯银等高级金属材料，充分衬托出高档正装鞋的名贵感。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">正装鞋标牌工艺加工非常重要，精美的正装鞋上装配一个制作粗糙的标牌会极不谐调，使鞋的整体品质大打折扣。横条舌式正装鞋上的横条配件常常是这种款式的鞋的审美视觉中心，设计师对此处设计应给予特别重视。横条配件除要在形态设计上新颖别致外，还要对工艺手法进行深入考虑，独特或精致的工艺手法可以使横条配件产生一种独特的工艺美感，尤其对皮质横条配件更是如此。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">正装鞋上横条配件颜色一般要求与鞋面材料颜色一致，如果是金属件，金色和银色都可以，其中银色用在黑色鞋面材料上最为合适。横条配件材质选择上以皮革和金属为主，皮革材质显得柔和、高雅、亲切、合谐，金属材质则显得冷峻、严谨、自信、刚毅。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">拌带耳扣式正装鞋配件设计主要集中在鞋钎上，这种鞋的形态设计变化一般在鞋的头式造型（楦头式造型）、拌带和鞋耳部件的造型变化上，鞋钎在这种款式鞋的整体造型的构成中发挥着重要作用，设计师应精心设计。鞋钎形态设计应遵循大方与新颖相结合的原则，只大方，不新颖，会失去装饰审美功能，只新颖（或怪异）不大方又会与正装鞋性质不相吻合。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">在色彩上，棕色或咖啡色鞋面材料适合配金色或古铜色鞋钎，黑色鞋面材料用银色、金色和古铜色都适宜。鞋钎在材质肌理上有光亮型和亚光型两种。一般情况下，配件肌理效果与鞋面材质肌理效果相对比为好，例如在鳄鱼皮、鸵鸟皮等<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=464038&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">漫反射</a>光肌理的鞋面上，搭配一个光亮型的鞋钎，会使鞋面上产生一种材质对比的美感。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">准正装鞋的出现是伴随西方雅皮士一族的出现而产生的。所谓雅皮士是一些受过良好教育、有较高收入和社会地位，又与社会格格不入的一批在20世纪五六十年代成长起来的人。他们崇尚自由，追求个性解放，反传统，但这些人在对待事物的做法上比较温和、含蓄，不像其后出现的朋克一族那样狂放不羁。这样，一些突破常规但又与传统保持一定联系的准正统事物成为他们的追求，准正装鞋由此出现。当然，在喜欢准正装鞋的不一定就是雅皮士，一些有个性的青年人也越来越迷恋这种类型的鞋。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">准正装鞋设计属于“中庸”设计，在总体设计风格上，既要有传统的大方、端庄、典雅、高贵的一面，又要有创新、个性的一面。准正装鞋整体上看是反正统<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=76485169&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">反潮流</a>的，但它并不完全抛弃传统，它是在传统中显示个性的存在，在和谐中制造一种矛盾。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">准正装鞋形态设计主要包括头式造型设计、结构式样设计和帮部件造型设计。对这三个方面的设计，设计师可以在某个方面突破，也可以在几个方面都突破，形态方面的创新设计对准正装鞋整体设计效果影响较大。形态设计的方法一般是在传统式样基础上进行适度的变化或夸张，如传统三节头式样，设计师可将鞋的头式造型变得尖一些、方一些或薄一些、加长一些等。包头部件与中帮部件和鞋耳部件长度比改变一下，鞋耳部件变得更方或更圆一些，也可以加一些装饰工艺、配件等。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">彩图75是一款比较典型的准正装鞋，设计师在鞋款的结构式样上进行了比较大胆的设计，将多种人们熟悉的传统式样进行了独特组合，为打破正统的式样，设计师又将鞋头前部正中破缝和<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=7906822&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">鞋舌</a>两侧破缝，并结合粗犷的辑线工艺和材质对比，使此款鞋造型在新颖、大方中蕴藏着一种不合谐的怪异感，充分显示了准正装鞋的独特品位。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">准正装鞋色彩设计除常用的黑色、棕色和咖啡色，也可使用棕红色、棕黄色、米色、白色等较鲜艳与明亮的颜色。从色彩上将这种鞋塑造得具有个性感，表现出准正装鞋独特的品位。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">准正装鞋材质设计注重不同材质的搭配使用，如漆皮革与鳄鱼皮革、有独特图案的压花革或编织皮革与无图案的全粒面革搭配等都可以获得一种独特的视觉效果。有金属配件的准正装鞋，对配件的设计运用要给予充分重视。配件对准正装鞋的风格塑造可以发挥很大作用。这种作用往往是通过配件奇特的造型、加大的体量和冷峻质感等方面表现出来的。</p><p><br/></p>', 1545026940, 1553845920, 1545027090, 0, 1, 0, 0),
(4, '劳力士经典高贵男士手表', 2, 0, 1000, 200, 1600, 888, '/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg', 'a:1:{i:0;s:71:\"/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg\";}', '<p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">劳力士手表是瑞士产的名贵手表，它的设计、制作、始终保持传统的风格。它的性能包括全自动、单历、双历、防水、防尘等，做工精益求精，特别是表盘、表把及表带、雕刻成的<a class=\"ed_inner_link\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=525485\" target=\"_blank\" ss_c=\"ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">王冠</a>更是其高品质的标志。因为名牌手表在制造方面使用先进设备、高质材料，达到了加工精细、高<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=7692022&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">光洁度</a>。真劳力士手表，不论做工、文字都十分精细，有完美的手感，这是鉴别真伪的一个重要方面。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">从外表看劳力士手表壳精细、表带、王冠、英文字清楚、完整，而仿造表外壳粗糙，文字稍模糊。尤其表底、盖齿，十分精细而清楚、洁亮而有立体感，而仿造品粗糙而没有立体感，一般都比较浅。重量方面，真的手感沉实一些，假的轻得多（注：但有些仿造的里面也加一个铜圈）。就<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=382645&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">18K金</a>质地<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=120269&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">劳力士</a>的表壳、表带而言，真品一般由新到旧黄金品质、颜色不变，仿造品有<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=4177473&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">14K</a>金或者更低一些K金或镀上18K金的，但时间一长，就会变回原色。<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=101570586&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">满天星</a>的劳力士、镶在表上的钻石都是真的，而仿造品的钻石则是假的。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">市场上也有出现真表壳、表带、里面放假机芯的<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=8269309&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">劳力士表</a>，所以最好用专用工具开表。真的劳力士机芯，一撬去自动舵，机芯夹板上刻有ROLEX字样，还刻有机芯号：1570、2135、3135、3035……等，假的则没有。真的机芯机件精细、纹线清楚，假的机芯比较粗糙发暗。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">另外，拆下表带，表壳的侧表耳中间有表厂的型批号，下侧表耳中间有表号，仿造的基本没有（有些仿品也有，但不够齐整和清楚）；劳力士手表都有质量<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=457769&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">保证书</a>，右上角有两行以上阿拉伯字号码针孔刺字，齐整而清楚，而假的只一行针孔刺字，很不统一，不太齐整，但不能以此来辨别，因为市场上也有假的保证书。</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">Cal 4130是Cal 4030的<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=55457161&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">替代者</a>，是Rolex自厂开发的第一款计时自动上链机芯，同样采用了柱状轮进行计时。但是其内部构造更好的解决了计时针启动和停止的时候有抖动情况发生的问题。同时解决让计时芯在计时情况下依旧可以保持腕表本身走时精准的问题。Rolex Daytona的Cal 4130依旧是“超级<a class=\"ed_inner_link\" target=\"_blank\" href=\"https://baike.sogou.com/lemma/ShowInnerLink.htm?lemmaId=470781&ss_c=ssc.citiao.link\" style=\"color: rgb(51, 102, 204); text-decoration-line: none;\">天文台</a>官方认证”，其旋入式计时按钮保证了计时按钮这个部位能够拥有100米的防水</p><p style=\"margin-top: 0px; margin-bottom: 15px; padding: 0px; font-family: arial, \" pingfang=\"\" text-indent:=\"\" line-height:=\"\" color:=\"\" font-size:=\"\" white-space:=\"\" background-color:=\"\">Rolex Daytona是仅次于Day Date的高价表，甚至其钢款都要超过6W RMB，哪怕是在HK或者JP购买。同样，我一直觉得Rolex Daytona是最好的计时表，不是因为它的牌子有多好，也不是因为其功能有多强。归根结底的一条——它秉承了Rolex最牛逼的传统——稳，准，狠——男人的表，就应该这样。</p><p><br/></p>', 1545027120, 1549093980, 1545027482, 3, 1, 0, 0),
(5, '男士二手背包', 2, 0, 88, 10, 0, 60, '/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg', 'a:1:{i:0;s:71:\"/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg\";}', '<div class=\"para-title level-3\" label-module=\"para-title\" style=\"clear: both; zoom: 1; margin: 20px 0px 12px; line-height: 20px; font-size: 18px; font-family: \" microsoft=\"\" color:=\"\" white-space:=\"\" background-color:=\"\"><h3 class=\"title-text\" style=\"margin: 0px; padding: 0px; font-size: 18px; font-weight: 400;\">根据人数</h3></div><div class=\"para\" label-module=\"para\" style=\"font-size: 14px; overflow-wrap: break-word; color: rgb(51, 51, 51); margin-bottom: 15px; text-indent: 2em; line-height: 24px; zoom: 1; font-family: arial, 宋体, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);\">独自<a target=\"_blank\" href=\"https://baike.baidu.com/item/%E9%83%8A%E6%B8%B8\" style=\"color: rgb(19, 110, 194); text-decoration-line: none;\">郊游</a>时，可选择25～35公升左右的背包。假日带家人孩子出游时，从照顾家人的角度考虑，需选择40公升左右的背包，外挂系统要多，方便帮助家人携带雨伞、相机、食物等物品。</div><div class=\"anchor-list\" style=\"position: relative; color: rgb(51, 51, 51); font-family: arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);\"><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"6_2\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"sub13349277_6_2\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"根据性别\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"6-2\"></a></div><div class=\"para-title level-3\" label-module=\"para-title\" style=\"clear: both; zoom: 1; margin: 20px 0px 12px; line-height: 20px; font-size: 18px; font-family: \" microsoft=\"\" color:=\"\" white-space:=\"\" background-color:=\"\"><h3 class=\"title-text\" style=\"margin: 0px; padding: 0px; font-size: 18px; font-weight: 400;\">根据性别</h3></div><div class=\"para\" label-module=\"para\" style=\"font-size: 14px; overflow-wrap: break-word; color: rgb(51, 51, 51); margin-bottom: 15px; text-indent: 2em; line-height: 24px; zoom: 1; font-family: arial, 宋体, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);\">由于男女的体型和承重力不同，户外背包的选择也不太一样。一般一两天的短途郊游，男女通用30公升左右的背包就可以了。而超过2至3天的长距离旅行或<a target=\"_blank\" href=\"https://baike.baidu.com/item/%E9%9C%B2%E8%90%A5\" style=\"color: rgb(19, 110, 194); text-decoration-line: none;\">露营</a>，要选择45至70升甚至更大的背包时，一般而男性选择55升左右背包，女性选择45升背包。</div><div class=\"anchor-list\" style=\"position: relative; color: rgb(51, 51, 51); font-family: arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);\"><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"6_3\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"sub13349277_6_3\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"根据行程\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"6-3\"></a></div><div class=\"para-title level-3\" label-module=\"para-title\" style=\"clear: both; zoom: 1; margin: 20px 0px 12px; line-height: 20px; font-size: 18px; font-family: \" microsoft=\"\" color:=\"\" white-space:=\"\" background-color:=\"\"><h3 class=\"title-text\" style=\"margin: 0px; padding: 0px; font-size: 18px; font-weight: 400;\">根据行程</h3></div><div class=\"para\" label-module=\"para\" style=\"font-size: 14px; overflow-wrap: break-word; color: rgb(51, 51, 51); margin-bottom: 15px; text-indent: 2em; line-height: 24px; zoom: 1; font-family: arial, 宋体, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);\">单日往返郊游、骑行、登山活动，选择30公升以下背包。二到三日露营可选择30—40公升的多功能背包，四日以上徒步旅行，要放置帐篷、睡袋、防潮垫等户外装备，可选择45以上的公升的背包。另外，一般的野外活动和<a target=\"_blank\" href=\"https://baike.baidu.com/item/%E6%94%80%E7%99%BB\" style=\"color: rgb(19, 110, 194); text-decoration-line: none;\">攀登</a>高山时所用的背包不同，登山所用的背包没有许多零件，喜欢登山的朋友需注意。</div><div class=\"anchor-list\" style=\"position: relative; color: rgb(51, 51, 51); font-family: arial, 宋体, sans-serif; font-size: 12px; white-space: normal; background-color: rgb(255, 255, 255);\"><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"6_4\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"sub13349277_6_4\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"根据身长\"></a><a style=\"color: rgb(19, 110, 194); position: absolute; top: -50px;\" name=\"6-4\"></a></div><div class=\"para-title level-3\" label-module=\"para-title\" style=\"clear: both; zoom: 1; margin: 20px 0px 12px; line-height: 20px; font-size: 18px; font-family: \" microsoft=\"\" color:=\"\" white-space:=\"\" background-color:=\"\"><h3 class=\"title-text\" style=\"margin: 0px; padding: 0px; font-size: 18px; font-weight: 400;\">根据身长</h3></div><div class=\"para\" label-module=\"para\" style=\"font-size: 14px; overflow-wrap: break-word; color: rgb(51, 51, 51); margin-bottom: 15px; text-indent: 2em; line-height: 24px; zoom: 1; font-family: arial, 宋体, sans-serif; white-space: normal; background-color: rgb(255, 255, 255);\">挑选背包之前，首先需要量一下自己的后上身长，即颈椎突起处至最后一节腰椎的距离。如果躯干长度小于45厘米，应该买一个小号的包。如果躯干长度在45—52厘米之间，应该选一个中号的包。如果你的躯干长度在52厘米以上，应该挑一个大号的包。</div><p><br/></p>', 1545029400, 1550217180, 1545029427, 0, 1, 0, 0),
(6, '拍卖1', 2, 0, 0, 0, 0, 0, '/public/attachment/images/20181224/2c3d6d1a750a61d07f20600732eda930.jpg', 'a:1:{i:0;s:71:\"/public/attachment/images/20181224/2c3d6d1a750a61d07f20600732eda930.jpg\";}', '<p>cewrwqer</p>', 1548317580, 1548317580, 1548317660, 0, 1, 0, 0),
(7, '001', 2, 0, 0, 0, 0, 0, '/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg', 'a:1:{i:0;s:71:\"/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg\";}', '<p>aewrwqr</p>', 1548317760, 1548490560, 1548317815, 0, 1, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_goods_category`
--

CREATE TABLE `suliss_shop_auction_goods_category` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) UNSIGNED DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_auction_goods_category`
--

TRUNCATE TABLE `suliss_shop_auction_goods_category`;
--
-- 转存表中的数据 `suliss_shop_auction_goods_category`
--

INSERT INTO `suliss_shop_auction_goods_category` (`id`, `name`, `thumb`, `displayorder`, `enabled`, `advimg`, `advurl`, `isrecommand`) VALUES
(2, '分类1', '/public/attachment/images/20181221/9f347732463586cd8208695dd225779b.png', 50, 1, '', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_order`
--

CREATE TABLE `suliss_shop_auction_order` (
  `id` int(10) UNSIGNED NOT NULL,
  `ordersn` varchar(50) NOT NULL COMMENT '订单编号',
  `mid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `goodsid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品编号',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '交易价格',
  `bondprice` decimal(10,2) DEFAULT '0.00' COMMENT '保证金',
  `stprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(3) DEFAULT '0',
  `paytype` tinyint(1) DEFAULT '0',
  `paystatus` tinyint(3) NOT NULL DEFAULT '0',
  `paytime` int(11) NOT NULL DEFAULT '0',
  `transid` varchar(30) DEFAULT '0',
  `addressid` int(11) DEFAULT '0',
  `address` text,
  `expresscom` varchar(30) NOT NULL DEFAULT '',
  `expresssn` varchar(50) NOT NULL DEFAULT '',
  `express` varchar(255) NOT NULL DEFAULT '',
  `sendtime` int(11) NOT NULL DEFAULT '0',
  `fetchtime` int(11) NOT NULL DEFAULT '0',
  `finishtime` int(11) NOT NULL DEFAULT '0',
  `mobile` varchar(11) NOT NULL DEFAULT '',
  `realname` varchar(50) NOT NULL DEFAULT '',
  `remark` varchar(500) NOT NULL DEFAULT '',
  `createtime` int(10) UNSIGNED NOT NULL COMMENT '购买时间',
  `deleted` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_auction_order`
--

TRUNCATE TABLE `suliss_shop_auction_order`;
--
-- 转存表中的数据 `suliss_shop_auction_order`
--

INSERT INTO `suliss_shop_auction_order` (`id`, `ordersn`, `mid`, `goodsid`, `price`, `bondprice`, `stprice`, `status`, `paytype`, `paystatus`, `paytime`, `transid`, `addressid`, `address`, `expresscom`, `expresssn`, `express`, `sendtime`, `fetchtime`, `finishtime`, `mobile`, `realname`, `remark`, `createtime`, `deleted`) VALUES
(25, 'AU20181218151237880746', 21, 1, '1150.00', '150.00', '500.00', 0, 1, 0, 0, '0', 0, NULL, '', '', '', 0, 0, 0, '', '', '', 1545117157, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_auction_record`
--

CREATE TABLE `suliss_shop_auction_record` (
  `id` int(10) UNSIGNED NOT NULL,
  `recordsn` varchar(50) NOT NULL COMMENT '订单编号',
  `mid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '用户ID',
  `goodsid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '商品id',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '交易价格',
  `status` tinyint(3) DEFAULT '0',
  `createtime` int(10) UNSIGNED NOT NULL COMMENT '购买时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_auction_record`
--

TRUNCATE TABLE `suliss_shop_auction_record`;
--
-- 转存表中的数据 `suliss_shop_auction_record`
--

INSERT INTO `suliss_shop_auction_record` (`id`, `recordsn`, `mid`, `goodsid`, `price`, `status`, `createtime`) VALUES
(1, 'AU20190104104039681381', 21, 1, '545.00', 1, 1546569639),
(2, 'AU20190104104506892644', 21, 1, '549.00', 1, 1546569906),
(3, 'AU20190104133004271469', 21, 1, '599.00', 1, 1546579804),
(4, 'AU20190104163800053367', 21, 4, '1200.00', 1, 1546591080),
(5, 'AU20190104163858948721', 21, 4, '1400.00', 1, 1546591138),
(6, 'AU20190104164734278444', 21, 4, '1600.00', 1, 1546591654);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_banner`
--

CREATE TABLE `suliss_shop_banner` (
  `id` int(11) NOT NULL,
  `bannername` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_banner`
--

TRUNCATE TABLE `suliss_shop_banner`;
--
-- 转存表中的数据 `suliss_shop_banner`
--

INSERT INTO `suliss_shop_banner` (`id`, `bannername`, `link`, `thumb`, `displayorder`, `enabled`) VALUES
(9, '6', '', '/public/attachment/images/20181211/59fb62ee2215b40e8a198842faa652ea.jpg', 6, 1),
(10, '88', '', '/public/attachment/images/20181211/473d7ff3d2f6e5cefc09bf07ea54bc48.jpg', 88, 1),
(11, '89', '', '/public/attachment/images/20181211/b6ab7194916f642e6d51b7286fc5d50e.jpg', 89, 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_bargain_actor`
--

CREATE TABLE `suliss_shop_bargain_actor` (
  `id` int(11) NOT NULL,
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
  `order` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_bargain_actor`
--

TRUNCATE TABLE `suliss_shop_bargain_actor`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_city_express`
--

CREATE TABLE `suliss_shop_city_express` (
  `id` int(11) NOT NULL,
  `merchid` int(11) DEFAULT '0',
  `start_fee` decimal(10,2) DEFAULT '0.00',
  `start_km` int(11) DEFAULT '0',
  `pre_km` int(11) DEFAULT '0',
  `pre_km_fee` decimal(10,2) DEFAULT '0.00',
  `fixed_km` int(11) DEFAULT '0',
  `fixed_fee` decimal(10,2) DEFAULT '0.00',
  `receive_goods` int(11) DEFAULT NULL,
  `lng` varchar(255) DEFAULT '',
  `lat` varchar(255) DEFAULT '',
  `range` int(11) DEFAULT '0',
  `zoom` int(11) NOT NULL DEFAULT '13',
  `express_type` int(11) NOT NULL DEFAULT '0',
  `config` varchar(255) NOT NULL DEFAULT '',
  `tel1` varchar(255) DEFAULT '',
  `tel2` varchar(255) DEFAULT '',
  `is_sum` tinyint(1) DEFAULT '0',
  `is_dispatch` tinyint(1) DEFAULT '1',
  `enabled` tinyint(1) DEFAULT '0',
  `geo_key` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_city_express`
--

TRUNCATE TABLE `suliss_shop_city_express`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_core_paylog`
--

CREATE TABLE `suliss_shop_core_paylog` (
  `plid` bigint(11) UNSIGNED NOT NULL,
  `type` varchar(20) NOT NULL DEFAULT '',
  `mid` int(10) NOT NULL DEFAULT '0',
  `uniontid` varchar(64) NOT NULL DEFAULT '',
  `tid` varchar(128) NOT NULL,
  `credit` int(10) NOT NULL DEFAULT '0',
  `creditmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `module` varchar(50) NOT NULL,
  `tag` varchar(2000) NOT NULL DEFAULT '',
  `is_usecard` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `card_type` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `card_id` varchar(50) NOT NULL DEFAULT '',
  `card_fee` decimal(10,2) UNSIGNED NOT NULL DEFAULT '0.00',
  `encrypt_code` varchar(100) NOT NULL DEFAULT '',
  `createtime` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_core_paylog`
--

TRUNCATE TABLE `suliss_shop_core_paylog`;
--
-- 转存表中的数据 `suliss_shop_core_paylog`
--

INSERT INTO `suliss_shop_core_paylog` (`plid`, `type`, `mid`, `uniontid`, `tid`, `credit`, `creditmoney`, `fee`, `status`, `module`, `tag`, `is_usecard`, `card_type`, `card_id`, `card_fee`, `encrypt_code`, `createtime`) VALUES
(832, '', 21, '', 'AUB20190103175227636468', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(831, '', 21, '', 'AUB20190103174647282968', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(829, '', 21, '', 'AUB20190103174602623232', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(830, '', 21, '', 'AUB20190103174615673464', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(828, '', 21, '', 'AUB20190103174556147467', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(826, '', 21, '', 'AUB20190103174512469646', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(827, '', 21, '', 'AUB20190103174546520686', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(825, '', 21, '', 'AUB20190103174416449686', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(824, '', 21, '', 'AUB20190103174318917636', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(823, '', 21, '', 'AUB20190103174256424696', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(822, '', 21, '', 'AUB20190103174228248616', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(821, '', 21, '', 'AUB20190103174210444208', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(820, '', 21, '', 'AUB20190103174145736244', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(819, '', 21, '', 'AUB20190103174141488381', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(818, '', 21, '', 'AUB20190103174122321462', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(817, '', 21, '', 'AUB20190103174035604886', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(816, '', 21, '', 'AUB20190103174035516866', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(815, '', 21, '', 'AUB20190103174034284241', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(814, '', 21, '', 'AUB20190103174033424734', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(813, '', 21, '', 'AUB20190103173755486366', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(812, '', 21, '', 'AUB20190103173711826278', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(811, '', 21, '', 'AUB20190103173509183446', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(810, '', 21, '', 'AUB20190103173342184292', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(809, '', 0, '', 'PT20190102173308875816', 0, '0.00', '0.04', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(808, 'wechat', 22, '', 'PT20190102110217822692', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000251201901027731163502\";}', 0, 0, '', '0.00', '', 1546398144),
(807, 'wechat', 22, '', 'PT20190102101521484667', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000254201901024526789787\";}', 0, 0, '', '0.00', '', 1546395328),
(806, 'wechat', 21, '', 'PT20190102092605223668', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000258201901028179661069\";}', 0, 0, '', '0.00', '', 1546392375),
(805, 'wechat', 22, '', 'PT20181229172045486610', 0, '0.00', '0.05', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000248201812298929482046\";}', 0, 0, '', '0.00', '', 1546075251),
(798, 'wechat', 22, '', 'PT20181229104410654748', 0, '0.00', '0.03', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000248201812299047555680\";}', 0, 0, '', '0.00', '', 1546051456),
(799, 'wechat', 22, '', 'PT20181229154545668252', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000251201812290557333236\";}', 0, 0, '', '0.00', '', 1546069551),
(800, 'wechat', 22, '', 'PT20181229163758490867', 0, '0.00', '0.04', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000253201812291260102561\";}', 0, 0, '', '0.00', '', 1546072699),
(801, '', 22, '', 'PT20181229163828210423', 0, '0.00', '0.03', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(802, 'wechat', 22, '', 'PT20181229163839235984', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000248201812293466518861\";}', 0, 0, '', '0.00', '', 1546072725),
(803, 'wechat', 22, '', 'PT20181229164814642183', 0, '0.00', '0.06', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201812298655854167\";}', 0, 0, '', '0.00', '', 1546073300),
(804, 'wechat', 22, '', 'PT20181229172003806149', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000245201812293630934750\";}', 0, 0, '', '0.00', '', 1546075209),
(796, '', 21, '', 'PT20181229092207802236', 0, '0.00', '0.02', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(797, 'wechat', 22, '', 'PT20181229104356606236', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201812299339586870\";}', 0, 0, '', '0.00', '', 1546051443),
(833, '', 21, '', 'AUB20190103175231284246', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(834, '', 21, '', 'AUB20190103175233438130', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(835, '', 21, '', 'AUB20190103175241664720', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(836, '', 21, '', 'AUB20190103175329600817', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(837, '', 21, '', 'AUB20190103175428251626', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(838, '', 21, '', 'AUB20190103175433942214', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(839, '', 21, '', 'AUB20190103175516962383', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(840, '', 21, '', 'AUB20190103175531418824', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(841, '', 21, '', 'AUB20190103175534221322', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(842, '', 21, '', 'AUB20190103175605578740', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(843, '', 21, '', 'AUB20190103175620792748', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(844, '', 21, '', 'AUB20190103175756503855', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(845, '', 21, '', 'AUB20190103175816202248', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(846, '', 21, '', 'AUB20190103175841086642', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(847, '', 21, '', 'AUB20190103175940714469', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(848, '', 21, '', 'AUB20190103180005272125', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(849, '', 21, '', 'AUB20190103180441982282', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(850, '', 21, '', 'AUB20190103180443850825', 0, '0.00', '150.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(851, '', 21, '', 'AUB20190104133531920242', 0, '0.00', '100.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1100, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(853, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(854, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(855, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(856, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(857, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(858, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(859, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(860, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(861, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(862, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(863, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(864, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(865, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(866, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(867, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(868, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(869, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(870, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(871, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(872, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(873, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(874, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(875, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(876, '', 21, '', 'AUB20190104163706774330', 0, '0.00', '888.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(877, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(878, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(879, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(880, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(881, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(882, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(883, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(884, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(885, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(886, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(887, 'wechat', 22, '', 'PT20190104230155945124', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901044039770422\";}', 0, 0, '', '0.00', '', 1546614121),
(888, 'wechat', 22, '', 'PT20190105095337652528', 0, '0.00', '0.05', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901056988496977\";}', 0, 0, '', '0.00', '', 1546653224),
(889, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(890, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(891, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(892, '', 0, '', 'PT20190105103720805269', 0, '0.00', '0.04', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(893, 'wechat', 21, '', 'PT20190105104918226304', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901057881227794\";}', 0, 0, '', '0.00', '', 1546656611),
(894, '', 21, '', 'AU20181218151237880746', 0, '0.00', '1150.00', 0, 'auction', '', 0, 0, '', '0.00', '', 0),
(895, '', 0, '', 'PT20190105134938888217', 0, '0.00', '0.03', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(896, '', 0, '', 'PT20190105135103562555', 0, '0.00', '0.05', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(897, 'wechat', 21, '', 'PT20190105135317486274', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000252201901055599541178\";}', 0, 0, '', '0.00', '', 1546667605),
(898, 'wechat', 21, '', 'PT20190105135339647846', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901058619354850\";}', 0, 0, '', '0.00', '', 1546667642),
(899, 'wechat', 22, '', 'PT20190105150354262980', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901051565227975\";}', 0, 0, '', '0.00', '', 1546671840),
(900, '', 22, '', 'PT20190105150413224223', 0, '0.00', '0.03', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(901, 'wechat', 22, '', 'PT20190105164626852821', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000241201901055960304189\";}', 0, 0, '', '0.00', '', 1546677992),
(902, 'wechat', 22, '', 'PT20190105165815426236', 0, '0.00', '0.04', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901051541421209\";}', 0, 0, '', '0.00', '', 1546678700),
(903, 'wechat', 22, '', 'PT20190108001457105221', 0, '0.00', '0.03', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901087446679853\";}', 0, 0, '', '0.00', '', 1546877704),
(904, '', 22, '', 'PT20190108110714845888', 0, '0.00', '0.05', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(905, '', 22, '', 'PT20190108111348684788', 0, '0.00', '0.02', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(906, '', 22, '', 'PT20190108112719044621', 0, '0.00', '0.04', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(907, 'wechat', 22, '', 'PT20190108112723064424', 0, '0.00', '0.04', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901083279963051\";}', 0, 0, '', '0.00', '', 1546918080),
(908, 'wechat', 22, '', 'PT20190108112935826472', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000254201901084443217181\";}', 0, 0, '', '0.00', '', 1546918183),
(909, 'wechat', 22, '', 'PT20190108113116877242', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901086050772426\";}', 0, 0, '', '0.00', '', 1546918283),
(910, 'wechat', 22, '', 'PT20190108113211694446', 0, '0.00', '0.03', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901088560362420\";}', 0, 0, '', '0.00', '', 1546918336),
(911, 'wechat', 22, '', 'PT20190108120334249709', 0, '0.00', '0.03', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000239201901089899604383\";}', 0, 0, '', '0.00', '', 1546920225),
(912, 'wechat', 24, '', 'PT20190108132454644496', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201901080898477324\";}', 0, 0, '', '0.00', '', 1546925116),
(913, 'wechat', 24, '', 'PT20190108132618198216', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901082614386636\";}', 0, 0, '', '0.00', '', 1546925184),
(914, 'wechat', 24, '', 'PT20190108132644925665', 0, '0.00', '0.03', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000251201901087379571341\";}', 0, 0, '', '0.00', '', 1546925215),
(915, 'wechat', 22, '', 'PT20190108132930354290', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000248201901086596587044\";}', 0, 0, '', '0.00', '', 1546925375),
(916, '', 22, '', 'PT20190108141352453163', 0, '0.00', '0.05', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(917, 'wechat', 22, '', 'PT20190108141400116788', 0, '0.00', '0.05', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201901087744603964\";}', 0, 0, '', '0.00', '', 1546928048),
(918, 'wechat', 22, '', 'PT20190108170041594963', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901085328232340\";}', 0, 0, '', '0.00', '', 1546938049),
(919, '', 22, '', 'PT20190108180406249632', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(920, 'wechat', 22, '', 'PT20190108180425842786', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901083562067845\";}', 0, 0, '', '0.00', '', 1546941870),
(921, 'wechat', 22, '', 'PT20190109091820146464', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901096212993774\";}', 0, 0, '', '0.00', '', 1546996706),
(922, 'wechat', 22, '', 'PT20190109102247692672', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201901090481739419\";}', 0, 0, '', '0.00', '', 1547000573),
(923, 'wechat', 22, '', 'PT20190109114015696362', 0, '0.00', '0.03', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000251201901091476166404\";}', 0, 0, '', '0.00', '', 1547005245),
(924, 'wechat', 22, '', 'PT20190109132846848614', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901090480045082\";}', 0, 0, '', '0.00', '', 1547011738),
(925, 'wechat', 22, '', 'PT20190109135626787693', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901099356820019\";}', 0, 0, '', '0.00', '', 1547013399),
(926, 'wechat', 22, '', 'PT20190109142942412738', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000250201901097756240585\";}', 0, 0, '', '0.00', '', 1547015388),
(927, 'wechat', 22, '', 'PT20190109152412684228', 0, '0.00', '0.05', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901095703051097\";}', 0, 0, '', '0.00', '', 1547018661),
(928, 'wechat', 22, '', 'PT20190109161002235486', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000248201901095720371545\";}', 0, 0, '', '0.00', '', 1547021409),
(929, 'wechat', 24, '', 'PT20190109161129848285', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901099852005796\";}', 0, 0, '', '0.00', '', 1547021495),
(930, 'wechat', 24, '', 'PT20190109161445275678', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901095378868493\";}', 0, 0, '', '0.00', '', 1547021706),
(931, 'wechat', 24, '', 'PT20190109161533489932', 0, '0.00', '0.03', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000256201901096779300350\";}', 0, 0, '', '0.00', '', 1547021738),
(935, '', 23, '', 'SH20190109163523258024', 0, '0.00', '109.00', 0, 'shop', '', 0, 0, '', '0.00', '', 0),
(936, 'wechat', 24, '', 'PT20190109165907010953', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901091393997818\";}', 0, 0, '', '0.00', '', 1547024352),
(937, '', 22, '', 'PT20190110123659460580', 0, '0.00', '0.04', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(938, '', 22, '', 'PT20190110123733757502', 0, '0.00', '0.04', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(939, 'wechat', 24, '', 'PT20190110165105984228', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000251201901107433580662\";}', 0, 0, '', '0.00', '', 1547110332),
(940, 'wechat', 22, '', 'PT20190111092450443267', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901116080067248\";}', 0, 0, '', '0.00', '', 1547169902),
(941, 'wechat', 24, '', 'PT20190111092627661442', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901111563155180\";}', 0, 0, '', '0.00', '', 1547169993),
(942, 'wechat', 24, '', 'PT20190111092928285146', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000256201901117788785913\";}', 0, 0, '', '0.00', '', 1547170190),
(943, 'wechat', 22, '', 'PT20190114101105052687', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000239201901144419189965\";}', 0, 0, '', '0.00', '', 1547431873),
(944, 'wechat', 21, '', 'PT20190114105203632482', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901145911027883\";}', 0, 0, '', '0.00', '', 1547434332),
(945, 'wechat', 21, '', 'PT20190114105757644309', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000251201901143724617790\";}', 0, 0, '', '0.00', '', 1547434690),
(946, 'wechat', 22, '', 'PT20190114111407229826', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901149090753420\";}', 0, 0, '', '0.00', '', 1547435652),
(947, 'wechat', 21, '', 'PT20190114111537761529', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901148598238321\";}', 0, 0, '', '0.00', '', 1547435745),
(948, 'wechat', 22, '', 'PT20190114112442234114', 0, '0.00', '0.04', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000255201901141857481168\";}', 0, 0, '', '0.00', '', 1547436303),
(949, 'wechat', 22, '', 'PT20190114133016622989', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901145226872402\";}', 0, 0, '', '0.00', '', 1547443836),
(950, 'wechat', 22, '', 'PT20190114133146342588', 0, '0.00', '0.04', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000239201901143287290203\";}', 0, 0, '', '0.00', '', 1547443913),
(951, 'wechat', 22, '', 'PT20190114150824027844', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901149228951799\";}', 0, 0, '', '0.00', '', 1547449710),
(952, 'wechat', 24, '', 'PT20190114154718467243', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901147219053599\";}', 0, 0, '', '0.00', '', 1547452044),
(953, 'wechat', 24, '', 'PT20190114154943481930', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901141438189068\";}', 0, 0, '', '0.00', '', 1547452188),
(954, 'wechat', 22, '', 'PT20190114155008266266', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000241201901141331093185\";}', 0, 0, '', '0.00', '', 1547452215),
(955, 'wechat', 22, '', 'PT20190114155357526233', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000254201901140778530514\";}', 0, 0, '', '0.00', '', 1547452441),
(956, 'wechat', 24, '', 'PT20190114155441417600', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000248201901145965767459\";}', 0, 0, '', '0.00', '', 1547452486),
(957, 'wechat', 24, '', 'PT20190114163230882610', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901144980062188\";}', 0, 0, '', '0.00', '', 1547454755),
(958, 'wechat', 22, '', 'PT20190114163333484466', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000239201901141118735256\";}', 0, 0, '', '0.00', '', 1547454818),
(959, 'wechat', 22, '', 'PT20190114180635688255', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901144566474641\";}', 0, 0, '', '0.00', '', 1547460400),
(960, 'wechat', 24, '', 'PT20190114180727210289', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000256201901142449492200\";}', 0, 0, '', '0.00', '', 1547460452),
(963, 'wechat', 22, '', 'PT20190116114218622442', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000258201901160575052524\";}', 0, 0, '', '0.00', '', 1547610325),
(964, 'wechat', 22, '', 'PT20190116143620826162', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000245201901168859181188\";}', 0, 0, '', '0.00', '', 1547620601),
(965, 'wechat', 21, '', 'PT20190116143800724894', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000252201901167897281018\";}', 0, 0, '', '0.00', '', 1547620710),
(966, 'wechat', 21, '', 'PT20190116161440252818', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000254201901168147403208\";}', 0, 0, '', '0.00', '', 1547626506),
(967, '', 21, '', 'PT20190116165827127804', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(968, 'wechat', 22, '', 'PT20190116170947728840', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000252201901163933036033\";}', 0, 0, '', '0.00', '', 1547629809),
(969, 'wechat', 21, '', 'PT20190116171124378215', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000250201901162814364209\";}', 0, 0, '', '0.00', '', 1547629907),
(973, '', 25, '', 'SH20190116171125888449', 0, '0.00', '0.01', 0, 'shop', '', 0, 0, '', '0.00', '', 0),
(974, 'wechat', 21, '', 'PT20190116171158167754', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000239201901163516822915\";}', 0, 0, '', '0.00', '', 1547629948),
(975, 'wechat', 22, '', 'PT20190116173233636661', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201901162000327025\";}', 0, 0, '', '0.00', '', 1547631174),
(976, 'wechat', 22, '', 'PT20190116181016404456', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000245201901166306915943\";}', 0, 0, '', '0.00', '', 1547633441),
(977, '', 22, '', 'PT20190117111147263679', 0, '0.00', '100.00', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(978, '', 21, '', 'PT20190117170842016626', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(979, 'alipay', 21, '', 'PT20190117170849855111', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011722001472151022912993\";}', 0, 0, '', '0.00', '', 1547804029),
(980, 'alipay', 21, '', 'PT20190117171915610221', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011722001472151022912998\";}', 0, 0, '', '0.00', '', 1547804627),
(981, '', 21, '', 'PT20190117172049226221', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(982, 'alipay', 21, '', 'PT20190117172054406497', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011722001472151023020351\";}', 0, 0, '', '0.00', '', 1547804691),
(983, 'alipay', 21, '', 'PT20190117172857222688', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011722001472151022917462\";}', 0, 0, '', '0.00', '', 1547805201),
(984, 'alipay', 21, '', 'PT20190117173508084460', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011722001472151023103103\";}', 0, 0, '', '0.00', '', 1547805565),
(985, 'alipay', 21, '', 'PT20190117175637668466', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011722001472151023159700\";}', 0, 0, '', '0.00', '', 1547806832),
(986, 'alipay', 21, '', 'PT20190118093253668290', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011822001472151022897473\";}', 0, 0, '', '0.00', '', 1547776607),
(987, '', 21, '', 'PT20190118094122381484', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(988, '', 21, '', 'PT20190118094145532226', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(989, '', 21, '', 'PT20190118094239826226', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(990, '', 21, '', 'PT20190118094247068544', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(997, '', 21, '', 'PT20190118094334730542', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(994, '', 22, '', 'PT20190118094659652628', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(996, '', 21, '', 'PT20190118094745442426', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(998, '', 21, '', 'PT20190118094818814644', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(999, '', 21, '', 'PT20190118094915124522', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1000, '', 21, '', 'PT20190118094944584488', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1001, 'alipay', 21, '', 'PT20190118095318444994', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011822001472151022889430\";}', 0, 0, '', '0.00', '', 1547776402),
(1002, 'alipay', 21, '', 'PT20190118095341200244', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011822001472151022917555\";}', 0, 0, '', '0.00', '', 1547776425),
(1003, 'alipay', 21, '', 'PT20190118095630482902', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011822001472151023190077\";}', 0, 0, '', '0.00', '', 1547776600),
(1004, 'wechat', 22, '', 'PT20190118100117141844', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201901182046607992\";}', 0, 0, '', '0.00', '', 1547776916),
(1005, 'wechat', 22, '', 'PT20190118100710698443', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000254201901182398924019\";}', 0, 0, '', '0.00', '', 1547777253),
(1006, 'wechat', 21, '', 'PT20190118101042542464', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901180973290952\";}', 0, 0, '', '0.00', '', 1547777464),
(1007, 'alipay', 21, '', 'PT20190118101125979899', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019011822001472151023076851\";}', 0, 0, '', '0.00', '', 1547777494),
(1008, 'wechat', 22, '', 'PT20190118112033588152', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000251201901189954096186\";}', 0, 0, '', '0.00', '', 1547781654),
(1009, 'wechat', 22, '', 'PT20190118114618280724', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901188463251830\";}', 0, 0, '', '0.00', '', 1547783215),
(1010, 'wechat', 22, '', 'PT20190118134323964410', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901187714189651\";}', 0, 0, '', '0.00', '', 1547790225),
(1011, 'wechat', 24, '', 'PT20190118134430466498', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000241201901188304651801\";}', 0, 0, '', '0.00', '', 1547790291),
(1012, 'wechat', 24, '', 'PT20190118134553810309', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000239201901185306894098\";}', 0, 0, '', '0.00', '', 1547790374),
(1013, 'wechat', 21, '', 'PT20190118134654299944', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901180367614675\";}', 0, 0, '', '0.00', '', 1547790437),
(1014, 'wechat', 21, '', 'PT20190118140121668574', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901180525528646\";}', 0, 0, '', '0.00', '', 1547791304),
(1015, 'wechat', 21, '', 'PT20190118142327366604', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201901187599158093\";}', 0, 0, '', '0.00', '', 1547792614),
(1016, 'wechat', 24, '', 'PT20190118142411016138', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000252201901186979159847\";}', 0, 0, '', '0.00', '', 1547792657),
(1017, 'wechat', 21, '', 'PT20190118142601426524', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000245201901180856896889\";}', 0, 0, '', '0.00', '', 1547792770),
(1018, 'wechat', 21, '', 'PT20190118142900399842', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901188590805101\";}', 0, 0, '', '0.00', '', 1547792948),
(1019, 'wechat', 24, '', 'PT20190118143443698256', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901184902990722\";}', 0, 0, '', '0.00', '', 1547793323),
(1020, 'wechat', 24, '', 'PT20190118143512662647', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201901188897070998\";}', 0, 0, '', '0.00', '', 1547793317),
(1021, 'wechat', 24, '', 'PT20190118143544887796', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901188244356227\";}', 0, 0, '', '0.00', '', 1547793351),
(1022, '', 24, '', 'PT20190118144932860616', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1023, 'wechat', 22, '', 'PT20190118152449562803', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000241201901187055797122\";}', 0, 0, '', '0.00', '', 1547796301),
(1024, 'wechat', 22, '', 'PT20190118152514288268', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000253201901181270532305\";}', 0, 0, '', '0.00', '', 1547796319),
(1025, 'wechat', 22, '', 'PT20190118152556865666', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901184074090776\";}', 0, 0, '', '0.00', '', 1547796363),
(1026, 'wechat', 22, '', 'PT20190118152613874212', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901189706140841\";}', 0, 0, '', '0.00', '', 1547796381),
(1027, 'wechat', 24, '', 'PT20190118152802084623', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000252201901188025026113\";}', 0, 0, '', '0.00', '', 1547796488),
(1028, 'wechat', 24, '', 'PT20190118152857459620', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901181312718527\";}', 0, 0, '', '0.00', '', 1547796542),
(1029, 'wechat', 24, '', 'PT20190118161659822331', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000248201901188868958862\";}', 0, 0, '', '0.00', '', 1547799428),
(1030, 'wechat', 24, '', 'PT20190118170709491226', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201901187697771956\";}', 0, 0, '', '0.00', '', 1547802436),
(1031, 'wechat', 24, '', 'PT20190118170834828118', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000245201901186096058268\";}', 0, 0, '', '0.00', '', 1547802522),
(1032, 'wechat', 24, '', 'PT20190118170905481202', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901182899049641\";}', 0, 0, '', '0.00', '', 1547802550),
(1033, 'wechat', 24, '', 'PT20190118171103882591', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000241201901182553550376\";}', 0, 0, '', '0.00', '', 1547802671),
(1034, 'wechat', 24, '', 'PT20190118171327252862', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000256201901180358847045\";}', 0, 0, '', '0.00', '', 1547802813),
(1035, 'wechat', 24, '', 'PT20190118171412188052', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901186035615838\";}', 0, 0, '', '0.00', '', 1547802862),
(1036, 'wechat', 24, '', 'PT20190118171449245023', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000256201901185079147362\";}', 0, 0, '', '0.00', '', 1547802896),
(1037, 'wechat', 24, '', 'PT20190118171945074377', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201901185841991286\";}', 0, 0, '', '0.00', '', 1547803193),
(1038, 'wechat', 24, '', 'PT20190118172024985582', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000253201901184692607354\";}', 0, 0, '', '0.00', '', 1547803232),
(1039, 'wechat', 24, '', 'PT20190118172329802727', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000241201901187491433939\";}', 0, 0, '', '0.00', '', 1547803418),
(1040, 'wechat', 24, '', 'PT20190118172841396343', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201901187756147423\";}', 0, 0, '', '0.00', '', 1547803730),
(1043, 'wechat', 22, '', 'PT20190121082847618792', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901215814308911\";}', 0, 0, '', '0.00', '', 1548030533),
(1044, 'wechat', 22, '', 'PT20190121091547594243', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901216358885544\";}', 0, 0, '', '0.00', '', 1548033371),
(1045, 'wechat', 22, '', 'PT20190121100435695809', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901211182516436\";}', 0, 0, '', '0.00', '', 1548036283),
(1046, 'wechat', 24, '', 'PT20190121101835894869', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901216189429715\";}', 0, 0, '', '0.00', '', 1548037121),
(1047, 'wechat', 22, '', 'PT20190121104015804244', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000256201901211667940826\";}', 0, 0, '', '0.00', '', 1548038422),
(1048, 'wechat', 24, '', 'PT20190121104120848686', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901219476502030\";}', 0, 0, '', '0.00', '', 1548038488),
(1049, 'wechat', 21, '', 'PT20190121104146742464', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901218006695327\";}', 0, 0, '', '0.00', '', 1548038514),
(1050, 'wechat', 24, '', 'PT20190121105259760637', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000243201901211711597582\";}', 0, 0, '', '0.00', '', 1548039184),
(1051, 'wechat', 24, '', 'PT20190121105510084364', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000253201901212098904972\";}', 0, 0, '', '0.00', '', 1548039316),
(1052, 'wechat', 24, '', 'PT20190121105658866664', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000250201901213210653726\";}', 0, 0, '', '0.00', '', 1548039425),
(1053, '', 24, '', 'PT20190121110902442466', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1054, 'wechat', 24, '', 'PT20190121110910278415', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901219417877209\";}', 0, 0, '', '0.00', '', 1548040174),
(1055, 'wechat', 22, '', 'PT20190121111053224302', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901212648731847\";}', 0, 0, '', '0.00', '', 1548040259),
(1056, 'wechat', 24, '', 'PT20190121111312956260', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000249201901218259196634\";}', 0, 0, '', '0.00', '', 1548040397),
(1057, 'wechat', 24, '', 'PT20190121111645245422', 0, '0.00', '0.02', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000253201901212118430707\";}', 0, 0, '', '0.00', '', 1548040611),
(1058, 'wechat', 24, '', 'PT20190121114936946831', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000255201901212524014392\";}', 0, 0, '', '0.00', '', 1548042581),
(1059, 'alipay', 24, '', 'PT20190121141951289586', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"2019012122001436831013273901\";}', 0, 0, '', '0.00', '', 1548051600),
(1060, 'wechat', 24, '', 'PT20190121142011449301', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000258201901216486510738\";}', 0, 0, '', '0.00', '', 1548051618),
(1061, 'wechat', 24, '', 'PT20190121142115760682', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901212302363833\";}', 0, 0, '', '0.00', '', 1548051680),
(1062, 'wechat', 24, '', 'PT20190121142134542922', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000241201901215444177092\";}', 0, 0, '', '0.00', '', 1548051702),
(1063, 'wechat', 24, '', 'PT20190121143214822243', 0, '0.00', '0.04', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201901215186280961\";}', 0, 0, '', '0.00', '', 1548052340),
(1064, '', 24, '', 'PT20190121144037429429', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1065, 'wechat', 24, '', 'PT20190121144045718322', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000257201901210742114091\";}', 0, 0, '', '0.00', '', 1548052850),
(1066, 'wechat', 24, '', 'ME20190121163456042694', 0, '0.00', '0.01', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000247201901212212185733\";}', 0, 0, '', '0.00', '', 1548059720),
(1067, 'wechat', 24, '', 'SH20190121163830829806', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000240201901213243800787\";}', 0, 0, '', '0.00', '', 1548059922),
(1068, 'wechat', 24, '', 'SH20190121164937426046', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201901213898049209\";}', 0, 0, '', '0.00', '', 1548060585),
(1069, 'alipay', 24, '', 'SH20190121165509466842', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"2019012122001436831013356530\";}', 0, 0, '', '0.00', '', 1548060914),
(1070, 'alipay', 24, '', 'SH20190121165602216923', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"2019012122001436831013247011\";}', 0, 0, '', '0.00', '', 1548060968),
(1072, '', 25, '', 'SH20190121174748499682', 0, '0.00', '0.02', 0, 'shop', '', 0, 0, '', '0.00', '', 0),
(1073, 'wechat', 24, '', 'PT20190121175001625422', 0, '0.00', '0.01', 1, 'groups', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901213527032538\";}', 0, 0, '', '0.00', '', 1548064208),
(1074, 'alipay', 24, '', 'SH20190121175052296476', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"2019012122001436831013274799\";}', 0, 0, '', '0.00', '', 1548064257),
(1075, 'wechat', 24, '', 'SH20190121175144294870', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000246201901212359404960\";}', 0, 0, '', '0.00', '', 1548064311),
(1076, 'wechat', 24, '', 'SH20190121175233832448', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000245201901215072735472\";}', 0, 0, '', '0.00', '', 1548064361),
(1077, 'wechat', 24, '', 'SH20190121175527856908', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000250201901217524247239\";}', 0, 0, '', '0.00', '', 1548064535),
(1078, 'wechat', 24, '', 'SH20190121175739476448', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901218370274577\";}', 0, 0, '', '0.00', '', 1548064669),
(1079, 'wechat', 24, '', 'SH20190122111710248883', 0, '0.00', '0.02', 1, 'shop', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000245201901224469672795\";}', 0, 0, '', '0.00', '', 1548127042),
(1080, '', 22, '', 'AUB20190124145250806267', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1081, '', 22, '', 'AUB20190124145316662145', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1082, '', 22, '', 'AUB20190124145422682662', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1083, '', 22, '', 'AUB20190124145918441072', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1084, '', 22, '', 'AUB20190124150040144868', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1085, '', 22, '', 'AUB20190124150108624446', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1086, '', 22, '', 'AUB20190124152731864944', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1087, '', 22, '', 'AUB20190124152931605146', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1088, '', 22, '', 'AUB20190124153222843122', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1089, '', 22, '', 'AUB20190124155433088873', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1090, '', 22, '', 'AUB20190124160642476268', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1091, '', 22, '', 'AUB20190124160703038043', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1092, '', 22, '', 'AUB20190124160717660224', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1093, '', 22, '', 'AUB20190124160727892606', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1094, '', 22, '', 'AUB20190124160734790996', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1095, '', 22, '', 'AUB20190124160744844280', 0, '0.00', '888.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1096, '', 22, '', 'AUB20190124160804646687', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1097, '', 22, '', 'AUB20190124161010024207', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1098, '', 22, '', 'AUB20190124161017666866', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1101, '', 22, '', 'AUB20190124161710838022', 0, '0.00', '0.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1102, '', 22, '', 'AUB20190124161731846022', 0, '0.00', '0.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1103, '', 22, '', 'AUB20190124163011653254', 0, '0.00', '0.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1104, '', 21, '', 'AUB20190124175137688685', 0, '0.00', '0.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1105, '', 22, '', 'AUB20190124175252891628', 0, '0.00', '60.00', 0, 'auction_bond', '', 0, 0, '', '0.00', '', 0),
(1106, '', 22, '', 'JF20190128112703928827', 0, '0.00', '0.04', 0, 'community', '', 0, 0, '', '0.00', '', 0),
(1107, 'alipay', 22, '', 'JF20190128113845440282', 0, '0.00', '0.04', 1, 'community', 'a:1:{s:14:\"transaction_id\";s:28:\"2019012822001436831014906640\";}', 0, 0, '', '0.00', '', 1548646731),
(1108, 'wechat', 22, '', 'JF20190128114649222322', 0, '0.00', '0.02', 1, 'community', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000244201901286736977443\";}', 0, 0, '', '0.00', '', 1548647216),
(1109, '', 22, '', 'JF20190128142424149348', 0, '0.00', '0.02', 0, 'community', '', 0, 0, '', '0.00', '', 0),
(1110, 'wechat', 22, '', 'JF20190128142444282226', 0, '0.00', '0.02', 1, 'community', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000242201901289627195945\";}', 0, 0, '', '0.00', '', 1548656712),
(1111, '', 22, '', 'JF20190128142507024086', 0, '0.00', '0.02', 0, 'community', '', 0, 0, '', '0.00', '', 0),
(1112, 'wechat', 22, '', 'JF20190128142956047320', 0, '0.00', '0.04', 1, 'community', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000255201901283078619891\";}', 0, 0, '', '0.00', '', 1548657004),
(1113, 'wechat', 22, '', 'JF20190128144218324681', 0, '0.00', '0.02', 1, 'community', 'a:1:{s:14:\"transaction_id\";s:28:\"4200000256201901280131381988\";}', 0, 0, '', '0.00', '', 1548657746),
(1116, '', 25, '', 'SH20190128174804874482', 0, '0.00', '0.02', 0, 'shop', '', 0, 0, '', '0.00', '', 0),
(1117, '', 22, '', 'PT20190129140402626488', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1118, '', 22, '', 'PT20190129153510528284', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1119, '', 25, '', 'PT20190129170509986900', 0, '0.00', '0.06', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1120, '', 25, '', 'PT20190129174624884249', 0, '0.00', '100.00', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1123, '', 25, '', 'PT20190129175100146664', 0, '0.00', '588.00', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1124, '', 25, '', 'PT20190129175205892408', 0, '0.00', '0.02', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1136, '', 23, '', 'PT20190129180256230614', 0, '0.00', '0.06', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1139, '', 23, '', 'SH20190129180838202447', 0, '0.00', '0.02', 0, 'shop', '', 0, 0, '', '0.00', '', 0),
(1140, '', 22, '', 'PT20190129181033678454', 0, '0.00', '0.01', 0, 'groups', '', 0, 0, '', '0.00', '', 0),
(1141, '', 27, '', 'ME20190819093938084544', 0, '0.00', '0.01', 0, 'shop', '', 0, 0, '', '0.00', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon`
--

CREATE TABLE `suliss_shop_coupon` (
  `id` int(11) NOT NULL,
  `catid` int(11) DEFAULT '0',
  `couponname` varchar(255) DEFAULT '',
  `gettype` tinyint(3) DEFAULT '0',
  `getmax` int(11) DEFAULT '0',
  `usetype` tinyint(3) DEFAULT '0',
  `returntype` tinyint(3) DEFAULT '0',
  `bgcolor` varchar(255) DEFAULT '',
  `enough` decimal(10,2) DEFAULT '0.00',
  `timelimit` tinyint(3) DEFAULT '0',
  `coupontype` tinyint(3) DEFAULT '0',
  `timedays` int(11) DEFAULT '0',
  `timestart` int(11) DEFAULT '0',
  `timeend` int(11) DEFAULT '0',
  `discount` decimal(10,2) DEFAULT '0.00',
  `deduct` decimal(10,2) DEFAULT '0.00',
  `backtype` tinyint(3) DEFAULT '0',
  `backmoney` varchar(50) DEFAULT '',
  `backcredit` varchar(50) DEFAULT '',
  `backredpack` varchar(50) DEFAULT '',
  `backwhen` tinyint(3) DEFAULT '0',
  `thumb` varchar(255) DEFAULT '',
  `desc` text,
  `createtime` int(11) DEFAULT '0',
  `total` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `money` decimal(10,2) DEFAULT '0.00',
  `respdesc` text,
  `respthumb` varchar(255) DEFAULT '',
  `resptitle` varchar(255) DEFAULT '',
  `respurl` varchar(255) DEFAULT '',
  `credit` int(11) DEFAULT '0',
  `usecredit2` tinyint(3) DEFAULT '0',
  `remark` varchar(1000) DEFAULT '',
  `descnoset` tinyint(3) DEFAULT '0',
  `pwdkey` varchar(255) DEFAULT '',
  `pwdsuc` text,
  `pwdfail` text,
  `pwdurl` varchar(255) DEFAULT '',
  `pwdask` text,
  `pwdstatus` tinyint(3) DEFAULT '0',
  `pwdtimes` int(11) DEFAULT '0',
  `pwdfull` text,
  `pwdwords` text,
  `pwdopen` tinyint(3) DEFAULT '0',
  `pwdown` text,
  `pwdexit` varchar(255) DEFAULT '',
  `pwdexitstr` text,
  `displayorder` int(11) DEFAULT '0',
  `pwdkey2` varchar(255) DEFAULT '',
  `merchid` int(11) DEFAULT '0',
  `limitgoodtype` tinyint(1) DEFAULT '0',
  `limitgoodcatetype` tinyint(1) DEFAULT '0',
  `limitgoodcateids` varchar(500) DEFAULT '',
  `limitgoodids` varchar(500) DEFAULT '',
  `islimitlevel` tinyint(1) DEFAULT '0',
  `limitmemberlevels` varchar(500) DEFAULT '',
  `limitagentlevels` varchar(500) DEFAULT '',
  `limitpartnerlevels` varchar(500) DEFAULT '',
  `limitaagentlevels` varchar(500) DEFAULT '',
  `tagtitle` varchar(20) DEFAULT '',
  `settitlecolor` tinyint(1) DEFAULT '0',
  `titlecolor` varchar(10) DEFAULT '',
  `limitdiscounttype` tinyint(1) DEFAULT '1',
  `quickget` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon`
--

TRUNCATE TABLE `suliss_shop_coupon`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_data`
--

CREATE TABLE `suliss_shop_coupon_data` (
  `id` int(11) NOT NULL,
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
  `textkey` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon_data`
--

TRUNCATE TABLE `suliss_shop_coupon_data`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_goodsendtask`
--

CREATE TABLE `suliss_shop_coupon_goodsendtask` (
  `id` int(11) NOT NULL,
  `goodsid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `starttime` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '1',
  `num` int(11) DEFAULT '0',
  `sendpoint` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon_goodsendtask`
--

TRUNCATE TABLE `suliss_shop_coupon_goodsendtask`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_log`
--

CREATE TABLE `suliss_shop_coupon_log` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `logno` varchar(255) DEFAULT '',
  `couponid` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  `paystatus` tinyint(3) DEFAULT '0',
  `creditstatus` tinyint(3) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `paytype` tinyint(3) DEFAULT '0',
  `getfrom` tinyint(3) DEFAULT '0',
  `merchid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon_log`
--

TRUNCATE TABLE `suliss_shop_coupon_log`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_sendshow`
--

CREATE TABLE `suliss_shop_coupon_sendshow` (
  `id` int(11) NOT NULL,
  `showkey` varchar(20) NOT NULL,
  `mid` int(11) NOT NULL,
  `coupondataid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon_sendshow`
--

TRUNCATE TABLE `suliss_shop_coupon_sendshow`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_sendtasks`
--

CREATE TABLE `suliss_shop_coupon_sendtasks` (
  `id` int(11) NOT NULL,
  `enough` decimal(10,2) DEFAULT '0.00',
  `couponid` int(11) DEFAULT '0',
  `starttime` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '1',
  `num` int(11) DEFAULT '0',
  `sendpoint` tinyint(1) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon_sendtasks`
--

TRUNCATE TABLE `suliss_shop_coupon_sendtasks`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_taskdata`
--

CREATE TABLE `suliss_shop_coupon_taskdata` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT NULL,
  `taskid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '0',
  `tasktype` tinyint(1) DEFAULT '0',
  `orderid` int(11) DEFAULT '0',
  `parentorderid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `sendpoint` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon_taskdata`
--

TRUNCATE TABLE `suliss_shop_coupon_taskdata`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_coupon_usesendtasks`
--

CREATE TABLE `suliss_shop_coupon_usesendtasks` (
  `id` int(11) NOT NULL,
  `usecouponid` int(11) DEFAULT '0',
  `couponid` int(11) DEFAULT '0',
  `starttime` int(11) DEFAULT '0',
  `endtime` int(11) DEFAULT '0',
  `sendnum` int(11) DEFAULT '1',
  `num` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED;

--
-- 插入之前先把表清空（truncate） `suliss_shop_coupon_usesendtasks`
--

TRUNCATE TABLE `suliss_shop_coupon_usesendtasks`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_banner`
--

CREATE TABLE `suliss_shop_creditshop_banner` (
  `id` int(11) NOT NULL,
  `bannername` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_banner`
--

TRUNCATE TABLE `suliss_shop_creditshop_banner`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_comment`
--

CREATE TABLE `suliss_shop_creditshop_comment` (
  `id` int(11) NOT NULL,
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
  `merchid` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_comment`
--

TRUNCATE TABLE `suliss_shop_creditshop_comment`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods`
--

CREATE TABLE `suliss_shop_creditshop_goods` (
  `id` int(11) NOT NULL,
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
  `packetsurplus` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_goods`
--

TRUNCATE TABLE `suliss_shop_creditshop_goods`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_category`
--

CREATE TABLE `suliss_shop_creditshop_goods_category` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) UNSIGNED DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_goods_category`
--

TRUNCATE TABLE `suliss_shop_creditshop_goods_category`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_option`
--

CREATE TABLE `suliss_shop_creditshop_goods_option` (
  `id` int(11) NOT NULL,
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
  `exchange_stock` int(11) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_goods_option`
--

TRUNCATE TABLE `suliss_shop_creditshop_goods_option`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_spec`
--

CREATE TABLE `suliss_shop_creditshop_goods_spec` (
  `id` int(11) NOT NULL,
  `goodsid` int(11) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `displaytype` tinyint(3) DEFAULT '0',
  `content` text,
  `displayorder` int(11) DEFAULT '0',
  `propId` varchar(255) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_goods_spec`
--

TRUNCATE TABLE `suliss_shop_creditshop_goods_spec`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_goods_spec_item`
--

CREATE TABLE `suliss_shop_creditshop_goods_spec_item` (
  `id` int(11) NOT NULL,
  `specid` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `show` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `valueId` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_goods_spec_item`
--

TRUNCATE TABLE `suliss_shop_creditshop_goods_spec_item`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_log`
--

CREATE TABLE `suliss_shop_creditshop_log` (
  `id` int(11) NOT NULL,
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
  `remarksaler` text,
  `dispatch` decimal(10,2) DEFAULT '0.00',
  `money` decimal(10,2) DEFAULT '0.00',
  `credit` int(11) DEFAULT '0',
  `goods_num` int(11) DEFAULT '0',
  `merchapply` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_log`
--

TRUNCATE TABLE `suliss_shop_creditshop_log`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_creditshop_verify`
--

CREATE TABLE `suliss_shop_creditshop_verify` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `logid` int(11) DEFAULT '0',
  `verifycode` varchar(45) DEFAULT NULL,
  `storeid` int(11) DEFAULT '0',
  `verifier` varchar(45) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `verifytime` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_creditshop_verify`
--

TRUNCATE TABLE `suliss_shop_creditshop_verify`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_dispatch`
--

CREATE TABLE `suliss_shop_dispatch` (
  `id` int(11) NOT NULL,
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
  `freeprice` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_dispatch`
--

TRUNCATE TABLE `suliss_shop_dispatch`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_express`
--

CREATE TABLE `suliss_shop_express` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT '',
  `express` varchar(50) DEFAULT '',
  `status` tinyint(1) DEFAULT '1',
  `displayorder` tinyint(3) UNSIGNED DEFAULT '0',
  `code` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_express`
--

TRUNCATE TABLE `suliss_shop_express`;
--
-- 转存表中的数据 `suliss_shop_express`
--

INSERT INTO `suliss_shop_express` (`id`, `name`, `express`, `status`, `displayorder`, `code`) VALUES
(1, '顺丰', 'shunfeng', 1, 0, 'JH_014'),
(2, '申通', 'shentong', 1, 0, 'JH_005'),
(3, '韵达快运', 'yunda', 1, 0, 'JH_003'),
(4, '天天快递', 'tiantian', 1, 0, 'JH_004'),
(5, '圆通速递', 'yuantong', 1, 0, 'JH_002'),
(6, '中通速递', 'zhongtong', 1, 0, 'JH_006'),
(7, 'ems快递', 'ems', 1, 0, 'JH_001'),
(8, '百世汇通', 'huitongkuaidi', 1, 0, 'JH_012'),
(9, '全峰快递', 'quanfengkuaidi', 1, 0, 'JH_009'),
(10, '宅急送', 'zhaijisong', 1, 0, 'JH_007'),
(11, 'aae全球专递', 'aae', 1, 0, 'JHI_049'),
(12, '安捷快递', 'anjie', 1, 0, ''),
(13, '安信达快递', 'anxindakuaixi', 1, 0, 'JH_131'),
(14, '彪记快递', 'biaojikuaidi', 1, 0, ''),
(15, 'bht', 'bht', 1, 0, 'JHI_008'),
(16, '百福东方国际物流', 'baifudongfang', 1, 0, 'JH_062'),
(17, '中国东方（COE）', 'coe', 1, 0, 'JHI_038'),
(18, '长宇物流', 'changyuwuliu', 1, 0, ''),
(19, '大田物流', 'datianwuliu', 1, 0, 'JH_050'),
(20, '德邦物流', 'debangwuliu', 1, 0, 'JH_011'),
(21, 'dhl', 'dhl', 1, 0, 'JHI_002'),
(22, 'dpex', 'dpex', 1, 0, 'JHI_011'),
(23, 'd速快递', 'dsukuaidi', 1, 0, 'JH_049'),
(24, '递四方', 'disifang', 1, 0, 'JHI_080'),
(25, 'fedex（国外）', 'fedex', 1, 0, 'JHI_014'),
(26, '飞康达物流', 'feikangda', 1, 0, 'JH_088'),
(27, '凤凰快递', 'fenghuangkuaidi', 1, 0, ''),
(28, '飞快达', 'feikuaida', 1, 0, 'JH_151'),
(29, '国通快递', 'guotongkuaidi', 1, 0, 'JH_010'),
(30, '港中能达物流', 'ganzhongnengda', 1, 0, 'JH_033'),
(31, '广东邮政物流', 'guangdongyouzhengwuliu', 1, 0, 'JH_135'),
(32, '共速达', 'gongsuda', 1, 0, 'JH_039'),
(33, '恒路物流', 'hengluwuliu', 1, 0, 'JH_048'),
(34, '华夏龙物流', 'huaxialongwuliu', 1, 0, 'JH_129'),
(35, '海红', 'haihongwangsong', 1, 0, 'JH_132'),
(36, '海外环球', 'haiwaihuanqiu', 1, 0, 'JHI_013'),
(37, '佳怡物流', 'jiayiwuliu', 1, 0, 'JH_035'),
(38, '京广速递', 'jinguangsudikuaijian', 1, 0, 'JH_041'),
(39, '急先达', 'jixianda', 1, 0, 'JH_040'),
(40, '佳吉物流', 'jiajiwuliu', 1, 0, 'JH_030'),
(41, '加运美物流', 'jymwl', 1, 0, 'JH_054'),
(42, '金大物流', 'jindawuliu', 1, 0, 'JH_079'),
(43, '嘉里大通', 'jialidatong', 1, 0, 'JH_060'),
(44, '晋越快递', 'jykd', 1, 0, 'JHI_046'),
(45, '快捷速递', 'kuaijiesudi', 1, 0, 'JH_008'),
(46, '联邦快递（国内）', 'lianb', 1, 0, 'JH_122'),
(47, '联昊通物流', 'lianhaowuliu', 1, 0, 'JH_021'),
(48, '龙邦物流', 'longbanwuliu', 1, 0, 'JH_019'),
(49, '立即送', 'lijisong', 1, 0, 'JH_044'),
(50, '乐捷递', 'lejiedi', 1, 0, 'JH_043'),
(51, '民航快递', 'minghangkuaidi', 1, 0, 'JH_100'),
(52, '美国快递', 'meiguokuaidi', 1, 0, 'JHI_044'),
(53, '门对门', 'menduimen', 1, 0, 'JH_036'),
(54, 'OCS', 'ocs', 1, 0, 'JHI_012'),
(55, '配思货运', 'peisihuoyunkuaidi', 1, 0, ''),
(56, '全晨快递', 'quanchenkuaidi', 1, 0, 'JH_055'),
(57, '全际通物流', 'quanjitong', 1, 0, 'JH_127'),
(58, '全日通快递', 'quanritongkuaidi', 1, 0, 'JH_029'),
(59, '全一快递', 'quanyikuaidi', 1, 0, 'JH_020'),
(60, '如风达', 'rufengda', 1, 0, 'JH_017'),
(61, '三态速递', 'santaisudi', 1, 0, 'JH_065'),
(62, '盛辉物流', 'shenghuiwuliu', 1, 0, 'JH_066'),
(63, '速尔物流', 'sue', 1, 0, 'JH_016'),
(64, '盛丰物流', 'shengfeng', 1, 0, 'JH_082'),
(65, '赛澳递', 'saiaodi', 1, 0, 'JH_042'),
(66, '天地华宇', 'tiandihuayu', 1, 0, 'JH_018'),
(67, 'tnt', 'tnt', 1, 0, 'JHI_003'),
(68, 'ups', 'ups', 1, 0, 'JHI_004'),
(69, '万家物流', 'wanjiawuliu', 1, 0, ''),
(70, '文捷航空速递', 'wenjiesudi', 1, 0, ''),
(71, '伍圆', 'wuyuan', 1, 0, ''),
(72, '万象物流', 'wxwl', 1, 0, 'JH_115'),
(73, '新邦物流', 'xinbangwuliu', 1, 0, 'JH_022'),
(74, '信丰物流', 'xinfengwuliu', 1, 0, 'JH_023'),
(75, '亚风速递', 'yafengsudi', 1, 0, 'JH_075'),
(76, '一邦速递', 'yibangwuliu', 1, 0, 'JH_064'),
(77, '优速物流', 'youshuwuliu', 1, 0, 'JH_013'),
(78, '邮政快递包裹', 'youzhengguonei', 1, 0, 'JH_077'),
(79, '邮政国际包裹挂号信', 'youzhengguoji', 1, 0, ''),
(80, '远成物流', 'yuanchengwuliu', 1, 0, 'JH_024'),
(81, '源伟丰快递', 'yuanweifeng', 1, 0, 'JH_141'),
(82, '元智捷诚快递', 'yuanzhijiecheng', 1, 0, 'JH_126'),
(83, '运通快递', 'yuntongkuaidi', 1, 0, 'JH_145'),
(84, '越丰物流', 'yuefengwuliu', 1, 0, 'JH_068'),
(85, '源安达', 'yad', 1, 0, 'JH_067'),
(86, '银捷速递', 'yinjiesudi', 1, 0, 'JH_148'),
(87, '中铁快运', 'zhongtiekuaiyun', 1, 0, 'JH_015'),
(88, '中邮物流', 'zhongyouwuliu', 1, 0, 'JH_027'),
(89, '忠信达', 'zhongxinda', 1, 0, 'JH_086'),
(90, '芝麻开门', 'zhimakaimen', 1, 0, 'JH_026'),
(91, '安能物流', 'annengwuliu', 1, 0, 'JH_059'),
(92, '京东快递', 'jd', 1, 0, 'JH_046'),
(93, '微特派', 'weitepai', 1, 0, ''),
(94, '九曳供应链', 'jiuyescm', 1, 0, '');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_express_cache`
--

CREATE TABLE `suliss_shop_express_cache` (
  `id` int(11) NOT NULL,
  `expresssn` varchar(50) DEFAULT NULL,
  `express` varchar(50) DEFAULT NULL,
  `lasttime` int(11) NOT NULL,
  `datas` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_express_cache`
--

TRUNCATE TABLE `suliss_shop_express_cache`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_fullback_goods`
--

CREATE TABLE `suliss_shop_fullback_goods` (
  `id` int(11) NOT NULL,
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
  `refund` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_fullback_goods`
--

TRUNCATE TABLE `suliss_shop_fullback_goods`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_fullback_log`
--

CREATE TABLE `suliss_shop_fullback_log` (
  `id` int(11) NOT NULL,
  `mid` int(11) NOT NULL DEFAULT '0',
  `orderid` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `priceevery` decimal(10,2) NOT NULL DEFAULT '0.00',
  `day` int(10) NOT NULL DEFAULT '0',
  `fullbackday` int(10) NOT NULL DEFAULT '0',
  `createtime` int(10) NOT NULL DEFAULT '0',
  `fullbacktime` int(10) NOT NULL DEFAULT '0',
  `isfullback` tinyint(3) NOT NULL DEFAULT '0',
  `goodsid` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_fullback_log`
--

TRUNCATE TABLE `suliss_shop_fullback_log`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_gift`
--

CREATE TABLE `suliss_shop_gift` (
  `id` int(11) NOT NULL,
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
  `share_desc` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_gift`
--

TRUNCATE TABLE `suliss_shop_gift`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods`
--

CREATE TABLE `suliss_shop_goods` (
  `id` int(11) NOT NULL,
  `pcate` int(11) DEFAULT '0',
  `ccate` int(11) DEFAULT '0',
  `tcate` int(11) DEFAULT '0',
  `type` tinyint(1) DEFAULT '1',
  `status` tinyint(1) DEFAULT '1',
  `displayorder` int(11) DEFAULT '0',
  `title` varchar(100) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `unit` varchar(5) DEFAULT '',
  `description` varchar(1000) DEFAULT NULL,
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
  `isdiscount_title` varchar(255) DEFAULT '',
  `isdiscount_time` int(11) DEFAULT '0',
  `isdiscount_discounts` text,
  `isrecommand` tinyint(1) DEFAULT '0',
  `issendfree` tinyint(1) DEFAULT '0',
  `istime` tinyint(1) DEFAULT '0',
  `iscomment` tinyint(1) DEFAULT '0',
  `timestart` int(11) DEFAULT '0',
  `timeend` int(11) DEFAULT '0',
  `viewcount` int(11) DEFAULT '0',
  `deleted` tinyint(3) DEFAULT '0',
  `hascommission` tinyint(3) DEFAULT '0',
  `commission1_rate` decimal(10,2) DEFAULT '0.00',
  `commission1_pay` decimal(10,2) DEFAULT '0.00',
  `commission2_rate` decimal(10,2) DEFAULT '0.00',
  `commission2_pay` decimal(10,2) DEFAULT '0.00',
  `commission3_rate` decimal(10,2) DEFAULT '0.00',
  `commission3_pay` decimal(10,2) DEFAULT '0.00',
  `commission` text,
  `score` decimal(10,2) DEFAULT '0.00',
  `catch_id` varchar(255) DEFAULT '',
  `catch_url` varchar(255) DEFAULT '',
  `catch_source` varchar(255) DEFAULT '',
  `updatetime` int(11) DEFAULT '0',
  `share_title` varchar(255) DEFAULT '',
  `share_icon` varchar(255) DEFAULT '',
  `cash` tinyint(3) DEFAULT '0',
  `commission_thumb` varchar(255) DEFAULT '',
  `isnodiscount` tinyint(3) DEFAULT '0',
  `showlevels` text,
  `buylevels` text,
  `showgroups` text,
  `buygroups` text,
  `isverify` tinyint(3) DEFAULT '0',
  `storeids` text,
  `noticemid` int(11) NOT NULL DEFAULT '0',
  `noticetype` text,
  `needfollow` tinyint(3) DEFAULT '0',
  `followurl` varchar(255) DEFAULT '',
  `followtip` varchar(255) DEFAULT '',
  `deduct` decimal(10,2) DEFAULT '0.00',
  `shorttitle` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0',
  `ccates` text,
  `discounts` text,
  `nocommission` tinyint(3) DEFAULT '0',
  `hidecommission` tinyint(3) DEFAULT '0',
  `pcates` text,
  `tcates` text,
  `merchcates` varchar(255) NOT NULL DEFAULT '',
  `merchpcate` int(11) NOT NULL DEFAULT '0',
  `merchccate` int(11) NOT NULL DEFAULT '0',
  `merchtcate` int(11) NOT NULL DEFAULT '0',
  `merchpcates` varchar(500) NOT NULL DEFAULT '',
  `merchccates` varchar(500) NOT NULL DEFAULT '',
  `merchtcates` varchar(500) NOT NULL DEFAULT '',
  `detail_logo` varchar(255) DEFAULT '',
  `detail_shopname` varchar(255) DEFAULT '',
  `detail_totaltitle` varchar(255) DEFAULT '',
  `detail_btntext1` varchar(255) DEFAULT '',
  `detail_btnurl1` varchar(255) DEFAULT '',
  `detail_btntext2` varchar(255) DEFAULT '',
  `detail_btnurl2` varchar(255) DEFAULT '',
  `cates` text,
  `artid` int(11) DEFAULT '0',
  `deduct2` decimal(10,2) DEFAULT '0.00',
  `ednum` int(11) DEFAULT '0',
  `edareas` text,
  `edmoney` decimal(10,2) DEFAULT '0.00',
  `dispatchtype` tinyint(1) DEFAULT '0',
  `dispatchid` int(11) DEFAULT '0',
  `dispatchprice` decimal(10,2) DEFAULT '0.00',
  `manydeduct` tinyint(1) DEFAULT '0',
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
  `quality` tinyint(3) DEFAULT '0',
  `groupstype` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `showtotal` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
  `subtitle` varchar(255) DEFAULT '',
  `sharebtn` tinyint(1) NOT NULL DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `checked` tinyint(3) DEFAULT '0',
  `thumb_first` tinyint(3) DEFAULT '0',
  `merchsale` tinyint(1) DEFAULT '0',
  `keywords` varchar(255) DEFAULT '',
  `labelname` text,
  `autoreceive` int(11) DEFAULT '0',
  `cannotrefund` tinyint(3) DEFAULT '0',
  `bargain` int(11) DEFAULT '0',
  `buyagain` decimal(10,2) DEFAULT '0.00',
  `buyagain_islong` tinyint(1) DEFAULT '0',
  `buyagain_condition` tinyint(1) DEFAULT '0',
  `buyagain_sale` tinyint(1) DEFAULT '0',
  `buyagain_commission` text,
  `buyagain_price` decimal(10,2) DEFAULT '0.00',
  `cashier` tinyint(1) DEFAULT '0',
  `isendtime` tinyint(3) NOT NULL DEFAULT '0',
  `usetime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `merchdisplayorder` int(11) NOT NULL DEFAULT '0',
  `exchange_stock` int(11) DEFAULT '0',
  `exchange_postage` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ispresell` tinyint(3) NOT NULL DEFAULT '0',
  `presellprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `presellover` tinyint(3) NOT NULL DEFAULT '0',
  `presellovertime` int(11) NOT NULL,
  `presellstart` tinyint(3) NOT NULL DEFAULT '0',
  `preselltimestart` int(11) NOT NULL DEFAULT '0',
  `presellend` tinyint(3) NOT NULL DEFAULT '0',
  `preselltimeend` int(11) NOT NULL DEFAULT '0',
  `presellsendtype` tinyint(3) NOT NULL DEFAULT '0',
  `presellsendstatrttime` int(11) NOT NULL DEFAULT '0',
  `presellsendtime` int(11) NOT NULL DEFAULT '0',
  `edareas_code` text NOT NULL,
  `unite_total` tinyint(3) NOT NULL DEFAULT '0',
  `threen` varchar(255) DEFAULT '',
  `catesinit3` text,
  `showtotaladd` tinyint(1) DEFAULT '0',
  `intervalfloor` tinyint(1) DEFAULT '0',
  `intervalprice` varchar(512) DEFAULT '',
  `isfullback` tinyint(3) NOT NULL DEFAULT '0',
  `isstatustime` tinyint(3) NOT NULL DEFAULT '0',
  `statustimestart` int(10) NOT NULL DEFAULT '0',
  `statustimeend` int(10) NOT NULL DEFAULT '0',
  `nosearch` tinyint(1) NOT NULL DEFAULT '0',
  `showsales` tinyint(3) NOT NULL DEFAULT '1',
  `nolive` tinyint(3) NOT NULL DEFAULT '0',
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
  `newgoods` tinyint(3) NOT NULL DEFAULT '0',
  `video` varchar(512) DEFAULT '',
  `officthumb` varchar(512) DEFAULT '',
  `verifygoodstype` tinyint(1) NOT NULL DEFAULT '0',
  `isforceverifystore` tinyint(1) NOT NULL DEFAULT '0',
  `taobaoid` varchar(255) DEFAULT '',
  `taotaoid` varchar(255) DEFAULT '',
  `taobaourl` varchar(255) DEFAULT '',
  `saleupdate40170` tinyint(3) DEFAULT '0',
  `saleupdate35843` tinyint(3) DEFAULT '0',
  `saleupdate42392` tinyint(3) DEFAULT '0',
  `minpriceupdated` tinyint(1) DEFAULT '0',
  `saleupdate33219` tinyint(3) DEFAULT '0',
  `saleupdate32484` tinyint(3) DEFAULT '0',
  `saleupdate36586` tinyint(3) DEFAULT '0',
  `saleupdate53481` tinyint(3) DEFAULT '0',
  `saleupdate30424` tinyint(3) DEFAULT '0',
  `saleupdate` tinyint(3) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods`
--

TRUNCATE TABLE `suliss_shop_goods`;
--
-- 转存表中的数据 `suliss_shop_goods`
--

INSERT INTO `suliss_shop_goods` (`id`, `pcate`, `ccate`, `tcate`, `type`, `status`, `displayorder`, `title`, `thumb`, `unit`, `description`, `content`, `goodssn`, `productsn`, `productprice`, `marketprice`, `costprice`, `originalprice`, `total`, `totalcnf`, `sales`, `salesreal`, `spec`, `createtime`, `weight`, `credit`, `maxbuy`, `usermaxbuy`, `hasoption`, `dispatch`, `thumb_url`, `isnew`, `ishot`, `isdiscount`, `isdiscount_title`, `isdiscount_time`, `isdiscount_discounts`, `isrecommand`, `issendfree`, `istime`, `iscomment`, `timestart`, `timeend`, `viewcount`, `deleted`, `hascommission`, `commission1_rate`, `commission1_pay`, `commission2_rate`, `commission2_pay`, `commission3_rate`, `commission3_pay`, `commission`, `score`, `catch_id`, `catch_url`, `catch_source`, `updatetime`, `share_title`, `share_icon`, `cash`, `commission_thumb`, `isnodiscount`, `showlevels`, `buylevels`, `showgroups`, `buygroups`, `isverify`, `storeids`, `noticemid`, `noticetype`, `needfollow`, `followurl`, `followtip`, `deduct`, `shorttitle`, `virtual`, `ccates`, `discounts`, `nocommission`, `hidecommission`, `pcates`, `tcates`, `merchcates`, `merchpcate`, `merchccate`, `merchtcate`, `merchpcates`, `merchccates`, `merchtcates`, `detail_logo`, `detail_shopname`, `detail_totaltitle`, `detail_btntext1`, `detail_btnurl1`, `detail_btntext2`, `detail_btnurl2`, `cates`, `artid`, `deduct2`, `ednum`, `edareas`, `edmoney`, `dispatchtype`, `dispatchid`, `dispatchprice`, `manydeduct`, `saleupdate37975`, `shopid`, `allcates`, `minbuy`, `invoice`, `repair`, `seven`, `money`, `minprice`, `maxprice`, `province`, `city`, `buyshow`, `buycontent`, `saleupdate51117`, `virtualsend`, `virtualsendcontent`, `verifytype`, `quality`, `groupstype`, `showtotal`, `subtitle`, `sharebtn`, `merchid`, `checked`, `thumb_first`, `merchsale`, `keywords`, `labelname`, `autoreceive`, `cannotrefund`, `bargain`, `buyagain`, `buyagain_islong`, `buyagain_condition`, `buyagain_sale`, `buyagain_commission`, `buyagain_price`, `cashier`, `isendtime`, `usetime`, `endtime`, `merchdisplayorder`, `exchange_stock`, `exchange_postage`, `ispresell`, `presellprice`, `presellover`, `presellovertime`, `presellstart`, `preselltimestart`, `presellend`, `preselltimeend`, `presellsendtype`, `presellsendstatrttime`, `presellsendtime`, `edareas_code`, `unite_total`, `threen`, `catesinit3`, `showtotaladd`, `intervalfloor`, `intervalprice`, `isfullback`, `isstatustime`, `statustimestart`, `statustimeend`, `nosearch`, `showsales`, `nolive`, `islive`, `liveprice`, `opencard`, `cardid`, `verifygoodsnum`, `verifygoodsdays`, `verifygoodslimittype`, `verifygoodslimitdate`, `minliveprice`, `maxliveprice`, `dowpayment`, `tempid`, `isstoreprice`, `beforehours`, `newgoods`, `video`, `officthumb`, `verifygoodstype`, `isforceverifystore`, `taobaoid`, `taotaoid`, `taobaourl`, `saleupdate40170`, `saleupdate35843`, `saleupdate42392`, `minpriceupdated`, `saleupdate33219`, `saleupdate32484`, `saleupdate36586`, `saleupdate53481`, `saleupdate30424`, `saleupdate`) VALUES
(1, 56, 57, 0, 1, 1, 50, '猪莉·粉糖四件套', '/public/attachment/images/20181210/a7248fa0e77d87ae97df1d0421c4c282.png', '件', '', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><br/></p><div class=\"m-video\"><img width=\"368\" height=\"207\" src=\"http://yanxuan.nosdn.127.net/256edd2dc2f7a31dd3749b64185f1939.mp4?vframe=&offset=4\"/></div><p><img src=\"http://yanxuan.nosdn.127.net/16820848d1f35b95891390db41a6f441.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86aa7b8af448a73af12db8d72a0888a4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c093dfbbefdda8f8be22d3e66814036b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0514979a88b6568b7cd2786854ba7433.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/787942f1d037517b4aa38a5704b70157.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a511c17045b4bdbef7a71c63e99ab89c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d1b7eb4c6be547b02f01f910bcfe843b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/97c29035f04f3912faf1a18431904a00.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7132607e355988ee45e84717ecd59df8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/735c8c60fc8eb52d1e51b13c6f64659d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d96182593217e8682d6fe71da8d27538.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/92f844580d3c9fed8b82b555faddd352.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff4c685654a77eb90ed4b28354a296ec.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/25b39ec8810ce59a37f1ebfef3614cd9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6d03785c213fa5a0810840ba8b610e89.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/879562ba343069423d2af8868e434954.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/757519b2360a7c3559c7c362123315de.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/16beb1e8860da262b0159d4efd7dc1c6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/20997b29c36bf421f1b76050ab1682b9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dd148fa5add9262e6f2c7c16c77349ce.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e67391537a0f245d5573c3a98fcce41.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5b212c56e861c94bdeedf2859ea235a9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0dc2f69256a56ee9c78e158e73e2dc68.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/72ef3b69497d7ec6117c4fa6db643ed5.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dbbbcb469695ca5c27b5c35ce5f51327.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f846972b6762aebabfaa93364a2e4a0e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e7c6a2f2beb6d714ec1b4fb62bd801a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9448b5b4d70cdf42e321e5dd0f5a1354.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/36a4526682a780c7e318de5cca0ddc98.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/607461923e1b91686e1a7451f924cf1c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/01b08572e1f8f7c81f42a38cbecf7aec.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e5357004352d59408a261d3623fa6db4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d78e455496f2d8ce08ee2f6ffeeceef6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3a12f91febf3e6f0de82e1e36f1fd189.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b5ef9709cefe5959381190dd48336ed4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6834026aaef2523ae52dd5476ca97d98.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2c1b40d85e173410338ba7f8f78fdf6d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a6ff63ef52dc8d0367186228ec3ccd9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a3f1e7d723fb10a2027fea9a43ed708f.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f37d538720167a5101b416b92867d67.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9ef3d4891fe981fd7d987fa1bd4fed48.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a702909fb81a72d85678c1977c1bbd1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc786a1d1f991a02756146d1fd4912fb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d12be9800d784c803dafc8a6269567e0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0814bfc11474065a5a34597d24ef1d6c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/593121b3ffbcd3033fedb9324f2934fa.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fb92aa7d48846c49dc5abacff3db8507.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/51f17afd7ae4ab84faaae6e04a860633.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0b172a016ef7e62cae713115a40c6377.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3114a532144c56b358eedabe9adf075b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/09dad662042f0c10f25d38004cbde232.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a9567ad83db0f9479b7cb6a9b7f5d161.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/967516e341abd1e5042ecfdc635a8584.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5d8e3d999fb3bc2b0406ef0fb5db0182.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b10cc9445fb7f430a41849620bf8a94d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d50cfc8007b4bac348e349ffa78bc3e5.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9e8144ccf011f25ad5aca4b834a656fa.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/eca8925de9d08dade2ff4c4e69afed3b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5eefd98438ee25db11d84ebc5b3c944e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f8a8e6274e8fc30ac8d72790ccc1fb04.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/69c80e09576cccde24fa548736e90d99.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3716bfa778e38917bda1e1d771a31a1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f090ad22e36eed58d87b08ff6376ba6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/04e52187779512e7eebd5553af8cc630.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/bc8169363cc03d8e321e30bf193d35db.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87e2bbb2068497e5960771a67c4012d0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b1555b323f6f77e5dcedb5660a46ed71.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/deff2ea53fb20ba7073ac72f263f1025.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ea7ff17b74a0a9010b4039b999663cc4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2e9874d8d6eb1ee1d7b0e5637be6761e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/28ffaaad895070e0c74dcc31abc5378a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5fc15398a28963ae67ddb0972e0540e1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6405db227d95f2502d583918d30bb2de.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff38dcf37f037b8a174946f3d5444343.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d4d7b742127af373f246d7c3db8765a8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7d7c6be1d18b77fb60c9c0627b6116eb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87a25e7bc34e64ef38aab39ff7304b1d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a4245c80915fac0b8aae2d2bc0edb831.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/46ba74c1baf8846898df0884045270b4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c3a014e207444539905b3e25fe4d3817.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87745a079ea8b55a7b1ea16d0c2ba0e4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9669b70840b86214b5e3b4e326143bb2.jpg\"/></p><p><br/></p><p><img src=\"http://yanxuan.nosdn.127.net/92865bd46621f8bd67a0941d9afbb6e7.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7dc1acdcbde47355db9dfeab79c865d9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/af17ff61a9a8c11c4e2d184659b202d0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c74cf78fb57dd9419ec909e8e3fc0816.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e44114ac0cd4fb9726d6f43e9860bd1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aafb796942e075bc1396988fe06d224.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7371291b0dff48ab549cd276a84dbb05.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/713c4ea349d58a39f4834f639566acf8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/95a08f272d43bc786ef967ec396533dc.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/35ec176c9ed18509722b6e85efe5058e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/402db49f70055a3666e4c93b7458276a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e644bc967e23a5a768d3e7b2c22f06.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3ebbf721703f1a69478683bca79549c9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e1e62e6a11a35d62ca792c428102bb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3f0b880791cf9f24ac729392aafbf2da.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc94701399fc6e6fd47e8b6c17814cb0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9db1ce616709dfa2c6c2c342c42c1100.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/4e559839a63353c5a491098006dde274.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2be43ddc03362e9694c289ce40040a9f.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/512d538ebc7dca777ac6e18da5da2597.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a02239b356367f81ee6afed493432ca.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aa348b8268d41cc15c4b64129d83246.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6fc763ffcb97ce199a4b5a92e87729bd.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3dedff21454f674c28a26d9a99dbf18.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/cdb73a18d481d87ef38e1fb38b5a3477.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/58a942c72f49959c50fd7a24f34c685b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86d29819d87e9d7a766c59cb5d796db9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e88cd8d8486d4bd99351c2bdeffd151.jpg\"/></p><p><br/></p></div></div>', '', '', '399.00', '399.00', '350.00', '0.00', 1, 0, 0, 0, '', 1544454366, '0.00', '', 0, 0, 1, 0, 'a:1:{i:1;s:71:\"/public/attachment/images/20181210/48e12fc306ac0458f386522a152a23dc.jpg\";}', 1, 1, 0, '', 1544453820, '{\"type\":1,\"default\":{\"option146\":\"\",\"option147\":\"\",\"option148\":\"\",\"option149\":\"\",\"option150\":\"\",\"option151\":\"\",\"option152\":\"\",\"option153\":\"\"}}', 1, 0, 0, 0, 1544453820, 1545058620, 97, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', NULL, '', 0, '', '', '', '', 1, '', 0, '', 0, '', '', '0.00', '粉糖四件套', 0, '57', '{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}', 0, 0, '', '', '', 0, 0, 0, '', '', '', '', '', '', '', '', '', '', '57', 0, '0.00', 0, '', '0.00', 0, 0, '0.00', NULL, 0, 0, NULL, 0, 0, 0, 0, '', '0.00', '9.00', '云南省', '昆明市', 0, '', 0, 0, '', 0, 0, 1, 1, '少女粉糯贡缎，猪莉甜美相赠 猪莉·粉糖四件套', 0, 0, 0, 1, 0, '粉糖四件套', 'N;', 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 0, 0, 0, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 1544453820, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 1544453820, 1547132220, 0, 0, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(6, 0, 0, 0, 1, 1, 50, '星诞奇遇 圣诞树双层玻璃杯', '/public/attachment/images/20181211/34fc0d293ae63c2c8ac7b79043edf55f.png', '件', '', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><img src=\"http://yanxuan.nosdn.127.net/4750f861bff779695d9dea0d8e0bae4b.jpg\"/><br/></p><p><img src=\"http://yanxuan.nosdn.127.net/9ac6fbd4bf79ed71058367503ebeabee.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fde3abf1fa4a641dc4b6fcccd8e80754.gif\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/74d8178d9c339ed0b836443cfc562e7e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a38ac1036ef97038b68b44e84344c541.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dcbfba860f3edd9d374d80a571396d55.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8422bcc4715caff5d692bb8ccb697f5d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7af70de92e8cebb7f2e9aaafa4975e7e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8a7aa8b36e0943321fcb3eb36c5deac4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/29406c2b399a25fc59b4ff05f5d12446.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/628e3e1e1fec7a8b9b0058a88ba85c1b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2276b16cd2d2939c9744230b0b402ab3.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b0009aea5fc155fe7523a8dca96600a4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e43f2e542d3e5ca12c6ac2761217bd22.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b614fa62d9f14669c19d776b7c91a133.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e66a1c962a7fe4b94c315f2703d31d11.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/4c448ece3f606f36c047b068463681cf.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/48d522a0b24c34ace9199d80243f8cd3.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1b422e071a1946bd3003ba37d3e58c72.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/51951e7826d538e0bb818dbbbde618cc.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/21ba2de622a8f7b7e1fd6058564770cb.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ddcfc1be43e1f84da61fae163aa3065f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5d9afc8dc0cdf5a3916b28043864b629.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/898ca6c5fbf74f44583ba8bab8f99014.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a330e269264fcc778e9b30ff9593878.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/36316e81a172e34d41b2dc8911af357c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/12422c795afcdf3b64632cc198bf35aa.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/20bd8cda2f8893ae44adc94bdccb225b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a7df94e2f8a2f461c83faca28de2a8bd.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ec7579aebb20ca36d6b4db112e32739a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/32b79c9b94358488fb4591230aa62c2a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fb95c5e7e2bdddac120120dace9e2a6e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0b6650b1fcda218cad9a47403feff8e9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/28a3b912cdeee7a883f9b16507e99d65.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/578cbb9c776a6ba920083d1c37696bb9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f3b41752df4b89cd4e6165dc68113dfd.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ca6dc1da683cf271eca8ec9c691a3cdb.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/15138c9255c997ed4819462a65c868df.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/22b219f31e34338eb01e95435796e973.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a61a2fd877931906bee2c1e991ca7c16.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7e9661273900ac56d23db92c4db81934.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6c07307206a2f89c1ba652661337bb2c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/24362d3c5ba68903fe1cb53d392dbd2c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/aa6f3e760f203a673e5c09bb0925f425.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/23e2f8e6acb8ef79ab11c83b9abd215a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3d93a51fbb3146c5e0f71b3966c75ef9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ed15036f881fd07ae87a2c0459a19f4a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/af2d75dd4754819eabe439d616178ce2.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1a5fbaf7dae7e5ec9fdb139a3577c69e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/12a9a335fade142543ed9cde5601e4d6.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7901dadb6a69d26383e8b95beb8eb77a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5f86f2649da87d1c34fb934679d6e15f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5880c2f9e0ff24b69e2e9552752f29da.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/78dc514ff420b87649e20e629d8b644f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/95533b0636f24d2e86603c83cd4aef71.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a1a447de24cf21f2c1c4708fd572ea2d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c7007c8a04b1aa19ef11a2a9494adae0.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d891195d44578db1a830e1ffc77a3e01.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e0554000709d06d6eb390879fbd87c72.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2acf658e49c4952419ddadc67a338f41.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/abbdb4f691bedff4a7383be2a9557a8a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9da312a1471356c323e1c2f97b84573e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7e5d989a470cd89a408107660b676647.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/4e0d77135c97c70b71dff5f6eff70e4c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e0198972b669bea214b22e453c66111d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f02ea71f8cf434fa64cbd0336980d814.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0d1e0118a68e2031aa56128e1418506a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a77e75feae6834afea838ab7815cc3c6.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f9067e9fa4d1160a27527d774abcf98d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/67d71c4f6560b498dd06edf2880c079f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/89b94270e6c3ef7a1cc369fa09789d15.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2df7ea708b51b6510112ee25026f79dd.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/244a2035000bf84a1040884439a33199.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ce75fe734109c360ae19a6c20299ca88.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/bf3905bbfedb1c9b185bcffdbbc2098f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6fefcea3f14ab9c3a09c516192d967f6.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9b5c38d9250a4738bbb65206eb82ea45.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fa0027e354fc3e11e83a16e9198cb22f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/64d4acaafa150dac52a502f7da993c43.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/06513d58f2d41a6b00ade77ba73a0445.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/74a816c18a8bfa13b9c0739be0652f3e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/932499130dbf01ca623a9e6d4c34bf68.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ae5480b87b0614c59699d06410086040.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c1fcfff8895f542d9d4d30c85cadd33c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f59fe8f16bbd6534c84a374863c1832b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c58adb551592fe0ca94a6fd0dab0c7de.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c071c0cba5ea03824ee1024367d84cf7.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/071ab6616e9f29122aa70bb3d5f14575.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/24f81709b4d14d1e1b2778caf4bf4778.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/20d66d9019bd3457ff0dbbc183f640e8.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/acbc7a66a260567913667c5c3d25dced.jpg\" style=\"\"/></p><p><span style=\"background-color: rgb(255, 255, 255); color: rgb(51, 51, 51); font-family: \"Microsoft Yahei\", 微软雅黑, verdana; font-size: 14px;\">*** 特别说明&nbsp;</span></p><p><span class=\"value\" data-reactid=\".2.1.2.0.1.1.$attr_2.1\" style=\"float: left; color: rgb(153, 153, 153); font-family: \"Microsoft Yahei\", 微软雅黑, verdana; font-size: 14px; background-color: rgb(255, 255, 255);\">1、双层杯底部小凹点是“防爆孔”，工艺性安全设计，并非商品瑕疵，请放心选购。<br/>2、为什么需要“防爆孔”？原因是内外杯结构之间存有一定的空气量和压力差，通过设置防爆孔和紫外线胶工艺处理，</span><span class=\"value\" data-reactid=\".2.1.2.0.1.1.$attr_2.1\" style=\"float: left; color: rgb(153, 153, 153); font-family: \"Microsoft Yahei\", 微软雅黑, verdana; font-size: 14px; background-color: rgb(255, 255, 255);\">实现防水和“呼吸”双重效果，不易脱落，确保内外杯压力平衡和使用安全。</span></p></div></div>', '', '', '110.00', '109.00', '100.00', '0.00', 99, 0, 0, 0, '', 1544507845, '0.00', '', 1, 0, 1, 0, 'a:4:{i:1;s:71:\"/public/attachment/images/20181211/4661c0c71c10628bb466e17d71636e98.jpg\";i:2;s:71:\"/public/attachment/images/20181211/89fb275227da439ee1f46334faccd8ac.jpg\";i:3;s:71:\"/public/attachment/images/20181211/4aef0ebce8ffbfd03792375bc368d39c.jpg\";i:4;s:71:\"/public/attachment/images/20181211/17930f5af05e354ff005519a90195ca8.jpg\";}', 1, 1, 0, '', 1544507460, '{\"type\":1,\"default\":{\"option145\":\"\"}}', 1, 0, 0, 0, 1544507460, 1545112260, 51, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', 0, '', 0, '', '', '', '', 2, '1', 0, '', 0, '', '', '0.00', '双层玻璃杯', 0, '', '{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}', 0, 0, '', '', '', 0, 0, 0, '', '', '', '', '', '', '', '', '', '', '81', 0, '0.00', 0, '', '0.00', 0, 0, '0.00', NULL, 0, 0, NULL, 0, 0, 1, 1, '', '109.00', '109.00', '云南省', '昆明市', 0, '', 0, 0, '', 0, 1, 0, 0, '被雪淹没的圣诞树，闪着一颗星星 星诞奇遇 圣诞树双层玻璃杯', 0, 0, 0, 0, 0, '双层玻璃杯', 'N;', 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 7, 0, 0, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 1544507460, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 1544507460, 1547185860, 0, 0, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(3, 56, 57, 60, 1, 1, 200, '唤自然·青林麋鹿抱枕套', '/public/attachment/images/20181211/d4a6e979a4a38f2df54930c8946a82ad.png', '个', '', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><img src=\"http://yanxuan.nosdn.127.net/d6e0dd0bade116716536965b2bece09e.jpg\"/><br/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/2a3d41857218b979e36ec84532de9ce7.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/633bb81502b152b179312bd644219243.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/2627be6b1cbe093feedc9b0d5c32737a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/65691bd56d5c9259ef37ea959ac37ac1.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1085555f2e02bd1763d6e4850cfdca4a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/870ce25327ba55bb074a773c13a3da61.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/43df5f126738f52b7fc38906d9fec8bf.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6798e206e8ffc0e0bda0178c14940b5b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ad82f73d7218af560bba41072eb72e67.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f9fbb60074ea31df9d83613aede8322b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/24b7f0b28ac2229adb90b4d815b51b43.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/73176d95fdd4f1fa661a801287247288.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/9d2c90ad5e3c009d716e9d1ab7211453.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/60033e8cf031b9f1b68aa465fddcfaaa.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/eeb81631bcf2136b2c7ea3aa30dc30c2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/00a647513023105e0c6e17c8655b0736.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a8a690748f438ab21ebc7ac17e7d9af3.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/30272cb9c35b5853d4c6065faa30dd2a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/924dc3e8d91a4b5e53247067e239ead3.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/28c57f9fb107c1e6565ea579390de8e2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/8fb7bb65095560c291b484481c08bd90.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/da859b6103c4da355139a5bcc6e18a6a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d7a551960db9e3f09a3d137848938aa4.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/e90940fb047d7b745dd7d3fdfb3b11a9.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7adac871370c664237159a962bfb31d2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a0004d373833cb67897e7a4c1effc884.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/963014f47da2673db8dae84ef637146f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/909a155fda0c4ef11e2c03a15358fc22.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/fc7da54d34677087ea2ff8dec64920c0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/fa9c722e2e9bced9fcdf936db7034980.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4b3923e9435a8dfcb23233f0c76a4d99.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4455b70c48f0946595abc3965c331654.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a502737e5a32c0e03b378d60bfaa2ced.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/10f74d54bd674a3e65b57e8d634a8337.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6a3b9d4f77ad1bd5cb6aec37209cb9d7.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/2078f92beb6253785f4d5768646ea021.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/47400c9fa97d790e101ecb6b44211b90.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6e27506647edeed5cf48100e05983c72.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1cbaf3a7e5827283824fbfdfdba5db30.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ecc3951314bfca738326391662e8ba57.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/e5f5d9e0917e90636533bcde2024a024.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/8feb906d5e6a76ea2097756714572f3e.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4685935f869485bf258b0e1b42634aff.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f61df10ab6221e9a94763bac7dc47d84.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/12761ec7eca2389c74cd7695b9210927.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/e9c8047731912cd60495a15049997456.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b8de1dcd6b70098c424cf084312d8541.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/172c5c678672790f026c4dff953a053b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/cf9c4e157b179209e9d95a27d4065a3e.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ebd825d892baa61bb394e03abd7b08b9.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/9f566f87d041d5e25bb6b3733a53d5e5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/dc37fb97a5cab8426201d35213703f6e.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/cb3ecc003e3ff65da3c8e89d894458b1.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/3928a311ecdd3768f75900e5546e585c.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b1d956ac2e0c52feb9e07ffa7159b3a1.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b4b239abcd22c288373e54696d0f8118.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/321c245a6f4fb165bf6bf23719bdf809.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/71426ca75e3bedf6b27a3e836bdd4713.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/502d68b9870374ddf3d1b094b6b141ef.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1ec26238b6c1350dfb1c0ce6e495ad09.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/728dd8a3fca31318edff1251f3cd30d3.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/94262e7d6504deb54b0d2b1688f6efa1.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ca10b750e580fca846ba3168e0f81681.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/473c10149133579c272253b8209746cb.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5359df62f5cfd62c4aa9f715a336b279.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/fbde846d1efaa21829ea86fede238890.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c2ccb9892f6a99dd00f5720b32c81c9d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ec6e6f24184b441ec29045271801af7f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d2b85b32672a205235ccede32a2f4593.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/0de5b525de0b23e63b8875b8eaa5e880.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5b9e4266f42ffbbb1bfd593e5902528f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ba8170af319b5ba8717bd75d083cec0c.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5e9640da4b9576a3afc85a2f214740e0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/93b7b815bfc2b5eb3c34d878ca0ef781.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/0c98e5c5bcb47f1b800c8852c0812a80.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1e2d5815ea94bb07092fc4920612d868.jpg\"/></p></div></div>', '', '', '0.01', '0.01', '0.00', '0.00', 599, 0, 0, 0, '', 0, '0.00', '', 0, 0, 1, 0, 'a:4:{i:1;s:71:\"/public/attachment/images/20181211/572729969cb74252a80f538a14f48950.jpg\";i:2;s:71:\"/public/attachment/images/20181211/77da1550f6cf5bfd5704a6e8fb560ccc.jpg\";i:3;s:71:\"/public/attachment/images/20181211/1afb55c9a4002118aafaa066f49993d1.jpg\";i:4;s:71:\"/public/attachment/images/20181211/fdcc9528fa67849b82d96cea9e23d71c.jpg\";}', 0, 0, 0, '', 1544505120, '{\"type\":1,\"default\":{\"option139\":\"\",\"option140\":\"\",\"option141\":\"\",\"option142\":\"\",\"option143\":\"\",\"option144\":\"\"},\"merch\":{\"option139\":null,\"option140\":null,\"option141\":null,\"option142\":null,\"option143\":null,\"option144\":null}}', 0, 0, 0, 0, 1548059580, 1548664380, 51, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', '{\"type\":0}', '0.00', '', '', '', 0, '', '', NULL, '', 1, '', '', '', '', 1, '', 0, '', 0, '', '', '0.00', '', 0, '', '{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}', 0, 0, '', '60', '', 0, 0, 0, '', '', '', '', '', '', '', '', '', '', '60', 0, '-1.00', 0, '', '0.00', 0, 0, '0.00', NULL, 0, 0, NULL, 0, 0, 0, 1, '', '0.01', '0.01', '请选择省份', '请选择城市', 1, '', 0, 0, '', 0, 1, 0, 0, '手绘自然风，品质绒面，装点高级家居 唤自然·青林麋鹿抱枕套', 0, 1, 0, 1, 1, '麋鹿抱枕套', 'N;', 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 0, 0, 50, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 1548059580, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 1548059580, 1550737980, 0, 1, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(4, 0, 0, 0, 2, 1, 50, '魔兽世界点卡30天月卡/90天季卡', '/public/attachment/images/20181211/17c4a060966d6d82e4e83c12c0be2457.png', '件', '', '<div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/7748ecc7d64004054aaab5125a31e803.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/9e042c4420692da14b05cf94a2b7eda4.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/0947f74518e2788aa788da8182f4618e.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/72cf2e668d0eab6d626c91e5dac35cc4.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/a546e33f62feceda16ae7057cddc3f70.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/59f09dbaa1abd49b9eecb990edf0d7c1.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/a186d88de21abacdcdc8b1bc4f9d49f0.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/0c626f56a051cd19ad3c81586f6ccaa0.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/3276dacff27fa82294f6fb4f892754de.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/4c7b872d6aea31039ec47bd67c0748c4.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/1698ab26a5f744a00af64547af9d8ac7.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/18d9c030f79e97b002c4ceb583ad8876.jpg\"/></p><p style=\"white-space: normal;\"><img src=\"http://yanxuan.nosdn.127.net/8e705b849ced824eaf5901119c7315c1.jpg\"/></p></div><p><br/></p>', '', '', '75.00', '75.00', '75.00', '0.00', 100, 0, 0, 0, '', 1544506546, '0.00', '', 0, 0, 0, 0, 'a:0:{}', 1, 1, 0, '', 1544506380, '{\"type\":0,\"default\":{\"option0\":\"\"}}', 1, 0, 0, 0, 1544506380, 1545111180, 19, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', 0, '', 0, '0', '0', '0', '', 1, '', 0, '', 0, '', '', '0.00', '魔兽世界点卡', 0, '', '{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}', 0, 0, '', '', '', 0, 0, 0, '', '', '', '', '', '', '', '', '', '', '82', 0, '0.00', 0, '', '0.00', 0, 0, '0.00', NULL, 0, 0, NULL, 0, 0, 0, 0, '', '75.00', '75.00', '云南省', '昆明市', 0, '', 0, 1, '', 0, 0, 0, 0, '官方品质，安全快速优惠 魔兽世界点卡30天月卡/90天季卡', 0, 0, 0, 1, 0, '魔兽世界点卡', 'N;', 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 0, 0, 0, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 1544506380, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 1544506380, 1547184780, 0, 0, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(5, 0, 0, 0, 2, 1, 100, '魔兽世界点卡30天月卡/90天季卡', '/public/attachment/images/20181211/17c4a060966d6d82e4e83c12c0be2457.png', '件', '', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><img src=\"http://yanxuan.nosdn.127.net/7748ecc7d64004054aaab5125a31e803.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9e042c4420692da14b05cf94a2b7eda4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0947f74518e2788aa788da8182f4618e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/72cf2e668d0eab6d626c91e5dac35cc4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a546e33f62feceda16ae7057cddc3f70.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/59f09dbaa1abd49b9eecb990edf0d7c1.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a186d88de21abacdcdc8b1bc4f9d49f0.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0c626f56a051cd19ad3c81586f6ccaa0.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3276dacff27fa82294f6fb4f892754de.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/4c7b872d6aea31039ec47bd67c0748c4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1698ab26a5f744a00af64547af9d8ac7.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/18d9c030f79e97b002c4ceb583ad8876.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8e705b849ced824eaf5901119c7315c1.jpg\" style=\"\"/></p><p><br/></p></div></div>', '', '', '75.00', '75.00', '0.00', '0.00', 100, 0, 0, 0, '', 0, '0.00', '', 0, 0, 0, 0, 'a:0:{}', 1, 0, 0, '', 1544506200, '{\"type\":0,\"merch\":{\"option0\":\"\"}}', 0, 0, 0, 0, 0, 0, 19, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', 0, '', 1, NULL, NULL, NULL, NULL, 1, '', 0, '', 0, '', '', '0.00', '', 0, '', NULL, 0, 0, '', '', '20,25,26', 20, 0, 0, '20', '25', '26', '', '', '', '', '', '', '', '20,25,26', 0, '-1.00', 0, '', '0.00', 0, 0, '0.00', 0, 0, 0, NULL, 0, 0, 0, 0, '', '75.00', '75.00', '请选择省份', '请选择城市', 0, '', 0, 1, '', 0, 0, 0, 0, '官方品质，安全快速优惠 魔兽世界点卡30天月卡/90天季卡', 0, 1, 0, 1, 1, '魔兽世界点卡', NULL, 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 0, 0, 50, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 0, 0, 0, 1, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `suliss_shop_goods` (`id`, `pcate`, `ccate`, `tcate`, `type`, `status`, `displayorder`, `title`, `thumb`, `unit`, `description`, `content`, `goodssn`, `productsn`, `productprice`, `marketprice`, `costprice`, `originalprice`, `total`, `totalcnf`, `sales`, `salesreal`, `spec`, `createtime`, `weight`, `credit`, `maxbuy`, `usermaxbuy`, `hasoption`, `dispatch`, `thumb_url`, `isnew`, `ishot`, `isdiscount`, `isdiscount_title`, `isdiscount_time`, `isdiscount_discounts`, `isrecommand`, `issendfree`, `istime`, `iscomment`, `timestart`, `timeend`, `viewcount`, `deleted`, `hascommission`, `commission1_rate`, `commission1_pay`, `commission2_rate`, `commission2_pay`, `commission3_rate`, `commission3_pay`, `commission`, `score`, `catch_id`, `catch_url`, `catch_source`, `updatetime`, `share_title`, `share_icon`, `cash`, `commission_thumb`, `isnodiscount`, `showlevels`, `buylevels`, `showgroups`, `buygroups`, `isverify`, `storeids`, `noticemid`, `noticetype`, `needfollow`, `followurl`, `followtip`, `deduct`, `shorttitle`, `virtual`, `ccates`, `discounts`, `nocommission`, `hidecommission`, `pcates`, `tcates`, `merchcates`, `merchpcate`, `merchccate`, `merchtcate`, `merchpcates`, `merchccates`, `merchtcates`, `detail_logo`, `detail_shopname`, `detail_totaltitle`, `detail_btntext1`, `detail_btnurl1`, `detail_btntext2`, `detail_btnurl2`, `cates`, `artid`, `deduct2`, `ednum`, `edareas`, `edmoney`, `dispatchtype`, `dispatchid`, `dispatchprice`, `manydeduct`, `saleupdate37975`, `shopid`, `allcates`, `minbuy`, `invoice`, `repair`, `seven`, `money`, `minprice`, `maxprice`, `province`, `city`, `buyshow`, `buycontent`, `saleupdate51117`, `virtualsend`, `virtualsendcontent`, `verifytype`, `quality`, `groupstype`, `showtotal`, `subtitle`, `sharebtn`, `merchid`, `checked`, `thumb_first`, `merchsale`, `keywords`, `labelname`, `autoreceive`, `cannotrefund`, `bargain`, `buyagain`, `buyagain_islong`, `buyagain_condition`, `buyagain_sale`, `buyagain_commission`, `buyagain_price`, `cashier`, `isendtime`, `usetime`, `endtime`, `merchdisplayorder`, `exchange_stock`, `exchange_postage`, `ispresell`, `presellprice`, `presellover`, `presellovertime`, `presellstart`, `preselltimestart`, `presellend`, `preselltimeend`, `presellsendtype`, `presellsendstatrttime`, `presellsendtime`, `edareas_code`, `unite_total`, `threen`, `catesinit3`, `showtotaladd`, `intervalfloor`, `intervalprice`, `isfullback`, `isstatustime`, `statustimestart`, `statustimeend`, `nosearch`, `showsales`, `nolive`, `islive`, `liveprice`, `opencard`, `cardid`, `verifygoodsnum`, `verifygoodsdays`, `verifygoodslimittype`, `verifygoodslimitdate`, `minliveprice`, `maxliveprice`, `dowpayment`, `tempid`, `isstoreprice`, `beforehours`, `newgoods`, `video`, `officthumb`, `verifygoodstype`, `isforceverifystore`, `taobaoid`, `taotaoid`, `taobaourl`, `saleupdate40170`, `saleupdate35843`, `saleupdate42392`, `minpriceupdated`, `saleupdate33219`, `saleupdate32484`, `saleupdate36586`, `saleupdate53481`, `saleupdate30424`, `saleupdate`) VALUES
(2, 0, 0, 0, 1, 1, 101, '猪莉·纳福抱枕', '/public/attachment/images/20181210/d1e5c9ac33b83d0f6068402e6b82fe8e.jpg', '个', '', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><br/></p><div class=\"m-video\"><img src=\"http://yanxuan.nosdn.127.net/388c3f6c4fa389795ec07d55c2b7a174.mp4?vframe=&offset=4\" width=\"368\" height=\"207\"/></div><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a7125f682ab75d225eb84b5aadc54e49.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/486d95c65b0fe1de6730f51dff9e914e.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d77b17514cf3da37db43cce3b68f7611.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c0d0957905d0b9e4730cbbfb8ed78028.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1c67419bdecdb4690f1bc9ef8c7b4b43.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b7c65123aad7edc697cdc47fdeb1b18d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/207682157908ab95394695aaac7009fb.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1a86d59f4d322bbc98bf63e2a374b69d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ec200e56bd86fd3a8ad11a7b80e468c5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4a688a941b252435cc5a39bb81a6552f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/776f6bc6edfb1f31cc960c14b0e7126f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/80038705ab41b35a799cdff72b00c899.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ebc2558282a87dd631ba8682f374ce97.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/9c96edf6b4397ea2ae841ba0f5495285.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c73405ae174283cccda7db7af8f2be06.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ac608679d3afaa02d779851a38cec645.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/de81f14baaeac87788876caf483af639.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/62b0bb71c22699fa09f44e4bf05117fe.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/dc4f0fddb04f9bcd0fbf1a64a9908f5f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/376d03685f83d33add32929369d83019.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/2dee55ef74b03e6a5d969d9e0fe5689f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5ca571082e220f0ff8c77fd75274e7f8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6a79ba401d07ee213151dcb76cf9c4bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4d7a5a1d06e4d98030456ef0eeeb69c0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b0686f435805d7e35bfca58866f7b1bc.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/01a985375f263101e003998b537c9b85.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a656883b2b5633755ba3793176cb97bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f07d872a96d319b695dfc82a1f8a8a21.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/307f8a98530efe11fbae97f2743c13d2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ccfdca01378496c3a3c2f2e67e6efc5c.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a6fa36e91d1c49d9791130de11b59ad8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/aaa4f018e26405747df41447b47ac665.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b1fd6be0cae7b9286870313d95dd159b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/70a5eea8c8382f4408d7e1a399798fa8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/be305ea1c529abcca266b9b564a1b66a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/90cbe7a4e0cc4bad7d39d2ed1409f086.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ba717068f213bb2c8221b1008d2912c5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6daf3117a1dc9ce41bcdf91d5dfeeda9.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4b1980888e569e2624e8663462bc61f8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/06ba853dc48a9da173161caa813c12bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4df8afaa3fc51f322af46d5faf90658f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b3adf6a13f81c644032d54d0a1808b71.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/9ad387742ee612eb73e851244ba95f6d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/59948a05277d171ec567fe3c0ccefda2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/8b184d85b332b08f3a0760d1f7cc85bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/aa483f0c1bb20529e3629f0d5ad288ab.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ae4cdaa6a264113f5e120228c8f163d0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/79d9ba6a304a6b9d17199032a36481ca.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/689778a147e0a17ae88a6b747501ca8b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/2b2726e273fd72a5e8dd18ae4f97415f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/33fe8f92077387ed999462531fbf8bf4.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d3212d4f9a7c0db9d41859a945bd8e9e.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5cc1af861ff3d67358d08a75086c20af.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5508570fdb510989d78acf5787d31ed2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/925f574104b0a15288eebeb7a1b8da03.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/e9593e38c8f12140032f023a5208d14f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/8f1883cf919585c4d299b29b6a2b30f5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/98abd745f898e999fec1aebd48a716fb.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/379449cef4806577dc3d5fb3effdc56c.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/270816a1b0be3c472d7cf8ab00c9b9b2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/cee3e84a9b7aa5722d6cccc5ce2db3e0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4ab8ea173fce5d5d95cc89b2d249e307.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/999c5767e34bb822a3edec12929714ae.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f0084f7d981f99f261b6b88f4b78c9a3.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d9fc70c5dda373375a8e9b6fa6680b20.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d5c4a1094241b5a7122aa8d320472f14.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/314ed5a55ffd3c69be486f45c09c2924.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/df75b1b0b212e97747d783b48e7910c6.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6c77ad9af74f652cc0add8379e7eccfc.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6f00b35cd555c1ce22126721c0b80dcf.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c6c63c2cd1a8e37e5bb2dc426d7a839b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/01fa2b4396d0b8103feeb8423a9db9f5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/98bd2652710ef6bf269e30d475e8dc78.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/602cc1a9ed5806ef4964bfad4c2b37aa.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ea3fd6fd1b43e81ebf724be6198fa434.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d7c696b580441199a0c7eef503434a87.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/368cf2cbd11f62408d006f1bc5406951.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d49fbfaaf92b39f047efb210b4fac8b6.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/384a2617bc2c52a31bfb396b597f9b70.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/70ea3c2a12ad61b820bc1f81e356d18d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/170e0c5c340142acceebcd851882cf0a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/3af9a87abbd05808121802f0e8ba39ed.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f024b7222e3a779e1ccab282933d690a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/eafb40aabe9eff3ebaf5d3190ba4fc7b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/15c314b4a5d7cec882c476c27f26a413.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/abee5be1233cac8977faa73417d1f151.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7687c153f4b59fad22f2548a8b2ebfec.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c3795bd0735057709948cdbc2306a9c0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/25ce962b593d585589e9fb7e06e444e0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5489d56f652a7321825011cbda9fee94.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7357b4d61af19063beac20aca19a2ccb.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/885c3c8022acccbd2adaeb22bb10ccf2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/01f0dd3305efdb9fe1b79d34359a59d0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7a1b4f3f03a93269c7df0292d9a90fd6.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b3327807a2b9593c40354ebf83f1acc9.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7173e5406b279bb97aee23a14d71e116.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/79901c7f0007d669f574867ccc95ed05.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d0c2f083998f94b4d83f724d163238ef.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/aa01347f27784aa501f2ca916f02e548.jpg\"/></p><p>‍<br/></p><p><br/></p></div></div>', '', '', '69.00', '69.00', '0.00', '0.00', 227, 0, 0, 0, '', 0, '0.00', '', 0, 0, 1, 0, 'a:1:{i:1;s:71:\"/public/attachment/images/20181210/5bdb279bcc2d61e3a0ea31f851127fdd.jpg\";}', 1, 0, 0, '', 1544454660, '{\"type\":1,\"default\":{\"option137\":\"\",\"option138\":\"\"},\"merch\":{\"option137\":null,\"option138\":null}}', 0, 0, 0, 0, 1544603700, 1545208500, 89, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', 0, '', 1, '', '', '', '', 2, '1,2', 0, '', 0, '', '', '0.00', '', 0, '', '{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}', 0, 0, '', '', '', 0, 0, 0, '', '', '', '', '', '', '', '', '', '', '', 0, '-1.00', 0, '', '0.00', 0, 0, '0.00', NULL, 0, 0, NULL, 0, 0, 0, 0, '', '0.02', '0.02', '请选择省份', '请选择城市', 0, '', 0, 0, '', 0, 0, 0, 0, '手感绵软讨喜，祝福好运喜乐 猪莉·纳福抱枕', 0, 0, 0, 1, 1, '抱枕', 'N;', 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 6, 0, 50, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 1544603700, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 1544603700, 1547282100, 0, 1, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(7, 77, 84, 0, 1, 1, 100, '男式轻薄羽绒背心', '/public/attachment/images/20181211/968d6a770a20027384acf5e6b6fcce33.jpg', '', '', '<p>·1321·321</p>', '122', '1222', '900.00', '599.00', '100.00', '0.00', 112222, 0, 0, 0, '', 1544518324, '1.00', '', 0, 0, 0, 0, 'a:3:{i:1;s:71:\"/public/attachment/images/20181211/4a1ba0c8b57d94cb6a90bf23f4038391.jpg\";i:2;s:71:\"/public/attachment/images/20181211/b9d99bfae1966d0857fbd5969112aa04.jpg\";i:3;s:71:\"/public/attachment/images/20181211/db11f9d60cddb10f53ad8dfc57624771.jpg\";}', 1, 1, 0, '', 1544518680, '{\"type\":0,\"default\":{\"option0\":\"\"}}', 1, 0, 0, 0, 1544518680, 1545123480, 30, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', 2, '', 0, '', '', '', '', 1, '', 0, '', 0, '', '', '0.00', '·223213', 0, '84', '{\"type\":\"0\",\"default\":\"\",\"default_pay\":\"\"}', 0, 0, '', '', '', 0, 0, 0, '', '', '', '', '', '', '', '', '', '', '84', 0, '0.00', 0, '', '0.00', 1, 0, '0.95', NULL, 0, 0, NULL, 0, 1, 0, 0, '', '599.00', '599.00', '云南省', '昆明市', 0, '', 0, 0, '', 0, 0, 1, 0, '21为1213为1·', 0, 0, 0, 1, 0, '男式轻薄羽绒背心', 'N;', 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 1, 0, 0, 0, 0, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 1544518680, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 1544518680, 1547197080, 0, 1, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(8, 0, 0, 0, 1, 1, 0, '奶萃咖啡包(袋泡咖啡)', '/public/attachment/images/20181211/0502e29487e395352e46a5aa11a003dc.jpg', '', '', '', '', '', '60.00', '50.00', '0.00', '0.00', 0, 0, 0, 0, '', 0, '0.00', '', 0, 0, 0, 0, 'a:2:{i:1;s:71:\"/public/attachment/images/20181211/563239374c718feec35d607f51cf6d2c.jpg\";i:2;s:71:\"/public/attachment/images/20181211/2170cd08da16214039da089542451e65.png\";}', 1, 0, 0, '', 1544521200, '{\"type\":0,\"merch\":{\"option0\":\"\"}}', 0, 0, 0, 0, 0, 0, 10, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', 2, '', 1, NULL, NULL, NULL, NULL, 1, '', 0, '', 0, '', '', '0.00', '', 0, NULL, NULL, 0, 0, NULL, NULL, '', 0, 0, 0, '', '', '', '', '', '', '', '', '', '', NULL, 0, '-1.00', 0, '', '0.00', 0, 0, '0.00', 0, 0, 0, NULL, 0, 0, 0, 0, '', '50.00', '50.00', '请选择省份', '请选择城市', 0, '', 0, 0, '', 0, 0, 0, 0, '奶萃咖啡包(袋泡咖啡)', 0, 1, 1, 1, 1, '奶萃咖啡包(袋泡咖啡)', NULL, 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 0, 0, 111, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 0, 0, 0, 1, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(9, 0, 0, 0, 1, 1, 0, '网易智造车载磁吸无线充支架', '/public/attachment/images/20181211/37e180d0c1d741616bf15746cc537009.png', '', '', '<p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/0502e29487e395352e46a5aa11a003dc.jpg\" width=\"100%\" style=\"\"/></p><p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/563239374c718feec35d607f51cf6d2c.jpg\" width=\"100%\" style=\"\"/></p><p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/2170cd08da16214039da089542451e65.png\" width=\"100%\" style=\"\"/></p><p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/f119cc6a8c51994e67a0fa9eb978103c.jpg\" width=\"100%\" style=\"\"/></p><p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/4a1ba0c8b57d94cb6a90bf23f4038391.jpg\" width=\"100%\" style=\"\"/></p><p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/b9d99bfae1966d0857fbd5969112aa04.jpg\" width=\"100%\" style=\"\"/></p><p><img src=\"http://test.doncheng.cn/public/attachment/images/20181211/db11f9d60cddb10f53ad8dfc57624771.jpg\" width=\"100%\" style=\"\"/></p><p><br/></p>', '', '', '50.00', '90.00', '0.00', '0.00', 0, 0, 22, 0, '', 0, '0.00', '', 0, 0, 0, 0, 'a:5:{i:1;s:71:\"/public/attachment/images/20181211/793d53030e9e6d995d072c3cb7041989.png\";i:2;s:71:\"/public/attachment/images/20181211/977186ee7963ca86b263dd66accc26de.png\";i:3;s:71:\"/public/attachment/images/20181211/afe7a00da2855a42d2d53354b7f534ff.png\";i:4;s:71:\"/public/attachment/images/20181211/d40fb01778cc89e8a73a5b5461c6dd62.png\";i:5;s:71:\"/public/attachment/images/20181211/43a2f592926df38a39490821a7c1f1cd.png\";}', 1, 0, 0, '', 1544522580, '{\"type\":0,\"merch\":{\"option0\":\"\"}}', 0, 0, 0, 0, 0, 0, 19, 0, 0, '0.00', '0.00', '0.00', '0.00', '0.00', '0.00', NULL, '0.00', '', '', '', 0, '', '', 2, '', 1, NULL, NULL, NULL, NULL, 1, '', 0, '', 0, '', '', '0.00', '', 0, NULL, NULL, 0, 0, NULL, NULL, '38', 34, 38, 0, '', '38', '', '', '', '', '', '', '', '', NULL, 0, '-1.00', 0, '', '0.00', 0, 0, '0.00', 0, 0, 0, NULL, 0, 0, 0, 0, '', '90.00', '90.00', '请选择省份', '请选择城市', 0, '', 0, 0, '', 0, 0, 0, 0, '网易智造车载磁吸无线充支架', 0, 1, 1, 1, 1, '网易智造车载磁吸无线充支架', NULL, 0, 0, 0, '0.00', 0, 0, 0, NULL, '0.00', 0, 0, 0, 0, 13, 0, '0.00', 0, '0.00', 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 0, '', NULL, 0, 0, '', 0, 0, 0, 0, 0, 1, 0, 0, '0.00', 0, '', 1, 1, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, 0, '', '', 0, 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_category`
--

CREATE TABLE `suliss_shop_goods_category` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `parentid` int(11) DEFAULT '0',
  `isrecommand` int(10) DEFAULT '0',
  `description` varchar(500) DEFAULT '',
  `displayorder` tinyint(3) UNSIGNED DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `ishome` tinyint(3) DEFAULT '0',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `level` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_category`
--

TRUNCATE TABLE `suliss_shop_goods_category`;
--
-- 转存表中的数据 `suliss_shop_goods_category`
--

INSERT INTO `suliss_shop_goods_category` (`id`, `name`, `thumb`, `parentid`, `isrecommand`, `description`, `displayorder`, `enabled`, `ishome`, `advimg`, `advurl`, `level`) VALUES
(56, '居家', '', 0, 0, '', 50, 1, 0, '', '', 1),
(57, '床品', '', 56, 1, '', 50, 1, 1, '', '', 2),
(58, '家居家装', '', 56, 1, '', 50, 1, 1, '', '', 2),
(59, '收纳', '', 56, 1, '', 50, 1, 1, '', '', 2),
(60, '床品件套', '/public/attachment/images/20190723/c32cb2659ec75fb1f6ecaef4ec20cfc8.png', 57, 1, '', 50, 1, 1, '', '', 3),
(61, '被枕', '/public/attachment/images/20190723/1233bce264e0c2503623b0a28e8b797f.png', 57, 1, '', 50, 1, 1, '', '', 3),
(62, '家具', '/public/attachment/images/20190723/d0478e0852281c0b7fa48461e46cf118.png', 58, 1, '', 50, 1, 1, '', '', 3),
(63, '灯具', '/public/attachment/images/20181210/b77cafa1255afe85301a11ce074d6df8.png', 58, 1, '', 50, 1, 1, '', '', 3),
(64, '布艺软装', '/public/attachment/images/20181210/c66ff297f6ee12794fbff432f8e2832a.png', 58, 1, '', 50, 1, 1, '', '', 3),
(65, '家饰', '/public/attachment/images/20181210/a74fd71b72614cb044b8ade0d7986aa4.png', 58, 1, '', 50, 1, 1, '', '', 3),
(66, '收纳', '/public/attachment/images/20181210/2eb78f4cf759fa59bfcfe6abad43f1bf.png', 59, 1, '', 50, 1, 1, '', '', 3),
(67, '旅行用品', '/public/attachment/images/20181210/640965c2b86a7d225cdb4aeafa46337f.png', 59, 0, '', 50, 1, 0, '', '', 3),
(68, '晾晒除味', '/public/attachment/images/20181210/cd7332e47cad11f1fee95e299a2e0009.png', 59, 0, '', 50, 1, 0, '', '', 3),
(69, '电器', '', 0, 0, '', 50, 1, 0, '', '', 1),
(70, '家用电器', '', 69, 0, '', 50, 1, 0, '', '', 2),
(71, '3D数码', '', 69, 0, '', 50, 1, 0, '', '', 2),
(72, '生活电器', '/public/attachment/images/20181210/9cdb0b34430be7999a5652bcac2a7ac4.png', 70, 0, '', 50, 1, 0, '', '', 3),
(73, '厨房电器', '/public/attachment/images/20181210/d2808e97f846021bc1b17e676e19b99b.png', 70, 0, '', 50, 1, 0, '', '', 3),
(74, '个护健康', '/public/attachment/images/20181210/bf6dcc2a1841aade03d848d898e2ff2e.png', 70, 0, '', 50, 1, 0, '', '', 3),
(75, '数码', '/public/attachment/images/20181210/f4ba32b4ba313c8ac7972bb2657453e3.png', 71, 0, '', 50, 1, 0, '', '', 3),
(76, '娱乐影音', '/public/attachment/images/20181210/cd4d98bd51cc0853edd17d41642f0db0.png', 71, 0, '', 50, 1, 0, '', '', 3),
(77, '服装', '', 0, 0, '', 50, 1, 0, '', '', 1),
(78, '鞋包配饰', '', 0, 0, '', 50, 1, 0, '', '', 1),
(79, '洗护', '', 0, 0, '', 50, 1, 0, '', '', 1),
(80, '饮食', '', 0, 0, '', 50, 1, 0, '', '', 1),
(81, '餐厨', '', 0, 0, '', 50, 1, 0, '', '', 1),
(82, '文体', '', 0, 0, '', 50, 1, 0, '', '', 1),
(83, '特色商品', '', 0, 0, '', 50, 1, 0, '', '', 1),
(84, '男装', '', 77, 0, '', 50, 1, 0, '', '', 2),
(85, '女装', '', 77, 0, '', 50, 1, 0, '', '', 2),
(86, '运动', '', 77, 0, '', 50, 1, 0, '', '', 2),
(87, '内衣家居服', '', 77, 0, '', 50, 1, 0, '', '', 2),
(88, '男士外套', '/public/attachment/images/20181210/d9422d4a2f894582f56ecb3a0fd4ee3e.png', 84, 0, '', 50, 1, 0, '', '', 3),
(112, '鞋配', '/public/attachment/images/20181211/953882bf6d38d501cbde17b34256a7ba.png', 108, 1, '', 0, 1, 1, '', '', 3),
(90, '男式都市户外羽绒服', '/public/attachment/images/20181211/b3b47d84695b214e891ee4c2a30a63ef.png', 86, 0, '', 2, 1, 0, '', '', 3),
(91, '男式弹力保暖软壳夹克', '/public/attachment/images/20181211/3f8b5151077f656c0f07698bb1fa78aa.png', 86, 0, '', 3, 1, 0, '', '', 3),
(103, '箱包', '', 78, 1, '', 0, 1, 1, '', '', 2),
(104, '行李箱', '/public/attachment/images/20181211/fed5656b1de74e0a207d0c0de5ef5462.png', 103, 1, '', 0, 1, 1, '', '', 3),
(105, '女士包袋', '/public/attachment/images/20181211/d48bb145f66fd5e419d9f29db0f3c92e.png', 103, 1, '', 0, 1, 1, '', '', 3),
(106, '男士包袋', '/public/attachment/images/20181211/b2cf30853a2a2b592c27f0f30c50870a.png', 103, 1, '', 0, 1, 1, '', '', 3),
(107, '钱包及小皮件', '/public/attachment/images/20181211/e48011979169b9327d2813083bfdced8.png', 103, 1, '', 0, 1, 1, '', '', 3),
(108, '鞋靴', '', 78, 1, '', 0, 1, 1, '', '', 2),
(109, '女鞋', '/public/attachment/images/20181211/c45bff31d04e9cb6e939f5dd43e30238.png', 108, 1, '', 0, 1, 1, '', '', 3),
(110, '男鞋', '/public/attachment/images/20181211/e5621ad943301fd2d1449b527e56fb39.png', 108, 1, '', 0, 1, 1, '', '', 3),
(111, '拖鞋', '/public/attachment/images/20181211/e6e36ca1aba20ee8b2fd72a37d9c94a1.png', 108, 1, '', 0, 1, 1, '', '', 3),
(114, '服饰配件', '', 78, 1, '', 0, 1, 1, '', '', 2),
(115, '围巾件套', '/public/attachment/images/20181211/8c1aaee5902368c820f4819e22f5bef1.png', 114, 1, '', 0, 1, 1, '', '', 3),
(116, '袜子', '/public/attachment/images/20181211/9534ab020cf49748cb3563a82560a0f7.png', 114, 1, '', 0, 1, 1, '', '', 3),
(117, '丝袜', '/public/attachment/images/20181211/ce7d97faa5c7ea785c13666cc6cbbeb3.png', 114, 1, '', 0, 1, 1, '', '', 3),
(118, '首饰', '/public/attachment/images/20181211/a755573652505fe3ff4ea179d83e0c4f.png', 114, 1, '', 0, 1, 1, '', '', 3),
(119, '配件', '/public/attachment/images/20181211/b01d2ae2c3cf3ec270dc3200ae815e5f.png', 114, 1, '', 0, 1, 1, '', '', 3),
(120, '眼镜', '/public/attachment/images/20181211/7cd7406b4ec1403e72f3905f712ba460.png', 114, 1, '', 0, 1, 1, '', '', 3),
(121, '纸品清洁', '', 79, 1, '', 0, 1, 1, '', '', 2),
(122, '纸品湿巾', '/public/attachment/images/20181211/ce7b14e03cac87618707bc878c4a8e79.png', 121, 1, '', 0, 1, 1, '', '', 3),
(123, '家庭清洁', '/public/attachment/images/20181211/7df00bb42dfb24c7ca736266fc0a807f.png', 121, 1, '', 0, 1, 1, '', '', 3),
(124, '浴室用具', '/public/attachment/images/20181211/87774051c6fa3b6e6041cac1ccb27dcb.png', 121, 1, '', 0, 1, 1, '', '', 3),
(125, '护理彩妆', '', 79, 1, '', 0, 1, 1, '', '', 2),
(126, '毛巾浴巾', '/public/attachment/images/20181211/66d7cf1d4365b6cfa0f8631e2dd3952f.png', 125, 1, '', 0, 1, 1, '', '', 3),
(127, '美妆', '/public/attachment/images/20181211/f5d84080e7fc337e26c7fcdbaa860c16.png', 125, 1, '', 0, 1, 1, '', '', 3),
(128, '香水香氛', '/public/attachment/images/20181211/0d8b41e81dfe3aeae5b37cd53eed5ac5.png', 125, 1, '', 0, 1, 1, '', '', 3),
(129, '面部口腔护理', '/public/attachment/images/20181211/1fcb2c02f4694d3fc67149fe2e7c0d11.png', 125, 1, '', 0, 1, 1, '', '', 3),
(130, '身体护理', '/public/attachment/images/20181211/cc7f66308d1ceb7b72048ea9a056c8b7.png', 125, 1, '', 0, 1, 1, '', '', 3),
(131, '洗发护发', '/public/attachment/images/20181211/0cf18b39dbd4ae5b296959f0401fd6b2.png', 125, 1, '', 0, 1, 1, '', '', 3),
(132, '生理用品', '', 79, 1, '', 0, 1, 1, '', '', 2),
(133, '生理用品', '/public/attachment/images/20181211/94af8b8f5261dc8f485c9f6db1679db1.png', 132, 1, '', 0, 1, 1, '', '', 3),
(134, '女性用品', '/public/attachment/images/20181211/43a2f592926df38a39490821a7c1f1cd.png', 132, 1, '', 0, 1, 1, '', '', 3),
(135, '休闲零食', '', 80, 1, '', 0, 1, 1, '', '', 2),
(136, '饼干糕点', '/public/attachment/images/20181211/d40fb01778cc89e8a73a5b5461c6dd62.png', 135, 1, '', 0, 1, 1, '', '', 3),
(137, '小食糖巧', '/public/attachment/images/20181211/afe7a00da2855a42d2d53354b7f534ff.png', 135, 1, '', 0, 1, 1, '', '', 3),
(138, '坚果炒货', '/public/attachment/images/20181211/3e369059f2e1a5381822cb5a98ae4138.png', 135, 1, '', 0, 1, 1, '', '', 3),
(139, '肉类零食', '/public/attachment/images/20181211/977186ee7963ca86b263dd66accc26de.png', 135, 1, '', 0, 1, 1, '', '', 3),
(140, '蜜饯果干', '/public/attachment/images/20181211/793d53030e9e6d995d072c3cb7041989.png', 135, 1, '', 0, 1, 1, '', '', 3),
(141, '冲饮茗茶', '', 80, 1, '', 0, 1, 1, '', '', 2),
(142, '冲调饮品', '/public/attachment/images/20181211/37e180d0c1d741616bf15746cc537009.png', 141, 1, '', 0, 1, 1, '', '', 3),
(143, '茶包花茶', '/public/attachment/images/20181211/1a67f7ef89997d82c5e234835a783660.png', 141, 1, '', 0, 1, 1, '', '', 3),
(144, '传统茗茶', '/public/attachment/images/20181211/cf9db5d3b3c4ad708487bf5f148d5dd0.png', 141, 1, '', 0, 1, 1, '', '', 3),
(145, '粮调速食', '', 80, 1, '', 0, 1, 1, '', '', 2),
(146, '方便食品', '/public/attachment/images/20181211/04883e5a336d4fa2d1650703a3934603.png', 145, 1, '', 0, 1, 1, '', '', 3),
(147, '米面粮油', '/public/attachment/images/20181211/377bf578e747704fe7377d15d2c499a7.png', 145, 1, '', 0, 1, 1, '', '', 3),
(148, '南北干货', '/public/attachment/images/20181211/6fdf86453a59d13c231378ce949b9627.png', 145, 1, '', 0, 1, 1, '', '', 3),
(149, '酒水饮料', '', 80, 1, '', 0, 1, 1, '', '', 2),
(150, '酒类', '/public/attachment/images/20181211/bbecd1426cbd2594a0f94ffd7be806bd.png', 149, 1, '', 0, 1, 1, '', '', 3),
(151, '滋补保健', '', 80, 1, '', 0, 1, 1, '', '', 2),
(152, '滋补食材', '/public/attachment/images/20181211/7c326ae71464306c11fb30c09f6c5a80.png', 151, 1, '', 0, 1, 1, '', '', 3);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_favorite`
--

CREATE TABLE `suliss_shop_goods_favorite` (
  `id` int(11) NOT NULL,
  `goodsid` int(10) DEFAULT '0',
  `mid` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `type` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_favorite`
--

TRUNCATE TABLE `suliss_shop_goods_favorite`;
--
-- 转存表中的数据 `suliss_shop_goods_favorite`
--

INSERT INTO `suliss_shop_goods_favorite` (`id`, `goodsid`, `mid`, `deleted`, `createtime`, `merchid`, `type`) VALUES
(44, 1, 22, 0, 1544607447, 0, 0),
(45, 6, 21, 0, 1544746524, 0, 0),
(46, 8, 22, 0, 1547713714, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_group`
--

CREATE TABLE `suliss_shop_goods_group` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `goodsids` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '50',
  `merchid` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_group`
--

TRUNCATE TABLE `suliss_shop_goods_group`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_label`
--

CREATE TABLE `suliss_shop_goods_label` (
  `id` int(11) NOT NULL,
  `label` varchar(255) NOT NULL DEFAULT '',
  `labelname` text NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0',
  `merchid` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_label`
--

TRUNCATE TABLE `suliss_shop_goods_label`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_option`
--

CREATE TABLE `suliss_shop_goods_option` (
  `id` int(11) NOT NULL,
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
  `liveprice` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_option`
--

TRUNCATE TABLE `suliss_shop_goods_option`;
--
-- 转存表中的数据 `suliss_shop_goods_option`
--

INSERT INTO `suliss_shop_goods_option` (`id`, `goodsid`, `title`, `thumb`, `productprice`, `marketprice`, `costprice`, `stock`, `weight`, `displayorder`, `specs`, `skuId`, `goodssn`, `productsn`, `virtual`, `exchange_stock`, `exchange_postage`, `presellprice`, `day`, `allfullbackprice`, `fullbackprice`, `allfullbackratio`, `fullbackratio`, `isfullback`, `islive`, `liveprice`) VALUES
(138, 2, '圆满猪莉', '', '0.01', '0.02', '0.01', 199, '500.00', 0, '57', '', '121212', '2323', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(137, 2, '福袋猪莉', '', '0.01', '0.02', '0.01', 28, '500.00', 0, '56', '', '121212', '2323', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(140, 3, '鹿·近赏（仅抱枕套）', '', '0.02', '0.01', '0.03', 100, '0.00', 0, '59', '', '', '', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(139, 3, '鹿·远望（仅抱枕套）', '', '0.02', '0.01', '0.03', 99, '0.00', 0, '58', '', '', '', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(141, 3, '简·青林（仅抱枕套）', '', '0.02', '0.01', '0.03', 100, '0.00', 0, '60', '', '', '', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(142, 3, '鹿·近赏（抱枕套+抱枕芯组合）', '', '0.02', '0.01', '0.03', 100, '0.00', 0, '61', '', '', '', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(143, 3, '鹿·远望（抱枕套+抱枕芯组合）', '', '0.02', '0.01', '0.03', 100, '0.00', 0, '62', '', '', '', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(144, 3, '简·青林（抱枕套+抱枕芯组合）', '', '0.02', '0.01', '0.03', 100, '0.00', 0, '63', '', '', '', 0, 0, '0.00', '0.01', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(145, 6, '300ml', '', '109.00', '109.00', '0.00', 99, '0.00', 0, '64', '', '', '', 0, 0, '0.00', '0.00', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(146, 1, '粉色+1.5m（5英尺）床', '', '10.00', '9.00', '5.00', 1, '5.00', 0, '65_52', '', '002', '1021', 0, 0, '0.00', '10.00', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(147, 1, '粉色+1.8m（6英尺）床', '', '0.00', '0.00', '0.00', 0, '0.00', 0, '65_53', '', '002', '1021', 0, 0, '0.00', '0.10', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(148, 1, '粉色+1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '', '0.00', '0.00', '0.00', 0, '0.00', 0, '65_54', '', '002', '1021', 0, 0, '0.00', '0.10', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(149, 1, '粉色+1.8m（6英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '', '0.00', '0.00', '0.00', 0, '0.00', 0, '65_55', '', '002', '1021', 0, 0, '0.00', '0.10', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(150, 1, '红色+1.5m（5英尺）床', '', '0.00', '0.00', '0.00', 0, '0.00', 0, '66_52', '', '002', '1021', 0, 0, '0.00', '0.10', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(151, 1, '红色+1.8m（6英尺）床', '', '0.00', '0.00', '0.00', 0, '0.00', 0, '66_53', '', '002', '1021', 0, 0, '0.00', '0.10', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(152, 1, '红色+1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '', '0.00', '0.00', '0.00', 0, '0.00', 0, '66_54', '', '002', '1021', 0, 0, '0.00', '0.10', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00'),
(153, 1, '红色+1.8m（6英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '', '0.00', '0.00', '0.00', 0, '0.00', 0, '66_55', '', '002', '1021', 0, 0, '0.00', '0.10', 0, '0.00', '0.00', '0.00', '0.00', 0, 0, '0.00');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_param`
--

CREATE TABLE `suliss_shop_goods_param` (
  `id` int(11) NOT NULL,
  `goodsid` int(10) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `value` text,
  `displayorder` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_param`
--

TRUNCATE TABLE `suliss_shop_goods_param`;
--
-- 转存表中的数据 `suliss_shop_goods_param`
--

INSERT INTO `suliss_shop_goods_param` (`id`, `goodsid`, `title`, `value`, `displayorder`) VALUES
(47, 1, '适用季节', '适用季节', 0),
(48, 1, '款式', '床单式', 1),
(49, 1, '面料', '全棉', 2),
(50, 1, '风格', '北欧、现代简约', 3),
(51, 1, '工艺', '印花', 4),
(52, 2, '工艺', '印花', 0),
(53, 3, '工艺', '绣花、印花', 0),
(54, 3, '面料', '100%聚酯纤维', 1),
(55, 6, '商品材质', '杯身：高硼硅玻璃 耐温范围-20℃~180℃', 0),
(56, 6, '适用', '冷热饮品，包括咖啡、茶、牛奶、水等', 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_spec`
--

CREATE TABLE `suliss_shop_goods_spec` (
  `id` int(11) NOT NULL,
  `goodsid` int(11) DEFAULT '0',
  `title` varchar(50) DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `displaytype` tinyint(3) DEFAULT '0',
  `content` text,
  `displayorder` int(11) DEFAULT '0',
  `propId` varchar(255) DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_spec`
--

TRUNCATE TABLE `suliss_shop_goods_spec`;
--
-- 转存表中的数据 `suliss_shop_goods_spec`
--

INSERT INTO `suliss_shop_goods_spec` (`id`, `goodsid`, `title`, `description`, `displaytype`, `content`, `displayorder`, `propId`) VALUES
(21, 1, '尺寸', '', 0, 'a:4:{i:0;s:2:\"52\";i:1;s:2:\"53\";i:2;s:2:\"54\";i:3;s:2:\"55\";}', 0, ''),
(22, 2, '规格', '', 0, 'a:2:{i:0;s:2:\"56\";i:1;s:2:\"57\";}', 0, ''),
(23, 3, '款式', '', 0, 'a:6:{i:0;s:2:\"58\";i:1;s:2:\"59\";i:2;s:2:\"60\";i:3;s:2:\"61\";i:4;s:2:\"62\";i:5;s:2:\"63\";}', 0, ''),
(24, 6, '规格', '', 0, 'a:1:{i:0;s:2:\"64\";}', 0, ''),
(25, 1, '颜色', '', 0, 'a:2:{i:0;s:2:\"65\";i:1;s:2:\"66\";}', 1, '');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_goods_spec_item`
--

CREATE TABLE `suliss_shop_goods_spec_item` (
  `id` int(11) NOT NULL,
  `specid` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `show` int(11) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `valueId` varchar(255) DEFAULT '',
  `virtual` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_goods_spec_item`
--

TRUNCATE TABLE `suliss_shop_goods_spec_item`;
--
-- 转存表中的数据 `suliss_shop_goods_spec_item`
--

INSERT INTO `suliss_shop_goods_spec_item` (`id`, `specid`, `title`, `thumb`, `show`, `displayorder`, `valueId`, `virtual`) VALUES
(52, 21, '1.5m（5英尺）床', '', 1, 0, '', 0),
(53, 21, '1.8m（6英尺）床', '', 1, 1, '', 0),
(54, 21, '1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '', 1, 2, '', 0),
(55, 21, '1.8m（6英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '', 1, 3, '', 0),
(56, 22, '福袋猪莉', '/public/attachment/images/20181210/49e16a20e1c6fffbd88bedd39982e236.png', 1, 0, '', 0),
(57, 22, '圆满猪莉', '/public/attachment/images/20181210/7f3ac76fca306b976fc46f23a0bf8500.png', 1, 1, '', 0),
(58, 23, '鹿·远望（仅抱枕套）', '/public/attachment/images/20181211/ad635674df85d95d94c1aaba238558a5.png', 1, 0, '', 0),
(59, 23, '鹿·近赏（仅抱枕套）', '', 1, 1, '', 0),
(60, 23, '简·青林（仅抱枕套）', '', 1, 2, '', 0),
(61, 23, '鹿·近赏（抱枕套+抱枕芯组合）', '', 1, 3, '', 0),
(62, 23, '鹿·远望（抱枕套+抱枕芯组合）', '', 1, 4, '', 0),
(63, 23, '简·青林（抱枕套+抱枕芯组合）', '', 1, 5, '', 0),
(64, 24, '300ml', '', 1, 0, '', 0),
(65, 25, '粉色', 'https://ps.ssl.qhmsg.com/bdr/1080__/t0118fa22ce84c9aeb7.jpg', 1, 0, '', 0),
(66, 25, '红色', 'https://p3.ssl.qhimgs1.com/bdr/200_200_/t0196f21c615925c5a3.jpg', 1, 1, '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_banner`
--

CREATE TABLE `suliss_shop_groups_banner` (
  `id` int(11) NOT NULL,
  `bannername` varchar(50) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_banner`
--

TRUNCATE TABLE `suliss_shop_groups_banner`;
--
-- 转存表中的数据 `suliss_shop_groups_banner`
--

INSERT INTO `suliss_shop_groups_banner` (`id`, `bannername`, `link`, `thumb`, `displayorder`, `enabled`) VALUES
(3, '幻灯1', '', '/public/attachment/images/20181221/880a8e887e43ca44a5eecab34acca7ca.jpg', 10, 1),
(4, '幻灯1', '', '/public/attachment/images/20181221/7153e21983f045827077f228335a4b4a.jpg', 50, 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_goods`
--

CREATE TABLE `suliss_shop_groups_goods` (
  `id` int(11) NOT NULL,
  `displayorder` int(11) UNSIGNED DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `category` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `stock` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) DEFAULT '0.00',
  `groupsprice` decimal(10,2) DEFAULT '0.00',
  `singleprice` decimal(10,2) DEFAULT '0.00',
  `goodsnum` int(11) NOT NULL DEFAULT '1',
  `units` varchar(255) NOT NULL DEFAULT '件',
  `freight` decimal(10,2) DEFAULT '0.00',
  `endtime` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `groupnum` int(10) NOT NULL DEFAULT '0',
  `sales` int(10) NOT NULL DEFAULT '0',
  `thumb` varchar(255) DEFAULT '',
  `description` varchar(1000) DEFAULT NULL,
  `content` text,
  `createtime` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `status` tinyint(3) NOT NULL DEFAULT '0',
  `ishot` tinyint(3) NOT NULL DEFAULT '0',
  `deleted` tinyint(3) NOT NULL DEFAULT '0',
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `goodssn` varchar(50) DEFAULT NULL,
  `productsn` varchar(50) DEFAULT NULL,
  `showstock` tinyint(2) NOT NULL,
  `purchaselimit` int(11) NOT NULL DEFAULT '0',
  `single` tinyint(2) NOT NULL DEFAULT '0',
  `dispatchtype` tinyint(2) NOT NULL,
  `dispatchid` int(11) NOT NULL DEFAULT '0',
  `isindex` tinyint(3) NOT NULL DEFAULT '0',
  `deduct` decimal(10,2) NOT NULL DEFAULT '0.00',
  `rights` tinyint(2) NOT NULL DEFAULT '1',
  `thumb_url` text,
  `gid` int(11) DEFAULT '0',
  `discount` tinyint(3) DEFAULT '0',
  `headstype` tinyint(3) DEFAULT NULL,
  `headsmoney` decimal(10,2) DEFAULT '0.00',
  `headsdiscount` int(11) DEFAULT '0',
  `isdiscount` tinyint(3) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `verifytype` tinyint(3) DEFAULT '0',
  `verifynum` int(11) DEFAULT '0',
  `storeids` text,
  `merchid` int(11) DEFAULT '0',
  `shorttitle` varchar(255) DEFAULT '',
  `teamnum` int(11) DEFAULT '0',
  `more_spec` tinyint(1) DEFAULT '0',
  `is_ladder` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_goods`
--

TRUNCATE TABLE `suliss_shop_groups_goods`;
--
-- 转存表中的数据 `suliss_shop_groups_goods`
--

INSERT INTO `suliss_shop_groups_goods` (`id`, `displayorder`, `title`, `category`, `stock`, `price`, `groupsprice`, `singleprice`, `goodsnum`, `units`, `freight`, `endtime`, `groupnum`, `sales`, `thumb`, `description`, `content`, `createtime`, `status`, `ishot`, `deleted`, `goodsid`, `goodssn`, `productsn`, `showstock`, `purchaselimit`, `single`, `dispatchtype`, `dispatchid`, `isindex`, `deduct`, `rights`, `thumb_url`, `gid`, `discount`, `headstype`, `headsmoney`, `headsdiscount`, `isdiscount`, `isverify`, `verifytype`, `verifynum`, `storeids`, `merchid`, `shorttitle`, `teamnum`, `more_spec`, `is_ladder`) VALUES
(5, 50, '男式轻薄羽绒背心', 2, 101, '799.00', '699.00', '0.01', 1, '件', '0.00', 24, 0, -1, '/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg', '21为1213为1·', '<p>·1321·321</p>', 1545371619, 1, 0, 0, 5, '122', '1222', 0, 0, 1, 0, 0, 1, '0.00', 1, 'a:1:{i:0;s:71:\"/public/attachment/images/20181221/fee7033d9cedb85099aaf4f6cd90b61a.jpg\";}', 7, 0, 0, '0.00', 0, 0, 0, 0, 0, '', 0, '', 0, 0, 1),
(6, 50, '男式轻薄羽绒背心', 2, 100, '599.00', '699.00', '699.00', 1, '件', '0.00', 1, 0, 0, '/public/attachment/images/20181221/9f347732463586cd8208695dd225779b.png', '21为1213为1·', '<p>·1321·321</p>', 1545371713, 1, 0, 0, 6, '122', '1222', 0, 0, 1, 0, 0, 1, '0.00', 1, 'a:1:{i:0;s:71:\"/public/attachment/images/20181221/9f347732463586cd8208695dd225779b.png\";}', 7, 0, 0, '0.00', 0, 0, 0, 0, 0, '', 0, '', 0, 0, 1),
(7, 10, '锦眠贡缎四件套', 2, 200, '0.02', '0.01', '0.02', 1, '件', '0.00', 3, 2, 10, '/public/attachment/images/20181224/2c3d6d1a750a61d07f20600732eda930.jpg', '高支纯棉贡缎，意外奢享高阶柔滑', '', 1545622201, 1, 0, 0, 7, 'U930209392039', 'U930209392039', 1, 10, 1, 0, 0, 1, '0.00', 1, 'a:4:{i:0;s:71:\"/public/attachment/images/20181224/2c3d6d1a750a61d07f20600732eda930.jpg\";i:1;s:71:\"/public/attachment/images/20181224/45852acd19a7798670a1e89f9343c13b.png\";i:2;s:71:\"/public/attachment/images/20181224/cdcaca23c249aa16fcbf5ccb7ff5da64.png\";i:3;s:71:\"/public/attachment/images/20181224/69e96fc5487350aed68698965f58d7c5.jpg\";}', 0, 0, 0, '0.00', 0, 0, 0, 0, 0, '', 0, '', 2, 0, 0),
(8, 50, '猪莉·粉糖四件套', 2, 394, '0.02', '0.01', '0.02', 1, '件', '0.01', 1, 2, 23, '/public/attachment/images/20181224/45852acd19a7798670a1e89f9343c13b.png', '少女粉糯贡缎，猪莉甜美相赠 猪莉·粉糖四件套', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><br/></p><div class=\"m-video\"><img src=\"http://yanxuan.nosdn.127.net/256edd2dc2f7a31dd3749b64185f1939.mp4?vframe=&offset=4\" width=\"368\" height=\"207\"/></div><p><img src=\"http://yanxuan.nosdn.127.net/16820848d1f35b95891390db41a6f441.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86aa7b8af448a73af12db8d72a0888a4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c093dfbbefdda8f8be22d3e66814036b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0514979a88b6568b7cd2786854ba7433.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/787942f1d037517b4aa38a5704b70157.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a511c17045b4bdbef7a71c63e99ab89c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d1b7eb4c6be547b02f01f910bcfe843b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/97c29035f04f3912faf1a18431904a00.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7132607e355988ee45e84717ecd59df8.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/735c8c60fc8eb52d1e51b13c6f64659d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d96182593217e8682d6fe71da8d27538.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/92f844580d3c9fed8b82b555faddd352.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff4c685654a77eb90ed4b28354a296ec.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/25b39ec8810ce59a37f1ebfef3614cd9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6d03785c213fa5a0810840ba8b610e89.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/879562ba343069423d2af8868e434954.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/757519b2360a7c3559c7c362123315de.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/16beb1e8860da262b0159d4efd7dc1c6.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/20997b29c36bf421f1b76050ab1682b9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dd148fa5add9262e6f2c7c16c77349ce.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e67391537a0f245d5573c3a98fcce41.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5b212c56e861c94bdeedf2859ea235a9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0dc2f69256a56ee9c78e158e73e2dc68.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/72ef3b69497d7ec6117c4fa6db643ed5.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dbbbcb469695ca5c27b5c35ce5f51327.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f846972b6762aebabfaa93364a2e4a0e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e7c6a2f2beb6d714ec1b4fb62bd801a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9448b5b4d70cdf42e321e5dd0f5a1354.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/36a4526682a780c7e318de5cca0ddc98.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/607461923e1b91686e1a7451f924cf1c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/01b08572e1f8f7c81f42a38cbecf7aec.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e5357004352d59408a261d3623fa6db4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d78e455496f2d8ce08ee2f6ffeeceef6.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3a12f91febf3e6f0de82e1e36f1fd189.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b5ef9709cefe5959381190dd48336ed4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6834026aaef2523ae52dd5476ca97d98.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2c1b40d85e173410338ba7f8f78fdf6d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a6ff63ef52dc8d0367186228ec3ccd9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a3f1e7d723fb10a2027fea9a43ed708f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f37d538720167a5101b416b92867d67.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9ef3d4891fe981fd7d987fa1bd4fed48.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a702909fb81a72d85678c1977c1bbd1.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc786a1d1f991a02756146d1fd4912fb.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d12be9800d784c803dafc8a6269567e0.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0814bfc11474065a5a34597d24ef1d6c.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/593121b3ffbcd3033fedb9324f2934fa.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fb92aa7d48846c49dc5abacff3db8507.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/51f17afd7ae4ab84faaae6e04a860633.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0b172a016ef7e62cae713115a40c6377.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3114a532144c56b358eedabe9adf075b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/09dad662042f0c10f25d38004cbde232.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a9567ad83db0f9479b7cb6a9b7f5d161.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/967516e341abd1e5042ecfdc635a8584.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5d8e3d999fb3bc2b0406ef0fb5db0182.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b10cc9445fb7f430a41849620bf8a94d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d50cfc8007b4bac348e349ffa78bc3e5.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9e8144ccf011f25ad5aca4b834a656fa.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/eca8925de9d08dade2ff4c4e69afed3b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5eefd98438ee25db11d84ebc5b3c944e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f8a8e6274e8fc30ac8d72790ccc1fb04.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/69c80e09576cccde24fa548736e90d99.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3716bfa778e38917bda1e1d771a31a1.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f090ad22e36eed58d87b08ff6376ba6.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/04e52187779512e7eebd5553af8cc630.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/bc8169363cc03d8e321e30bf193d35db.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87e2bbb2068497e5960771a67c4012d0.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b1555b323f6f77e5dcedb5660a46ed71.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/deff2ea53fb20ba7073ac72f263f1025.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ea7ff17b74a0a9010b4039b999663cc4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2e9874d8d6eb1ee1d7b0e5637be6761e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/28ffaaad895070e0c74dcc31abc5378a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5fc15398a28963ae67ddb0972e0540e1.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6405db227d95f2502d583918d30bb2de.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff38dcf37f037b8a174946f3d5444343.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d4d7b742127af373f246d7c3db8765a8.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7d7c6be1d18b77fb60c9c0627b6116eb.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87a25e7bc34e64ef38aab39ff7304b1d.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a4245c80915fac0b8aae2d2bc0edb831.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/46ba74c1baf8846898df0884045270b4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c3a014e207444539905b3e25fe4d3817.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87745a079ea8b55a7b1ea16d0c2ba0e4.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9669b70840b86214b5e3b4e326143bb2.jpg\" style=\"\"/></p><p><br/></p><p><img src=\"http://yanxuan.nosdn.127.net/92865bd46621f8bd67a0941d9afbb6e7.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7dc1acdcbde47355db9dfeab79c865d9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/af17ff61a9a8c11c4e2d184659b202d0.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c74cf78fb57dd9419ec909e8e3fc0816.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e44114ac0cd4fb9726d6f43e9860bd1.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aafb796942e075bc1396988fe06d224.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7371291b0dff48ab549cd276a84dbb05.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/713c4ea349d58a39f4834f639566acf8.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/95a08f272d43bc786ef967ec396533dc.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/35ec176c9ed18509722b6e85efe5058e.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/402db49f70055a3666e4c93b7458276a.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e644bc967e23a5a768d3e7b2c22f06.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3ebbf721703f1a69478683bca79549c9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e1e62e6a11a35d62ca792c428102bb.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3f0b880791cf9f24ac729392aafbf2da.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc94701399fc6e6fd47e8b6c17814cb0.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9db1ce616709dfa2c6c2c342c42c1100.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/4e559839a63353c5a491098006dde274.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2be43ddc03362e9694c289ce40040a9f.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/512d538ebc7dca777ac6e18da5da2597.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a02239b356367f81ee6afed493432ca.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aa348b8268d41cc15c4b64129d83246.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6fc763ffcb97ce199a4b5a92e87729bd.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3dedff21454f674c28a26d9a99dbf18.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/cdb73a18d481d87ef38e1fb38b5a3477.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/58a942c72f49959c50fd7a24f34c685b.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86d29819d87e9d7a766c59cb5d796db9.jpg\" style=\"\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e88cd8d8486d4bd99351c2bdeffd151.jpg\" style=\"\"/></p><p><br/></p></div></div>', 1545643964, 1, 0, 0, 8, '', '', 0, 100, 1, 0, 0, 1, '0.00', 1, 'a:1:{i:0;s:71:\"/public/attachment/images/20181224/45852acd19a7798670a1e89f9343c13b.png\";}', 1, 0, 0, '0.00', 0, 0, 0, 0, 0, '', 0, '', 0, 1, 0),
(9, 1000, '猪莉·纳福抱枕', 2, 1970, '10.00', '0.01', '0.00', 1, '件', '0.00', 1, 0, 40, '/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg', '手感绵软讨喜，祝福好运喜乐 猪莉·纳福抱枕', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><br/></p><div class=\"m-video\"><img src=\"http://yanxuan.nosdn.127.net/388c3f6c4fa389795ec07d55c2b7a174.mp4?vframe=&offset=4\" width=\"368\" height=\"207\"/></div><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a7125f682ab75d225eb84b5aadc54e49.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/486d95c65b0fe1de6730f51dff9e914e.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d77b17514cf3da37db43cce3b68f7611.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c0d0957905d0b9e4730cbbfb8ed78028.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1c67419bdecdb4690f1bc9ef8c7b4b43.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b7c65123aad7edc697cdc47fdeb1b18d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/207682157908ab95394695aaac7009fb.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/1a86d59f4d322bbc98bf63e2a374b69d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ec200e56bd86fd3a8ad11a7b80e468c5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4a688a941b252435cc5a39bb81a6552f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/776f6bc6edfb1f31cc960c14b0e7126f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/80038705ab41b35a799cdff72b00c899.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ebc2558282a87dd631ba8682f374ce97.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/9c96edf6b4397ea2ae841ba0f5495285.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c73405ae174283cccda7db7af8f2be06.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ac608679d3afaa02d779851a38cec645.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/de81f14baaeac87788876caf483af639.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/62b0bb71c22699fa09f44e4bf05117fe.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/dc4f0fddb04f9bcd0fbf1a64a9908f5f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/376d03685f83d33add32929369d83019.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/2dee55ef74b03e6a5d969d9e0fe5689f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5ca571082e220f0ff8c77fd75274e7f8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6a79ba401d07ee213151dcb76cf9c4bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4d7a5a1d06e4d98030456ef0eeeb69c0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b0686f435805d7e35bfca58866f7b1bc.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/01a985375f263101e003998b537c9b85.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a656883b2b5633755ba3793176cb97bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f07d872a96d319b695dfc82a1f8a8a21.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/307f8a98530efe11fbae97f2743c13d2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ccfdca01378496c3a3c2f2e67e6efc5c.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/a6fa36e91d1c49d9791130de11b59ad8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/aaa4f018e26405747df41447b47ac665.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b1fd6be0cae7b9286870313d95dd159b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/70a5eea8c8382f4408d7e1a399798fa8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/be305ea1c529abcca266b9b564a1b66a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/90cbe7a4e0cc4bad7d39d2ed1409f086.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ba717068f213bb2c8221b1008d2912c5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6daf3117a1dc9ce41bcdf91d5dfeeda9.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4b1980888e569e2624e8663462bc61f8.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/06ba853dc48a9da173161caa813c12bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4df8afaa3fc51f322af46d5faf90658f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b3adf6a13f81c644032d54d0a1808b71.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/9ad387742ee612eb73e851244ba95f6d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/59948a05277d171ec567fe3c0ccefda2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/8b184d85b332b08f3a0760d1f7cc85bd.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/aa483f0c1bb20529e3629f0d5ad288ab.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ae4cdaa6a264113f5e120228c8f163d0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/79d9ba6a304a6b9d17199032a36481ca.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/689778a147e0a17ae88a6b747501ca8b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/2b2726e273fd72a5e8dd18ae4f97415f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/33fe8f92077387ed999462531fbf8bf4.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d3212d4f9a7c0db9d41859a945bd8e9e.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5cc1af861ff3d67358d08a75086c20af.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5508570fdb510989d78acf5787d31ed2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/925f574104b0a15288eebeb7a1b8da03.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/e9593e38c8f12140032f023a5208d14f.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/8f1883cf919585c4d299b29b6a2b30f5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/98abd745f898e999fec1aebd48a716fb.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/379449cef4806577dc3d5fb3effdc56c.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/270816a1b0be3c472d7cf8ab00c9b9b2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/cee3e84a9b7aa5722d6cccc5ce2db3e0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/4ab8ea173fce5d5d95cc89b2d249e307.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/999c5767e34bb822a3edec12929714ae.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f0084f7d981f99f261b6b88f4b78c9a3.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d9fc70c5dda373375a8e9b6fa6680b20.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d5c4a1094241b5a7122aa8d320472f14.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/314ed5a55ffd3c69be486f45c09c2924.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/df75b1b0b212e97747d783b48e7910c6.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6c77ad9af74f652cc0add8379e7eccfc.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/6f00b35cd555c1ce22126721c0b80dcf.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c6c63c2cd1a8e37e5bb2dc426d7a839b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/01fa2b4396d0b8103feeb8423a9db9f5.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/98bd2652710ef6bf269e30d475e8dc78.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/602cc1a9ed5806ef4964bfad4c2b37aa.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/ea3fd6fd1b43e81ebf724be6198fa434.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d7c696b580441199a0c7eef503434a87.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/368cf2cbd11f62408d006f1bc5406951.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d49fbfaaf92b39f047efb210b4fac8b6.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/384a2617bc2c52a31bfb396b597f9b70.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/70ea3c2a12ad61b820bc1f81e356d18d.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/170e0c5c340142acceebcd851882cf0a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/3af9a87abbd05808121802f0e8ba39ed.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/f024b7222e3a779e1ccab282933d690a.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/eafb40aabe9eff3ebaf5d3190ba4fc7b.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/15c314b4a5d7cec882c476c27f26a413.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/abee5be1233cac8977faa73417d1f151.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7687c153f4b59fad22f2548a8b2ebfec.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/c3795bd0735057709948cdbc2306a9c0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/25ce962b593d585589e9fb7e06e444e0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/5489d56f652a7321825011cbda9fee94.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7357b4d61af19063beac20aca19a2ccb.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/885c3c8022acccbd2adaeb22bb10ccf2.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/01f0dd3305efdb9fe1b79d34359a59d0.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7a1b4f3f03a93269c7df0292d9a90fd6.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/b3327807a2b9593c40354ebf83f1acc9.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/7173e5406b279bb97aee23a14d71e116.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/79901c7f0007d669f574867ccc95ed05.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/d0c2f083998f94b4d83f724d163238ef.jpg\"/></p><p><img style=\"\" src=\"http://yanxuan.nosdn.127.net/aa01347f27784aa501f2ca916f02e548.jpg\"/></p><p>‍<br/></p><p><br/></p></div></div>', 1545721409, 1, 0, 0, 9, '', '', 1, 100, 0, 0, 0, 1, '0.00', 1, 'a:2:{i:0;s:71:\"/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg\";i:1;s:71:\"/public/attachment/images/20181225/43555cfcb33fdacb7971645e9fc70b66.jpg\";}', 2, 0, 0, '0.00', 0, 0, 0, 0, 0, '1,2', 0, '', 3, 0, 1),
(10, 50, '猪莉·粉糖四件套', 2, 800, '399.00', '100.00', '0.00', 1, '件', '0.00', 5, 2, 0, '/public/attachment/images/20181210/a7248fa0e77d87ae97df1d0421c4c282.png', '少女粉糯贡缎，猪莉甜美相赠 猪莉·粉糖四件套', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><br/></p><div class=\"m-video\"><img width=\"368\" height=\"207\" src=\"http://yanxuan.nosdn.127.net/256edd2dc2f7a31dd3749b64185f1939.mp4?vframe=&offset=4\"/></div><p><img src=\"http://yanxuan.nosdn.127.net/16820848d1f35b95891390db41a6f441.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86aa7b8af448a73af12db8d72a0888a4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c093dfbbefdda8f8be22d3e66814036b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0514979a88b6568b7cd2786854ba7433.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/787942f1d037517b4aa38a5704b70157.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a511c17045b4bdbef7a71c63e99ab89c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d1b7eb4c6be547b02f01f910bcfe843b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/97c29035f04f3912faf1a18431904a00.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7132607e355988ee45e84717ecd59df8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/735c8c60fc8eb52d1e51b13c6f64659d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d96182593217e8682d6fe71da8d27538.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/92f844580d3c9fed8b82b555faddd352.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff4c685654a77eb90ed4b28354a296ec.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/25b39ec8810ce59a37f1ebfef3614cd9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6d03785c213fa5a0810840ba8b610e89.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/879562ba343069423d2af8868e434954.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/757519b2360a7c3559c7c362123315de.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/16beb1e8860da262b0159d4efd7dc1c6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/20997b29c36bf421f1b76050ab1682b9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dd148fa5add9262e6f2c7c16c77349ce.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e67391537a0f245d5573c3a98fcce41.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5b212c56e861c94bdeedf2859ea235a9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0dc2f69256a56ee9c78e158e73e2dc68.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/72ef3b69497d7ec6117c4fa6db643ed5.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dbbbcb469695ca5c27b5c35ce5f51327.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f846972b6762aebabfaa93364a2e4a0e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e7c6a2f2beb6d714ec1b4fb62bd801a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9448b5b4d70cdf42e321e5dd0f5a1354.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/36a4526682a780c7e318de5cca0ddc98.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/607461923e1b91686e1a7451f924cf1c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/01b08572e1f8f7c81f42a38cbecf7aec.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e5357004352d59408a261d3623fa6db4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d78e455496f2d8ce08ee2f6ffeeceef6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3a12f91febf3e6f0de82e1e36f1fd189.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b5ef9709cefe5959381190dd48336ed4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6834026aaef2523ae52dd5476ca97d98.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2c1b40d85e173410338ba7f8f78fdf6d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a6ff63ef52dc8d0367186228ec3ccd9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a3f1e7d723fb10a2027fea9a43ed708f.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f37d538720167a5101b416b92867d67.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9ef3d4891fe981fd7d987fa1bd4fed48.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a702909fb81a72d85678c1977c1bbd1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc786a1d1f991a02756146d1fd4912fb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d12be9800d784c803dafc8a6269567e0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0814bfc11474065a5a34597d24ef1d6c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/593121b3ffbcd3033fedb9324f2934fa.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fb92aa7d48846c49dc5abacff3db8507.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/51f17afd7ae4ab84faaae6e04a860633.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0b172a016ef7e62cae713115a40c6377.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3114a532144c56b358eedabe9adf075b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/09dad662042f0c10f25d38004cbde232.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a9567ad83db0f9479b7cb6a9b7f5d161.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/967516e341abd1e5042ecfdc635a8584.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5d8e3d999fb3bc2b0406ef0fb5db0182.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b10cc9445fb7f430a41849620bf8a94d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d50cfc8007b4bac348e349ffa78bc3e5.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9e8144ccf011f25ad5aca4b834a656fa.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/eca8925de9d08dade2ff4c4e69afed3b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5eefd98438ee25db11d84ebc5b3c944e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f8a8e6274e8fc30ac8d72790ccc1fb04.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/69c80e09576cccde24fa548736e90d99.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3716bfa778e38917bda1e1d771a31a1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f090ad22e36eed58d87b08ff6376ba6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/04e52187779512e7eebd5553af8cc630.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/bc8169363cc03d8e321e30bf193d35db.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87e2bbb2068497e5960771a67c4012d0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b1555b323f6f77e5dcedb5660a46ed71.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/deff2ea53fb20ba7073ac72f263f1025.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ea7ff17b74a0a9010b4039b999663cc4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2e9874d8d6eb1ee1d7b0e5637be6761e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/28ffaaad895070e0c74dcc31abc5378a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5fc15398a28963ae67ddb0972e0540e1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6405db227d95f2502d583918d30bb2de.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff38dcf37f037b8a174946f3d5444343.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d4d7b742127af373f246d7c3db8765a8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7d7c6be1d18b77fb60c9c0627b6116eb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87a25e7bc34e64ef38aab39ff7304b1d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a4245c80915fac0b8aae2d2bc0edb831.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/46ba74c1baf8846898df0884045270b4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c3a014e207444539905b3e25fe4d3817.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87745a079ea8b55a7b1ea16d0c2ba0e4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9669b70840b86214b5e3b4e326143bb2.jpg\"/></p><p><br/></p><p><img src=\"http://yanxuan.nosdn.127.net/92865bd46621f8bd67a0941d9afbb6e7.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7dc1acdcbde47355db9dfeab79c865d9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/af17ff61a9a8c11c4e2d184659b202d0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c74cf78fb57dd9419ec909e8e3fc0816.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e44114ac0cd4fb9726d6f43e9860bd1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aafb796942e075bc1396988fe06d224.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7371291b0dff48ab549cd276a84dbb05.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/713c4ea349d58a39f4834f639566acf8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/95a08f272d43bc786ef967ec396533dc.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/35ec176c9ed18509722b6e85efe5058e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/402db49f70055a3666e4c93b7458276a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e644bc967e23a5a768d3e7b2c22f06.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3ebbf721703f1a69478683bca79549c9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e1e62e6a11a35d62ca792c428102bb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3f0b880791cf9f24ac729392aafbf2da.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc94701399fc6e6fd47e8b6c17814cb0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9db1ce616709dfa2c6c2c342c42c1100.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/4e559839a63353c5a491098006dde274.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2be43ddc03362e9694c289ce40040a9f.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/512d538ebc7dca777ac6e18da5da2597.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a02239b356367f81ee6afed493432ca.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aa348b8268d41cc15c4b64129d83246.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6fc763ffcb97ce199a4b5a92e87729bd.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3dedff21454f674c28a26d9a99dbf18.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/cdb73a18d481d87ef38e1fb38b5a3477.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/58a942c72f49959c50fd7a24f34c685b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86d29819d87e9d7a766c59cb5d796db9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e88cd8d8486d4bd99351c2bdeffd151.jpg\"/></p><p><br/></p></div></div>', 1547459968, 1, 0, 0, 0, '', '', 0, 0, 0, 0, 0, 0, '0.00', 1, 'a:2:{i:0;s:71:\"/public/attachment/images/20181210/a7248fa0e77d87ae97df1d0421c4c282.png\";i:1;s:71:\"/public/attachment/images/20181210/48e12fc306ac0458f386522a152a23dc.jpg\";}', 1, 0, 0, '0.00', 0, 0, 0, 0, 0, '', 0, '', 0, 1, 0),
(11, 50, '猪莉·粉糖四件套', 2, 800, '399.00', '100.00', '0.00', 1, '件', '0.00', 5, 2, 0, '/public/attachment/images/20181210/a7248fa0e77d87ae97df1d0421c4c282.png', '少女粉糯贡缎，猪莉甜美相赠 猪莉·粉糖四件套', '<div class=\"dt-section dt-section-1\" data-reactid=\".0.0.1.0.j.2\"><div class=\"m-detailHtml\" data-reactid=\".0.0.1.0.j.2.0\"><p><br/></p><div class=\"m-video\"><img width=\"368\" height=\"207\" src=\"http://yanxuan.nosdn.127.net/256edd2dc2f7a31dd3749b64185f1939.mp4?vframe=&offset=4\"/></div><p><img src=\"http://yanxuan.nosdn.127.net/16820848d1f35b95891390db41a6f441.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86aa7b8af448a73af12db8d72a0888a4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c093dfbbefdda8f8be22d3e66814036b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0514979a88b6568b7cd2786854ba7433.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/787942f1d037517b4aa38a5704b70157.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a511c17045b4bdbef7a71c63e99ab89c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d1b7eb4c6be547b02f01f910bcfe843b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/97c29035f04f3912faf1a18431904a00.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7132607e355988ee45e84717ecd59df8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/735c8c60fc8eb52d1e51b13c6f64659d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d96182593217e8682d6fe71da8d27538.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/92f844580d3c9fed8b82b555faddd352.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff4c685654a77eb90ed4b28354a296ec.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/25b39ec8810ce59a37f1ebfef3614cd9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6d03785c213fa5a0810840ba8b610e89.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/879562ba343069423d2af8868e434954.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/757519b2360a7c3559c7c362123315de.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/16beb1e8860da262b0159d4efd7dc1c6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/20997b29c36bf421f1b76050ab1682b9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dd148fa5add9262e6f2c7c16c77349ce.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e67391537a0f245d5573c3a98fcce41.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5b212c56e861c94bdeedf2859ea235a9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0dc2f69256a56ee9c78e158e73e2dc68.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/72ef3b69497d7ec6117c4fa6db643ed5.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/dbbbcb469695ca5c27b5c35ce5f51327.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f846972b6762aebabfaa93364a2e4a0e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e7c6a2f2beb6d714ec1b4fb62bd801a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9448b5b4d70cdf42e321e5dd0f5a1354.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/36a4526682a780c7e318de5cca0ddc98.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/607461923e1b91686e1a7451f924cf1c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/01b08572e1f8f7c81f42a38cbecf7aec.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/e5357004352d59408a261d3623fa6db4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d78e455496f2d8ce08ee2f6ffeeceef6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3a12f91febf3e6f0de82e1e36f1fd189.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b5ef9709cefe5959381190dd48336ed4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6834026aaef2523ae52dd5476ca97d98.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2c1b40d85e173410338ba7f8f78fdf6d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a6ff63ef52dc8d0367186228ec3ccd9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a3f1e7d723fb10a2027fea9a43ed708f.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f37d538720167a5101b416b92867d67.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9ef3d4891fe981fd7d987fa1bd4fed48.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a702909fb81a72d85678c1977c1bbd1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc786a1d1f991a02756146d1fd4912fb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d12be9800d784c803dafc8a6269567e0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0814bfc11474065a5a34597d24ef1d6c.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/593121b3ffbcd3033fedb9324f2934fa.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fb92aa7d48846c49dc5abacff3db8507.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/51f17afd7ae4ab84faaae6e04a860633.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/0b172a016ef7e62cae713115a40c6377.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3114a532144c56b358eedabe9adf075b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/09dad662042f0c10f25d38004cbde232.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a9567ad83db0f9479b7cb6a9b7f5d161.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/967516e341abd1e5042ecfdc635a8584.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5d8e3d999fb3bc2b0406ef0fb5db0182.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b10cc9445fb7f430a41849620bf8a94d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d50cfc8007b4bac348e349ffa78bc3e5.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9e8144ccf011f25ad5aca4b834a656fa.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/eca8925de9d08dade2ff4c4e69afed3b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5eefd98438ee25db11d84ebc5b3c944e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/f8a8e6274e8fc30ac8d72790ccc1fb04.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/69c80e09576cccde24fa548736e90d99.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3716bfa778e38917bda1e1d771a31a1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/8f090ad22e36eed58d87b08ff6376ba6.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/04e52187779512e7eebd5553af8cc630.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/bc8169363cc03d8e321e30bf193d35db.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87e2bbb2068497e5960771a67c4012d0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/b1555b323f6f77e5dcedb5660a46ed71.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/deff2ea53fb20ba7073ac72f263f1025.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ea7ff17b74a0a9010b4039b999663cc4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2e9874d8d6eb1ee1d7b0e5637be6761e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/28ffaaad895070e0c74dcc31abc5378a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/5fc15398a28963ae67ddb0972e0540e1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6405db227d95f2502d583918d30bb2de.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/ff38dcf37f037b8a174946f3d5444343.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d4d7b742127af373f246d7c3db8765a8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7d7c6be1d18b77fb60c9c0627b6116eb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87a25e7bc34e64ef38aab39ff7304b1d.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a4245c80915fac0b8aae2d2bc0edb831.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/46ba74c1baf8846898df0884045270b4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c3a014e207444539905b3e25fe4d3817.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/87745a079ea8b55a7b1ea16d0c2ba0e4.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9669b70840b86214b5e3b4e326143bb2.jpg\"/></p><p><br/></p><p><img src=\"http://yanxuan.nosdn.127.net/92865bd46621f8bd67a0941d9afbb6e7.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7dc1acdcbde47355db9dfeab79c865d9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/af17ff61a9a8c11c4e2d184659b202d0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/c74cf78fb57dd9419ec909e8e3fc0816.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3e44114ac0cd4fb9726d6f43e9860bd1.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aafb796942e075bc1396988fe06d224.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7371291b0dff48ab549cd276a84dbb05.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/713c4ea349d58a39f4834f639566acf8.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/95a08f272d43bc786ef967ec396533dc.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/35ec176c9ed18509722b6e85efe5058e.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/402db49f70055a3666e4c93b7458276a.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e644bc967e23a5a768d3e7b2c22f06.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3ebbf721703f1a69478683bca79549c9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/a0e1e62e6a11a35d62ca792c428102bb.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/3f0b880791cf9f24ac729392aafbf2da.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/fc94701399fc6e6fd47e8b6c17814cb0.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/9db1ce616709dfa2c6c2c342c42c1100.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/4e559839a63353c5a491098006dde274.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/2be43ddc03362e9694c289ce40040a9f.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/512d538ebc7dca777ac6e18da5da2597.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/7a02239b356367f81ee6afed493432ca.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6aa348b8268d41cc15c4b64129d83246.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/6fc763ffcb97ce199a4b5a92e87729bd.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/d3dedff21454f674c28a26d9a99dbf18.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/cdb73a18d481d87ef38e1fb38b5a3477.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/58a942c72f49959c50fd7a24f34c685b.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/86d29819d87e9d7a766c59cb5d796db9.jpg\"/></p><p><img src=\"http://yanxuan.nosdn.127.net/1e88cd8d8486d4bd99351c2bdeffd151.jpg\"/></p><p><br/></p></div></div>', 1547460048, 1, 0, 0, 11, '', '', 0, 0, 0, 0, 0, 0, '0.00', 1, 'a:2:{i:0;s:71:\"/public/attachment/images/20181210/a7248fa0e77d87ae97df1d0421c4c282.png\";i:1;s:71:\"/public/attachment/images/20181210/48e12fc306ac0458f386522a152a23dc.jpg\";}', 1, 0, 0, '0.00', 0, 0, 0, 0, 0, '', 0, '', 0, 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_goods_category`
--

CREATE TABLE `suliss_shop_groups_goods_category` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) UNSIGNED DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_goods_category`
--

TRUNCATE TABLE `suliss_shop_groups_goods_category`;
--
-- 转存表中的数据 `suliss_shop_groups_goods_category`
--

INSERT INTO `suliss_shop_groups_goods_category` (`id`, `name`, `thumb`, `displayorder`, `enabled`, `advimg`, `advurl`, `isrecommand`) VALUES
(2, '居家', '/public/attachment/images/20181221/9f347732463586cd8208695dd225779b.png', 50, 1, '', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_goods_option`
--

CREATE TABLE `suliss_shop_groups_goods_option` (
  `id` int(11) NOT NULL,
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `groups_goods_id` int(255) NOT NULL DEFAULT '0',
  `goods_option_id` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `marketprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `single_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `specs` varchar(255) NOT NULL DEFAULT '',
  `stock` int(255) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_goods_option`
--

TRUNCATE TABLE `suliss_shop_groups_goods_option`;
--
-- 转存表中的数据 `suliss_shop_groups_goods_option`
--

INSERT INTO `suliss_shop_groups_goods_option` (`id`, `goodsid`, `groups_goods_id`, `goods_option_id`, `title`, `marketprice`, `price`, `single_price`, `specs`, `stock`) VALUES
(1, 1, 8, 129, '1.5m（5英尺）床', '0.01', '0.01', '0.01', '52', 6),
(2, 1, 8, 130, '1.8m（6英尺）床', '0.01', '0.02', '0.02', '53', 0),
(3, 1, 8, 131, '1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '0.01', '0.03', '0.01', '54', 0),
(4, 1, 8, 132, '1.8m（6英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '0.01', '0.04', '0.01', '55', 0),
(5, 1, 11, 146, '粉色+1.5m（5英尺）床', '9.00', '100.00', '0.00', '52_65', 100),
(6, 1, 11, 147, '粉色+1.8m（6英尺）床', '0.00', '100.00', '0.00', '53_65', 100),
(7, 1, 11, 148, '粉色+1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '0.00', '100.00', '0.00', '54_65', 100),
(8, 1, 11, 149, '粉色+1.8m（6英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '0.00', '100.00', '0.00', '55_65', 100),
(9, 1, 11, 150, '红色+1.5m（5英尺）床', '0.00', '100.00', '0.00', '52_66', 100),
(10, 1, 11, 151, '红色+1.8m（6英尺）床', '0.00', '100.00', '0.00', '53_66', 100),
(11, 1, 11, 152, '红色+1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '0.00', '100.00', '0.00', '54_66', 100),
(12, 1, 11, 153, '红色+1.8m（6英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', '0.00', '100.00', '0.00', '55_66', 100);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_ladder`
--

CREATE TABLE `suliss_shop_groups_ladder` (
  `id` int(11) NOT NULL,
  `goods_id` int(11) DEFAULT '0',
  `ladder_num` int(11) DEFAULT NULL,
  `ladder_price` decimal(10,2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_ladder`
--

TRUNCATE TABLE `suliss_shop_groups_ladder`;
--
-- 转存表中的数据 `suliss_shop_groups_ladder`
--

INSERT INTO `suliss_shop_groups_ladder` (`id`, `goods_id`, `ladder_num`, `ladder_price`) VALUES
(6, 5, 4, '0.02'),
(5, 5, 3, '0.02'),
(3, 6, 2, '599.00'),
(4, 6, 3, '588.00'),
(7, 5, 5, '0.01'),
(8, 9, 2, '0.01'),
(9, 9, 3, '0.04'),
(10, 9, 5, '0.03'),
(11, 9, 6, '0.02'),
(12, 9, 7, '0.01'),
(13, 9, 20, '0.06');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_order`
--

CREATE TABLE `suliss_shop_groups_order` (
  `id` int(11) NOT NULL,
  `mid` int(11) NOT NULL DEFAULT '0',
  `orderno` varchar(45) NOT NULL DEFAULT '',
  `groupnum` int(11) NOT NULL DEFAULT '0',
  `paytime` int(11) NOT NULL DEFAULT '0',
  `credit` int(11) NOT NULL DEFAULT '0',
  `creditmoney` decimal(11,2) NOT NULL DEFAULT '0.00',
  `price` decimal(11,2) NOT NULL DEFAULT '0.00',
  `freight` decimal(11,2) NOT NULL DEFAULT '0.00',
  `status` int(9) NOT NULL,
  `paytype` tinyint(3) NOT NULL DEFAULT '0',
  `pay_type` varchar(45) NOT NULL DEFAULT '',
  `dispatchid` int(11) NOT NULL DEFAULT '0',
  `addressid` int(11) NOT NULL DEFAULT '0',
  `address` varchar(1000) NOT NULL DEFAULT '',
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `teamid` int(11) NOT NULL DEFAULT '0',
  `is_team` int(2) NOT NULL DEFAULT '0',
  `heads` int(11) NOT NULL DEFAULT '0',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `starttime` int(11) NOT NULL DEFAULT '0',
  `canceltime` int(11) NOT NULL DEFAULT '0',
  `endtime` int(11) NOT NULL DEFAULT '0',
  `createtime` int(11) NOT NULL DEFAULT '0',
  `finishtime` int(11) NOT NULL DEFAULT '0',
  `successtime` int(11) NOT NULL DEFAULT '0',
  `refundid` int(11) NOT NULL DEFAULT '0',
  `refundstate` tinyint(2) NOT NULL DEFAULT '0',
  `refundtime` int(11) NOT NULL DEFAULT '0',
  `express` varchar(45) NOT NULL DEFAULT '',
  `expresscom` varchar(100) NOT NULL DEFAULT '',
  `expresssn` varchar(45) NOT NULL DEFAULT '',
  `sendtime` int(45) NOT NULL DEFAULT '0',
  `remark` varchar(255) NOT NULL DEFAULT '',
  `remarkclose` text,
  `remarksend` text,
  `message` varchar(255) NOT NULL DEFAULT '',
  `success` int(2) NOT NULL DEFAULT '0',
  `deleted` int(2) NOT NULL DEFAULT '0',
  `realname` varchar(20) NOT NULL DEFAULT '',
  `mobile` varchar(11) NOT NULL DEFAULT '',
  `verifytype` tinyint(3) NOT NULL DEFAULT '0',
  `isverify` tinyint(3) NOT NULL DEFAULT '0',
  `verifycode` varchar(45) NOT NULL DEFAULT '0',
  `verifynum` int(11) NOT NULL DEFAULT '0',
  `printstate` int(11) NOT NULL DEFAULT '0',
  `printstate2` int(11) NOT NULL DEFAULT '0',
  `apppay` tinyint(3) NOT NULL DEFAULT '0',
  `isborrow` tinyint(1) NOT NULL DEFAULT '0',
  `borrowopenid` varchar(50) NOT NULL DEFAULT '',
  `source` tinyint(1) NOT NULL DEFAULT '0',
  `ladder_id` tinyint(1) NOT NULL DEFAULT '0',
  `is_ladder` tinyint(1) NOT NULL DEFAULT '0',
  `more_spec` tinyint(1) NOT NULL DEFAULT '0',
  `wxapp_prepay_id` varchar(255) NOT NULL DEFAULT '',
  `cancel_reason` varchar(255) NOT NULL DEFAULT '',
  `goods_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `goods_option_id` int(11) NOT NULL DEFAULT '0',
  `specs` varchar(255) NOT NULL DEFAULT '',
  `transid` varchar(30) NOT NULL DEFAULT '',
  `iscomment` tinyint(3) NOT NULL DEFAULT '0',
  `delete` int(2) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_order`
--

TRUNCATE TABLE `suliss_shop_groups_order`;
--
-- 转存表中的数据 `suliss_shop_groups_order`
--

INSERT INTO `suliss_shop_groups_order` (`id`, `mid`, `orderno`, `groupnum`, `paytime`, `credit`, `creditmoney`, `price`, `freight`, `status`, `paytype`, `pay_type`, `dispatchid`, `addressid`, `address`, `goodsid`, `teamid`, `is_team`, `heads`, `discount`, `starttime`, `canceltime`, `endtime`, `createtime`, `finishtime`, `successtime`, `refundid`, `refundstate`, `refundtime`, `express`, `expresscom`, `expresssn`, `sendtime`, `remark`, `remarkclose`, `remarksend`, `message`, `success`, `deleted`, `realname`, `mobile`, `verifytype`, `isverify`, `verifycode`, `verifynum`, `printstate`, `printstate2`, `apppay`, `isborrow`, `borrowopenid`, `source`, `ladder_id`, `is_ladder`, `more_spec`, `wxapp_prepay_id`, `cancel_reason`, `goods_price`, `goods_option_id`, `specs`, `transid`, `iscomment`, `delete`) VALUES
(209, 24, 'PT20190121111645245422', 6, 1548040611, 0, '0.00', '0.02', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 209, 1, 1, '0.00', 1548040611, 1548044217, 1, 1548040605, 0, 0, 0, 0, 1548064960, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 11, 1, 0, '', '', '0.00', 0, '', '4200000253201901212118430707', 0, 0),
(208, 24, 'PT20190121111312956260', 2, 1548040397, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 207, 1, 0, '0.00', 1548040397, 0, 1, 1548040392, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000249201901218259196634', 0, 0),
(207, 22, 'PT20190121111053224302', 2, 1548040259, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 207, 1, 1, '0.00', 1548040259, 0, 1, 1548040253, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000246201901212648731847', 0, 0),
(206, 24, 'PT20190121110910278415', 7, 1548040173, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 206, 1, 1, '0.00', 1548040173, 1548043773, 1, 1548040150, 0, 0, 0, 0, 1548064959, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000242201901219417877209', 0, 0),
(205, 24, 'PT20190121110902442466', 7, 0, 0, '0.00', '0.01', '0.00', -1, 1, '', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 205, 1, 1, '0.00', 0, 1548041950, 1, 1548040142, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(204, 24, 'PT20190121105658866664', 2, 1548039425, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 204, 1, 1, '0.00', 1548039425, 1548043028, 1, 1548039418, 0, 0, 0, 0, 1548064958, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000250201901213210653726', 0, 0),
(203, 24, 'PT20190121105510084364', 2, 1548039316, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 203, 1, 1, '0.00', 1548039316, 1548042917, 1, 1548039310, 0, 0, 0, 0, 1548064957, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000253201901212098904972', 0, 0),
(202, 24, 'PT20190121105259760637', 2, 1548039184, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 202, 1, 1, '0.00', 1548039184, 1548042786, 1, 1548039179, 0, 0, 0, 0, 1548064956, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000243201901211711597582', 0, 0),
(201, 21, 'PT20190121104146742464', 2, 1548038514, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 201, 1, 1, '0.00', 1548038514, 1548042122, 1, 1548038506, 0, 0, 0, 0, 1548064954, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000257201901218006695327', 0, 0),
(200, 24, 'PT20190121104120848686', 2, 1548038487, 0, '0.00', '0.01', '0.00', 2, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 199, 1, 0, '0.00', 1548038487, 0, 1, 1548038480, 0, 0, 0, 0, 0, 'shentong', '申通', '889877784049468382', 1548038548, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000247201901219476502030', 0, 0),
(199, 22, 'PT20190121104015804244', 2, 1548038421, 0, '0.00', '0.01', '0.00', 2, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 199, 1, 1, '0.00', 1548038421, 0, 1, 1548038415, 0, 0, 0, 0, 0, '', '', '1232323', 1548040221, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000256201901211667940826', 0, 0),
(198, 24, 'PT20190121101835894869', 2, 1548037121, 0, '0.00', '0.01', '0.00', 2, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 197, 1, 0, '0.00', 1548037121, 0, 1, 1548037115, 0, 0, 0, 0, 0, 'yuantong', '圆通速递', '889877784049468382', 1548037278, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000242201901216189429715', 0, 0),
(197, 22, 'PT20190121100435695809', 2, 1548036283, 0, '0.00', '0.01', '0.00', 2, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 197, 1, 1, '0.00', 1548036283, 0, 1, 1548036275, 0, 0, 0, 0, 0, 'yuantong', '圆通速递', '889877784049468382', 1548037374, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000246201901211182516436', 0, 0),
(196, 22, 'PT20190121091547594243', 2, 1548033370, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 196, 1, 1, '0.00', 1548033370, 1548036975, 1, 1548033347, 0, 0, 0, 0, 1548064953, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000257201901216358885544', 0, 0),
(195, 22, 'PT20190121082847618792', 2, 1548030533, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 195, 1, 1, '0.00', 1548030533, 1548034143, 1, 1548030527, 0, 0, 0, 0, 1548038454, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000246201901215814308911', 0, 0),
(194, 24, 'PT20190118172841396343', 2, 1547803730, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 194, 1, 1, '0.00', 1547803730, 1547807356, 1, 1547803721, 0, 0, 0, 0, 1548033277, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000244201901187756147423', 0, 0),
(193, 24, 'PT20190118172329802727', 2, 1547803418, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 193, 1, 1, '0.00', 1547803418, 1547807025, 1, 1547803409, 0, 0, 0, 0, 1548033276, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000241201901187491433939', 0, 0),
(192, 24, 'PT20190118172024985582', 2, 1547803232, 0, '0.00', '0.01', '0.01', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 192, 1, 1, '0.00', 1547803232, 1547806844, 1, 1547803224, 0, 0, 0, 0, 1548033274, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000253201901184692607354', 0, 0),
(191, 24, 'PT20190118171945074377', 2, 1547803193, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 191, 1, 1, '0.00', 1547803193, 1547806813, 1, 1547803185, 0, 0, 0, 0, 1548033273, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000244201901185841991286', 0, 0),
(190, 24, 'PT20190118171449245023', 2, 1547802896, 0, '0.00', '0.01', '0.01', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 190, 1, 1, '0.00', 1547802896, 1547806513, 1, 1547802889, 0, 0, 0, 0, 1548033272, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000256201901185079147362', 0, 0),
(189, 24, 'PT20190118171412188052', 2, 1547802861, 0, '0.00', '0.01', '0.01', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 189, 1, 1, '0.00', 1547802861, 1547806483, 1, 1547802852, 0, 0, 0, 0, 1548033271, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000246201901186035615838', 0, 0),
(188, 24, 'PT20190118171327252862', 2, 1547802812, 0, '0.00', '0.01', '0.01', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 188, 1, 1, '0.00', 1547802812, 1547806423, 1, 1547802807, 0, 0, 0, 0, 1548033269, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000256201901180358847045', 0, 0),
(187, 24, 'PT20190118171103882591', 2, 1547802670, 0, '0.00', '0.01', '0.01', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 187, 1, 1, '0.00', 1547802670, 1547806273, 1, 1547802663, 0, 0, 0, 0, 1548033268, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000241201901182553550376', 0, 0),
(186, 24, 'PT20190118170905481202', 2, 1547802550, 0, '0.00', '0.01', '0.01', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 186, 1, 1, '0.00', 1547802550, 1547806153, 1, 1547802545, 0, 0, 0, 0, 1548033267, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000257201901182899049641', 0, 0),
(185, 24, 'PT20190118170834828118', 2, 1547802521, 0, '0.00', '0.01', '0.01', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 185, 1, 1, '0.00', 1547802521, 1547806123, 1, 1547802514, 0, 0, 0, 0, 1548033093, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000245201901186096058268', 0, 0),
(182, 24, 'PT20190118152857459620', 2, 1547796542, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 179, 1, 0, '0.00', 1547796542, 0, 1, 1547796537, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000247201901181312718527', 0, 0),
(183, 24, 'PT20190118161659822331', 2, 1547799427, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 183, 1, 1, '0.00', 1547799427, 1547803084, 1, 1547799419, 0, 0, 0, 0, 1548033090, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000248201901188868958862', 0, 0),
(180, 22, 'PT20190118152613874212', 2, 1547796381, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 180, 1, 1, '0.00', 1547796381, 0, 1, 1547796373, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000246201901189706140841', 0, 0),
(181, 24, 'PT20190118152802084623', 2, 1547796487, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 180, 1, 0, '0.00', 1547796487, 0, 1, 1547796482, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000252201901188025026113', 0, 0),
(179, 22, 'PT20190118152556865666', 2, 1547796363, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 179, 1, 1, '0.00', 1547796363, 0, 1, 1547796356, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000257201901184074090776', 0, 0),
(177, 22, 'PT20190118152449562803', 7, 1547796301, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 175, 1, 0, '0.00', 1547796301, 1547796954, 1, 1547796289, 0, 0, 0, 0, 1547804985, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000241201901187055797122', 0, 0),
(176, 24, 'PT20190118144932860616', 2, 0, 0, '0.00', '0.01', '0.00', -1, 1, '', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 176, 1, 1, '0.00', 0, 1547795992, 1, 1547794172, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(175, 24, 'PT20190118143544887796', 7, 1547793351, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 175, 1, 1, '0.00', 1547793351, 1547796954, 1, 1547793344, 0, 0, 0, 0, 1547804984, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000247201901188244356227', 0, 0),
(174, 24, 'PT20190118143512662647', 2, 1547793317, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 174, 1, 1, '0.00', 1547793317, 0, 1, 1547793312, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000244201901188897070998', 0, 0),
(173, 24, 'PT20190118143443698256', 7, 1547793323, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 173, 1, 1, '0.00', 1547793323, 1547797014, 1, 1547793283, 0, 0, 0, 0, 1547804982, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000243201901184902990722', 0, 0),
(172, 21, 'PT20190118142900399842', 2, 1547792947, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 172, 1, 1, '0.00', 1547792947, 1547796593, 1, 1547792940, 0, 0, 0, 0, 1547804981, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000242201901188590805101', 0, 0),
(171, 21, 'PT20190118142601426524', 2, 1547792769, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 170, 1, 0, '0.00', 1547792769, 0, 1, 1547792761, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000245201901180856896889', 0, 0),
(170, 24, 'PT20190118142411016138', 2, 1547792656, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 170, 1, 1, '0.00', 1547792656, 0, 1, 1547792651, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000252201901186979159847', 0, 0),
(169, 21, 'PT20190118142327366604', 2, 1547792613, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 169, 1, 1, '0.00', 1547792613, 1547796233, 1, 1547792607, 0, 0, 0, 0, 1547804980, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000244201901187599158093', 0, 0),
(168, 21, 'PT20190118140121668574', 2, 1547792551, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 168, 1, 1, '0.00', 1547792551, 1547796173, 1, 1547791281, 0, 0, 0, 0, 1547804979, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000249201901180525528646', 0, 0),
(167, 21, 'PT20190118134654299944', 2, 1547790421, 0, '0.00', '0.01', '0.01', 1, 1, 'wechat', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 166, 1, 0, '0.00', 1547790421, 0, 1, 1547790414, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000242201901180367614675', 0, 0),
(166, 24, 'PT20190118134553810309', 2, 1547790358, 0, '0.00', '0.01', '0.01', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 8, 166, 1, 1, '0.00', 1547790358, 0, 1, 1547790353, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '52', '4200000239201901185306894098', 0, 0),
(165, 24, 'PT20190118134430466498', 2, 1547790275, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 164, 1, 0, '0.00', 1547790275, 0, 1, 1547790270, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000241201901188304651801', 0, 0),
(164, 22, 'PT20190118134323964410', 2, 1547790208, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 164, 1, 1, '0.00', 1547790208, 0, 1, 1547790203, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000242201901187714189651', 0, 0),
(163, 22, 'PT20190118114618280724', 2, 1547783199, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 162, 1, 0, '0.00', 1547783199, 1547785295, 1, 1547783178, 0, 0, 0, 0, 1547804977, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000257201901188463251830', 0, 0),
(162, 22, 'PT20190118112033588152', 2, 1547781639, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 162, 1, 1, '0.00', 1547781639, 1547785295, 1, 1547781633, 0, 0, 0, 0, 1547804976, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000251201901189954096186', 0, 0),
(160, 21, 'PT20190118101042542464', 7, 1547777449, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 158, 1, 0, '0.00', 1547777449, 1547780487, 1, 1547777442, 0, 0, 0, 0, 1547804975, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000257201901180973290952', 0, 0),
(159, 22, 'PT20190118100710698443', 2, 1547777236, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 159, 1, 1, '0.00', 1547777236, 0, 1, 1547777230, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000254201901182398924019', 0, 0),
(158, 22, 'PT20190118100117141844', 7, 1547776885, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 158, 1, 1, '0.00', 1547776885, 1547780487, 1, 1547776877, 0, 0, 0, 0, 1547804973, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000244201901182046607992', 0, 0),
(157, 21, 'PT20190118095630482902', 2, 1547776600, 0, '0.00', '0.01', '0.00', 1, 2, 'alipay', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 157, 1, 1, '0.00', 1547776600, 1547780246, 1, 1547776590, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '2019011822001472151023190077', 0, 0),
(184, 24, 'PT20190118170709491226', 2, 1547802436, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 184, 1, 1, '0.00', 1547802436, 1547806063, 1, 1547802429, 0, 0, 0, 0, 1548033091, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000240201901187697771956', 0, 0),
(178, 22, 'PT20190118152514288268', 2, 1547796318, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 174, 1, 0, '0.00', 1547796318, 0, 1, 1547796314, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000253201901181270532305', 0, 0),
(161, 21, 'PT20190118101125979899', 2, 1547777493, 0, '0.00', '0.01', '0.00', 1, 2, 'alipay', 0, 2, 'a:16:{s:2:\"id\";i:2;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 159, 1, 0, '0.00', 1547777493, 0, 1, 1547777485, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '2019011822001472151023076851', 0, 0),
(210, 24, 'PT20190121114936946831', 2, 1548042581, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 210, 1, 1, '0.00', 1548042581, 1548046189, 1, 1548042576, 0, 0, 0, 0, 1548064962, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000255201901212524014392', 0, 0),
(211, 24, 'PT20190121141951289586', 2, 1548051600, 0, '0.00', '0.01', '0.00', 1, 2, 'alipay', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 211, 1, 1, '0.00', 1548051600, 1548055206, 1, 1548051591, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '2019012122001436831013273901', 0, 0),
(212, 24, 'PT20190121142011449301', 7, 1548051617, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 212, 1, 1, '0.00', 1548051617, 1548055226, 1, 1548051611, 0, 0, 0, 0, 1548064963, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000258201901216486510738', 0, 0),
(213, 24, 'PT20190121142115760682', 7, 1548051680, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 213, 1, 1, '0.00', 1548051680, 1548055287, 1, 1548051675, 0, 0, 0, 0, 1548064964, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000247201901212302363833', 0, 0),
(214, 24, 'PT20190121142134542922', 7, 1548051701, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 214, 1, 1, '0.00', 1548051701, 1548055307, 1, 1548051694, 0, 0, 0, 0, 1548064965, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '4200000241201901215444177092', 0, 0),
(215, 24, 'PT20190121143214822243', 3, 1548052339, 0, '0.00', '0.04', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 215, 1, 1, '0.00', 1548052339, 1548055942, 1, 1548052334, 0, 0, 0, 0, 1548064966, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 9, 1, 0, '', '', '0.00', 0, '', '4200000240201901215186280961', 0, 0),
(216, 24, 'PT20190121144037429429', 2, 0, 0, '0.00', '0.01', '0.00', -1, 1, '', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 216, 1, 1, '0.00', 0, 1548054642, 1, 1548052837, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(217, 24, 'PT20190121144045718322', 2, 1548052850, 0, '0.00', '0.01', '0.00', -1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 217, 1, 1, '0.00', 1548052850, 1548056455, 1, 1548052845, 0, 0, 0, 0, 1548064968, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000257201901210742114091', 0, 0),
(218, 24, 'PT20190121175001625422', 2, 1548064207, 0, '0.00', '0.01', '0.00', 1, 1, 'wechat', 0, 6, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 218, 1, 1, '0.00', 1548064207, 1548067813, 1, 1548064201, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', -1, 0, '', '', 0, 0, '0', 1, 0, 0, 1, 0, '', 0, 8, 1, 0, '', '', '0.00', 0, '', '4200000246201901213527032538', 0, 0),
(219, 22, 'PT20190129140402626488', 7, 0, 0, '0.00', '0.01', '0.00', -1, 1, '', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 219, 1, 1, '0.00', 0, 1548743654, 1, 1548741842, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(220, 22, 'PT20190129153510528284', 7, 0, 0, '0.00', '0.01', '0.00', -1, 1, '', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 220, 1, 1, '0.00', 0, 1548749115, 1, 1548747310, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(221, 25, 'PT20190129170509986900', 20, 0, 0, '0.00', '0.06', '0.00', -1, 1, '', 0, 8, 'a:16:{s:2:\"id\";i:8;s:3:\"mid\";i:25;s:8:\"realname\";s:9:\"李晶晶\";s:6:\"mobile\";s:11:\"18487165037\";s:8:\"province\";s:9:\"安徽省\";s:4:\"city\";s:9:\"合肥市\";s:4:\"area\";s:9:\"瑶海区\";s:7:\"address\";s:6:\"昆明\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 221, 1, 0, '0.00', 0, 1548754515, 1, 1548752709, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '不不不', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 13, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(222, 25, 'PT20190129174624884249', 2, 0, 0, '0.00', '100.00', '0.00', -1, 1, '', 0, 8, 'a:16:{s:2:\"id\";i:8;s:3:\"mid\";i:25;s:8:\"realname\";s:9:\"李晶晶\";s:6:\"mobile\";s:11:\"18487165037\";s:8:\"province\";s:9:\"安徽省\";s:4:\"city\";s:9:\"合肥市\";s:4:\"area\";s:9:\"瑶海区\";s:7:\"address\";s:6:\"昆明\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 11, 222, 1, 0, '0.00', 0, 1548757425, 5, 1548755184, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 0, 0, 1, '', '', '0.00', 0, '54_65', '', 0, 0);
INSERT INTO `suliss_shop_groups_order` (`id`, `mid`, `orderno`, `groupnum`, `paytime`, `credit`, `creditmoney`, `price`, `freight`, `status`, `paytype`, `pay_type`, `dispatchid`, `addressid`, `address`, `goodsid`, `teamid`, `is_team`, `heads`, `discount`, `starttime`, `canceltime`, `endtime`, `createtime`, `finishtime`, `successtime`, `refundid`, `refundstate`, `refundtime`, `express`, `expresscom`, `expresssn`, `sendtime`, `remark`, `remarkclose`, `remarksend`, `message`, `success`, `deleted`, `realname`, `mobile`, `verifytype`, `isverify`, `verifycode`, `verifynum`, `printstate`, `printstate2`, `apppay`, `isborrow`, `borrowopenid`, `source`, `ladder_id`, `is_ladder`, `more_spec`, `wxapp_prepay_id`, `cancel_reason`, `goods_price`, `goods_option_id`, `specs`, `transid`, `iscomment`, `delete`) VALUES
(223, 25, 'PT20190129175100146664', 3, 0, 0, '0.00', '588.00', '0.00', -1, 1, '', 0, 8, 'a:16:{s:2:\"id\";i:8;s:3:\"mid\";i:25;s:8:\"realname\";s:9:\"李晶晶\";s:6:\"mobile\";s:11:\"18487165037\";s:8:\"province\";s:9:\"安徽省\";s:4:\"city\";s:9:\"合肥市\";s:4:\"area\";s:9:\"瑶海区\";s:7:\"address\";s:6:\"昆明\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 6, 223, 1, 0, '0.00', 0, 1548757425, 1, 1548755460, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 4, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(224, 25, 'PT20190129175205892408', 6, 0, 0, '0.00', '0.02', '0.00', -1, 1, '', 0, 8, 'a:16:{s:2:\"id\";i:8;s:3:\"mid\";i:25;s:8:\"realname\";s:9:\"李晶晶\";s:6:\"mobile\";s:11:\"18487165037\";s:8:\"province\";s:9:\"安徽省\";s:4:\"city\";s:9:\"合肥市\";s:4:\"area\";s:9:\"瑶海区\";s:7:\"address\";s:6:\"昆明\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 224, 1, 0, '0.00', 0, 1548757425, 1, 1548755525, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 11, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(225, 23, 'PT20190129180256230614', 20, 0, 0, '0.00', '0.06', '0.00', -1, 1, '', 0, 9, 'a:16:{s:2:\"id\";i:9;s:3:\"mid\";i:23;s:8:\"realname\";s:6:\"许嵩\";s:6:\"mobile\";s:11:\"15559952836\";s:8:\"province\";s:9:\"安徽省\";s:4:\"city\";s:9:\"合肥市\";s:4:\"area\";s:9:\"瑶海区\";s:7:\"address\";s:6:\"昆明\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 225, 1, 0, '0.00', 0, 1548757987, 1, 1548756176, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 13, 1, 0, '', '', '0.00', 0, '', '', 0, 0),
(226, 22, 'PT20190129181033678454', 7, 0, 0, '0.00', '0.01', '0.00', -1, 1, '', 0, 3, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 9, 226, 1, 1, '0.00', 0, 1548758449, 1, 1548756633, 0, 0, 0, 0, 0, '', '', '', 0, '', NULL, NULL, '', 0, 0, '', '', 0, 0, '0', 1, 0, 0, 0, 0, '', 0, 12, 1, 0, '', '', '0.00', 0, '', '', 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_order_comment`
--

CREATE TABLE `suliss_shop_groups_order_comment` (
  `id` int(11) NOT NULL,
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
  `isanonymous` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_order_comment`
--

TRUNCATE TABLE `suliss_shop_groups_order_comment`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_order_goods`
--

CREATE TABLE `suliss_shop_groups_order_goods` (
  `id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL DEFAULT '0',
  `groups_goods_id` int(11) NOT NULL DEFAULT '0',
  `groups_goods_option_id` int(11) NOT NULL DEFAULT '0',
  `groups_order_id` int(11) NOT NULL DEFAULT '0',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `option_name` varchar(255) NOT NULL DEFAULT '',
  `create_time` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_order_goods`
--

TRUNCATE TABLE `suliss_shop_groups_order_goods`;
--
-- 转存表中的数据 `suliss_shop_groups_order_goods`
--

INSERT INTO `suliss_shop_groups_order_goods` (`id`, `goods_id`, `groups_goods_id`, `groups_goods_option_id`, `groups_order_id`, `price`, `option_name`, `create_time`) VALUES
(201, 2, 9, 0, 218, '0.01', '', 1548064201),
(199, 2, 9, 0, 216, '0.01', '', 1548052837),
(200, 2, 9, 0, 217, '0.01', '', 1548052845),
(198, 2, 9, 0, 215, '0.04', '', 1548052334),
(197, 2, 9, 0, 214, '0.01', '', 1548051694),
(196, 2, 9, 0, 213, '0.01', '', 1548051675),
(195, 2, 9, 0, 212, '0.01', '', 1548051611),
(193, 2, 9, 0, 210, '0.01', '', 1548042576),
(194, 2, 9, 0, 211, '0.01', '', 1548051591),
(192, 2, 9, 0, 209, '0.02', '', 1548040605),
(191, 2, 9, 0, 208, '0.01', '', 1548040392),
(190, 2, 9, 0, 207, '0.01', '', 1548040253),
(189, 2, 9, 0, 206, '0.01', '', 1548040150),
(187, 2, 9, 0, 204, '0.01', '', 1548039418),
(188, 2, 9, 0, 205, '0.01', '', 1548040142),
(186, 2, 9, 0, 203, '0.01', '', 1548039310),
(184, 2, 9, 0, 201, '0.01', '', 1548038506),
(185, 2, 9, 0, 202, '0.01', '', 1548039179),
(182, 2, 9, 0, 199, '0.01', '', 1548038415),
(183, 2, 9, 0, 200, '0.01', '', 1548038480),
(181, 2, 9, 0, 198, '0.01', '', 1548037115),
(180, 2, 9, 0, 197, '0.01', '', 1548036275),
(179, 2, 9, 0, 196, '0.01', '', 1548033347),
(178, 2, 9, 0, 195, '0.01', '', 1548030527),
(177, 2, 9, 0, 194, '0.01', '', 1547803721),
(176, 2, 9, 0, 193, '0.01', '', 1547803409),
(175, 1, 8, 1, 192, '0.01', '1.5m（5英尺）床', 1547803224),
(174, 2, 9, 0, 191, '0.01', '', 1547803185),
(173, 1, 8, 1, 190, '0.01', '1.5m（5英尺）床', 1547802889),
(172, 1, 8, 1, 189, '0.01', '1.5m（5英尺）床', 1547802852),
(171, 1, 8, 1, 188, '0.01', '1.5m（5英尺）床', 1547802807),
(170, 1, 8, 1, 187, '0.01', '1.5m（5英尺）床', 1547802663),
(169, 1, 8, 1, 186, '0.01', '1.5m（5英尺）床', 1547802545),
(168, 1, 8, 1, 185, '0.01', '1.5m（5英尺）床', 1547802514),
(167, 2, 9, 0, 184, '0.01', '', 1547802429),
(166, 2, 9, 0, 183, '0.01', '', 1547799419),
(165, 2, 9, 0, 182, '0.01', '', 1547796537),
(164, 2, 9, 0, 181, '0.01', '', 1547796482),
(163, 2, 9, 0, 180, '0.01', '', 1547796373),
(162, 2, 9, 0, 179, '0.01', '', 1547796356),
(161, 2, 9, 0, 178, '0.01', '', 1547796314),
(160, 2, 9, 0, 177, '0.01', '', 1547796289),
(159, 2, 9, 0, 176, '0.01', '', 1547794172),
(158, 2, 9, 0, 175, '0.01', '', 1547793344),
(157, 2, 9, 0, 174, '0.01', '', 1547793312),
(156, 2, 9, 0, 173, '0.01', '', 1547793283),
(155, 2, 9, 0, 172, '0.01', '', 1547792940),
(154, 2, 9, 0, 171, '0.01', '', 1547792761),
(153, 2, 9, 0, 170, '0.01', '', 1547792651),
(152, 2, 9, 0, 169, '0.01', '', 1547792607),
(151, 2, 9, 0, 168, '0.01', '', 1547791281),
(148, 2, 9, 0, 165, '0.01', '', 1547790270),
(149, 1, 8, 1, 166, '0.01', '1.5m（5英尺）床', 1547790353),
(150, 1, 8, 1, 167, '0.01', '1.5m（5英尺）床', 1547790414),
(147, 2, 9, 0, 164, '0.01', '', 1547790203),
(146, 2, 9, 0, 163, '0.01', '', 1547783178),
(145, 2, 9, 0, 162, '0.01', '', 1547781633),
(144, 2, 9, 0, 161, '0.01', '', 1547777485),
(143, 2, 9, 0, 160, '0.01', '', 1547777442),
(142, 2, 9, 0, 159, '0.01', '', 1547777230),
(140, 2, 9, 0, 157, '0.01', '', 1547776590),
(141, 2, 9, 0, 158, '0.01', '', 1547776877),
(202, 2, 9, 0, 219, '0.01', '', 1548741842),
(203, 2, 9, 0, 220, '0.01', '', 1548747310),
(204, 2, 9, 0, 221, '0.06', '', 1548752709),
(205, 1, 11, 7, 222, '100.00', '粉色+1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', 1548755184),
(206, 7, 6, 0, 223, '588.00', '', 1548755460),
(207, 2, 9, 0, 224, '0.02', '', 1548755525),
(208, 2, 9, 0, 225, '0.06', '', 1548756176),
(209, 2, 9, 0, 226, '0.01', '', 1548756633);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_order_refund`
--

CREATE TABLE `suliss_shop_groups_order_refund` (
  `id` int(11) NOT NULL,
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
  `rexpresssn` varchar(45) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_order_refund`
--

TRUNCATE TABLE `suliss_shop_groups_order_refund`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_set`
--

CREATE TABLE `suliss_shop_groups_set` (
  `id` int(11) NOT NULL,
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
  `headsdiscount` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_set`
--

TRUNCATE TABLE `suliss_shop_groups_set`;
--
-- 转存表中的数据 `suliss_shop_groups_set`
--

INSERT INTO `suliss_shop_groups_set` (`id`, `groups`, `groupsurl`, `groups_description`, `description`, `opengroups`, `creditdeduct`, `groupsdeduct`, `credit`, `groupsmoney`, `refund`, `refundday`, `goodsid`, `rules`, `receive`, `discount`, `headstype`, `headsmoney`, `headsdiscount`) VALUES
(2, 0, NULL, '', 0, 0, 0, 0, 1, '0.00', 1, 0, '0', '', 0, 0, 0, '0.00', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_groups_verify`
--

CREATE TABLE `suliss_shop_groups_verify` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `orderid` int(11) DEFAULT '0',
  `verifycode` varchar(45) DEFAULT '',
  `storeid` int(11) DEFAULT '0',
  `verifier` int(11) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `verifytime` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_groups_verify`
--

TRUNCATE TABLE `suliss_shop_groups_verify`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_member_address`
--

CREATE TABLE `suliss_shop_member_address` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `realname` varchar(20) DEFAULT '',
  `mobile` varchar(11) DEFAULT '',
  `province` varchar(30) DEFAULT '',
  `city` varchar(30) DEFAULT '',
  `area` varchar(30) DEFAULT '',
  `address` varchar(300) DEFAULT '',
  `isdefault` tinyint(1) DEFAULT '0',
  `zipcode` varchar(255) DEFAULT '',
  `deleted` tinyint(1) DEFAULT '0',
  `street` varchar(50) NOT NULL DEFAULT '',
  `datavalue` varchar(50) NOT NULL DEFAULT '',
  `streetdatavalue` varchar(30) NOT NULL DEFAULT '',
  `lng` varchar(255) NOT NULL DEFAULT '',
  `lat` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_member_address`
--

TRUNCATE TABLE `suliss_shop_member_address`;
--
-- 转存表中的数据 `suliss_shop_member_address`
--

INSERT INTO `suliss_shop_member_address` (`id`, `mid`, `realname`, `mobile`, `province`, `city`, `area`, `address`, `isdefault`, `zipcode`, `deleted`, `street`, `datavalue`, `streetdatavalue`, `lng`, `lat`) VALUES
(1, 27, '敖敖', '13099907747', '云南省', '昆明市', '西山区', '万达广场', 1, '', 0, '', '', '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_member_cart`
--

CREATE TABLE `suliss_shop_member_cart` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `total` int(11) DEFAULT '0',
  `marketprice` decimal(10,2) DEFAULT '0.00',
  `deleted` tinyint(1) DEFAULT '0',
  `optionid` int(11) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `selected` tinyint(1) DEFAULT '1',
  `selectedadd` tinyint(1) DEFAULT '1',
  `merchid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_member_cart`
--

TRUNCATE TABLE `suliss_shop_member_cart`;
--
-- 转存表中的数据 `suliss_shop_member_cart`
--

INSERT INTO `suliss_shop_member_cart` (`id`, `mid`, `goodsid`, `total`, `marketprice`, `deleted`, `optionid`, `createtime`, `selected`, `selectedadd`, `merchid`) VALUES
(297, 21, 3, 1, '35.00', 1, 139, 1544508205, 1, 1, 1),
(298, 21, 3, 1, '35.00', 1, 144, 1544508211, 1, 1, 1),
(299, 22, 7, 1, '599.00', 0, 0, 1545792296, 1, 1, 0),
(300, 22, 1, 1, '399.00', 0, 129, 1545792313, 1, 1, 0),
(301, 23, 3, 1, '35.00', 1, 143, 1547090278, 1, 1, 1),
(302, 23, 3, 1, '35.00', 1, 139, 1547092351, 1, 1, 1),
(303, 24, 1, 1, '399.00', 0, 129, 1547099289, 1, 1, 0),
(304, 24, 1, 1, '399.00', 0, 131, 1547099297, 1, 1, 0),
(305, 23, 3, 1, '35.00', 0, 142, 1547107120, 1, 1, 1),
(306, 25, 3, 1, '35.00', 1, 142, 1547629934, 1, 1, 1),
(307, 25, 7, 2, '599.00', 1, 0, 1547629939, 1, 1, 0),
(308, 25, 1, 1, '399.00', 1, 146, 1547629970, 1, 1, 0),
(309, 21, 3, 1, '0.01', 0, 144, 1563759676, 1, 1, 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_member_history`
--

CREATE TABLE `suliss_shop_member_history` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `goodsid` int(10) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `times` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_member_history`
--

TRUNCATE TABLE `suliss_shop_member_history`;
--
-- 转存表中的数据 `suliss_shop_member_history`
--

INSERT INTO `suliss_shop_member_history` (`id`, `mid`, `goodsid`, `deleted`, `createtime`, `times`, `merchid`) VALUES
(120, 21, 1, 0, 1544455373, 13, 0),
(121, 21, 2, 0, 1544455379, 5, 0),
(122, 21, 3, 0, 1544506144, 13, 0),
(123, 21, 6, 0, 1544507857, 10, 0),
(124, 21, 4, 0, 1544514331, 3, 0),
(125, 21, 5, 0, 1544514368, 2, 0),
(126, 21, 7, 0, 1544520392, 1, 0),
(127, 22, 7, 0, 1544602953, 18, 0),
(128, 22, 4, 0, 1544602966, 6, 0),
(129, 22, 6, 0, 1544603522, 11, 0),
(130, 22, 1, 0, 1544603794, 41, 0),
(131, 22, 2, 0, 1544603829, 21, 0),
(132, 22, 5, 0, 1544606153, 8, 0),
(133, 22, 3, 0, 1545723832, 11, 0),
(134, 22, 9, 0, 1545796275, 6, 0),
(135, 21, 8, 0, 1545806960, 1, 0),
(136, 21, 9, 0, 1545806964, 3, 0),
(137, 23, 9, 0, 1546579862, 4, 0),
(138, 23, 8, 0, 1546583326, 3, 0),
(139, 23, 7, 0, 1546583327, 6, 0),
(140, 23, 5, 0, 1546588940, 5, 0),
(141, 23, 2, 0, 1546589969, 32, 0),
(142, 23, 3, 0, 1546655664, 12, 0),
(143, 23, 6, 0, 1546673637, 6, 0),
(144, 23, 4, 0, 1546843921, 4, 0),
(145, 23, 1, 0, 1546843934, 2, 0),
(146, 24, 2, 0, 1547021859, 9, 0),
(147, 24, 9, 0, 1547023071, 2, 0),
(148, 24, 1, 0, 1547024484, 10, 0),
(149, 24, 8, 0, 1547084686, 3, 0),
(150, 22, 8, 0, 1547094955, 1, 0),
(151, 24, 6, 0, 1547518003, 5, 0),
(152, 25, 2, 0, 1547629925, 15, 0),
(153, 25, 3, 0, 1547629930, 5, 0),
(154, 25, 7, 0, 1547629938, 2, 0),
(155, 25, 4, 0, 1547629942, 6, 0),
(156, 25, 1, 0, 1547629946, 4, 0),
(157, 25, 6, 0, 1548040506, 2, 0),
(158, 24, 3, 0, 1548059508, 4, 0),
(159, 25, 5, 0, 1548732292, 2, 0),
(160, 27, 3, 0, 1566178337, 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch`
--

CREATE TABLE `suliss_shop_merch` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `regid` int(11) DEFAULT '0',
  `groupid` int(11) DEFAULT '0',
  `merchno` varchar(255) NOT NULL DEFAULT '',
  `merchname` varchar(255) NOT NULL DEFAULT '',
  `salecate` varchar(255) NOT NULL DEFAULT '',
  `score` decimal(10,2) NOT NULL DEFAULT '0.00',
  `desc` varchar(500) NOT NULL DEFAULT '',
  `realname` varchar(255) NOT NULL DEFAULT '',
  `mobile` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `accounttime` int(11) DEFAULT '0',
  `applytime` int(11) DEFAULT '0',
  `accounttotal` int(11) DEFAULT '0',
  `remark` text,
  `collectcount` int(11) NOT NULL DEFAULT '0',
  `jointime` int(11) DEFAULT '0',
  `accountid` int(11) DEFAULT '0',
  `sets` text,
  `logo` varchar(255) NOT NULL DEFAULT '',
  `banner` text NOT NULL,
  `paymid` int(11) NOT NULL DEFAULT '0',
  `payrate` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isrecommand` tinyint(1) DEFAULT '0',
  `cateid` int(11) DEFAULT '0',
  `address` varchar(255) DEFAULT '',
  `tel` varchar(255) DEFAULT '',
  `lat` varchar(255) DEFAULT '',
  `lng` varchar(255) DEFAULT '',
  `pluginset` text NOT NULL,
  `uname` varchar(50) NOT NULL DEFAULT '',
  `upass` varchar(255) NOT NULL DEFAULT '',
  `maxgoods` int(11) NOT NULL DEFAULT '0',
  `iscredit` tinyint(3) NOT NULL DEFAULT '1',
  `creditrate` int(10) NOT NULL DEFAULT '1',
  `iscreditmoney` int(3) NOT NULL DEFAULT '1',
  `deleted` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch`
--

TRUNCATE TABLE `suliss_shop_merch`;
--
-- 转存表中的数据 `suliss_shop_merch`
--

INSERT INTO `suliss_shop_merch` (`id`, `mid`, `regid`, `groupid`, `merchno`, `merchname`, `salecate`, `score`, `desc`, `realname`, `mobile`, `status`, `accounttime`, `applytime`, `accounttotal`, `remark`, `collectcount`, `jointime`, `accountid`, `sets`, `logo`, `banner`, `paymid`, `payrate`, `isrecommand`, `cateid`, `address`, `tel`, `lat`, `lng`, `pluginset`, `uname`, `upass`, `maxgoods`, `iscredit`, `creditrate`, `iscreditmoney`, `deleted`) VALUES
(1, 0, 0, 1, '', '我的小店', '食物', '0.00', '我的小店aaa', '敖敖', '13099907747', 1, 1594569600, 0, 5, '', 2, 1544451879, 1, NULL, '/public/attachment/images/20181210/8e04a93737ef839f657c19719adf5973.jpg', '/public/attachment/images/20181211/b28494b5c43308a65c830ebf2d850a50.jpg', 0, '10.00', 1, 5, '云南省昆明市西山区万达广场', '0871-67170726', '25.014105290187', '102.71640067451092', 'a:1:{s:10:\"creditshop\";a:1:{s:5:\"close\";s:1:\"0\";}}', '', '', 15, 1, 0, 1, 0),
(2, 0, 0, 1, '', '123', '咖啡', '0.00', '是否但是如果如果特古特', '12345678987', '13456788765', 1, 1575993600, 0, 0, '123456789', 0, 1544523188, 3, NULL, '/public/attachment/images/20181211/563239374c718feec35d607f51cf6d2c.jpg', '', 0, '0.00', 1, 5, '是否但是如果如果特古特', '12345676543', '', '', 'a:1:{s:10:\"creditshop\";a:1:{s:5:\"close\";s:1:\"0\";}}', '', '', 2, 0, 12, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_account`
--

CREATE TABLE `suliss_shop_merch_account` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `username` varchar(255) DEFAULT '',
  `pwd` varchar(255) DEFAULT '',
  `salt` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `perms` text,
  `isfounder` tinyint(3) DEFAULT '0',
  `lastip` varchar(255) DEFAULT '',
  `lastvisit` varchar(255) DEFAULT '',
  `roleid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_account`
--

TRUNCATE TABLE `suliss_shop_merch_account`;
--
-- 转存表中的数据 `suliss_shop_merch_account`
--

INSERT INTO `suliss_shop_merch_account` (`id`, `mid`, `merchid`, `username`, `pwd`, `salt`, `status`, `perms`, `isfounder`, `lastip`, `lastvisit`, `roleid`) VALUES
(1, 0, 1, 'doncheng', 'a65509024759f46f65abae024db05545', 'K4z3oof3', 1, 'a:0:{}', 1, '', '', 0),
(2, 0, 1, 'account1', '0d77d87adcc31bb808026b5e74949d58', 'EwEAD44r', 1, NULL, 0, '', '', 1),
(3, 0, 2, '蒙蒙', 'affe88fc5745e9ef1bd6fad987ff1a7a', 'vghoHzLm', 1, 'a:0:{}', 1, '', '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_account_log`
--

CREATE TABLE `suliss_shop_merch_account_log` (
  `id` int(11) NOT NULL,
  `uid` int(11) DEFAULT '0',
  `name` varchar(255) DEFAULT '',
  `type` varchar(255) DEFAULT '',
  `op` text,
  `ip` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `merchid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_account_log`
--

TRUNCATE TABLE `suliss_shop_merch_account_log`;
--
-- 转存表中的数据 `suliss_shop_merch_account_log`
--

INSERT INTO `suliss_shop_merch_account_log` (`id`, `uid`, `name`, `type`, `op`, `ip`, `createtime`, `merchid`) VALUES
(1, 1, 'shop.category.add', 'shop.category.add', '添加分类 ID: 20', '218.62.244.141', 1544452406, 1),
(2, 1, 'shop.category.add', 'shop.category.add', '添加分类 ID: 21', '218.62.244.141', 1544452458, 1),
(3, 1, 'shop.category.add', 'shop.category.add', '添加分类 ID: 22', '218.62.244.141', 1544452522, 1),
(4, 1, 'shop.category.add', 'shop.category.add', '添加分类 ID: 23', '218.62.244.141', 1544452550, 1),
(5, 1, 'shop.category.add', 'shop.category.add', '添加分类 ID: 24', '218.62.244.141', 1544452587, 1),
(6, 1, 'shop.category.add', 'shop.category.add', '添加分类 ID: 25', '218.62.244.141', 1544452678, 1),
(7, 1, 'shop.category.add', 'shop.category.add', '添加分类 ID: 26', '218.62.244.141', 1544452693, 1),
(8, 1, 'goods.add', 'goods.add', '添加商品 ID: 2<br>是否参与分销 -- 是', '218.62.244.141', 1544455063, 1),
(9, 1, 'goods.edit', 'goods.edit', '编辑商品 ID: 2<br>是否参与分销 -- 是', '182.245.71.143', 1544493889, 1),
(10, 1, 'goods.edit', 'goods.edit', '编辑商品 ID: 2<br>是否参与分销 -- 是', '182.245.71.143', 1544494112, 1),
(11, 1, 'goods.add', 'goods.add', '添加商品 ID: 3<br>是否参与分销 -- 是', '182.245.71.143', 1544505973, 1),
(12, 1, 'goods.add', 'goods.add', '添加商品 ID: 5<br>是否参与分销 -- 是', '182.245.71.143', 1544507200, 1),
(13, 1, 'perm.role.add', 'perm.role.add', '添加角色 ID: 1 ', '182.245.71.143', 1544511296, 1),
(14, 1, 'goods.edit', 'goods.edit', '编辑商品 ID: 5<br>是否参与分销 -- 是', '182.245.71.143', 1544514920, 1),
(15, 1, 'goods.edit', 'goods.edit', '编辑商品 ID: 5<br>是否参与分销 -- 是', '182.245.71.143', 1544515211, 1),
(16, 1, 'goods.edit', 'goods.edit', '编辑商品 ID: 5<br>是否参与分销 -- 是', '182.245.71.143', 1544515604, 1),
(17, 1, 'goods.edit', 'goods.edit', '编辑商品 ID: 5<br>是否参与分销 -- 是', '182.245.71.143', 1544515647, 1),
(18, 2, 'goods.add', 'goods.add', '添加商品 ID: 8<br>是否参与分销 -- 是', '182.245.71.143', 1544521451, 1),
(19, 2, 'goods.edit', 'goods.edit', '编辑商品 ID: 8<br>是否参与分销 -- 是', '182.245.71.143', 1544521459, 1),
(20, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 27', '182.245.71.143', 1544521615, 1),
(21, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 28', '182.245.71.143', 1544521639, 1),
(22, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 29', '182.245.71.143', 1544521712, 1),
(23, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 30', '182.245.71.143', 1544521768, 1),
(24, 2, 'shop.category.edit', 'shop.category.edit', '修改分类 ID: 30', '182.245.71.143', 1544521900, 1),
(25, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 31', '182.245.71.143', 1544521935, 1),
(26, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 32', '182.245.71.143', 1544521975, 1),
(27, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 33', '182.245.71.143', 1544522019, 1),
(28, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 34', '182.245.71.143', 1544522073, 1),
(29, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 35', '182.245.71.143', 1544522121, 1),
(30, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 36', '182.245.71.143', 1544522188, 1),
(31, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 37', '182.245.71.143', 1544522482, 1),
(32, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 38', '182.245.71.143', 1544522509, 1),
(33, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 39', '182.245.71.143', 1544522539, 1),
(34, 2, 'shop.category.add', 'shop.category.add', '添加分类 ID: 40', '182.245.71.143', 1544522600, 1),
(35, 2, 'goods.add', 'goods.add', '添加商品 ID: 9<br>是否参与分销 -- 是', '182.245.71.143', 1544522681, 1),
(36, 2, 'goods.edit', 'goods.edit', '编辑商品 ID: 9<br>是否参与分销 -- 是', '182.245.71.143', 1544522709, 1),
(37, 2, 'goods.edit', 'goods.edit', '编辑商品 ID: 9<br>是否参与分销 -- 是', '182.245.71.143', 1544522715, 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_bill`
--

CREATE TABLE `suliss_shop_merch_bill` (
  `id` int(11) NOT NULL,
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
  `creditstatus` tinyint(3) NOT NULL DEFAULT '0',
  `creditrate` int(10) NOT NULL DEFAULT '1',
  `creditnum` int(10) NOT NULL DEFAULT '0',
  `creditmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `passcreditnum` int(10) NOT NULL DEFAULT '0',
  `passcreditmoney` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isbillcredit` int(10) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_bill`
--

TRUNCATE TABLE `suliss_shop_merch_bill`;
--
-- 转存表中的数据 `suliss_shop_merch_bill`
--

INSERT INTO `suliss_shop_merch_bill` (`id`, `applyno`, `merchid`, `orderids`, `realprice`, `realpricerate`, `finalprice`, `payrateprice`, `payrate`, `money`, `applytime`, `checktime`, `paytime`, `invalidtime`, `refusetime`, `remark`, `status`, `ordernum`, `orderprice`, `price`, `passrealprice`, `passrealpricerate`, `passorderids`, `passordernum`, `passorderprice`, `alipay`, `bankname`, `bankcard`, `applyrealname`, `applytype`, `handpay`, `creditstatus`, `creditrate`, `creditnum`, `creditmoney`, `passcreditnum`, `passcreditmoney`, `isbillcredit`) VALUES
(1, 'MO20181211144544681166', 1, 'a:2:{i:0;i:2;i:1;i:4;}', '179.00', '161.10', '179.00', '16.11', '10.00', '0.00', 1544510744, 0, 0, 0, 0, '', 1, 2, '179.00', '179.00', '0.00', '0.00', '', 0, '0.00', '13099907747', '', '', '一个人', 2, 0, 2, 1, 0, '0.00', 0, '0.00', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_billo`
--

CREATE TABLE `suliss_shop_merch_billo` (
  `id` int(11) NOT NULL,
  `billid` int(11) NOT NULL DEFAULT '0',
  `orderid` int(11) NOT NULL DEFAULT '0',
  `ordermoney` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_billo`
--

TRUNCATE TABLE `suliss_shop_merch_billo`;
--
-- 转存表中的数据 `suliss_shop_merch_billo`
--

INSERT INTO `suliss_shop_merch_billo` (`id`, `billid`, `orderid`, `ordermoney`) VALUES
(1, 1, 2, '69.00'),
(2, 1, 4, '110.00');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_category`
--

CREATE TABLE `suliss_shop_merch_category` (
  `id` int(11) NOT NULL,
  `catename` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '0',
  `displayorder` int(11) DEFAULT '0',
  `thumb` varchar(500) DEFAULT '',
  `isrecommand` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_category`
--

TRUNCATE TABLE `suliss_shop_merch_category`;
--
-- 转存表中的数据 `suliss_shop_merch_category`
--

INSERT INTO `suliss_shop_merch_category` (`id`, `catename`, `createtime`, `status`, `displayorder`, `thumb`, `isrecommand`) VALUES
(5, '商户分类', 1544449147, 1, 50, '/public/attachment/images/20181210/8e04a93737ef839f657c19719adf5973.jpg', 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_collect`
--

CREATE TABLE `suliss_shop_merch_collect` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `deleted` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_collect`
--

TRUNCATE TABLE `suliss_shop_merch_collect`;
--
-- 转存表中的数据 `suliss_shop_merch_collect`
--

INSERT INTO `suliss_shop_merch_collect` (`id`, `mid`, `merchid`, `createtime`, `deleted`) VALUES
(4, 21, 1, 1544666141, 0),
(5, 23, 1, 1546843870, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_goods_category`
--

CREATE TABLE `suliss_shop_merch_goods_category` (
  `id` int(11) NOT NULL,
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
  `level` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_goods_category`
--

TRUNCATE TABLE `suliss_shop_merch_goods_category`;
--
-- 转存表中的数据 `suliss_shop_merch_goods_category`
--

INSERT INTO `suliss_shop_merch_goods_category` (`id`, `merchid`, `name`, `thumb`, `parentid`, `displayorder`, `isrecommand`, `description`, `ishome`, `advimg`, `advurl`, `enabled`, `level`) VALUES
(20, 1, '文体', '', 0, 50, 0, '', 0, '', '', 1, 1),
(21, 1, '文体音像', '/public/attachment/images/20181210/f185dbcdc7495b4cb34793ec8e4df07b.png', 20, 50, 1, '', 1, '', '', 1, 2),
(22, 1, '文具', '/public/attachment/images/20181210/f185dbcdc7495b4cb34793ec8e4df07b.png', 21, 50, 1, '', 1, '', '', 1, 3),
(23, 1, '户外运动', '/public/attachment/images/20181210/9f35edd2be24385e8ec05b6951a7c00b.png', 21, 50, 1, '', 1, '', '', 1, 3),
(24, 1, '乐器唱片', '/public/attachment/images/20181210/5de69687c3152bde897d888961c44e5b.png', 21, 50, 1, '', 1, '', '', 1, 3),
(25, 1, '礼品店卡', '/public/attachment/images/20181210/6704bd626143c8b130778fd0a9fdc62d.png', 20, 50, 1, '', 1, '', '', 1, 2),
(26, 1, '游戏点卡', '/public/attachment/images/20181210/6704bd626143c8b130778fd0a9fdc62d.png', 25, 50, 1, '', 1, '', '', 1, 3),
(27, 1, '服饰', '', 0, 0, 0, '', 0, '', '', 1, 1),
(28, 1, '男装', '', 27, 0, 1, '', 1, '', '', 1, 2),
(29, 1, '男士外套', '/public/attachment/images/20181210/d9422d4a2f894582f56ecb3a0fd4ee3e.png', 28, 0, 1, '', 1, '', '', 1, 3),
(30, 1, '女装', '', 27, 0, 1, '', 1, '', '', 1, 2),
(31, 1, '女士外套', '/public/attachment/images/20181211/43d75c5d89e700b778f1840cb9c1dccc.png', 30, 0, 1, '', 1, '', '', 1, 3),
(32, 1, '运动', '', 27, 0, 1, '', 1, '', '', 1, 2),
(33, 1, '男士运动外套', '/public/attachment/images/20181211/db11f9d60cddb10f53ad8dfc57624771.jpg', 32, 0, 1, '二分法如果v人格人格个人', 1, '/public/attachment/images/20181211/968d6a770a20027384acf5e6b6fcce33.jpg', '', 1, 3),
(34, 1, '电器', '', 0, 0, 0, '', 0, '', '', 1, 1),
(35, 1, '家用电器', '', 34, 0, 1, '', 1, '', '', 1, 2),
(36, 1, '生活电器', '/public/attachment/images/20181210/d2808e97f846021bc1b17e676e19b99b.png', 35, 0, 1, '', 1, '', '', 1, 3),
(37, 1, '个户健康', '/public/attachment/images/20181210/bf6dcc2a1841aade03d848d898e2ff2e.png', 35, 0, 1, '', 1, '', '', 1, 3),
(38, 1, '3C数码', '', 34, 0, 1, '', 1, '', '', 1, 2),
(39, 1, '数码', '/public/attachment/images/20181210/f4ba32b4ba313c8ac7972bb2657453e3.png', 38, 0, 1, '', 1, '', '', 1, 3),
(40, 1, '音影愉乐', '/public/attachment/images/20181210/cd4d98bd51cc0853edd17d41642f0db0.png', 38, 0, 1, '', 1, '', '', 1, 3);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_group`
--

CREATE TABLE `suliss_shop_merch_group` (
  `id` int(11) NOT NULL,
  `groupname` varchar(255) DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `isdefault` tinyint(1) DEFAULT '0',
  `goodschecked` tinyint(1) DEFAULT '0',
  `commissionchecked` tinyint(1) DEFAULT '0',
  `changepricechecked` tinyint(1) DEFAULT '0',
  `finishchecked` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_group`
--

TRUNCATE TABLE `suliss_shop_merch_group`;
--
-- 转存表中的数据 `suliss_shop_merch_group`
--

INSERT INTO `suliss_shop_merch_group` (`id`, `groupname`, `createtime`, `status`, `isdefault`, `goodschecked`, `commissionchecked`, `changepricechecked`, `finishchecked`) VALUES
(1, '商户组1', 1544449407, 1, 1, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_perm_role`
--

CREATE TABLE `suliss_shop_merch_perm_role` (
  `id` int(11) NOT NULL,
  `merchid` int(11) DEFAULT '0',
  `rolename` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `perms` text,
  `deleted` tinyint(3) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_perm_role`
--

TRUNCATE TABLE `suliss_shop_merch_perm_role`;
--
-- 转存表中的数据 `suliss_shop_merch_perm_role`
--

INSERT INTO `suliss_shop_merch_perm_role` (`id`, `merchid`, `rolename`, `status`, `perms`, `deleted`) VALUES
(1, 1, '管理员', 1, '', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_merch_reg`
--

CREATE TABLE `suliss_shop_merch_reg` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `merchname` varchar(255) DEFAULT '',
  `salecate` varchar(255) DEFAULT '',
  `desc` varchar(500) DEFAULT '',
  `realname` varchar(255) DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `applytime` int(11) DEFAULT '0',
  `reason` text,
  `uname` varchar(50) NOT NULL DEFAULT '',
  `upass` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_merch_reg`
--

TRUNCATE TABLE `suliss_shop_merch_reg`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_nav`
--

CREATE TABLE `suliss_shop_nav` (
  `id` int(11) NOT NULL,
  `navname` varchar(255) DEFAULT '',
  `icon` varchar(255) DEFAULT '',
  `url` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `status` tinyint(3) DEFAULT '0',
  `iswxapp` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_nav`
--

TRUNCATE TABLE `suliss_shop_nav`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_notice`
--

CREATE TABLE `suliss_shop_notice` (
  `id` int(11) NOT NULL,
  `displayorder` int(11) DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `cate` varchar(20) NOT NULL DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `link` varchar(255) DEFAULT '',
  `detail` text,
  `status` tinyint(3) DEFAULT '0',
  `createtime` int(11) DEFAULT NULL,
  `merchid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_notice`
--

TRUNCATE TABLE `suliss_shop_notice`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order`
--

CREATE TABLE `suliss_shop_order` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `agentid` int(11) DEFAULT '0',
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
  `createtime` int(10) DEFAULT '0',
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
  `canceltime` int(11) DEFAULT '0',
  `cancelpaytime` int(11) DEFAULT '0',
  `refundtime` int(11) DEFAULT '0',
  `isverify` tinyint(3) DEFAULT '0',
  `verified` tinyint(3) DEFAULT '0',
  `verifyoperatorid` varchar(255) DEFAULT '',
  `verifytime` int(11) DEFAULT '0',
  `verifycode` varchar(255) DEFAULT '',
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
  `closereason` text,
  `remarksaler` text,
  `printstate` tinyint(1) DEFAULT '0',
  `printstate2` tinyint(1) DEFAULT '0',
  `address_send` text,
  `refundstate` tinyint(3) DEFAULT '0',
  `remarkclose` text,
  `remarksend` text,
  `ismr` int(1) NOT NULL DEFAULT '0',
  `isdiscountprice` decimal(10,2) DEFAULT '0.00',
  `isvirtualsend` tinyint(1) DEFAULT '0',
  `virtualsend_info` text,
  `verifyinfo` text,
  `verifytype` tinyint(1) DEFAULT '0',
  `verifycodes` text,
  `merchid` int(11) DEFAULT '0',
  `invoicename` varchar(255) DEFAULT '',
  `ismerch` tinyint(1) DEFAULT '0',
  `parentid` int(11) DEFAULT '0',
  `isparent` tinyint(1) DEFAULT '0',
  `grprice` decimal(10,2) DEFAULT '0.00',
  `merchshow` tinyint(1) DEFAULT '0',
  `merchdeductenough` decimal(10,2) DEFAULT '0.00',
  `couponmerchid` int(11) DEFAULT '0',
  `isglobonus` tinyint(3) DEFAULT '0',
  `merchapply` tinyint(1) DEFAULT '0',
  `isabonus` tinyint(3) DEFAULT '0',
  `isborrow` tinyint(3) DEFAULT '0',
  `borrowopenid` varchar(100) DEFAULT '',
  `apppay` tinyint(3) NOT NULL DEFAULT '0',
  `coupongoodprice` decimal(10,2) DEFAULT '1.00',
  `buyagainprice` decimal(10,2) DEFAULT '0.00',
  `authorid` int(11) DEFAULT '0',
  `isauthor` tinyint(1) DEFAULT '0',
  `ispackage` tinyint(3) DEFAULT '0',
  `packageid` int(11) DEFAULT '0',
  `taskdiscountprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `merchisdiscountprice` decimal(10,2) DEFAULT '0.00',
  `seckilldiscountprice` decimal(10,2) DEFAULT '0.00',
  `verifyendtime` int(11) NOT NULL DEFAULT '0',
  `willcancelmessage` tinyint(1) DEFAULT '0',
  `sendtype` tinyint(3) NOT NULL DEFAULT '0',
  `lotterydiscountprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `contype` tinyint(1) DEFAULT '0',
  `dispatchkey` varchar(30) NOT NULL DEFAULT '',
  `quickid` int(11) NOT NULL DEFAULT '0',
  `istrade` tinyint(3) NOT NULL DEFAULT '0',
  `isnewstore` tinyint(3) NOT NULL DEFAULT '0',
  `liveid` int(11) DEFAULT '0',
  `ordersn_trade` varchar(32) NOT NULL DEFAULT '',
  `tradestatus` tinyint(1) DEFAULT '0',
  `tradepaytype` tinyint(1) NOT NULL DEFAULT '0',
  `tradepaytime` int(11) DEFAULT '0',
  `dowpayment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `betweenprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isshare` int(11) NOT NULL DEFAULT '0',
  `officcode` varchar(50) NOT NULL DEFAULT '',
  `wxapp_prepay_id` varchar(100) DEFAULT '',
  `iswxappcreate` tinyint(1) DEFAULT '0',
  `cashtime` int(11) DEFAULT '0',
  `random_code` varchar(4) DEFAULT '',
  `print_template` text,
  `city_express_state` tinyint(1) NOT NULL DEFAULT '0',
  `is_cashier` tinyint(3) NOT NULL DEFAULT '0',
  `commissionmoney` decimal(10,2) DEFAULT '0.00',
  `iscycelbuy` tinyint(3) DEFAULT '0',
  `cycelbuy_predict_time` int(11) DEFAULT '0',
  `cycelbuy_periodic` varchar(255) DEFAULT '',
  `invoice_img` varchar(255) DEFAULT '',
  `headsid` int(11) NOT NULL DEFAULT '0',
  `dividend` text,
  `dividend_applytime` int(11) NOT NULL DEFAULT '0',
  `dividend_checktime` int(11) NOT NULL DEFAULT '0',
  `dividend_paytime` int(11) NOT NULL DEFAULT '0',
  `dividend_invalidtime` int(11) NOT NULL DEFAULT '0',
  `dividend_deletetime` int(11) NOT NULL DEFAULT '0',
  `dividend_status` tinyint(3) NOT NULL DEFAULT '0',
  `dividend_content` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_order`
--

TRUNCATE TABLE `suliss_shop_order`;
--
-- 转存表中的数据 `suliss_shop_order`
--

INSERT INTO `suliss_shop_order` (`id`, `mid`, `agentid`, `ordersn`, `price`, `goodsprice`, `discountprice`, `status`, `paytype`, `transid`, `remark`, `addressid`, `dispatchprice`, `dispatchid`, `createtime`, `dispatchtype`, `carrier`, `refundid`, `iscomment`, `creditadd`, `deleted`, `userdeleted`, `finishtime`, `paytime`, `expresscom`, `expresssn`, `express`, `sendtime`, `fetchtime`, `cash`, `canceltime`, `cancelpaytime`, `refundtime`, `isverify`, `verified`, `verifyoperatorid`, `verifytime`, `verifycode`, `verifystoreid`, `deductprice`, `deductcredit`, `deductcredit2`, `deductenough`, `virtual`, `virtual_info`, `virtual_str`, `address`, `sysdeleted`, `ordersn2`, `changeprice`, `changedispatchprice`, `oldprice`, `olddispatchprice`, `isvirtual`, `couponid`, `couponprice`, `storeid`, `closereason`, `remarksaler`, `printstate`, `printstate2`, `address_send`, `refundstate`, `remarkclose`, `remarksend`, `ismr`, `isdiscountprice`, `isvirtualsend`, `virtualsend_info`, `verifyinfo`, `verifytype`, `verifycodes`, `merchid`, `invoicename`, `ismerch`, `parentid`, `isparent`, `grprice`, `merchshow`, `merchdeductenough`, `couponmerchid`, `isglobonus`, `merchapply`, `isabonus`, `isborrow`, `borrowopenid`, `apppay`, `coupongoodprice`, `buyagainprice`, `authorid`, `isauthor`, `ispackage`, `packageid`, `taskdiscountprice`, `merchisdiscountprice`, `seckilldiscountprice`, `verifyendtime`, `willcancelmessage`, `sendtype`, `lotterydiscountprice`, `contype`, `dispatchkey`, `quickid`, `istrade`, `isnewstore`, `liveid`, `ordersn_trade`, `tradestatus`, `tradepaytype`, `tradepaytime`, `dowpayment`, `betweenprice`, `isshare`, `officcode`, `wxapp_prepay_id`, `iswxappcreate`, `cashtime`, `random_code`, `print_template`, `city_express_state`, `is_cashier`, `commissionmoney`, `iscycelbuy`, `cycelbuy_predict_time`, `cycelbuy_periodic`, `invoice_img`, `headsid`, `dividend`, `dividend_applytime`, `dividend_checktime`, `dividend_paytime`, `dividend_invalidtime`, `dividend_deletetime`, `dividend_status`, `dividend_content`) VALUES
(1, 21, 0, 'SH20181211110412626626', '399.00', '399.00', '0.00', 1, 1, '', '', 62, '0.00', 0, 1544497452, 0, 'a:2:{s:16:\"carrier_realname\";s:0:\"\";s:14:\"carrier_mobile\";s:0:\"\";}', 0, 0, 0, 0, 0, 0, 1544499989, '', '', '', 0, 0, 0, NULL, 0, 0, 0, 0, '', 0, '', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, 'a:12:{s:2:\"id\";i:62;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:6:\"street\";s:0:\"\";s:7:\"address\";s:12:\"万达广场\";s:7:\"zipcode\";s:0:\"\";s:9:\"isdefault\";i:1;s:7:\"deleted\";i:0;}', 0, 0, '0.00', '0.00', '399.00', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '399.00', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(2, 21, 0, 'ME20181211114405928904', '69.00', '69.00', '0.00', 3, 2, '', NULL, 62, '0.00', 0, 1544499845, 0, 'a:2:{s:16:\"carrier_realname\";s:0:\"\";s:14:\"carrier_mobile\";s:0:\"\";}', 0, 0, 0, 0, 0, 1544500607, 1544500029, '申通', '54546549687454345464646684', 'shentong', 1544500601, 0, 0, NULL, 0, 0, 0, 0, '', 0, '', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, 'a:12:{s:2:\"id\";i:62;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:6:\"street\";s:0:\"\";s:7:\"address\";s:12:\"万达广场\";s:7:\"zipcode\";s:0:\"\";s:9:\"isdefault\";i:1;s:7:\"deleted\";i:0;}', 0, 0, '0.00', '0.00', '69.00', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 1, '', 1, 0, 0, '69.00', 1, NULL, 0, 0, 1, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(3, 21, 0, 'SH20181211135835449958', '109.00', '109.00', '0.00', -1, 11, '', '', 0, '0.00', 0, 1544507915, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"SUL1SS\";s:14:\"carrier_mobile\";s:11:\"13099907747\";}', 0, 0, 0, 0, 0, 0, 1544507937, '', '', '', 0, 0, 0, 1545974666, 0, 0, 1, 0, '', 0, '22214588', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '109.00', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '109.00', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545112715, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(4, 21, 0, 'ME20181211141519094488', '110.00', '110.00', '0.00', 3, 2, '', NULL, 62, '0.00', 0, 1544508919, 0, 'a:2:{s:16:\"carrier_realname\";s:0:\"\";s:14:\"carrier_mobile\";s:0:\"\";}', 0, 0, 0, 0, 0, 1544509054, 1544508930, '速尔物流', '4643546743546749', 'sue', 1544509042, 0, 0, NULL, 0, 0, 0, 0, '', 0, '', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, 'a:12:{s:2:\"id\";i:62;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:6:\"street\";s:0:\"\";s:7:\"address\";s:12:\"万达广场\";s:7:\"zipcode\";s:0:\"\";s:9:\"isdefault\";i:1;s:7:\"deleted\";i:0;}', 0, 0, '0.00', '0.00', '110.00', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 1, '', 1, 0, 0, '110.00', 1, NULL, 0, 0, 1, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(5, 22, 0, 'SH20181212163944004914', '0.01', '0.01', '0.00', -1, 1, '4200000200201812127737607934', '', 0, '0.00', 0, 1544603984, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老朱\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 1544603991, '', '', '', 0, 0, 0, 1545974759, 0, 0, 1, 0, '', 0, '68888664', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545122384, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(6, 22, 0, 'SH20181212172302423262', '0.01', '0.01', '0.00', -1, 1, '4200000194201812126626526994', '测试', 0, '0.00', 0, 1544606582, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老朱\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 1544606605, '', '', '', 0, 0, 0, 1545974759, 0, 0, 1, 0, '', 0, '66282826', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545124982, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(7, 22, 0, 'SH20181212172358248322', '0.01', '0.01', '0.00', -1, 1, '4200000208201812125904411037', '', 0, '0.00', 0, 1544606638, 0, 'a:2:{s:16:\"carrier_realname\";s:3:\"咯\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 1544606663, '', '', '', 0, 0, 0, 1545974759, 0, 0, 1, 0, '', 0, '61888564', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125038, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(8, 22, 0, 'SH20181212172547632266', '0.01', '0.01', '0.00', -1, 2, '', '', 0, '0.00', 0, 1544606747, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老朱\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361433, 0, 0, 1, 0, '', 0, '08222005', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125147, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(9, 22, 0, 'SH20181212173014080844', '0.01', '0.01', '0.00', -1, 1, '', '', 0, '0.00', 0, 1544607014, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老朱\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361434, 0, 0, 1, 0, '', 0, '29209652', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125414, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(10, 22, 0, 'SH20181212173028806222', '0.01', '0.01', '0.00', -1, 1, '', '', 0, '0.00', 0, 1544607028, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老朱\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361434, 0, 0, 1, 0, '', 0, '83484088', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125428, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(11, 22, 0, 'SH20181212173036562180', '0.01', '0.01', '0.00', -1, 1, '', '', 0, '0.00', 0, 1544607036, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老了\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361434, 0, 0, 1, 0, '', 0, '44422402', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125436, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(12, 22, 0, 'SH20181212173053232831', '0.01', '0.01', '0.00', -1, 2, '', '', 0, '0.00', 0, 1544607053, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老了\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361434, 0, 0, 1, 0, '', 0, '43086257', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125453, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(13, 22, 0, 'SH20181212173405788047', '0.01', '0.01', '0.00', -1, 1, '', '', 0, '0.00', 0, 1544607245, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"软银\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361435, 0, 0, 1, 0, '', 0, '36448346', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125645, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(14, 22, 0, 'SH20181212173408438861', '0.01', '0.01', '0.00', -1, 2, '', '', 0, '0.00', 0, 1544607248, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"软银\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361435, 0, 0, 1, 0, '', 0, '38617860', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545125648, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(15, 21, 0, 'SH20181212181347844274', '439.00', '439.00', '0.00', -1, 1, '', '', 62, '0.00', 0, 1544609627, 0, 'a:2:{s:16:\"carrier_realname\";s:0:\"\";s:14:\"carrier_mobile\";s:0:\"\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361435, 0, 0, 0, 0, '', 0, '', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, 'a:12:{s:2:\"id\";i:62;s:3:\"mid\";i:21;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:6:\"street\";s:0:\"\";s:7:\"address\";s:12:\"万达广场\";s:7:\"zipcode\";s:0:\"\";s:9:\"isdefault\";i:1;s:7:\"deleted\";i:0;}', 0, 0, '0.00', '0.00', '439.00', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '439.00', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(16, 22, 0, 'SH20181213113549155836', '0.01', '0.01', '0.00', -1, 1, '', '', 0, '0.00', 0, 1544672149, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"软银\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361435, 0, 0, 1, 0, '', 0, '35783343', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545190549, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(17, 22, 0, 'SH20181213113730286868', '0.01', '0.01', '0.00', -1, 1, '', '', 0, '0.00', 0, 1544672250, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"软银\";s:14:\"carrier_mobile\";s:11:\"18687510604\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545361436, 0, 0, 1, 0, '', 0, '38466324', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, '', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1545190650, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(18, 22, 0, 'SH20181228104430694824', '510.00', '510.00', '0.00', -1, 1, '', '', 3, '0.00', 0, 1545965070, 0, 'a:2:{s:16:\"carrier_realname\";s:0:\"\";s:14:\"carrier_mobile\";s:0:\"\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1545965083, 0, 0, 0, 0, '', 0, '', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, 'a:16:{s:2:\"id\";i:3;s:3:\"mid\";i:22;s:8:\"realname\";s:6:\"老朱\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"五华区\";s:7:\"address\";s:26:\"前卫西路高朱村20号\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 0, 0, '0.00', '0.00', '510.00', '0.00', 0, NULL, '0.00', 0, '犬夜叉', NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '510.00', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(19, 23, 0, 'SH20190109163523258024', '109.00', '109.00', '0.00', -1, 1, '', '', 0, '0.00', 0, 1547022923, 0, 'a:2:{s:16:\"carrier_realname\";s:4:\"zhut\";s:14:\"carrier_mobile\";s:11:\"15559952836\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1547023883, 0, 0, 1, 0, '', 0, '24806824', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '109.00', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '109.00', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1547627723, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(20, 25, 0, 'SH20190116171125888449', '0.01', '0.01', '0.00', -1, 1, '', '', 0, '0.00', 0, 1547629885, 0, 'a:2:{s:16:\"carrier_realname\";s:9:\"zhut\nzhut\";s:14:\"carrier_mobile\";s:11:\"18487165037\";}', 0, 0, 0, 0, 1, 0, 0, '', '', '', 0, 0, 0, 1547630800, 0, 0, 1, 0, '', 0, '79202346', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548148285, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(21, 24, 0, 'ME20190121163456042694', '0.01', '0.01', '0.00', 1, 1, '4200000247201901212212185733', NULL, 6, '0.00', 0, 1548059696, 0, 'a:2:{s:16:\"carrier_realname\";s:0:\"\";s:14:\"carrier_mobile\";s:0:\"\";}', 0, 0, 0, 0, 0, 0, 1548059702, '', '', '', 0, 0, 0, NULL, 0, 0, 0, 0, '', 0, '', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, 'a:16:{s:2:\"id\";i:6;s:3:\"mid\";i:24;s:8:\"realname\";s:9:\"朱撷潼\";s:6:\"mobile\";s:11:\"18687510604\";s:8:\"province\";s:9:\"北京市\";s:4:\"city\";s:9:\"北京市\";s:4:\"area\";s:9:\"东城区\";s:7:\"address\";s:9:\"马自达\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 0, 0, '0.00', '0.00', '0.01', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 1, '', 1, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(22, 24, 0, 'SH20190121163830829806', '0.02', '0.02', '0.00', 3, 1, '4200000240201901213243800787', '', 0, '0.00', 0, 1548059910, 0, 'a:2:{s:16:\"carrier_realname\";s:9:\"马自达\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 1548060443, 1548059922, '', '', '', 1548060443, 0, 0, NULL, 0, 0, 1, 1, '', 1548060443, '58980262', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548578310, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(23, 24, 0, 'SH20190121164937426046', '0.02', '0.02', '0.00', 3, 1, '4200000244201901213898049209', '', 0, '0.00', 0, 1548060577, 0, 'a:2:{s:16:\"carrier_realname\";s:3:\"我\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 1548060597, 1548060584, '', '', '', 1548060597, 0, 0, NULL, 0, 0, 1, 1, '', 1548060597, '33958222', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548578977, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(24, 24, 0, 'SH20190121165509466842', '0.02', '0.02', '0.00', 3, 2, '2019012122001436831013356530', '', 0, '0.00', 0, 1548060909, 0, 'a:2:{s:16:\"carrier_realname\";s:9:\"马自达\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 1548060927, 1548060914, '', '', '', 1548060927, 0, 0, NULL, 0, 0, 1, 1, '', 1548060927, '11688208', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548579309, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(25, 24, 0, 'SH20190121165602216923', '0.02', '0.02', '0.00', 3, 2, '2019012122001436831013247011', '', 0, '0.00', 0, 1548060962, 0, 'a:2:{s:16:\"carrier_realname\";s:9:\"马自达\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 1548060981, 1548060967, '', '', '', 1548060981, 0, 0, NULL, 0, 0, 1, 1, '', 1548060981, '22067946', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548579362, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(26, 25, 0, 'SH20190121174748499682', '0.02', '0.02', '0.00', -1, 1, '', '', 0, '0.00', 0, 1548064068, 0, 'a:2:{s:16:\"carrier_realname\";s:9:\"如若飞\";s:14:\"carrier_mobile\";s:11:\"18487165037\";}', 0, 0, 0, 0, 1, 0, 0, '', '', '', 0, 0, 0, 1548064968, 0, 0, 1, 0, '', 0, '43815827', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548582468, 1, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(27, 24, 0, 'SH20190121175052296476', '0.02', '0.02', '0.00', -1, 2, '2019012122001436831013274799', '', 0, '0.00', 0, 1548064252, 0, 'a:2:{s:16:\"carrier_realname\";s:9:\"临走时\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 0, 1548064257, '', '', '', 0, 0, 0, 1548582670, 0, 0, 1, 0, '', 0, '68870219', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548582652, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(28, 24, 0, 'SH20190121175144294870', '0.02', '0.02', '0.00', -1, 1, '4200000246201901212359404960', '', 0, '0.00', 0, 1548064304, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"在找\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 0, 1548064310, '', '', '', 0, 0, 0, 1548582710, 0, 0, 1, 0, '', 0, '14178385', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548582704, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(29, 24, 0, 'SH20190121175233832448', '0.02', '0.02', '0.00', -1, 1, '4200000245201901215072735472', '', 0, '0.00', 0, 1548064353, 0, 'a:2:{s:16:\"carrier_realname\";s:3:\"我\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 0, 1548064360, '', '', '', 0, 0, 0, 1548582770, 0, 0, 1, 0, '', 0, '94890678', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548582753, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(30, 24, 0, 'SH20190121175527856908', '0.02', '0.02', '0.00', -1, 1, '4200000250201901217524247239', '', 0, '0.00', 0, 1548064527, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"老朱\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 0, 1548064535, '', '', '', 0, 0, 0, 1548582931, 0, 0, 1, 0, '', 0, '16302846', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548582927, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(31, 24, 0, 'SH20190121175739476448', '0.02', '0.02', '0.00', -1, 1, '4200000242201901218370274577', '', 0, '0.00', 0, 1548064659, 0, 'a:2:{s:16:\"carrier_realname\";s:3:\"我\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 0, 1548064669, '', '', '', 0, 0, 0, 1548583071, 0, 0, 1, 0, '', 0, '80636662', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548583059, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(32, 24, 0, 'SH20190122111710248883', '0.02', '0.02', '0.00', -1, 1, '4200000245201901224469672795', '', 0, '0.00', 0, 1548127030, 0, 'a:2:{s:16:\"carrier_realname\";s:2:\"ID\";s:14:\"carrier_mobile\";s:11:\"18687510603\";}', 0, 0, 0, 0, 0, 0, 1548127042, '', '', '', 0, 0, 0, 1548645433, 0, 0, 1, 0, '', 0, '48267762', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 1, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1548645430, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(33, 25, 0, 'SH20190128174804874482', '0.02', '0.02', '0.00', -1, 2, '', '', 0, '0.00', 0, 1548668884, 0, 'a:2:{s:16:\"carrier_realname\";s:2:\"gg\";s:14:\"carrier_mobile\";s:11:\"15484152485\";}', 0, 0, 0, 0, 1, 0, 0, '', '', '', 0, 0, 0, 1548669791, 0, 0, 1, 0, '', 0, '67227611', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1549187284, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(34, 23, 0, 'SH20190129180838202447', '0.02', '0.02', '0.00', -1, 1, '', '', 0, '0.00', 0, 1548756518, 0, 'a:2:{s:16:\"carrier_realname\";s:6:\"不包\";s:14:\"carrier_mobile\";s:11:\"18487165037\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1548757425, 0, 0, 1, 0, '', 0, '80210261', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, NULL, 0, 0, '0.00', '0.00', '0.02', '0.00', 0, NULL, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 0, '', 0, 0, 0, '0.02', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 1549274918, 0, 0, '0.00', NULL, '', 0, 0, 0, 0, '', 0, 0, 0, '0.00', '0.00', 0, '0', NULL, 0, 0, NULL, NULL, 0, 0, '0.00', 0, NULL, NULL, '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL),
(36, 27, 0, 'ME20190819093938084544', '0.01', '0.01', '0.00', -1, 1, '', '', 1, '0.00', 0, 1566178778, 0, 'a:2:{s:16:\"carrier_realname\";s:0:\"\";s:14:\"carrier_mobile\";s:0:\"\";}', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, 0, 1566179692, 0, 0, 0, 0, '', 0, '', 0, '0.00', 0, '0.00', '0.00', 0, NULL, NULL, 'a:16:{s:2:\"id\";i:1;s:3:\"mid\";i:27;s:8:\"realname\";s:6:\"敖敖\";s:6:\"mobile\";s:11:\"13099907747\";s:8:\"province\";s:9:\"云南省\";s:4:\"city\";s:9:\"昆明市\";s:4:\"area\";s:9:\"西山区\";s:7:\"address\";s:12:\"万达广场\";s:9:\"isdefault\";i:1;s:7:\"zipcode\";s:0:\"\";s:7:\"deleted\";i:0;s:6:\"street\";s:0:\"\";s:9:\"datavalue\";s:0:\"\";s:15:\"streetdatavalue\";s:0:\"\";s:3:\"lng\";s:0:\"\";s:3:\"lat\";s:0:\"\";}', 0, 0, '0.00', '0.00', '0.01', '0.00', 0, 0, '0.00', 0, NULL, NULL, 0, 0, NULL, 0, NULL, NULL, 0, '0.00', 0, NULL, 'a:0:{}', 0, '', 1, '', 1, 0, 0, '0.01', 1, NULL, 0, 0, 0, 0, 0, '', 0, '0.00', '0.00', 0, 0, 0, 0, '0.00', '0.00', '0.00', 0, 0, 0, '0.00', 0, '', 0, 0, 0, 27, '', 0, 0, 0, '0.00', '0.00', 0, '0', '', 0, 0, '', NULL, 0, 0, '0.00', 0, 0, '', '', 0, NULL, 0, 0, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_comment`
--

CREATE TABLE `suliss_shop_order_comment` (
  `id` int(11) NOT NULL,
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
  `isanonymous` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_order_comment`
--

TRUNCATE TABLE `suliss_shop_order_comment`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_goods`
--

CREATE TABLE `suliss_shop_order_goods` (
  `id` int(11) NOT NULL,
  `orderid` int(11) DEFAULT '0',
  `goodsid` int(11) DEFAULT '0',
  `price` decimal(10,2) DEFAULT '0.00',
  `total` int(11) DEFAULT '1',
  `optionid` int(10) DEFAULT '0',
  `createtime` int(11) DEFAULT '0',
  `optionname` text,
  `commission1` text,
  `applytime1` int(11) DEFAULT '0',
  `checktime1` int(10) DEFAULT '0',
  `paytime1` int(11) DEFAULT '0',
  `invalidtime1` int(11) DEFAULT '0',
  `deletetime1` int(11) DEFAULT '0',
  `status1` tinyint(3) DEFAULT '0',
  `content1` text,
  `commission2` text,
  `applytime2` int(11) DEFAULT '0',
  `checktime2` int(10) DEFAULT '0',
  `paytime2` int(11) DEFAULT '0',
  `invalidtime2` int(11) DEFAULT '0',
  `deletetime2` int(11) DEFAULT '0',
  `status2` tinyint(3) DEFAULT '0',
  `content2` text,
  `commission3` text,
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
  `nocommission` tinyint(3) DEFAULT '0',
  `changeprice` decimal(10,2) DEFAULT '0.00',
  `oldprice` decimal(10,2) DEFAULT '0.00',
  `commissions` text,
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
  `remarksend` text,
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
  `single_refundid` int(11) NOT NULL DEFAULT '0',
  `single_refundstate` tinyint(3) NOT NULL DEFAULT '0',
  `single_refundtime` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_order_goods`
--

TRUNCATE TABLE `suliss_shop_order_goods`;
--
-- 转存表中的数据 `suliss_shop_order_goods`
--

INSERT INTO `suliss_shop_order_goods` (`id`, `orderid`, `goodsid`, `price`, `total`, `optionid`, `createtime`, `optionname`, `commission1`, `applytime1`, `checktime1`, `paytime1`, `invalidtime1`, `deletetime1`, `status1`, `content1`, `commission2`, `applytime2`, `checktime2`, `paytime2`, `invalidtime2`, `deletetime2`, `status2`, `content2`, `commission3`, `applytime3`, `checktime3`, `paytime3`, `invalidtime3`, `deletetime3`, `status3`, `content3`, `realprice`, `goodssn`, `productsn`, `nocommission`, `changeprice`, `oldprice`, `commissions`, `mid`, `printstate`, `printstate2`, `refundid`, `rstate`, `refundtime`, `merchid`, `parentorderid`, `merchsale`, `isdiscountprice`, `canbuyagain`, `seckill`, `seckill_taskid`, `seckill_roomid`, `seckill_timeid`, `is_make`, `sendtype`, `expresscom`, `expresssn`, `express`, `sendtime`, `finishtime`, `remarksend`, `prohibitrefund`, `storeid`, `trade_time`, `optime`, `tdate_time`, `dowpayment`, `peopleid`, `esheetprintnum`, `ordercode`, `iscomment`, `single_refundid`, `single_refundstate`, `single_refundtime`) VALUES
(6, 1, 1, '399.00', 1, 129, 1544497452, '1.5m（5英尺）床', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '399.00', '', '', 0, '0.00', '399.00', NULL, 21, 0, 0, 0, 10, 0, 0, 0, 0, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(7, 2, 2, '69.00', 1, 138, 1544499845, '圆满猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '69.00', '', '', 0, '0.00', '69.00', NULL, 21, 0, 0, 0, 12, 0, 1, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(8, 3, 6, '109.00', 1, 145, 1544507915, '300ml', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '109.00', '', '', 0, '0.00', '109.00', NULL, 21, 0, 0, 0, 10, 0, 0, 0, 0, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(9, 4, 3, '75.00', 1, 144, 1544508919, '简·青林（抱枕套+抱枕芯组合）', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '75.00', '', '', 0, '0.00', '75.00', NULL, 21, 0, 0, 0, 12, 0, 1, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(10, 4, 3, '35.00', 1, 139, 1544508919, '鹿·远望（仅抱枕套）', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '35.00', '', '', 0, '0.00', '35.00', NULL, 21, 0, 0, 0, 12, 0, 1, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(11, 5, 2, '0.01', 1, 137, 1544603984, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(12, 6, 2, '0.01', 1, 137, 1544606582, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(13, 7, 2, '0.01', 1, 138, 1544606638, '圆满猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(14, 8, 2, '0.01', 1, 138, 1544606747, '圆满猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(15, 9, 2, '0.01', 1, 137, 1544607014, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(16, 10, 2, '0.01', 1, 137, 1544607028, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(17, 11, 2, '0.01', 1, 138, 1544607036, '圆满猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(18, 12, 2, '0.01', 1, 138, 1544607053, '圆满猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(19, 13, 2, '0.01', 1, 137, 1544607245, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(20, 14, 2, '0.01', 1, 137, 1544607248, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(21, 15, 1, '439.00', 1, 130, 1544609627, '1.8m（6英尺）床', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '439.00', '', '', 0, '0.00', '439.00', NULL, 21, 0, 0, 0, 0, 0, 0, 0, 0, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(22, 16, 2, '0.01', 1, 137, 1544672149, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(23, 17, 2, '0.01', 1, 137, 1544672250, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(24, 18, 1, '510.00', 1, 131, 1545965070, '1.5m（5英尺）床+圆满猪莉抱枕+福袋猪莉抱枕', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '510.00', '', '', 0, '0.00', '510.00', NULL, 22, 0, 0, 0, 0, 0, 0, 0, 0, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(25, 19, 6, '109.00', 1, 145, 1547022923, '300ml', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '109.00', '', '', 0, '0.00', '109.00', NULL, 23, 0, 0, 0, 0, 0, 0, 0, 0, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(26, 20, 2, '0.01', 1, 137, 1547629885, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '121212', '2323', 0, '0.00', '0.01', NULL, 25, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(27, 21, 3, '0.01', 1, 139, 1548059696, '鹿·远望（仅抱枕套）', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '', '', 0, '0.00', '0.01', NULL, 24, 0, 0, 0, 0, 0, 1, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(28, 22, 2, '0.02', 1, 137, 1548059910, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(29, 23, 2, '0.02', 1, 137, 1548060577, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(30, 24, 2, '0.02', 1, 137, 1548060909, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(31, 25, 2, '0.02', 1, 137, 1548060962, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(32, 26, 2, '0.02', 1, 137, 1548064068, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 25, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(33, 27, 2, '0.02', 1, 137, 1548064252, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(34, 28, 2, '0.02', 1, 137, 1548064304, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(35, 29, 2, '0.02', 1, 137, 1548064353, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(36, 30, 2, '0.02', 1, 137, 1548064527, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(37, 31, 2, '0.02', 1, 137, 1548064659, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(38, 32, 2, '0.02', 1, 137, 1548127030, '福袋猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 24, 0, 0, 0, 10, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(39, 33, 2, '0.02', 1, 138, 1548668884, '圆满猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 25, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(40, 34, 2, '0.02', 1, 138, 1548756518, '圆满猪莉', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.02', '121212', '2323', 0, '0.00', '0.02', NULL, 23, 0, 0, 0, 0, 0, 0, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, '', 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0),
(41, 36, 3, '0.01', 1, 139, 1566178778, '鹿·远望（仅抱枕套）', NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, NULL, 0, 0, 0, 0, 0, 0, NULL, '0.01', '', '', 0, '0.00', '0.01', NULL, 27, 0, 0, 0, 0, 0, 1, 0, 1, '0.00', 0, 0, 0, 0, 0, 0, 0, '', '', '', 0, 0, NULL, 0, '', 0, '', 0, '0.00', 0, 0, '', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_refund`
--

CREATE TABLE `suliss_shop_order_refund` (
  `id` int(11) NOT NULL,
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
  `lastupdate` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_order_refund`
--

TRUNCATE TABLE `suliss_shop_order_refund`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_order_refund_log`
--

CREATE TABLE `suliss_shop_order_refund_log` (
  `id` int(11) NOT NULL,
  `refundid` int(11) DEFAULT '0',
  `operator` varchar(255) DEFAULT '',
  `msgtype` tinyint(3) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT '',
  `content` text,
  `link` varchar(255) NOT NULL DEFAULT '',
  `createtime` int(11) DEFAULT '0',
  `issend` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_order_refund_log`
--

TRUNCATE TABLE `suliss_shop_order_refund_log`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_package`
--

CREATE TABLE `suliss_shop_package` (
  `id` int(11) NOT NULL,
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
  `displayorder` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_package`
--

TRUNCATE TABLE `suliss_shop_package`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_package_goods`
--

CREATE TABLE `suliss_shop_package_goods` (
  `id` int(11) NOT NULL,
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
  `commission3` decimal(10,2) DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_package_goods`
--

TRUNCATE TABLE `suliss_shop_package_goods`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_package_goods_option`
--

CREATE TABLE `suliss_shop_package_goods_option` (
  `id` int(11) NOT NULL,
  `goodsid` int(11) NOT NULL DEFAULT '0',
  `optionid` int(11) NOT NULL DEFAULT '0',
  `pid` int(11) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `packageprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `marketprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission1` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission2` decimal(10,2) NOT NULL DEFAULT '0.00',
  `commission3` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_package_goods_option`
--

TRUNCATE TABLE `suliss_shop_package_goods_option`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_payment`
--

CREATE TABLE `suliss_shop_payment` (
  `id` int(11) NOT NULL,
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
  `createtime` int(10) UNSIGNED DEFAULT '0',
  `paytype` tinyint(3) NOT NULL DEFAULT '0',
  `alitype` tinyint(3) NOT NULL DEFAULT '0',
  `alipay_sec` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_payment`
--

TRUNCATE TABLE `suliss_shop_payment`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_perm_plugin`
--

CREATE TABLE `suliss_shop_perm_plugin` (
  `id` int(11) NOT NULL,
  `uid` int(11) DEFAULT '0',
  `type` tinyint(3) DEFAULT '0',
  `plugins` text,
  `coms` text,
  `datas` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_perm_plugin`
--

TRUNCATE TABLE `suliss_shop_perm_plugin`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_refund_address`
--

CREATE TABLE `suliss_shop_refund_address` (
  `id` int(11) NOT NULL,
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
  `deleted` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_refund_address`
--

TRUNCATE TABLE `suliss_shop_refund_address`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_saler`
--

CREATE TABLE `suliss_shop_saler` (
  `id` int(11) NOT NULL,
  `storeid` int(11) DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
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
  `roleid` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_saler`
--

TRUNCATE TABLE `suliss_shop_saler`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_sms_set`
--

CREATE TABLE `suliss_shop_sms_set` (
  `id` int(11) NOT NULL,
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
  `meilian` tinyint(3) NOT NULL DEFAULT '0',
  `meilian_username` varchar(255) NOT NULL DEFAULT '',
  `meilian_password_md5` varchar(255) NOT NULL DEFAULT '',
  `meilian_apikey` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_sms_set`
--

TRUNCATE TABLE `suliss_shop_sms_set`;
--
-- 转存表中的数据 `suliss_shop_sms_set`
--

INSERT INTO `suliss_shop_sms_set` (`id`, `juhe`, `juhe_key`, `emay`, `emay_url`, `emay_appid`, `emay_pw`, `emay_sk`, `emay_phost`, `emay_pport`, `emay_puser`, `emay_ppw`, `emay_out`, `emay_outresp`, `emay_warn`, `emay_mobile`, `emay_warn_time`, `aliyun_new`, `aliyun_new_keyid`, `aliyun_new_keysecret`, `meilian`, `meilian_username`, `meilian_password_md5`, `meilian_apikey`) VALUES
(3, 0, '', 1, 'http://bjmtn.b2m.cn', 'EUCP-EMY-SMS0-JDSTQ', '4343342840991302', 'EUCP-EMY-SMS0-JDSTQ', '', 0, '', '', 0, 30, '0.00', '0', 0, 0, '', '', 0, '', '', '');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_store`
--

CREATE TABLE `suliss_shop_store` (
  `id` int(11) NOT NULL,
  `merchid` int(11) NOT NULL DEFAULT '0',
  `storename` varchar(255) DEFAULT '',
  `address` varchar(255) DEFAULT '',
  `tel` varchar(255) DEFAULT '',
  `lat` varchar(255) DEFAULT '',
  `lng` varchar(255) DEFAULT '',
  `status` tinyint(3) DEFAULT '0',
  `type` tinyint(1) DEFAULT '0',
  `realname` varchar(255) DEFAULT '',
  `mobile` varchar(255) DEFAULT '',
  `fetchtime` varchar(255) DEFAULT '',
  `logo` varchar(255) DEFAULT '',
  `saletime` varchar(255) DEFAULT '',
  `desc` text,
  `displayorder` int(11) DEFAULT '0',
  `order_printer` varchar(500) DEFAULT '',
  `order_template` int(11) DEFAULT '0',
  `ordertype` varchar(500) DEFAULT '',
  `banner` text,
  `label` varchar(255) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `classify` tinyint(1) DEFAULT NULL,
  `perms` text,
  `citycode` varchar(20) DEFAULT '',
  `opensend` tinyint(3) NOT NULL DEFAULT '0',
  `province` varchar(30) NOT NULL DEFAULT '',
  `city` varchar(30) NOT NULL DEFAULT '',
  `area` varchar(30) NOT NULL DEFAULT '',
  `provincecode` varchar(30) NOT NULL DEFAULT '',
  `areacode` varchar(30) NOT NULL DEFAULT '',
  `storegroupid` int(11) DEFAULT NULL,
  `cates` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 插入之前先把表清空（truncate） `suliss_shop_store`
--

TRUNCATE TABLE `suliss_shop_store`;
--
-- 转存表中的数据 `suliss_shop_store`
--

INSERT INTO `suliss_shop_store` (`id`, `merchid`, `storename`, `address`, `tel`, `lat`, `lng`, `status`, `type`, `realname`, `mobile`, `fetchtime`, `logo`, `saletime`, `desc`, `displayorder`, `order_printer`, `order_template`, `ordertype`, `banner`, `label`, `tag`, `classify`, `perms`, `citycode`, `opensend`, `province`, `city`, `area`, `provincecode`, `areacode`, `storegroupid`, `cates`) VALUES
(1, 0, '我的线下小店一号', '推动', '087163811176', '25.014783268041928', '102.7156975382343', 1, 3, '巴啦啦小魔仙', '087163811176', '', '/public/attachment/images/20181210/8e04a93737ef839f657c19719adf5973.jpg', '12:00-22:00', '', 0, '', 0, '', NULL, '', '', NULL, '', '', 0, '云南省', '昆明市', '西山区', '', '', NULL, ''),
(2, 0, '南屏街店', '南屏步行街31号401', '18687510604', '25.047082540430907', '102.71704052265582', 1, 3, '朱撷潼', '18687510604', '', '/public/attachment/images/20181211/0502e29487e395352e46a5aa11a003dc.jpg', '10:00 ～ 22:00', '测试门店', 0, '', 0, '', NULL, '', '', NULL, 'storeinfo,saler,stock,delete,norder', '', 0, '云南省', '昆明市', '五华区', '', '', NULL, '');

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_sysset`
--

CREATE TABLE `suliss_shop_sysset` (
  `id` int(11) NOT NULL,
  `sets` longtext,
  `plugins` longtext,
  `sec` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_system_copyright`
--

CREATE TABLE `suliss_shop_system_copyright` (
  `id` int(11) NOT NULL,
  `bgcolor` varchar(255) DEFAULT '',
  `ismanage` tinyint(3) DEFAULT '0',
  `logo` varchar(255) DEFAULT '',
  `title` varchar(255) DEFAULT '',
  `copyright` text,
  `agreement` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_system_copyright`
--

TRUNCATE TABLE `suliss_shop_system_copyright`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_task_extension_join`
--

CREATE TABLE `suliss_shop_task_extension_join` (
  `id` int(11) NOT NULL,
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
  `logo` varchar(255) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_task_extension_join`
--

TRUNCATE TABLE `suliss_shop_task_extension_join`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_treasure_banner`
--

CREATE TABLE `suliss_shop_treasure_banner` (
  `id` int(11) NOT NULL,
  `bannername` varchar(50) DEFAULT NULL,
  `link` varchar(255) DEFAULT '',
  `thumb` varchar(255) DEFAULT '',
  `displayorder` int(11) DEFAULT '0',
  `enabled` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_treasure_banner`
--

TRUNCATE TABLE `suliss_shop_treasure_banner`;
--
-- 转存表中的数据 `suliss_shop_treasure_banner`
--

INSERT INTO `suliss_shop_treasure_banner` (`id`, `bannername`, `link`, `thumb`, `displayorder`, `enabled`) VALUES
(1, '幻灯1', '', '/public/attachment/images/20181221/7153e21983f045827077f228335a4b4a.jpg', 50, 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_treasure_goods`
--

CREATE TABLE `suliss_shop_treasure_goods` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `canyurenshu` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `periods` smallint(6) UNSIGNED NOT NULL DEFAULT '0',
  `maxperiods` smallint(5) UNSIGNED NOT NULL DEFAULT '0',
  `thumb` varchar(255) NOT NULL DEFAULT '',
  `thumb_url` text,
  `content` mediumtext,
  `createtime` int(11) UNSIGNED NOT NULL DEFAULT '0',
  `pos` tinyint(4) UNSIGNED NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `isnew` tinyint(3) NOT NULL DEFAULT '0',
  `ishot` tinyint(3) NOT NULL DEFAULT '0',
  `jiexiaotime` int(11) NOT NULL DEFAULT '0',
  `couponid` int(11) NOT NULL DEFAULT '0',
  `init_money` int(11) NOT NULL DEFAULT '0',
  `maxnum` int(11) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0',
  `next_init_money` int(11) NOT NULL DEFAULT '0',
  `automatic` varchar(145) NOT NULL DEFAULT '0',
  `isalert` tinyint(3) NOT NULL DEFAULT '0',
  `merchid` int(11) NOT NULL DEFAULT '0',
  `isalone` tinyint(3) NOT NULL DEFAULT '0',
  `aloneprice` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deleted` tinyint(3) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_treasure_goods`
--

TRUNCATE TABLE `suliss_shop_treasure_goods`;
--
-- 转存表中的数据 `suliss_shop_treasure_goods`
--

INSERT INTO `suliss_shop_treasure_goods` (`id`, `category`, `title`, `price`, `canyurenshu`, `periods`, `maxperiods`, `thumb`, `thumb_url`, `content`, `createtime`, `pos`, `status`, `isnew`, `ishot`, `jiexiaotime`, `couponid`, `init_money`, `maxnum`, `sort`, `next_init_money`, `automatic`, `isalert`, `merchid`, `isalone`, `aloneprice`, `deleted`) VALUES
(1, 1, '测试商品1', '5.00', 0, 0, 2, '/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg', 'a:2:{i:0;s:71:\"/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg\";i:1;s:71:\"/public/attachment/images/20181225/43555cfcb33fdacb7971645e9fc70b66.jpg\";}', '<p>啊</p>', 1547545581, 0, 0, 0, 0, 120, 0, 1, 1, 50, 0, '', 1, 0, 0, '0.00', 0),
(2, 1, '测试商品1', '5.00', 0, 0, 2, '/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg', 'a:2:{i:0;s:71:\"/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg\";i:1;s:71:\"/public/attachment/images/20181225/43555cfcb33fdacb7971645e9fc70b66.jpg\";}', '<p>啊</p>', 1547545805, 0, 0, 0, 0, 120, 0, 1, 1, 50, 0, '', 1, 0, 0, '0.00', 0),
(3, 1, '测试商品1', '5.00', 0, 1, 2, '/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg', 'a:2:{i:0;s:71:\"/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg\";i:1;s:71:\"/public/attachment/images/20181225/43555cfcb33fdacb7971645e9fc70b66.jpg\";}', '<p>啊</p>', 1547545826, 0, 1, 0, 0, 120, 0, 1, 1, 50, 0, '', 1, 0, 0, '0.00', 0),
(4, 1, '测试商品1', '5.00', 0, 1, 2, '/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg', 'a:2:{i:0;s:71:\"/public/attachment/images/20181225/11dff62422d0f1ebecbab5a73c8bb30e.jpg\";i:1;s:71:\"/public/attachment/images/20181225/43555cfcb33fdacb7971645e9fc70b66.jpg\";}', '<p>啊</p>', 1547545853, 0, 1, 0, 0, 120, 0, 1, 1, 50, 0, '', 1, 0, 0, '0.00', 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_treasure_goods_category`
--

CREATE TABLE `suliss_shop_treasure_goods_category` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  `displayorder` tinyint(3) UNSIGNED DEFAULT '0',
  `enabled` tinyint(1) DEFAULT '1',
  `advimg` varchar(255) DEFAULT '',
  `advurl` varchar(500) DEFAULT '',
  `isrecommand` tinyint(3) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_treasure_goods_category`
--

TRUNCATE TABLE `suliss_shop_treasure_goods_category`;
--
-- 转存表中的数据 `suliss_shop_treasure_goods_category`
--

INSERT INTO `suliss_shop_treasure_goods_category` (`id`, `name`, `thumb`, `displayorder`, `enabled`, `advimg`, `advurl`, `isrecommand`) VALUES
(1, '分类1', '/public/attachment/images/20181221/9f347732463586cd8208695dd225779b.png', 50, 1, '', '', 1);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_treasure_goods_period`
--

CREATE TABLE `suliss_shop_treasure_goods_period` (
  `id` int(11) NOT NULL,
  `goodsid` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `periods` int(11) NOT NULL,
  `nickname` varchar(145) NOT NULL,
  `avatar` varchar(225) NOT NULL,
  `partakes` int(11) NOT NULL,
  `code` varchar(45) NOT NULL,
  `endtime` varchar(145) NOT NULL,
  `jiexiaotime` int(11) NOT NULL,
  `confirmtime` int(11) NOT NULL,
  `taketime` int(11) NOT NULL,
  `realname` varchar(20) NOT NULL,
  `mobile` varchar(11) NOT NULL,
  `address` varchar(200) NOT NULL,
  `express` varchar(45) NOT NULL,
  `expressn` varchar(145) NOT NULL,
  `sendtime` varchar(145) NOT NULL,
  `codes` longtext NOT NULL,
  `uniacid` int(11) NOT NULL,
  `shengyucodes` int(11) NOT NULL,
  `zongcodes` int(11) NOT NULL,
  `period_number` varchar(145) NOT NULL,
  `canyurenshu` int(11) NOT NULL,
  `status` int(4) NOT NULL,
  `scale` int(11) NOT NULL,
  `createtime` varchar(145) NOT NULL,
  `recordid` int(11) NOT NULL,
  `allcodes` longtext NOT NULL,
  `comment` varchar(2048) NOT NULL,
  `machinetime` varchar(145) NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_treasure_goods_period`
--

TRUNCATE TABLE `suliss_shop_treasure_goods_period`;
--
-- 转存表中的数据 `suliss_shop_treasure_goods_period`
--

INSERT INTO `suliss_shop_treasure_goods_period` (`id`, `goodsid`, `mid`, `periods`, `nickname`, `avatar`, `partakes`, `code`, `endtime`, `jiexiaotime`, `confirmtime`, `taketime`, `realname`, `mobile`, `address`, `express`, `expressn`, `sendtime`, `codes`, `uniacid`, `shengyucodes`, `zongcodes`, `period_number`, `canyurenshu`, `status`, `scale`, `createtime`, `recordid`, `allcodes`, `comment`, `machinetime`, `sort`) VALUES
(1, 3, 0, 1, '', '', 0, '', '', 120, 0, 0, '', '', '', '', '', '', '', 0, 5, 5, '20190115458267788332', 0, 1, 0, '1547545826', 0, 'a:1:{i:0;s:3:\"0:5\";}', '', '', 50),
(2, 4, 0, 1, '', '', 0, '', '', 120, 0, 0, '', '', '', '', '', '', 'a:5:{i:0;i:1000003;i:1;i:1000001;i:2;i:1000002;i:3;i:1000005;i:4;i:1000004;}', 0, 5, 5, '20190115458532403831', 0, 1, 0, '1547545853', 0, '', '', '', 50);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_verifygoods`
--

CREATE TABLE `suliss_shop_verifygoods` (
  `id` int(11) NOT NULL,
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
  `limitdate` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_verifygoods`
--

TRUNCATE TABLE `suliss_shop_verifygoods`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_verifygoods_log`
--

CREATE TABLE `suliss_shop_verifygoods_log` (
  `id` int(11) NOT NULL,
  `verifygoodsid` int(11) DEFAULT NULL,
  `salerid` int(11) DEFAULT NULL,
  `storeid` int(11) DEFAULT NULL,
  `verifynum` int(11) DEFAULT NULL,
  `verifydate` int(11) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_verifygoods_log`
--

TRUNCATE TABLE `suliss_shop_verifygoods_log`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_virtual_data`
--

CREATE TABLE `suliss_shop_virtual_data` (
  `id` int(11) NOT NULL,
  `mid` int(11) NOT NULL DEFAULT '0',
  `typeid` int(11) NOT NULL DEFAULT '0',
  `pvalue` varchar(255) DEFAULT '',
  `fields` text NOT NULL,
  `usetime` int(11) NOT NULL DEFAULT '0',
  `orderid` int(11) DEFAULT '0',
  `ordersn` varchar(255) DEFAULT '',
  `price` decimal(10,2) DEFAULT '0.00',
  `merchid` int(11) DEFAULT '0',
  `createtime` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_virtual_data`
--

TRUNCATE TABLE `suliss_shop_virtual_data`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_virtual_type`
--

CREATE TABLE `suliss_shop_virtual_type` (
  `id` int(11) NOT NULL,
  `cate` int(11) DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `fields` text NOT NULL,
  `usedata` int(11) NOT NULL DEFAULT '0',
  `alldata` int(11) NOT NULL DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `linktext` varchar(50) DEFAULT NULL,
  `linkurl` varchar(255) DEFAULT NULL,
  `recycled` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_shop_virtual_type`
--

TRUNCATE TABLE `suliss_shop_virtual_type`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_shop_wxcard`
--

CREATE TABLE `suliss_shop_wxcard` (
  `id` int(11) NOT NULL,
  `card_id` varchar(255) DEFAULT '0',
  `displayorder` int(11) DEFAULT NULL,
  `catid` int(11) DEFAULT NULL,
  `card_type` varchar(50) DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `wxlogourl` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `code_type` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `notice` varchar(50) DEFAULT NULL,
  `service_phone` varchar(50) DEFAULT NULL,
  `description` text,
  `datetype` varchar(50) DEFAULT NULL,
  `begin_timestamp` int(11) DEFAULT NULL,
  `end_timestamp` int(11) DEFAULT NULL,
  `fixed_term` int(11) DEFAULT NULL,
  `fixed_begin_term` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `total_quantity` varchar(255) DEFAULT NULL,
  `use_limit` int(11) DEFAULT NULL,
  `get_limit` int(11) DEFAULT NULL,
  `use_custom_code` tinyint(1) DEFAULT NULL,
  `bind_openid` tinyint(1) DEFAULT NULL,
  `can_share` tinyint(1) DEFAULT NULL,
  `can_give_friend` tinyint(1) DEFAULT NULL,
  `center_title` varchar(20) DEFAULT NULL,
  `center_sub_title` varchar(20) DEFAULT NULL,
  `center_url` varchar(255) DEFAULT NULL,
  `setcustom` tinyint(1) DEFAULT NULL,
  `custom_url_name` varchar(20) DEFAULT NULL,
  `custom_url_sub_title` varchar(20) DEFAULT NULL,
  `custom_url` varchar(255) DEFAULT NULL,
  `setpromotion` tinyint(1) DEFAULT NULL,
  `promotion_url_name` varchar(20) DEFAULT NULL,
  `promotion_url_sub_title` varchar(20) DEFAULT NULL,
  `promotion_url` varchar(255) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `can_use_with_other_discount` tinyint(1) DEFAULT NULL,
  `setabstract` tinyint(1) DEFAULT NULL,
  `abstract` varchar(50) DEFAULT NULL,
  `abstractimg` varchar(255) DEFAULT NULL,
  `icon_url_list` varchar(255) DEFAULT NULL,
  `accept_category` varchar(50) DEFAULT NULL,
  `reject_category` varchar(50) DEFAULT NULL,
  `least_cost` decimal(10,2) DEFAULT NULL,
  `reduce_cost` decimal(10,2) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `limitgoodtype` tinyint(1) DEFAULT '0',
  `limitgoodcatetype` tinyint(1) UNSIGNED DEFAULT '0',
  `limitgoodcateids` varchar(255) DEFAULT NULL,
  `limitgoodids` varchar(255) DEFAULT NULL,
  `limitdiscounttype` tinyint(1) UNSIGNED DEFAULT '0',
  `merchid` int(11) DEFAULT '0',
  `gettype` tinyint(3) DEFAULT NULL,
  `islimitlevel` tinyint(1) DEFAULT '0',
  `limitmemberlevels` varchar(500) DEFAULT '',
  `limitagentlevels` varchar(500) DEFAULT '',
  `limitpartnerlevels` varchar(500) DEFAULT '',
  `limitaagentlevels` varchar(500) DEFAULT '',
  `settitlecolor` tinyint(1) DEFAULT '0',
  `titlecolor` varchar(10) DEFAULT '',
  `tagtitle` varchar(20) DEFAULT '',
  `use_condition` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

--
-- 插入之前先把表清空（truncate） `suliss_shop_wxcard`
--

TRUNCATE TABLE `suliss_shop_wxcard`;
-- --------------------------------------------------------

--
-- 表的结构 `suliss_sms_log`
--

CREATE TABLE `suliss_sms_log` (
  `id` int(11) NOT NULL COMMENT '表id',
  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号',
  `type` varchar(25) NOT NULL DEFAULT '',
  `code` varchar(10) NOT NULL DEFAULT '' COMMENT '验证码',
  `createtime` int(32) NOT NULL DEFAULT '0' COMMENT '发送时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_sms_log`
--

TRUNCATE TABLE `suliss_sms_log`;
--
-- 转存表中的数据 `suliss_sms_log`
--

INSERT INTO `suliss_sms_log` (`id`, `mobile`, `type`, `code`, `createtime`) VALUES
(97, '13099907747', 'register', '9104', 1548642425);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_system_bank`
--

CREATE TABLE `suliss_system_bank` (
  `id` int(11) NOT NULL,
  `bankname` varchar(255) NOT NULL DEFAULT '',
  `content` varchar(255) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `displayorder` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_system_bank`
--

TRUNCATE TABLE `suliss_system_bank`;
--
-- 转存表中的数据 `suliss_system_bank`
--

INSERT INTO `suliss_system_bank` (`id`, `bankname`, `content`, `status`, `displayorder`) VALUES
(3, '中国银行', '', 1, 0),
(4, '建设银行', '', 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `suliss_system_feedback`
--

CREATE TABLE `suliss_system_feedback` (
  `id` int(11) NOT NULL,
  `mid` int(11) DEFAULT '0',
  `desc` varchar(1000) NOT NULL DEFAULT '',
  `thumbs_url` text NOT NULL,
  `createtime` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 插入之前先把表清空（truncate） `suliss_system_feedback`
--

TRUNCATE TABLE `suliss_system_feedback`;
--
-- 转存表中的数据 `suliss_system_feedback`
--

INSERT INTO `suliss_system_feedback` (`id`, `mid`, `desc`, `thumbs_url`, `createtime`, `status`) VALUES
(32, 25, 'tvgvvg', 'a:8:{i:0;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/111cf261cc6809b86d0b920cc7558205.jpg\";i:1;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/a6a25530160ad566207c9d07f6af340e.jpg\";i:2;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/e7b951ddf7dfdb8139dee9bd1638bcdb.png\";i:3;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/a9acded3bd4807666b9702eafc716eb7.jpg\";i:4;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/fba5ab55c23b199b4e1ff575d521041e.jpg\";i:5;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/708a1388ddce6015a4e6385e760b7426.jpg\";i:6;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/9f4d7781224c853609db0e4fc4ea5f97.jpg\";i:7;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/a2024bdc4c7210660df5239dc843260b.jpg\";}', 1547706232, 0),
(33, 25, 'ff', 'a:10:{i:0;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/9e163bf2687c2b33c6f24732370894b0.jpg\";i:1;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/196767753fd30f15b179cbc3b3f2d21c.jpg\";i:2;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/e821b21680d8baaef5ce04ad2629fa26.jpg\";i:3;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/2a006ae2d52317f4a7f8984194da11af.jpg\";i:4;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/7cb79f4209806af27e44a86cb98bc09b.jpg\";i:5;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/5b194f9335eaf953021bcb57e4438b1e.jpg\";i:6;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/249bda97c06a894846bf3c706799b7fa.jpg\";i:7;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/9520e95380d5fda3968d80cad3463376.jpg\";i:8;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/f8dc9f3ffcae7211f2f3f567d3e02b54.jpg\";i:9;s:91:\"http://aoao.doncheng.cn/public/attachment/app/20190117/799d19fc6133827c22b472fdb2a9af9a.jpg\";}', 1547706280, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `suliss_admin`
--
ALTER TABLE `suliss_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `suliss_admin_log`
--
ALTER TABLE `suliss_admin_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_adminid` (`adminid`),
  ADD KEY `idx_createtime` (`createtime`);
ALTER TABLE `suliss_admin_log` ADD FULLTEXT KEY `idx_type` (`type`);
ALTER TABLE `suliss_admin_log` ADD FULLTEXT KEY `idx_op` (`op`);

--
-- Indexes for table `suliss_attachment_group`
--
ALTER TABLE `suliss_attachment_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_auth_group`
--
ALTER TABLE `suliss_auth_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_auth_group_access`
--
ALTER TABLE `suliss_auth_group_access`
  ADD UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `suliss_auth_rule`
--
ALTER TABLE `suliss_auth_rule`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `suliss_core_attachment`
--
ALTER TABLE `suliss_core_attachment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_livemall_goods_agent`
--
ALTER TABLE `suliss_livemall_goods_agent`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_livemall_reg`
--
ALTER TABLE `suliss_livemall_reg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_member`
--
ALTER TABLE `suliss_member`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_level` (`level`),
  ADD KEY `idx_mobile` (`mobile`),
  ADD KEY `idx_groupid` (`groupid`);

--
-- Indexes for table `suliss_member_credits_record`
--
ALTER TABLE `suliss_member_credits_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mid` (`mid`);

--
-- Indexes for table `suliss_member_failed_login`
--
ALTER TABLE `suliss_member_failed_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip_username` (`ip`,`username`);

--
-- Indexes for table `suliss_member_group`
--
ALTER TABLE `suliss_member_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_member_level`
--
ALTER TABLE `suliss_member_level`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_member_message`
--
ALTER TABLE `suliss_member_message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_createtime` (`createtime`);

--
-- Indexes for table `suliss_prefix_jobs`
--
ALTER TABLE `suliss_prefix_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_adv`
--
ALTER TABLE `suliss_shop_adv`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_area_config`
--
ALTER TABLE `suliss_shop_area_config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_article`
--
ALTER TABLE `suliss_shop_article`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_article_title` (`article_title`),
  ADD KEY `idx_article_content` (`article_content`(10));

--
-- Indexes for table `suliss_shop_article_category`
--
ALTER TABLE `suliss_shop_article_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_name` (`category_name`);

--
-- Indexes for table `suliss_shop_auction_banner`
--
ALTER TABLE `suliss_shop_auction_banner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_auction_bondorder`
--
ALTER TABLE `suliss_shop_auction_bondorder`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_paytime` (`paytime`),
  ADD KEY `idx_finishtime` (`finishtime`),
  ADD KEY `idx_ordersn` (`ordersn`) USING BTREE;

--
-- Indexes for table `suliss_shop_auction_goods`
--
ALTER TABLE `suliss_shop_auction_goods`
  ADD PRIMARY KEY (`id`,`endtime`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `suliss_shop_auction_goods_category`
--
ALTER TABLE `suliss_shop_auction_goods_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_displayorder` (`displayorder`),
  ADD KEY `idx_enabled` (`enabled`);

--
-- Indexes for table `suliss_shop_auction_order`
--
ALTER TABLE `suliss_shop_auction_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mid` (`mid`) USING BTREE,
  ADD KEY `goodsid` (`goodsid`) USING BTREE;

--
-- Indexes for table `suliss_shop_auction_record`
--
ALTER TABLE `suliss_shop_auction_record`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mid` (`mid`),
  ADD KEY `goodsid` (`goodsid`);

--
-- Indexes for table `suliss_shop_banner`
--
ALTER TABLE `suliss_shop_banner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_bargain_actor`
--
ALTER TABLE `suliss_shop_bargain_actor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_city_express`
--
ALTER TABLE `suliss_shop_city_express`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_core_paylog`
--
ALTER TABLE `suliss_shop_core_paylog`
  ADD PRIMARY KEY (`plid`),
  ADD KEY `idx_tid` (`tid`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `uniontid` (`uniontid`);

--
-- Indexes for table `suliss_shop_coupon`
--
ALTER TABLE `suliss_shop_coupon`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coupontype` (`coupontype`),
  ADD KEY `idx_timestart` (`timestart`),
  ADD KEY `idx_timeend` (`timeend`),
  ADD KEY `idx_timelimit` (`timelimit`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_givetype` (`backtype`),
  ADD KEY `idx_catid` (`catid`);

--
-- Indexes for table `suliss_shop_coupon_data`
--
ALTER TABLE `suliss_shop_coupon_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_couponid` (`couponid`),
  ADD KEY `idx_gettype` (`gettype`),
  ADD KEY `idx_used` (`used`),
  ADD KEY `idx_gettime` (`gettime`);

--
-- Indexes for table `suliss_shop_coupon_goodsendtask`
--
ALTER TABLE `suliss_shop_coupon_goodsendtask`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_coupon_log`
--
ALTER TABLE `suliss_shop_coupon_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_couponid` (`couponid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_paystatus` (`paystatus`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_getfrom` (`getfrom`),
  ADD KEY `idx_logno` (`logno`);

--
-- Indexes for table `suliss_shop_coupon_sendshow`
--
ALTER TABLE `suliss_shop_coupon_sendshow`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_coupon_sendtasks`
--
ALTER TABLE `suliss_shop_coupon_sendtasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_coupon_taskdata`
--
ALTER TABLE `suliss_shop_coupon_taskdata`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_coupon_usesendtasks`
--
ALTER TABLE `suliss_shop_coupon_usesendtasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_creditshop_banner`
--
ALTER TABLE `suliss_shop_creditshop_banner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_creditshop_comment`
--
ALTER TABLE `suliss_shop_creditshop_comment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_creditshop_goods`
--
ALTER TABLE `suliss_shop_creditshop_goods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_endtime` (`endtime`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_displayorder` (`displayorder`),
  ADD KEY `idx_deleted` (`deleted`),
  ADD KEY `idx_istop` (`istop`),
  ADD KEY `idx_isrecommand` (`isrecommand`),
  ADD KEY `idx_istime` (`istime`),
  ADD KEY `idx_timestart` (`timestart`),
  ADD KEY `idx_timeend` (`timeend`),
  ADD KEY `idx_goodstype` (`goodstype`);

--
-- Indexes for table `suliss_shop_creditshop_goods_category`
--
ALTER TABLE `suliss_shop_creditshop_goods_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_displayorder` (`displayorder`),
  ADD KEY `idx_enabled` (`enabled`);

--
-- Indexes for table `suliss_shop_creditshop_goods_option`
--
ALTER TABLE `suliss_shop_creditshop_goods_option`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_creditshop_goods_spec`
--
ALTER TABLE `suliss_shop_creditshop_goods_spec`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_creditshop_goods_spec_item`
--
ALTER TABLE `suliss_shop_creditshop_goods_spec_item`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_creditshop_log`
--
ALTER TABLE `suliss_shop_creditshop_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_creditshop_verify`
--
ALTER TABLE `suliss_shop_creditshop_verify`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_dispatch`
--
ALTER TABLE `suliss_shop_dispatch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_express`
--
ALTER TABLE `suliss_shop_express`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_express_cache`
--
ALTER TABLE `suliss_shop_express_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expresssn` (`expresssn`) USING BTREE,
  ADD KEY `idx_express` (`express`) USING BTREE;

--
-- Indexes for table `suliss_shop_fullback_goods`
--
ALTER TABLE `suliss_shop_fullback_goods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_fullback_log`
--
ALTER TABLE `suliss_shop_fullback_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_gift`
--
ALTER TABLE `suliss_shop_gift`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_goods`
--
ALTER TABLE `suliss_shop_goods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pcate` (`pcate`),
  ADD KEY `idx_ccate` (`ccate`),
  ADD KEY `idx_isnew` (`isnew`),
  ADD KEY `idx_ishot` (`ishot`),
  ADD KEY `idx_isdiscount` (`isdiscount`),
  ADD KEY `idx_isrecommand` (`isrecommand`),
  ADD KEY `idx_iscomment` (`iscomment`),
  ADD KEY `idx_issendfree` (`issendfree`),
  ADD KEY `idx_istime` (`istime`),
  ADD KEY `idx_deleted` (`deleted`),
  ADD KEY `idx_scate` (`tcate`),
  ADD KEY `idx_merchid` (`merchid`),
  ADD KEY `idx_checked` (`checked`),
  ADD KEY `idx_productsn` (`productsn`) USING BTREE;

--
-- Indexes for table `suliss_shop_goods_category`
--
ALTER TABLE `suliss_shop_goods_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_displayorder` (`displayorder`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_parentid` (`parentid`),
  ADD KEY `idx_isrecommand` (`isrecommand`),
  ADD KEY `idx_ishome` (`ishome`);

--
-- Indexes for table `suliss_shop_goods_favorite`
--
ALTER TABLE `suliss_shop_goods_favorite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_deleted` (`deleted`),
  ADD KEY `idx_createtime` (`createtime`);

--
-- Indexes for table `suliss_shop_goods_group`
--
ALTER TABLE `suliss_shop_goods_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enabled` (`enabled`);

--
-- Indexes for table `suliss_shop_goods_label`
--
ALTER TABLE `suliss_shop_goods_label`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_goods_option`
--
ALTER TABLE `suliss_shop_goods_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_displayorder` (`displayorder`),
  ADD KEY `idx_productsn` (`productsn`);

--
-- Indexes for table `suliss_shop_goods_param`
--
ALTER TABLE `suliss_shop_goods_param`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_goods_spec`
--
ALTER TABLE `suliss_shop_goods_spec`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_goods_spec_item`
--
ALTER TABLE `suliss_shop_goods_spec_item`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_specid` (`specid`),
  ADD KEY `idx_show` (`show`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_groups_banner`
--
ALTER TABLE `suliss_shop_groups_banner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_groups_goods`
--
ALTER TABLE `suliss_shop_groups_goods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`category`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `suliss_shop_groups_goods_category`
--
ALTER TABLE `suliss_shop_groups_goods_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_displayorder` (`displayorder`),
  ADD KEY `idx_enabled` (`enabled`);

--
-- Indexes for table `suliss_shop_groups_goods_option`
--
ALTER TABLE `suliss_shop_groups_goods_option`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_groups_ladder`
--
ALTER TABLE `suliss_shop_groups_ladder`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_groups_order`
--
ALTER TABLE `suliss_shop_groups_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`) USING BTREE,
  ADD KEY `idx_orderno` (`orderno`) USING BTREE,
  ADD KEY `idx_paytime` (`paytime`) USING BTREE,
  ADD KEY `idx_pay_type` (`pay_type`) USING BTREE,
  ADD KEY `idx_teamid` (`teamid`) USING BTREE,
  ADD KEY `idx_verifycode` (`verifycode`) USING BTREE,
  ADD KEY `idx_createtime` (`createtime`) USING BTREE;

--
-- Indexes for table `suliss_shop_groups_order_comment`
--
ALTER TABLE `suliss_shop_groups_order_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_orderid` (`orderid`);

--
-- Indexes for table `suliss_shop_groups_order_goods`
--
ALTER TABLE `suliss_shop_groups_order_goods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_groups_order_refund`
--
ALTER TABLE `suliss_shop_groups_order_refund`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_groups_set`
--
ALTER TABLE `suliss_shop_groups_set`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_groups_verify`
--
ALTER TABLE `suliss_shop_groups_verify`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_member_address`
--
ALTER TABLE `suliss_shop_member_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_isdefault` (`isdefault`),
  ADD KEY `idx_deleted` (`deleted`);

--
-- Indexes for table `suliss_shop_member_cart`
--
ALTER TABLE `suliss_shop_member_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_deleted` (`deleted`);

--
-- Indexes for table `suliss_shop_member_history`
--
ALTER TABLE `suliss_shop_member_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_deleted` (`deleted`),
  ADD KEY `idx_createtime` (`createtime`);

--
-- Indexes for table `suliss_shop_merch`
--
ALTER TABLE `suliss_shop_merch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_groupid` (`groupid`),
  ADD KEY `idx_regid` (`regid`),
  ADD KEY `idx_cateid` (`cateid`);

--
-- Indexes for table `suliss_shop_merch_account`
--
ALTER TABLE `suliss_shop_merch_account`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_merchid` (`merchid`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `suliss_shop_merch_account_log`
--
ALTER TABLE `suliss_shop_merch_account_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_merchid` (`merchid`),
  ADD KEY `uid` (`uid`);

--
-- Indexes for table `suliss_shop_merch_bill`
--
ALTER TABLE `suliss_shop_merch_bill`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_merchid` (`merchid`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `suliss_shop_merch_billo`
--
ALTER TABLE `suliss_shop_merch_billo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_billid` (`billid`);

--
-- Indexes for table `suliss_shop_merch_category`
--
ALTER TABLE `suliss_shop_merch_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_merch_collect`
--
ALTER TABLE `suliss_shop_merch_collect`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_storeid` (`merchid`),
  ADD KEY `idx_mid` (`mid`);

--
-- Indexes for table `suliss_shop_merch_goods_category`
--
ALTER TABLE `suliss_shop_merch_goods_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_merch_group`
--
ALTER TABLE `suliss_shop_merch_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_merch_perm_role`
--
ALTER TABLE `suliss_shop_merch_perm_role`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_deleted` (`deleted`),
  ADD KEY `merchid` (`merchid`);

--
-- Indexes for table `suliss_shop_merch_reg`
--
ALTER TABLE `suliss_shop_merch_reg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_nav`
--
ALTER TABLE `suliss_shop_nav`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `suliss_shop_notice`
--
ALTER TABLE `suliss_shop_notice`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_order`
--
ALTER TABLE `suliss_shop_order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_shareid` (`agentid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_refundid` (`refundid`),
  ADD KEY `idx_paytime` (`paytime`),
  ADD KEY `idx_finishtime` (`finishtime`),
  ADD KEY `idx_merchid` (`merchid`),
  ADD KEY `idx_ordersn` (`ordersn`) USING BTREE;

--
-- Indexes for table `suliss_shop_order_comment`
--
ALTER TABLE `suliss_shop_order_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_orderid` (`orderid`);

--
-- Indexes for table `suliss_shop_order_goods`
--
ALTER TABLE `suliss_shop_order_goods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orderid` (`orderid`),
  ADD KEY `idx_goodsid` (`goodsid`),
  ADD KEY `idx_createtime` (`createtime`),
  ADD KEY `idx_applytime1` (`applytime1`),
  ADD KEY `idx_checktime1` (`checktime1`),
  ADD KEY `idx_status1` (`status1`),
  ADD KEY `idx_applytime2` (`applytime2`),
  ADD KEY `idx_checktime2` (`checktime2`),
  ADD KEY `idx_status2` (`status2`),
  ADD KEY `idx_applytime3` (`applytime3`),
  ADD KEY `idx_invalidtime1` (`invalidtime1`),
  ADD KEY `idx_checktime3` (`checktime3`),
  ADD KEY `idx_invalidtime2` (`invalidtime2`),
  ADD KEY `idx_invalidtime3` (`invalidtime3`),
  ADD KEY `idx_status3` (`status3`),
  ADD KEY `idx_paytime1` (`paytime1`),
  ADD KEY `idx_paytime2` (`paytime2`),
  ADD KEY `idx_paytime3` (`paytime3`);

--
-- Indexes for table `suliss_shop_order_refund`
--
ALTER TABLE `suliss_shop_order_refund`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_createtime` (`createtime`);

--
-- Indexes for table `suliss_shop_order_refund_log`
--
ALTER TABLE `suliss_shop_order_refund_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refundid` (`refundid`),
  ADD KEY `idx_createtime` (`createtime`);
ALTER TABLE `suliss_shop_order_refund_log` ADD FULLTEXT KEY `idx_content` (`content`);

--
-- Indexes for table `suliss_shop_package`
--
ALTER TABLE `suliss_shop_package`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_package_goods`
--
ALTER TABLE `suliss_shop_package_goods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_package_goods_option`
--
ALTER TABLE `suliss_shop_package_goods_option`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_payment`
--
ALTER TABLE `suliss_shop_payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`) USING BTREE;

--
-- Indexes for table `suliss_shop_perm_plugin`
--
ALTER TABLE `suliss_shop_perm_plugin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uid` (`uid`),
  ADD KEY `idx_type` (`type`);

--
-- Indexes for table `suliss_shop_refund_address`
--
ALTER TABLE `suliss_shop_refund_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_isdefault` (`isdefault`),
  ADD KEY `idx_deleted` (`deleted`);

--
-- Indexes for table `suliss_shop_saler`
--
ALTER TABLE `suliss_shop_saler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_storeid` (`storeid`),
  ADD KEY `idx_mid` (`mid`);

--
-- Indexes for table `suliss_shop_sms_set`
--
ALTER TABLE `suliss_shop_sms_set`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_store`
--
ALTER TABLE `suliss_shop_store`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `suliss_shop_sysset`
--
ALTER TABLE `suliss_shop_sysset`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_system_copyright`
--
ALTER TABLE `suliss_shop_system_copyright`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_task_extension_join`
--
ALTER TABLE `suliss_shop_task_extension_join`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_treasure_banner`
--
ALTER TABLE `suliss_shop_treasure_banner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_enabled` (`enabled`),
  ADD KEY `idx_displayorder` (`displayorder`);

--
-- Indexes for table `suliss_shop_treasure_goods`
--
ALTER TABLE `suliss_shop_treasure_goods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `suliss_shop_treasure_goods_category`
--
ALTER TABLE `suliss_shop_treasure_goods_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_displayorder` (`displayorder`),
  ADD KEY `idx_enabled` (`enabled`);

--
-- Indexes for table `suliss_shop_treasure_goods_period`
--
ALTER TABLE `suliss_shop_treasure_goods_period`
  ADD PRIMARY KEY (`id`),
  ADD KEY `period_number` (`period_number`);

--
-- Indexes for table `suliss_shop_verifygoods`
--
ALTER TABLE `suliss_shop_verifygoods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verifycode` (`verifycode`) USING BTREE;

--
-- Indexes for table `suliss_shop_verifygoods_log`
--
ALTER TABLE `suliss_shop_verifygoods_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_shop_virtual_data`
--
ALTER TABLE `suliss_shop_virtual_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_typeid` (`typeid`),
  ADD KEY `idx_usetime` (`usetime`),
  ADD KEY `idx_orderid` (`orderid`);

--
-- Indexes for table `suliss_shop_virtual_type`
--
ALTER TABLE `suliss_shop_virtual_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cate` (`cate`);

--
-- Indexes for table `suliss_shop_wxcard`
--
ALTER TABLE `suliss_shop_wxcard`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_sms_log`
--
ALTER TABLE `suliss_sms_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_system_bank`
--
ALTER TABLE `suliss_system_bank`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suliss_system_feedback`
--
ALTER TABLE `suliss_system_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_mid` (`mid`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_createtime` (`createtime`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `suliss_admin`
--
ALTER TABLE `suliss_admin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `suliss_admin_log`
--
ALTER TABLE `suliss_admin_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=772;

--
-- 使用表AUTO_INCREMENT `suliss_attachment_group`
--
ALTER TABLE `suliss_attachment_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `suliss_auth_group`
--
ALTER TABLE `suliss_auth_group`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_auth_rule`
--
ALTER TABLE `suliss_auth_rule`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `suliss_core_attachment`
--
ALTER TABLE `suliss_core_attachment`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `suliss_livemall_goods_agent`
--
ALTER TABLE `suliss_livemall_goods_agent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_livemall_reg`
--
ALTER TABLE `suliss_livemall_reg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_member`
--
ALTER TABLE `suliss_member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- 使用表AUTO_INCREMENT `suliss_member_credits_record`
--
ALTER TABLE `suliss_member_credits_record`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `suliss_member_failed_login`
--
ALTER TABLE `suliss_member_failed_login`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- 使用表AUTO_INCREMENT `suliss_member_group`
--
ALTER TABLE `suliss_member_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_member_level`
--
ALTER TABLE `suliss_member_level`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `suliss_member_message`
--
ALTER TABLE `suliss_member_message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1066;

--
-- 使用表AUTO_INCREMENT `suliss_prefix_jobs`
--
ALTER TABLE `suliss_prefix_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_shop_adv`
--
ALTER TABLE `suliss_shop_adv`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_area_config`
--
ALTER TABLE `suliss_shop_area_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_article`
--
ALTER TABLE `suliss_shop_article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_article_category`
--
ALTER TABLE `suliss_shop_article_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_auction_banner`
--
ALTER TABLE `suliss_shop_auction_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `suliss_shop_auction_bondorder`
--
ALTER TABLE `suliss_shop_auction_bondorder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- 使用表AUTO_INCREMENT `suliss_shop_auction_goods`
--
ALTER TABLE `suliss_shop_auction_goods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键', AUTO_INCREMENT=8;

--
-- 使用表AUTO_INCREMENT `suliss_shop_auction_goods_category`
--
ALTER TABLE `suliss_shop_auction_goods_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_auction_order`
--
ALTER TABLE `suliss_shop_auction_order`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 使用表AUTO_INCREMENT `suliss_shop_auction_record`
--
ALTER TABLE `suliss_shop_auction_record`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `suliss_shop_banner`
--
ALTER TABLE `suliss_shop_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `suliss_shop_bargain_actor`
--
ALTER TABLE `suliss_shop_bargain_actor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_city_express`
--
ALTER TABLE `suliss_shop_city_express`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_core_paylog`
--
ALTER TABLE `suliss_shop_core_paylog`
  MODIFY `plid` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1142;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon`
--
ALTER TABLE `suliss_shop_coupon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon_data`
--
ALTER TABLE `suliss_shop_coupon_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon_goodsendtask`
--
ALTER TABLE `suliss_shop_coupon_goodsendtask`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon_log`
--
ALTER TABLE `suliss_shop_coupon_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon_sendshow`
--
ALTER TABLE `suliss_shop_coupon_sendshow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon_sendtasks`
--
ALTER TABLE `suliss_shop_coupon_sendtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon_taskdata`
--
ALTER TABLE `suliss_shop_coupon_taskdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_coupon_usesendtasks`
--
ALTER TABLE `suliss_shop_coupon_usesendtasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_banner`
--
ALTER TABLE `suliss_shop_creditshop_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_comment`
--
ALTER TABLE `suliss_shop_creditshop_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_goods`
--
ALTER TABLE `suliss_shop_creditshop_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_goods_category`
--
ALTER TABLE `suliss_shop_creditshop_goods_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_goods_option`
--
ALTER TABLE `suliss_shop_creditshop_goods_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_goods_spec`
--
ALTER TABLE `suliss_shop_creditshop_goods_spec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_goods_spec_item`
--
ALTER TABLE `suliss_shop_creditshop_goods_spec_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_log`
--
ALTER TABLE `suliss_shop_creditshop_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_creditshop_verify`
--
ALTER TABLE `suliss_shop_creditshop_verify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_dispatch`
--
ALTER TABLE `suliss_shop_dispatch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_express`
--
ALTER TABLE `suliss_shop_express`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- 使用表AUTO_INCREMENT `suliss_shop_express_cache`
--
ALTER TABLE `suliss_shop_express_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_fullback_goods`
--
ALTER TABLE `suliss_shop_fullback_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_fullback_log`
--
ALTER TABLE `suliss_shop_fullback_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_gift`
--
ALTER TABLE `suliss_shop_gift`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods`
--
ALTER TABLE `suliss_shop_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_category`
--
ALTER TABLE `suliss_shop_goods_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_favorite`
--
ALTER TABLE `suliss_shop_goods_favorite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_group`
--
ALTER TABLE `suliss_shop_goods_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_label`
--
ALTER TABLE `suliss_shop_goods_label`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_option`
--
ALTER TABLE `suliss_shop_goods_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_param`
--
ALTER TABLE `suliss_shop_goods_param`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_spec`
--
ALTER TABLE `suliss_shop_goods_spec`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- 使用表AUTO_INCREMENT `suliss_shop_goods_spec_item`
--
ALTER TABLE `suliss_shop_goods_spec_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_banner`
--
ALTER TABLE `suliss_shop_groups_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_goods`
--
ALTER TABLE `suliss_shop_groups_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_goods_category`
--
ALTER TABLE `suliss_shop_groups_goods_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_goods_option`
--
ALTER TABLE `suliss_shop_groups_goods_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_ladder`
--
ALTER TABLE `suliss_shop_groups_ladder`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_order`
--
ALTER TABLE `suliss_shop_groups_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_order_comment`
--
ALTER TABLE `suliss_shop_groups_order_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_order_goods`
--
ALTER TABLE `suliss_shop_groups_order_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=210;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_order_refund`
--
ALTER TABLE `suliss_shop_groups_order_refund`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_set`
--
ALTER TABLE `suliss_shop_groups_set`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_groups_verify`
--
ALTER TABLE `suliss_shop_groups_verify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_member_address`
--
ALTER TABLE `suliss_shop_member_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_member_cart`
--
ALTER TABLE `suliss_shop_member_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=310;

--
-- 使用表AUTO_INCREMENT `suliss_shop_member_history`
--
ALTER TABLE `suliss_shop_member_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch`
--
ALTER TABLE `suliss_shop_merch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_account`
--
ALTER TABLE `suliss_shop_merch_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_account_log`
--
ALTER TABLE `suliss_shop_merch_account_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_bill`
--
ALTER TABLE `suliss_shop_merch_bill`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_billo`
--
ALTER TABLE `suliss_shop_merch_billo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_category`
--
ALTER TABLE `suliss_shop_merch_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_collect`
--
ALTER TABLE `suliss_shop_merch_collect`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_goods_category`
--
ALTER TABLE `suliss_shop_merch_goods_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_group`
--
ALTER TABLE `suliss_shop_merch_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_perm_role`
--
ALTER TABLE `suliss_shop_merch_perm_role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_merch_reg`
--
ALTER TABLE `suliss_shop_merch_reg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_nav`
--
ALTER TABLE `suliss_shop_nav`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_notice`
--
ALTER TABLE `suliss_shop_notice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `suliss_shop_order`
--
ALTER TABLE `suliss_shop_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- 使用表AUTO_INCREMENT `suliss_shop_order_comment`
--
ALTER TABLE `suliss_shop_order_comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- 使用表AUTO_INCREMENT `suliss_shop_order_goods`
--
ALTER TABLE `suliss_shop_order_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- 使用表AUTO_INCREMENT `suliss_shop_order_refund`
--
ALTER TABLE `suliss_shop_order_refund`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- 使用表AUTO_INCREMENT `suliss_shop_order_refund_log`
--
ALTER TABLE `suliss_shop_order_refund_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- 使用表AUTO_INCREMENT `suliss_shop_package`
--
ALTER TABLE `suliss_shop_package`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_package_goods`
--
ALTER TABLE `suliss_shop_package_goods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_package_goods_option`
--
ALTER TABLE `suliss_shop_package_goods_option`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_payment`
--
ALTER TABLE `suliss_shop_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_perm_plugin`
--
ALTER TABLE `suliss_shop_perm_plugin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_refund_address`
--
ALTER TABLE `suliss_shop_refund_address`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_saler`
--
ALTER TABLE `suliss_shop_saler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_sms_set`
--
ALTER TABLE `suliss_shop_sms_set`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_shop_store`
--
ALTER TABLE `suliss_shop_store`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_sysset`
--
ALTER TABLE `suliss_shop_sysset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `suliss_shop_system_copyright`
--
ALTER TABLE `suliss_shop_system_copyright`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_task_extension_join`
--
ALTER TABLE `suliss_shop_task_extension_join`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_treasure_banner`
--
ALTER TABLE `suliss_shop_treasure_banner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_treasure_goods`
--
ALTER TABLE `suliss_shop_treasure_goods`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `suliss_shop_treasure_goods_category`
--
ALTER TABLE `suliss_shop_treasure_goods_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `suliss_shop_treasure_goods_period`
--
ALTER TABLE `suliss_shop_treasure_goods_period`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `suliss_shop_verifygoods`
--
ALTER TABLE `suliss_shop_verifygoods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_verifygoods_log`
--
ALTER TABLE `suliss_shop_verifygoods_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_virtual_data`
--
ALTER TABLE `suliss_shop_virtual_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_virtual_type`
--
ALTER TABLE `suliss_shop_virtual_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_shop_wxcard`
--
ALTER TABLE `suliss_shop_wxcard`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `suliss_sms_log`
--
ALTER TABLE `suliss_sms_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '表id', AUTO_INCREMENT=102;

--
-- 使用表AUTO_INCREMENT `suliss_system_bank`
--
ALTER TABLE `suliss_system_bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- 使用表AUTO_INCREMENT `suliss_system_feedback`
--
ALTER TABLE `suliss_system_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
