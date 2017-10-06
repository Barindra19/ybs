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

 Date: 09/25/2017 22:08:52 PM
*/

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `email_templates`
-- ----------------------------
DROP TABLE IF EXISTS `email_templates`;
CREATE TABLE `email_templates` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `template` text,
  `notes` text,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int(6) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
--  Records of `email_templates`
-- ----------------------------
BEGIN;
INSERT INTO `email_templates` VALUES ('1', 'VERIFICATION_REGISTER_CUSTOMER', '<pre>\r\nSelamat bergabung dengan Your Bag Spa, marketplace online untuk dealer.\r\n\r\n<span style=\"color:#93a1a1\">\r\n</span>Anda mendaftar di Your Bag Spa, Atas Nama <em><strong>@FULLNAME</strong></em><span style=\"color:#93a1a1\"> </span>dengan email : <em><strong><span style=\"color:#dc322f\">@EMAIL</span></strong></em>.\r\n\r\n\r\n\r\nHarap ingat selalu password akun Anda, password Anda adalah password yang anda masukan ketika mendaftar di Distribution Portal System.\r\n\r\n<em><strong>@LINKVERIFICATION</strong></em>\r\n<span style=\"color:#93a1a1\">\r\n</span>\r\nCatatan penting : Pergunakan Email anda sebagai Username &amp; Password ini digunakan saat Anda melakukan Login di Your Bag Spa.\r\n\r\n\r\n\r\nTerima kasih</pre>', '<p>Masukan Parameter sebagai berikut untuk mengganti dengan data Sistem :</p>\r\n\r\n<pre>\r\n<em><strong>@FULLNAME</strong></em> = Nama Dari customer</pre>\r\n\r\n<p><em><strong>@Email</strong></em> = Email dari customer,</p>\r\n\r\n<pre>\r\n<em><strong>@LINKVERIFICATION </strong></em>= Link verifikasi yang akan dikirimkan ke email customer</pre>\r\n\r\n<p>&nbsp;</p>', '2017-09-25 14:43:12', '1', '2017-09-25 14:43:12', '1'), ('2', 'VERIFICATION_REGISTER', '<pre>\r\n<em>Selamat bergabung di Your Bag Spa. Silakan ganti password anda di link di bawah ini :</em>\r\n\r\n<em><strong>@LINKVERIFICATIONREGISTER\r\n</strong></em>\r\nTerima kasih\r\n</pre>', '<p>Masukan Parameter sebagai berikut untuk mengganti dengan data Sistem :<br />\r\n<em><strong>@FULLNAME</strong></em> = Nama lengkap tujuan.<br />\r\n<em><strong>@EMAIL</strong></em> = Email tujuan.<br />\r\n<em><strong>@</strong></em><em><strong>LINKVERIFICATIONREGISTER</strong></em> = Link verifikasi yang akan dikirimkan ke email tujuan.</p>\r\n\r\n<p>&nbsp;</p>', '2017-09-25 14:45:18', '1', '2017-09-25 15:03:10', '1'), ('3', 'RESET_PASSWORD', '<pre>\r\nDear <em><strong>@FULLNAME</strong></em>,<span style=\"color:#93a1a1\">\r\n</span>Untuk me reset password account anda silakan klik link di bawah ini:\r\n\r\n<em><strong>@LINKRESETPASSWORD</strong></em>\r\n\r\nSalam Sukses\r\n</pre>', '<pre>\r\n<em><strong>@FULLNAME : </strong></em>Nama Lengkap dari pemilik account.</pre>\r\n\r\n<p><em><strong>@EMAIL </strong></em>: Email dari pemilik account.</p>\r\n\r\n<p><em><strong>@LINKRESETPASSWORD </strong></em>: Url yang akan dikirimkan untuk mereset password user.</p>', '2017-09-25 14:57:35', '1', '2017-09-25 14:57:35', '1');
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
