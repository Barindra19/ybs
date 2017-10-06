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

 Date: 09/20/2017 08:32:21 AM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `inouts`
-- ----------------------------
DROP TABLE IF EXISTS `inouts`;
CREATE TABLE `inouts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` int(6) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `total` float(15,0) DEFAULT NULL,
  `payment_type_id` int(1) DEFAULT '0',
  `date_transaction` date DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `is_active` int(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(6) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_id` (`id`),
  KEY `idx_account_id` (`account_id`),
  KEY `branch_id` (`branch_id`),
  KEY `idx_branhc_id` (`branch_id`),
  CONSTRAINT `idx_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
