/*
 Navicat Premium Data Transfer

 Source Server         : Localhost
 Source Server Type    : MySQL
 Source Server Version : 50635
 Source Host           : localhost
 Source Database       : db_ybs

 Target Server Type    : MySQL
 Target Server Version : 50635
 File Encoding         : utf-8

 Date: 09/20/2017 08:33:18 AM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `accounts`
-- ----------------------------
DROP TABLE IF EXISTS `accounts`;
CREATE TABLE `accounts` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `flow` enum('I','O') DEFAULT NULL,
  `is_active` int(1) DEFAULT '1' COMMENT '0 not active, 1 active',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(6) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `accounts`
-- ----------------------------
BEGIN;
INSERT INTO `accounts` VALUES ('1', 'Pendapatan Lain-lain', 'I', '1', '2017-09-18 14:08:41', '1', '2017-09-18 14:29:34', '1'), ('2', 'Pengeluaran Lain-lain', 'O', '1', '2017-09-18 14:09:03', '1', '2017-09-18 14:09:03', '1');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
