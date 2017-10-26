/*
Navicat MySQL Data Transfer

Source Server         : 47.94.215.209
Source Server Version : 50556
Source Host           : 47.94.215.209:3306
Source Database       : mmkj

Target Server Type    : MYSQL
Target Server Version : 50556
File Encoding         : 65001

Date: 2017-10-25 18:48:14
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for mm_admin
-- ----------------------------
DROP TABLE IF EXISTS `mm_admin`;
CREATE TABLE `mm_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL COMMENT '用户名',
  `auth_key` varchar(32) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT '密码',
  `email` varchar(255) NOT NULL COMMENT '邮箱',
  `reg_ip` int(11) NOT NULL DEFAULT '0' COMMENT '创建或注册IP',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` int(11) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户状态 1正常 0禁用',
  `created_at` int(11) NOT NULL COMMENT '创建或注册时间',
  `updated_at` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_admin
-- ----------------------------
INSERT INTO `mm_admin` VALUES ('1', 'admin', 'SbSY36BLw3V2lU-GB7ZAzCVJKDFx82IJ', '$2y$13$0UVcG.mXF6Og0rnjfwJd2.wixT2gdn.wDO9rN44jGtIGc6JvBqR7i', '771405950@qq.com', '2130706433', '1508927979', '2130706433', '1', '1482305564', '1508927979');
INSERT INTO `mm_admin` VALUES ('2', 'longkui', 'dOoJdecV48wXn-um5g9ho2QCJcmVGezh', '$2y$13$QXu66Dd87Lx/vGWyoo.jQuj34syrM87K.7L4Fs581kGsid5zc.Ifa', '657277185@qq.com', '2130706433', '1508910018', '2130706433', '1', '1508817472', '1508910018');

-- ----------------------------
-- Table structure for mm_auth_assignment
-- ----------------------------
DROP TABLE IF EXISTS `mm_auth_assignment`;
CREATE TABLE `mm_auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` varchar(64) NOT NULL,
  `created_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  CONSTRAINT `mm_auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `mm_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_auth_assignment
-- ----------------------------
INSERT INTO `mm_auth_assignment` VALUES ('超级管理员', '1', '1508910097');
INSERT INTO `mm_auth_assignment` VALUES ('超级管理员', '2', '1508897858');

-- ----------------------------
-- Table structure for mm_auth_item
-- ----------------------------
DROP TABLE IF EXISTS `mm_auth_item`;
CREATE TABLE `mm_auth_item` (
  `name` varchar(64) NOT NULL,
  `type` int(11) NOT NULL,
  `description` text,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  KEY `type` (`type`),
  CONSTRAINT `mm_auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `mm_auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_auth_item
-- ----------------------------
INSERT INTO `mm_auth_item` VALUES ('/admin/admin/auth', '2', '', '/admin/admin/auth', null, '1508918549', '1508918630');
INSERT INTO `mm_auth_item` VALUES ('/admin/admin/create', '2', '', '/admin/admin/create', null, '1508918549', '1508918629');
INSERT INTO `mm_auth_item` VALUES ('/admin/admin/delete', '2', '', '/admin/admin/delete', null, '1508918550', '1508918630');
INSERT INTO `mm_auth_item` VALUES ('/admin/admin/index', '2', '', '/admin/admin/index', null, '1508918232', '1508918629');
INSERT INTO `mm_auth_item` VALUES ('/admin/admin/update', '2', '', '/admin/admin/update', null, '1508918549', '1508918630');
INSERT INTO `mm_auth_item` VALUES ('/admin/config/attachment', '2', '', '/admin/config/attachment', null, '1508918499', '1508918628');
INSERT INTO `mm_auth_item` VALUES ('/admin/config/basic', '2', '', '/admin/config/basic', null, '1508918231', '1508918628');
INSERT INTO `mm_auth_item` VALUES ('/admin/config/send-mail', '2', '', '/admin/config/send-mail', null, '1508918472', '1508918628');
INSERT INTO `mm_auth_item` VALUES ('/admin/database/export', '2', '', '/admin/database/export', null, '1508918232', '1508918631');
INSERT INTO `mm_auth_item` VALUES ('/admin/index/index', '2', '', '/admin/index/index', null, '1508918231', '1508918627');
INSERT INTO `mm_auth_item` VALUES ('/admin/menu/create', '2', '', '/admin/menu/create', null, '1508918548', '1508918629');
INSERT INTO `mm_auth_item` VALUES ('/admin/menu/delete', '2', '', '/admin/menu/delete', null, '1508918549', '1508918629');
INSERT INTO `mm_auth_item` VALUES ('/admin/menu/index', '2', '', '/admin/menu/index', null, '1508918548', '1508918628');
INSERT INTO `mm_auth_item` VALUES ('/admin/menu/update', '2', '', '/admin/menu/update', null, '1508918548', '1508918629');
INSERT INTO `mm_auth_item` VALUES ('/admin/role/auth', '2', '', '/admin/role/auth', null, '1508918550', '1508918630');
INSERT INTO `mm_auth_item` VALUES ('/admin/role/create', '2', '', '/admin/role/create', null, '1508918550', '1508918630');
INSERT INTO `mm_auth_item` VALUES ('/admin/role/delete', '2', '', '/admin/role/delete', null, '1508918550', '1508918631');
INSERT INTO `mm_auth_item` VALUES ('/admin/role/index', '2', '', '/admin/role/index', null, '1508918550', '1508918630');
INSERT INTO `mm_auth_item` VALUES ('/admin/role/update', '2', '', '/admin/role/update', null, '1508918550', '1508918630');
INSERT INTO `mm_auth_item` VALUES ('admin/admin/index', '2', '', 'admin/admin/index', null, '1508897677', '1508910081');
INSERT INTO `mm_auth_item` VALUES ('admin/index/index', '2', '', 'admin/index/index', null, '1508912730', '1508912730');
INSERT INTO `mm_auth_item` VALUES ('config/basic', '2', '', 'config/basic', null, '1508897677', '1508912730');
INSERT INTO `mm_auth_item` VALUES ('config/send-mail', '2', '', 'config/send-mail', null, '1508910074', '1508910074');
INSERT INTO `mm_auth_item` VALUES ('database/export', '2', '', 'database/export', null, '1508897677', '1508912731');
INSERT INTO `mm_auth_item` VALUES ('子管理员1', '1', '子管理员1', null, null, '1508897720', '1508897720');
INSERT INTO `mm_auth_item` VALUES ('超级管理员', '1', '超级管理员拥有所有权限', null, null, '1508897626', '1508897626');

-- ----------------------------
-- Table structure for mm_auth_item_child
-- ----------------------------
DROP TABLE IF EXISTS `mm_auth_item_child`;
CREATE TABLE `mm_auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`),
  CONSTRAINT `mm_auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `mm_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mm_auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `mm_auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_auth_item_child
-- ----------------------------
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/admin/auth');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/admin/create');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/admin/delete');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/admin/index');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/admin/update');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/config/attachment');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/config/basic');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/config/send-mail');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/database/export');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/index/index');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/menu/create');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/menu/delete');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/menu/index');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/menu/update');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/role/auth');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/role/create');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/role/delete');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/role/index');
INSERT INTO `mm_auth_item_child` VALUES ('超级管理员', '/admin/role/update');
INSERT INTO `mm_auth_item_child` VALUES ('子管理员1', 'database/export');

-- ----------------------------
-- Table structure for mm_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `mm_auth_rule`;
CREATE TABLE `mm_auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` text,
  `created_at` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_auth_rule
-- ----------------------------
INSERT INTO `mm_auth_rule` VALUES ('/admin/admin/auth', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:17:\"/admin/admin/auth\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918549;s:9:\"updatedAt\";i:1508918630;}', '1508918549', '1508918630');
INSERT INTO `mm_auth_rule` VALUES ('/admin/admin/create', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:19:\"/admin/admin/create\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918549;s:9:\"updatedAt\";i:1508918629;}', '1508918549', '1508918629');
INSERT INTO `mm_auth_rule` VALUES ('/admin/admin/delete', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:19:\"/admin/admin/delete\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918549;s:9:\"updatedAt\";i:1508918630;}', '1508918549', '1508918630');
INSERT INTO `mm_auth_rule` VALUES ('/admin/admin/index', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/admin/index\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918232;s:9:\"updatedAt\";i:1508918629;}', '1508918232', '1508918629');
INSERT INTO `mm_auth_rule` VALUES ('/admin/admin/update', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:19:\"/admin/admin/update\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918549;s:9:\"updatedAt\";i:1508918630;}', '1508918549', '1508918630');
INSERT INTO `mm_auth_rule` VALUES ('/admin/config/attachment', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:24:\"/admin/config/attachment\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918499;s:9:\"updatedAt\";i:1508918628;}', '1508918499', '1508918628');
INSERT INTO `mm_auth_rule` VALUES ('/admin/config/basic', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:19:\"/admin/config/basic\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918231;s:9:\"updatedAt\";i:1508918628;}', '1508918231', '1508918628');
INSERT INTO `mm_auth_rule` VALUES ('/admin/config/send-mail', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:23:\"/admin/config/send-mail\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918472;s:9:\"updatedAt\";i:1508918628;}', '1508918472', '1508918628');
INSERT INTO `mm_auth_rule` VALUES ('/admin/database/export', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:22:\"/admin/database/export\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918232;s:9:\"updatedAt\";i:1508918631;}', '1508918232', '1508918631');
INSERT INTO `mm_auth_rule` VALUES ('/admin/index/index', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/index/index\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918231;s:9:\"updatedAt\";i:1508918627;}', '1508918231', '1508918627');
INSERT INTO `mm_auth_rule` VALUES ('/admin/menu/create', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/menu/create\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918548;s:9:\"updatedAt\";i:1508918629;}', '1508918548', '1508918629');
INSERT INTO `mm_auth_rule` VALUES ('/admin/menu/delete', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/menu/delete\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918549;s:9:\"updatedAt\";i:1508918629;}', '1508918549', '1508918629');
INSERT INTO `mm_auth_rule` VALUES ('/admin/menu/index', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:17:\"/admin/menu/index\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918548;s:9:\"updatedAt\";i:1508918628;}', '1508918548', '1508918628');
INSERT INTO `mm_auth_rule` VALUES ('/admin/menu/update', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/menu/update\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918548;s:9:\"updatedAt\";i:1508918629;}', '1508918548', '1508918629');
INSERT INTO `mm_auth_rule` VALUES ('/admin/role/auth', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:16:\"/admin/role/auth\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918550;s:9:\"updatedAt\";i:1508918630;}', '1508918550', '1508918630');
INSERT INTO `mm_auth_rule` VALUES ('/admin/role/create', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/role/create\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918550;s:9:\"updatedAt\";i:1508918630;}', '1508918550', '1508918630');
INSERT INTO `mm_auth_rule` VALUES ('/admin/role/delete', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/role/delete\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918550;s:9:\"updatedAt\";i:1508918631;}', '1508918550', '1508918631');
INSERT INTO `mm_auth_rule` VALUES ('/admin/role/index', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:17:\"/admin/role/index\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918550;s:9:\"updatedAt\";i:1508918630;}', '1508918550', '1508918630');
INSERT INTO `mm_auth_rule` VALUES ('/admin/role/update', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:18:\"/admin/role/update\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508918550;s:9:\"updatedAt\";i:1508918630;}', '1508918550', '1508918630');
INSERT INTO `mm_auth_rule` VALUES ('admin/admin/index', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:11:\"admin/index\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508897677;s:9:\"updatedAt\";i:1508910081;}', '1508897677', '1508910081');
INSERT INTO `mm_auth_rule` VALUES ('admin/index/index', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:11:\"index/index\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508912730;s:9:\"updatedAt\";i:1508912730;}', '1508912730', '1508912730');
INSERT INTO `mm_auth_rule` VALUES ('config/basic', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:12:\"config/basic\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508897677;s:9:\"updatedAt\";i:1508912730;}', '1508897677', '1508912730');
INSERT INTO `mm_auth_rule` VALUES ('config/send-mail', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:16:\"config/send-mail\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508910074;s:9:\"updatedAt\";i:1508910074;}', '1508910074', '1508910074');
INSERT INTO `mm_auth_rule` VALUES ('database/export', 'O:29:\"modules\\admin\\models\\AuthRule\":4:{s:4:\"name\";s:15:\"database/export\";s:36:\"\0modules\\admin\\models\\AuthRule\0_rule\";r:1;s:9:\"createdAt\";i:1508897677;s:9:\"updatedAt\";i:1508912731;}', '1508897677', '1508912731');

-- ----------------------------
-- Table structure for mm_config
-- ----------------------------
DROP TABLE IF EXISTS `mm_config`;
CREATE TABLE `mm_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `keyid` varchar(20) NOT NULL DEFAULT '',
  `title` varchar(100) NOT NULL DEFAULT '',
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `keyid` (`keyid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_config
-- ----------------------------
INSERT INTO `mm_config` VALUES ('1', 'basic', '', '{\"sitename\":\"\\u79d8\\u5bc6\\u7a7a\\u95f4\",\"url\":\"http:\\/\\/mmkj.com\",\"logo\":\"\\/statics\\/themes\\/admin\\/images\\/timg.jpg\",\"seo_keywords\":\"\\u79d8\\u5bc6\\u7a7a\\u95f4\",\"seo_description\":\"\\u79d8\\u5bc6\\u7a7a\\u95f4\",\"copyright\":\"@\\u79d8\\u5bc6\\u7a7a\\u95f4\",\"statcode\":\"\",\"close\":\"0\",\"close_reason\":\"\\u7ad9\\u70b9\\u5347\\u7ea7\\u4e2d, \\u8bf7\\u7a0d\\u540e\\u8bbf\\u95ee!\"}');
INSERT INTO `mm_config` VALUES ('2', 'sendmail', '', '{\"mail_type\":\"0\",\"smtp_server\":\"smtp.qq.com\",\"smtp_port\":\"25\",\"auth\":\"1\",\"openssl\":\"1\",\"smtp_user\":\"771405950\",\"smtp_pwd\":\"qiaoBo1989122\",\"send_email\":\"771405950@qq.com\",\"nickname\":\"\\u8fb9\\u8d70\\u8fb9\\u4e54\",\"sign\":\"<hr \\/>\\r\\n\\u90ae\\u4ef6\\u7b7e\\u540d\\uff1a\\u6b22\\u8fce\\u8bbf\\u95ee <a href=\\\"http:\\/\\/www.test-mmcms.com\\\" target=\\\"_blank\\\">mm CMS<\\/a>\"}');
INSERT INTO `mm_config` VALUES ('3', 'attachment', '', '{\"attachment_size\":\"2048\",\"attachment_suffix\":\"jpg|jpeg|gif|bmp|png\",\"watermark_enable\":\"1\",\"watermark_pos\":\"0\",\"watermark_text\":\"mm CMS\"}');

-- ----------------------------
-- Table structure for mm_demo
-- ----------------------------
DROP TABLE IF EXISTS `mm_demo`;
CREATE TABLE `mm_demo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` varchar(255) NOT NULL,
  `sex` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_demo
-- ----------------------------
INSERT INTO `mm_demo` VALUES ('1', '小汉哥', '小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥小汉哥', '2');
INSERT INTO `mm_demo` VALUES ('2', '小红红', '小红红小红红小红红小红红小红红小红红小红红小红红小红红', '1');

-- ----------------------------
-- Table structure for mm_menu
-- ----------------------------
DROP TABLE IF EXISTS `mm_menu`;
CREATE TABLE `mm_menu` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL DEFAULT '',
  `url` varchar(60) NOT NULL DEFAULT '',
  `icon_style` varchar(50) NOT NULL DEFAULT '',
  `display` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `sort` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_menu
-- ----------------------------
INSERT INTO `mm_menu` VALUES ('1', '0', '我的面板', '/admin/index/index', 'fa-home', '1', '0');
INSERT INTO `mm_menu` VALUES ('2', '0', '站点设置', '/admin/config/basic', 'fa-cogs', '1', '0');
INSERT INTO `mm_menu` VALUES ('3', '0', '管理员设置', '/admin/admin/index', 'fa-user', '1', '0');
INSERT INTO `mm_menu` VALUES ('4', '0', '内容设置', '', 'fa-edit', '1', '0');
INSERT INTO `mm_menu` VALUES ('5', '0', '用户设置', '', 'fa-users', '1', '0');
INSERT INTO `mm_menu` VALUES ('6', '0', '数据库设置', '/admin/database/export', 'fa-hdd-o', '1', '0');
INSERT INTO `mm_menu` VALUES ('7', '0', '界面设置', '', 'fa-picture-o', '1', '0');
INSERT INTO `mm_menu` VALUES ('8', '1', '系统信息', '/admin/index/index', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('9', '2', '站点配置', '/admin/config/basic', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('10', '2', '后台菜单管理', '/admin/menu/index', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('11', '3', '管理员管理', '/admin/admin/index', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('12', '3', '角色管理', '/admin/role/index', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('13', '4', '内容管理', '', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('14', '4', '栏目管理', '', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('15', '4', '模型管理', '', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('16', '5', '用户管理', '', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('17', '6', '数据库管理', '/admin/database/export', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('18', '7', '主题管理', '', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('19', '7', '模板管理', '', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('20', '9', '基本配置', '/admin/config/basic', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('21', '9', '邮箱配置', '/admin/config/send-mail', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('22', '9', '附件配置', '/admin/config/attachment', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('23', '10', '添加菜单', '/admin/menu/create', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('24', '10', '更新', '/admin/menu/update', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('25', '10', '删除', '/admin/menu/delete', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('26', '11', '添加', '/admin/admin/create', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('27', '11', '更新', '/admin/admin/update', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('28', '11', '授权', '/admin/admin/auth', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('29', '11', '删除', '/admin/admin/delete', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('30', '12', '添加', '/admin/role/create', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('31', '12', '更新', '/admin/role/update', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('32', '12', '授权', '/admin/role/auth', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('33', '12', '删除', '/admin/role/delete', '', '1', '0');
INSERT INTO `mm_menu` VALUES ('34', '1', '通知公告', '/notice/index', '', '0', '1');
INSERT INTO `mm_menu` VALUES ('36', '1', '微信端', '/wechat/index', '', '1', '2');
INSERT INTO `mm_menu` VALUES ('38', '0', '测试', '/admin/demo/lst', 'icon-comment', '1', '1');
INSERT INTO `mm_menu` VALUES ('39', '38', 'demo', '/admin/demo/lst', 'icon-comment', '1', '1');

-- ----------------------------
-- Table structure for mm_migration
-- ----------------------------
DROP TABLE IF EXISTS `mm_migration`;
CREATE TABLE `mm_migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_migration
-- ----------------------------
INSERT INTO `mm_migration` VALUES ('m000000_000000_base', '1482231528');
INSERT INTO `mm_migration` VALUES ('m130524_201442_init', '1482231534');

-- ----------------------------
-- Table structure for mm_session
-- ----------------------------
DROP TABLE IF EXISTS `mm_session`;
CREATE TABLE `mm_session` (
  `id` char(40) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of mm_session
-- ----------------------------
INSERT INTO `mm_session` VALUES ('3nimblasnjbra4jg1na4g66tp0', '1508911746', 0x5F5F666C6173687C613A303A7B7D);
INSERT INTO `mm_session` VALUES ('he8brd53rnicqojqqt92o0v6j4', '1508929278', 0x5F5F666C6173687C613A303A7B7D5F5F69647C733A313A2231223B);
INSERT INTO `mm_session` VALUES ('j010qm70e94jr6npbt2hrrs7l6', '1508929862', 0x5F5F666C6173687C613A303A7B7D5F5F69647C733A313A2231223B);
INSERT INTO `mm_session` VALUES ('mljsr9tvcpdv58dful3mgu8u23', '1508916554', 0x5F5F666C6173687C613A303A7B7D);
INSERT INTO `mm_session` VALUES ('on2ebaebcrvvetm579bf7dq4b2', '1508910662', 0x5F5F666C6173687C613A303A7B7D5F5F69647C733A313A2231223B);
INSERT INTO `mm_session` VALUES ('qdi83uk9hl9lbibn1i2s4egeu5', '1508926000', 0x5F5F666C6173687C613A303A7B7D);
INSERT INTO `mm_session` VALUES ('vkmkqf7grtr9sbvh430lktbvf5', '1508921267', 0x5F5F666C6173687C613A303A7B7D);

-- ----------------------------
-- Table structure for mm_user
-- ----------------------------
DROP TABLE IF EXISTS `mm_user`;
CREATE TABLE `mm_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `auth_key` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` smallint(6) NOT NULL DEFAULT '10',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `password_reset_token` (`password_reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- ----------------------------
-- Records of mm_user
-- ----------------------------
