/*
 Navicat Premium Data Transfer

 Source Server         : root
 Source Server Type    : MySQL
 Source Server Version : 50726 (5.7.26)
 Source Host           : localhost:3306
 Source Schema         : works

 Target Server Type    : MySQL
 Target Server Version : 50726 (5.7.26)
 File Encoding         : 65001

 Date: 07/10/2023 10:11:27
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`  (
  `id` int(11) NOT NULL COMMENT '主健',
  `uname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名',
  `pwd` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '密码',
  `nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '管理员昵称',
  `email_system` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '邮箱服务器',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '管理员个人邮箱（用于发放密码）',
  `check_code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '邮箱服务器开放秘钥',
  `add_time` datetime NOT NULL COMMENT '添加时间',
  `last_time` datetime NOT NULL COMMENT '最近登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_stu_name`(`nickname`) USING BTREE COMMENT '姓名查询'
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES (1, 'admin', 'admin', '管理员', 'smtp.163.com', 'srq2138099022@163.com', 'TOWATVEECUOBBIOU', '2023-03-15 14:22:19', '2023-10-06 21:45:35');

-- ----------------------------
-- Table structure for classes
-- ----------------------------
DROP TABLE IF EXISTS `classes`;
CREATE TABLE `classes`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `class_id` int(11) NOT NULL COMMENT '班级代码',
  `class_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '课程名称',
  `start_time` datetime NOT NULL COMMENT '班级创建时间',
  `class_time` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '上课时间地点',
  `remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL COMMENT '网站备注',
  `status` tinyint(4) NOT NULL COMMENT '0正常1关闭',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of classes
-- ----------------------------
INSERT INTO `classes` VALUES (22, 2023001, 'Web程序开发课程', '2023-10-06 21:55:12', '公楼C404 周二上午一二节', '本课程旨在于培养学生的程序开发兴趣', 0);
INSERT INTO `classes` VALUES (23, 2023002, 'Web前端开发', '2023-10-07 09:09:33', '公楼C404 周二上午一二节', '本课程旨在于HTML、CSS、JavaScript基础学习', 0);

-- ----------------------------
-- Table structure for is_work
-- ----------------------------
DROP TABLE IF EXISTS `is_work`;
CREATE TABLE `is_work`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `class_id` int(11) NOT NULL COMMENT '班级代码',
  `work_id` int(11) NOT NULL COMMENT '作业号',
  `stu_no` int(11) NOT NULL COMMENT '学号',
  `is_true` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '0未上传、1已上传',
  `last_time` datetime NOT NULL COMMENT '最近提交时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of is_work
-- ----------------------------
INSERT INTO `is_work` VALUES (1, 2023001, 1, 2023001, '1', '2023-10-07 09:31:45');
INSERT INTO `is_work` VALUES (2, 2023002, 1, 2023001, '1', '2023-10-07 09:37:50');

-- ----------------------------
-- Table structure for score
-- ----------------------------
DROP TABLE IF EXISTS `score`;
CREATE TABLE `score`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `class_id` int(11) NOT NULL COMMENT '班级代码',
  `work_id` int(11) NOT NULL COMMENT '被评价的作业次数',
  `stu_no` int(11) NOT NULL COMMENT '被评价的学生',
  `to_stu_no` int(11) NOT NULL COMMENT '参与评分的学生',
  `score` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '作业分数',
  `start_time` datetime NOT NULL COMMENT '评价时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of score
-- ----------------------------
INSERT INTO `score` VALUES (1, 2023001, 1, 2023001, 2023002, '100', '2023-10-07 09:32:42');

-- ----------------------------
-- Table structure for stu
-- ----------------------------
DROP TABLE IF EXISTS `stu`;
CREATE TABLE `stu`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主健字段',
  `class_id` int(11) NOT NULL COMMENT '所属班级代码',
  `stu_no` int(11) NOT NULL COMMENT '学生学号',
  `stu_pwd` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '密码',
  `stu_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '学生姓名',
  `gender` tinyint(3) NOT NULL COMMENT '性别0男1女',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '学生邮箱',
  `add_time` datetime NOT NULL COMMENT '添加时间',
  `last_time` datetime NOT NULL COMMENT '最近登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_stu_name`(`stu_name`) USING BTREE COMMENT '姓名查询'
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of stu
-- ----------------------------
INSERT INTO `stu` VALUES (2, 2023001, 2023001, '2023001', '测试人员a', 0, '2833924820@qq.com', '2023-10-07 09:30:30', '2023-10-07 09:31:18');
INSERT INTO `stu` VALUES (3, 2023001, 2023002, '2023002', '测试人员b', 1, '2833924820@qq.com', '2023-10-07 09:30:30', '2023-10-07 09:32:25');
INSERT INTO `stu` VALUES (4, 2023002, 2023001, '2023001', '测试人员a', 0, '2833924820@qq.com', '2023-10-07 09:35:08', '2023-10-07 09:36:45');
INSERT INTO `stu` VALUES (5, 2023002, 2023002, '2023002', '测试人员b', 0, '2833924820@qq.com', '2023-10-07 09:35:08', '2023-10-07 09:35:08');

-- ----------------------------
-- Table structure for work
-- ----------------------------
DROP TABLE IF EXISTS `work`;
CREATE TABLE `work`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `class_id` int(11) NOT NULL COMMENT '班级代码',
  `work_id` tinyint(4) NOT NULL COMMENT '第几次作业,填写1，2，3',
  `work_remarks` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '作业描述',
  `work_start_time` datetime NOT NULL COMMENT '作业创建时间',
  `work_last_time` datetime NOT NULL COMMENT '作业截止时间',
  `status` tinyint(4) NOT NULL COMMENT '作业状态，0开启，1关闭（手动调整的）',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of work
-- ----------------------------
INSERT INTO `work` VALUES (16, 2023001, 1, '智慧作业管理系统的设计与实现', '2023-10-06 22:17:53', '2030-10-06 00:00:00', 0);
INSERT INTO `work` VALUES (17, 2023002, 1, '设计仿小米商城网页', '2023-10-07 09:36:14', '2030-10-07 00:00:00', 0);

-- ----------------------------
-- Table structure for works
-- ----------------------------
DROP TABLE IF EXISTS `works`;
CREATE TABLE `works`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `class_id` int(11) NOT NULL COMMENT '班级代码',
  `work_id` int(11) NOT NULL COMMENT '所属作业的id',
  `stu_no` int(11) NOT NULL COMMENT '所属的学生',
  `filename` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '文件的名称',
  `work_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '作业的路径',
  `start_time` datetime NOT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '这是一个存储学生文件上传路径的表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of works
-- ----------------------------
INSERT INTO `works` VALUES (1, 2023001, 1, 2023001, 'index.pdf', 'storage/2023001/stu_work/1/2023001/index.pdf', '2023-10-07 09:31:40');
INSERT INTO `works` VALUES (2, 2023002, 1, 2023001, 'index.html', 'storage/2023002/stu_work/1/2023001/index.html', '2023-10-07 09:37:43');
INSERT INTO `works` VALUES (3, 2023002, 1, 2023001, 'mi.css', 'storage/2023002/stu_work/1/2023001/mi.css', '2023-10-07 09:37:43');

SET FOREIGN_KEY_CHECKS = 1;
