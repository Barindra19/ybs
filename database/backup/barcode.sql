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

 Date: 09/18/2017 14:51:38 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `barcode_lists`
-- ----------------------------
DROP TABLE IF EXISTS `barcode_lists`;
CREATE TABLE `barcode_lists` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `barcode_id` int(6) NOT NULL,
  `barcode` varchar(13) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(6) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `barcode_id` (`barcode_id`),
  FULLTEXT KEY `barcode_text` (`barcode`),
  CONSTRAINT `foreign_key_Customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  CONSTRAINT `foreign_key_barcode` FOREIGN KEY (`barcode_id`) REFERENCES `barcodes` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Table structure for `barcodes`
-- ----------------------------
DROP TABLE IF EXISTS `barcodes`;
CREATE TABLE `barcodes` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `count` int(6) DEFAULT NULL,
  `file` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(6) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;
