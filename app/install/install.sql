/*
 Navicat Premium Dump SQL

 Source Server         : file_vmssr_co
 Source Server Type    : MySQL
 Source Server Version : 50744 (5.7.44-log)
 Source Host           : 154.13.6.14:3306
 Source Schema         : file_vmssr_co

 Target Server Type    : MySQL
 Target Server Version : 50744 (5.7.44-log)
 File Encoding         : 65001

 Date: 14/09/2024 15:24:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for __PREFIX__admin
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin`;
CREATE TABLE `__PREFIX__admin`  (
  `id` mediumint(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '分组id',
  `branch_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门id',
  `jobs_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '岗位id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '帐号',
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户昵称',
  `pwd` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `sex` tinyint(1) NOT NULL DEFAULT 1 COMMENT '性别',
  `tags` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户标签',
  `face` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '/static/admin/images/user.png' COMMENT '头像',
  `mood` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '每日心情',
  `email` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱',
  `area` char(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '区号',
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '手机',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '简介',
  `count` smallint(6) NULL DEFAULT NULL COMMENT '登录次数',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户地址',
  `login_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '登录IP',
  `login_time` int(11) NULL DEFAULT NULL COMMENT '最后登录时间',
  `create_ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '注册IP',
  `status` int(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '用户状态',
  `banned` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '封号原因',
  `create_time` int(11) NULL DEFAULT NULL,
  `update_time` int(11) NULL DEFAULT NULL,
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `name`(`name`(191)) USING BTREE,
  INDEX `pwd`(`pwd`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '后台管理员表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin
-- ----------------------------
INSERT INTO `__PREFIX__admin` VALUES (1, '1,3', '2', '3', 'admin', '管理员', '13682bec405cf4b9002e6e8306312ce6', 1, 'a:2:{i:0;s:12:\"代码狂人\";i:1;s:15:\"隔壁帅小伙\";}', '/static/images/avatar/master.jpg', '海阔天空，有容乃大', 'admin@swiftadmin.net', '0310', '15188888888', '高级管理人员', 297, '河北省邯郸市', '141.11.87.57', 1726232205, '127.0.0.1', 1, NULL, 1596682835, 1726248479, NULL);

-- ----------------------------
-- Table structure for __PREFIX__admin_access
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_access`;
CREATE TABLE `__PREFIX__admin_access`  (
  `admin_id` mediumint(8) UNSIGNED NOT NULL COMMENT '用户ID',
  `group_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '管理员分组',
  `rules` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '自定义权限',
  `cates` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '栏目权限',
  PRIMARY KEY (`admin_id`) USING BTREE,
  INDEX `uid`(`admin_id`) USING BTREE,
  INDEX `group_id`(`group_id`(191)) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '组规则表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_access
-- ----------------------------
INSERT INTO `__PREFIX__admin_access` VALUES (1, '1', '', NULL);

-- ----------------------------
-- Table structure for __PREFIX__admin_group
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_group`;
CREATE TABLE `__PREFIX__admin_group`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NOT NULL COMMENT '父组id',
  `jobid` int(11) NULL DEFAULT NULL COMMENT '体系id',
  `title` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '分组名称',
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '标识',
  `type` int(11) NULL DEFAULT NULL COMMENT '分组类型',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注',
  `rules` varchar(2048) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '规则字符串',
  `cates` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '栏目权限',
  `color` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '颜色',
  `create_time` int(11) NULL DEFAULT NULL,
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户组表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_group
-- ----------------------------
INSERT INTO `__PREFIX__admin_group` VALUES (1, 0, 1, '超级管理员', 'admin', 1, 1, '网站超级管理员组的', '1,2,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,57,58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77,78,79,80,81,82,83,84,85,86,87,88,89', NULL, 'layui-bg-blue', 1607832158, NULL);

-- ----------------------------
-- Table structure for __PREFIX__admin_log
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_log`;
CREATE TABLE `__PREFIX__admin_log`  (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '访问ID',
  `name` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '账号',
  `nickname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '用户昵称',
  `user_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '用户 IP',
  `user_agent` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '浏览器 UA',
  `user_os` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '操作系统',
  `user_browser` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '浏览器',
  `status` int(1) NULL DEFAULT 0 COMMENT '登录状态',
  `error` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '错误信息',
  `update_time` int(11) UNSIGNED NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_ip`(`user_ip`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户登录记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_log
-- ----------------------------
INSERT INTO `__PREFIX__admin_log` VALUES (1, 'admin', '管理员', '141.11.87.57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Safari/537.36', 'Windows NT 10.0', 'Chrome/128.0.0.0 Safari/537.36', 1, '登录成功', 1726232205, 1726232205);

-- ----------------------------
-- Table structure for __PREFIX__admin_notice
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_notice`;
CREATE TABLE `__PREFIX__admin_notice`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` enum('notice','message','todo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'notice' COMMENT '消息类型',
  `admin_id` int(11) NOT NULL COMMENT '管理员ID',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '消息标题',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '消息内容',
  `send_id` int(11) UNSIGNED NULL DEFAULT 0 COMMENT '发送者ID',
  `send_ip` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '10.10.11.22' COMMENT '发送者IP地址',
  `is_system` int(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否为系统消息',
  `push` int(1) UNSIGNED NULL DEFAULT 0 COMMENT '推送状态',
  `status` int(1) UNSIGNED NULL DEFAULT 0 COMMENT '消息状态',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员消息表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_notice
-- ----------------------------
INSERT INTO `__PREFIX__admin_notice` VALUES (1, 'notice', 1, '系统通知', '拟录取名单予以公示，公示期为5天。', 0, '10.10.11.22', 0, 0, 1, 1668393025, NULL);
INSERT INTO `__PREFIX__admin_notice` VALUES (2, 'message', 1, '请注意查收短消息', '听说你又接了一个大项目', 2, '10.10.11.22', 0, 0, 1, 1668323353, NULL);
INSERT INTO `__PREFIX__admin_notice` VALUES (3, 'todo', 1, '您有一项待办', '请完成项目的迭代工作与BUG修复', 0, '10.10.11.22', 0, 0, 1, 1668393025, NULL);
INSERT INTO `__PREFIX__admin_notice` VALUES (4, 'message', 2, '发送给ID为2的消息', '这里是内容', 1, '10.10.11.22', 0, 0, 0, 1668393025, NULL);

-- ----------------------------
-- Table structure for __PREFIX__admin_rules
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__admin_rules`;
CREATE TABLE `__PREFIX__admin_rules`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '父栏目id',
  `title` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '菜单标题',
  `router` char(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '路由地址',
  `alias` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '权限标识',
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '菜单，按钮，接口，系统',
  `note` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注信息',
  `condition` char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '正则表达式',
  `sort` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '排序',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '图标',
  `auth` tinyint(4) NULL DEFAULT 1 COMMENT '状态',
  `status` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT 'normal' COMMENT '状态码',
  `isSystem` tinyint(3) UNSIGNED NULL DEFAULT 0 COMMENT '系统级,只可手动操作',
  `update_time` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 265 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '菜单权限表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__admin_rules
-- ----------------------------
INSERT INTO `__PREFIX__admin_rules` VALUES (1, 0, 'Dashboard', 'Dashboard', 'dashboard', 0, '', '', 1, 'layui-icon-home', 0, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (2, 1, '控制台', '/index/console', 'index:console', 0, '', '', 2, '', 0, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (5, 0, '系统管理', 'System', 'system', 0, '', '', 5, 'layui-icon-set-fill', 1, 'normal', 0, 1700303729, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (6, 5, '基本设置', '/Index/baseCfg', 'Index:baseCfg', 0, '', '', 6, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (7, 6, '修改配置', '/Index/baseSet', 'Index:baseSet', 2, '', '', 7, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (8, 6, 'FTP接口', '/Index/testFtp', 'Index:testFtp', 2, '', '', 8, '', 0, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (9, 6, '邮件接口', '/Index/testEmail', 'Index:testEmail', 2, '', '', 9, '', 0, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (10, 6, '缓存接口', '/Index/testCache', 'Index:testCache', 2, '', '', 10, '', 0, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (11, 5, '用户管理', '/system.admin/index', 'system.admin:index', 0, '', '', 11, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (12, 11, '查看', '/system.Admin/index', 'system.Admin:index', 1, '', '', 12, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (13, 11, '添加', '/system.Admin/add', 'system.Admin:add', 1, '', '', 13, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (14, 11, '编辑', '/system.Admin/edit', 'system.Admin:edit', 1, '', '', 14, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (15, 11, '删除', '/system.Admin/del', 'system.Admin:del', 1, '', '', 15, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (16, 11, '状态', '/system.Admin/status', 'system.Admin:status', 2, '', '', 16, '', 1, 'normal', 0, 1688481042, 1688481042, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (17, 11, '编辑权限', '/system.Admin/editrules', 'system.Admin:editrules', 2, '', '', 17, '', 1, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (18, 11, '编辑栏目', '/system.Admin/editcates', 'system.Admin:editcates', 2, '', '', 18, '', 1, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (19, 11, '获取节点', '/system.Admin/getRuleCateTree', 'system.Admin:getRuleCateTree', 2, '', '', 19, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (20, 11, '数据接口', '/system.Admin/authorities', 'system.Admin:authorities', 3, '', '', 20, '', 0, 'normal', 1, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (21, 11, '即时接口', '/system/Admin/instant', 'system:Admin:instant', 2, '', '', 21, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (22, 5, '用户中心', '/system.Admin/center', 'system.Admin:center', 0, '', '', 22, '', 1, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (23, 22, '系统模板', '/system.Admin/theme', 'system.Admin:theme', 2, '', '', 23, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (24, 22, '修改资料', '/system.Admin/modify', 'system.Admin:modify', 2, '', '', 24, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (25, 22, '修改密码', '/system.Admin/pwd', 'system.Admin:pwd', 2, '', '', 25, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (26, 22, '系统语言', '/system.Admin/language', 'system.Admin:language', 2, '', '', 26, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (27, 22, '清理缓存', '/system.Admin/clear', 'system.Admin:clear', 2, '', '', 27, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (28, 5, '消息中心', '/system.AdminNotice/index', 'system.AdminNotice:index', 0, '', '', 28, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (29, 28, '我的消息', '/system.AdminNotice/bells', 'system.AdminNotice:bells', 2, '', '', 29, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (30, 28, '分类消息', '/system.AdminNotice/getBells', 'system.AdminNotice:getBells', 2, '', '', 30, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (31, 28, '阅读消息', '/system.AdminNotice/read', 'system.AdminNotice:read', 2, '', '', 31, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (32, 28, '创建消息', '/system.AdminNotice/add', 'system.AdminNotice:add', 2, '', '', 32, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (33, 28, '清空消息', '/system.AdminNotice/clear', 'system.AdminNotice:clear', 2, '', '', 33, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (34, 28, '设置已读', '/system.AdminNotice/readAll', 'system.AdminNotice:readAll', 2, '', '', 34, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (35, 28, '删除消息', '/system.AdminNotice/del', 'system.AdminNotice:del', 2, '', '', 35, '', 0, 'normal', 0, 1688481043, 1688481043, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (36, 5, '角色管理', '/system.AdminGroup/index', 'system.AdminGroup:index', 0, '', '', 36, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (37, 36, '查看', '/system.AdminGroup/index', 'system.AdminGroup:index', 1, '', '', 37, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (38, 36, '添加', '/system.AdminGroup/add', 'system.AdminGroup:add', 1, '', '', 38, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (39, 36, '编辑', '/system.AdminGroup/edit', 'system.AdminGroup:edit', 1, '', '', 39, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (40, 36, '删除', '/system.AdminGroup/del', 'system.AdminGroup:del', 1, '', '', 40, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (41, 36, '状态', '/system.AdminGroup/status', 'system.AdminGroup:status', 2, '', '', 41, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (42, 36, '编辑权限', '/system.AdminGroup/editrules', 'system.AdminGroup:editrules', 2, '', '', 42, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (43, 36, '编辑栏目', '/system.AdminGroup/editcates', 'system.AdminGroup:editcates', 2, '', '', 43, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (44, 36, '获取节点', '/system.AdminGroup/getRuleCateTree', 'system.AdminGroup:getRuleCateTree', 2, '', '', 44, '', 0, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (45, 5, '菜单管理', '/system.AdminRules/index', 'system.AdminRules:index', 0, '', '', 45, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (46, 45, '查询', '/system.AdminRules/index', 'system.AdminRules:index', 1, '', '', 46, NULL, 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (47, 45, '添加', '/system.AdminRules/add', 'system.AdminRules:add', 1, '', '', 47, NULL, 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (48, 45, '编辑', '/system.AdminRules/edit', 'system.AdminRules:edit', 1, '', '', 48, NULL, 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (49, 45, '删除', '/system.AdminRules/del', 'system.AdminRules:del', 1, '', '', 49, NULL, 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (50, 45, '状态', '/system.AdminRules/status', 'system.AdminRules:status', 2, '', '', 50, NULL, 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (51, 5, '操作日志', '/system.SystemLog/index', 'system.SystemLog:index', 0, '', '', 51, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (52, 51, '查询', '/system.SystemLog/index', 'system.SystemLog:index', 1, '', '', 52, '', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (53, 5, '登录日志', '/system.AdminLog/index', 'system.AdminLog:index', 0, '', '', 53, '', 0, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (54, 0, '高级管理', 'Management', 'management', 0, '', '', 54, 'layui-icon-engine', 1, 'normal', 0, 1688481044, 1688481044, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (55, 54, '公司管理', '/system.Company/index', 'system.Company:index', 0, '', '', 55, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (56, 55, '查看', '/system.Company/index', 'system.Company:index', 1, '', '', 56, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (57, 55, '添加', '/system.Company/add', 'system.Company:add', 1, '', '', 57, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (58, 55, '编辑', '/system.Company/edit', 'system.Company:edit', 1, '', '', 58, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (59, 55, '删除', '/system.Company/del', 'system.Company:del', 1, '', '', 59, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (60, 55, '状态', '/system.Company/status', 'system.Company:status', 2, '', '', 60, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (61, 54, '部门管理', '/system.Department/index', 'system.Department:index', 0, '', '', 61, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (62, 61, '查看', '/system.Department/index', 'system.Department:index', 1, '', '', 62, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (63, 61, '添加', '/system.Department/add', 'system.Department:add', 1, '', '', 63, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (64, 61, '编辑', '/system.Department/edit', 'system.Department:edit', 1, '', '', 64, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (65, 61, '删除', '/system.Department/del', 'system.Department:del', 1, '', '', 65, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (66, 61, '状态', '/system.Department/status', 'system.Department:status', 2, '', '', 66, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (67, 54, '岗位管理', '/system.Jobs/index', 'system.Jobs:index', 0, '', '', 67, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (68, 67, '查看', '/system.Jobs/index', 'system.Jobs:index', 1, '', '', 68, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (69, 67, '添加', '/system.Jobs/add', 'system.Jobs:add', 1, '', '', 69, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (70, 67, '编辑', '/system.Jobs/edit', 'system.Jobs:edit', 1, '', '', 70, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (71, 67, '删除', '/system.Jobs/del', 'system.Jobs:del', 1, '', '', 71, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (72, 67, '状态', '/system.Jobs/status', 'system.Jobs:status', 2, '', '', 72, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (73, 54, '字典设置', '/system.Dictionary/index', 'system.Dictionary:index', 0, '', '', 73, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (74, 73, '查看', '/system.Dictionary/index', 'system.Dictionary:index', 1, '', '', 74, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (75, 73, '添加', '/system.Dictionary/add', 'system.Dictionary:add', 1, '', '', 75, '', 1, 'normal', 0, 1688481045, 1688481045, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (76, 73, '编辑', '/system.Dictionary/edit', 'system.Dictionary:edit', 1, '', '', 76, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (77, 73, '删除', '/system.Dictionary/del', 'system.Dictionary:del', 1, '', '', 77, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (78, 73, '状态', '/system.Dictionary/status', 'system.Dictionary:status', 2, '', '', 78, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (79, 54, '附件管理', '/system.Attachment/index', 'system.Attachment:index', 0, '', '', 79, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (80, 79, '查看', '/system.Attachment/index', 'system.Attachment:index', 1, '', '', 80, '', 0, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (81, 79, '编辑', '/system.Attachment/edit', 'system.Attachment:edit', 1, '', '', 81, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (82, 79, '删除', '/system.Attachment/del', 'system.Attachment:del', 1, '', '', 82, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (83, 79, '附件上传', '/Ajax/upload', 'Ajax:upload', 2, '', '', 83, '', 0, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (84, 79, '远程下载', '/Ajax/getImage', 'Ajax:getImage', 2, '', '', 84, '', 0, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (85, 54, '蜘蛛统计', '/system.Spider/index', 'system.Spider:index', 0, '', '', 85, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (86, 85, '查看统计', '/system.Spider/index', 'system.Spider:index', 1, '', '', 86, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (87, 85, '爬虫日志', '/system.Spider/getSpiderLog', 'system.Spider:getSpiderLog', 1, '', '', 87, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (88, 85, '爬虫详情', '/system.Spider/getSpiderDetail', 'system.Spider:getSpiderDetail', 2, '', '', 88, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);
INSERT INTO `__PREFIX__admin_rules` VALUES (89, 85, '小时统计', '/system.Spider/hours', 'system.Spider:hours', 2, '', '', 89, '', 1, 'normal', 0, 1688481046, 1688481046, NULL);

-- ----------------------------
-- Table structure for __PREFIX__attachment
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__attachment`;
CREATE TABLE `__PREFIX__attachment`  (
  `id` int(20) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '类别',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '物理路径',
  `extension` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件后缀',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件名称',
  `filesize` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
  `mimetype` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT 'mime类型',
  `sha1` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' COMMENT '文件 sha1编码',
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员ID',
  `admin_id` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理员ID',
  `update_time` int(11) NULL DEFAULT NULL COMMENT '更新时间',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建日期',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '附件表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__attachment
-- ----------------------------

-- ----------------------------
-- Table structure for __PREFIX__company
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__company`;
CREATE TABLE `__PREFIX__company`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公司名称',
  `alias` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公司标识',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '公司地址',
  `postcode` int(11) NULL DEFAULT NULL COMMENT '邮编',
  `contact` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系人',
  `mobile` bigint(20) NULL DEFAULT NULL COMMENT '手机号',
  `phone` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '联系电话',
  `email` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `blicense` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '营业执照代码',
  `longitude` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '地图经度',
  `latitude` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '地图纬度',
  `create_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '公司信息表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__company
-- ----------------------------
INSERT INTO `__PREFIX__company` VALUES (1, '北京总部技术公司', 'bj', '北京市东城区长安街880号', 10000, '权栈', 15100000001, '010-10000', 'coolsec@foxmail.com', '91130403XXA0AJ7XXM', '01', '02', 1613711884);
INSERT INTO `__PREFIX__company` VALUES (2, '河北分公司', 'hb', '河北省邯郸市丛台区公园路880号', 56000, '权栈', 12345678901, '0310-12345678', 'coolsec@foxmail.com', 'code', '', '', 1613787702);

-- ----------------------------
-- Table structure for __PREFIX__config
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__config`;
CREATE TABLE `__PREFIX__config`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '字段',
  `system` int(1) UNSIGNED NULL DEFAULT 0 COMMENT '系统',
  `group` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '配置组',
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '字段类型',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字段值',
  `tips` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '提示信息',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`(191)) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 91 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统配置表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__config
-- ----------------------------
INSERT INTO `__PREFIX__config` VALUES (1, 'site_name', 1, 'site', 'string', 'SwiftAdmin', '网站名称');
INSERT INTO `__PREFIX__config` VALUES (2, 'site_url', 1, 'site', 'string', 'www.swiftadmin.net', '网站URL');
INSERT INTO `__PREFIX__config` VALUES (3, 'site_logo', 1, 'site', 'string', '/static/images/logo.png', '网站logo');
INSERT INTO `__PREFIX__config` VALUES (4, 'site_http', 1, 'site', 'string', 'https://www.swiftadmin.net', 'HTTP地址');
INSERT INTO `__PREFIX__config` VALUES (5, 'site_state', 1, 'site', 'string', '0', '是否开启手机版');
INSERT INTO `__PREFIX__config` VALUES (6, 'site_type', 1, 'site', 'string', '0', '手机版类型');
INSERT INTO `__PREFIX__config` VALUES (7, 'site_mobile', 1, 'site', 'string', 'https://m.swiftadmin.net', '手机版地址');
INSERT INTO `__PREFIX__config` VALUES (8, 'site_icp', 1, 'site', 'string', '冀ICP备2021014458号', '备案号');
INSERT INTO `__PREFIX__config` VALUES (9, 'site_email', 1, 'site', 'string', 'coolsec#foxmail.com', '站长邮箱');
INSERT INTO `__PREFIX__config` VALUES (10, 'site_keyword', 1, 'site', 'string', 'swiftadmin,layui后台,PHP框架', '网站关键字');
INSERT INTO `__PREFIX__config` VALUES (11, 'site_description', 1, 'site', 'string', 'swiftadmin是一款基于Webman和layui的极速后台开发框架, 你可以使用swiftadmin快速开发你的后台管理、会员中心、API接口、移动应用等等功能!', '网站描述');
INSERT INTO `__PREFIX__config` VALUES (12, 'site_total', 1, 'site', 'string', '', '统计代码');
INSERT INTO `__PREFIX__config` VALUES (13, 'site_copyright', 1, 'site', 'string', '版权信息：', '版权信息');
INSERT INTO `__PREFIX__config` VALUES (15, 'site_status', 1, 'site', 'string', '0', '运营状态');
INSERT INTO `__PREFIX__config` VALUES (16, 'site_notice', 1, 'site', 'string', '本站内容正在审核中', '关闭通知');
INSERT INTO `__PREFIX__config` VALUES (17, 'auth_key', 0, NULL, 'string', '38nfCIlkqNMI2', '授权码');
INSERT INTO `__PREFIX__config` VALUES (18, 'auth_code', 0, NULL, 'string', 'wMRkfKO4Lr37HTJQ', '加密KEY');
INSERT INTO `__PREFIX__config` VALUES (19, 'system_alogs', 0, NULL, 'string', '0', '后台日志');
INSERT INTO `__PREFIX__config` VALUES (20, 'system_exception', 0, NULL, 'string', '1', '异常日志');
INSERT INTO `__PREFIX__config` VALUES (21, 'cache_status', 0, 'cache', 'string', '1', '缓存状态');
INSERT INTO `__PREFIX__config` VALUES (22, 'cache_type', 0, 'cache', 'string', 'file', '缓存类型');
INSERT INTO `__PREFIX__config` VALUES (23, 'cache_time', 0, 'cache', 'string', '6000', '缓存时间');
INSERT INTO `__PREFIX__config` VALUES (24, 'cache_host', 0, 'cache', 'string', '127.0.0.1', '服务器IP');
INSERT INTO `__PREFIX__config` VALUES (25, 'cache_port', 0, 'cache', 'string', '6379', '端口');
INSERT INTO `__PREFIX__config` VALUES (26, 'cache_select', 0, 'cache', 'string', '6', '缓存数据库');
INSERT INTO `__PREFIX__config` VALUES (27, 'cache_user', 0, 'cache', 'string', '', '用户名');
INSERT INTO `__PREFIX__config` VALUES (28, 'cache_pass', 0, 'cache', 'string', '', '密码');
INSERT INTO `__PREFIX__config` VALUES (29, 'upload_path', 0, 'upload', 'string', 'upload', '上传路径');
INSERT INTO `__PREFIX__config` VALUES (30, 'upload_style', 0, 'upload', 'string', 'Y-m-d', '文件夹格式');
INSERT INTO `__PREFIX__config` VALUES (31, 'upload_class', 0, 'upload', 'array', '{\"images\":\".bmp.jpg.jpeg.png.gif.svg\",\"video\":\".flv.swf.mkv.avi.rm.rmvb.mpeg.mpg.ogg.ogv.mov.wmv.mp4.webm.mp3.wav.mid\",\"document\":\".txt.doc.xls.ppt.docx.xlsx.pptx\",\"files\":\".exe.dll.sys.so.dmg.iso.zip.rar.7z.sql.pem.pdf.psd.blob\"}', '文件分类');
INSERT INTO `__PREFIX__config` VALUES (32, 'upload_ftp', 0, 'upload', 'string', '0', 'FTP上传');
INSERT INTO `__PREFIX__config` VALUES (33, 'upload_del', 0, 'upload', 'string', '0', '上传后删除');
INSERT INTO `__PREFIX__config` VALUES (34, 'upload_ftp_host', 0, 'upload', 'string', '127.0.0.1', 'FTP服务器');
INSERT INTO `__PREFIX__config` VALUES (35, 'upload_ftp_port', 0, 'upload', 'string', '21', 'FTP端口');
INSERT INTO `__PREFIX__config` VALUES (36, 'upload_ftp_user', 0, 'upload', 'string', '', 'FTP用户名');
INSERT INTO `__PREFIX__config` VALUES (37, 'upload_ftp_pass', 0, 'upload', 'string', '', 'FTP密码');
INSERT INTO `__PREFIX__config` VALUES (38, 'upload_http_prefix', 0, 'upload', 'string', '', '图片CDN地址');
INSERT INTO `__PREFIX__config` VALUES (39, 'upload_chunk_size', 0, 'upload', 'string', '2097152', '文件分片大小 字节');
INSERT INTO `__PREFIX__config` VALUES (40, 'upload_thumb', 0, 'upload', 'string', '0', '是否开启缩略图');
INSERT INTO `__PREFIX__config` VALUES (41, 'upload_thumb_w', 0, 'upload', 'string', '120', '宽度');
INSERT INTO `__PREFIX__config` VALUES (42, 'upload_thumb_h', 0, 'upload', 'string', '140', '高度');
INSERT INTO `__PREFIX__config` VALUES (43, 'upload_water', 0, 'upload', 'string', '0', '是否水印');
INSERT INTO `__PREFIX__config` VALUES (44, 'upload_water_type', 0, 'upload', 'string', '1', '水印类型');
INSERT INTO `__PREFIX__config` VALUES (45, 'upload_water_font', 0, 'upload', 'string', 'www.swiftadmin.net', '水印文字');
INSERT INTO `__PREFIX__config` VALUES (46, 'upload_water_size', 0, 'upload', 'string', '20', '字体大小');
INSERT INTO `__PREFIX__config` VALUES (47, 'upload_water_color', 0, 'upload', 'string', '#0fbeea', '字体颜色');
INSERT INTO `__PREFIX__config` VALUES (48, 'upload_water_pct', 0, 'upload', 'string', '47', '透明度');
INSERT INTO `__PREFIX__config` VALUES (49, 'upload_water_img', 0, 'upload', 'string', '/', '图片水印地址');
INSERT INTO `__PREFIX__config` VALUES (50, 'upload_water_pos', 0, 'upload', 'string', '9', '水印位置');
INSERT INTO `__PREFIX__config` VALUES (56, 'email', 0, NULL, 'array', '{\"smtp_debug\":\"0\",\"smtp_host\":\"smtp.qq.com\",\"smtp_port\":\"465\",\"smtp_name\":\"\",\"smtp_user\":\"\",\"smtp_pass\":\"\",\"smtp_test\":\"\"}', '邮箱配置');
INSERT INTO `__PREFIX__config` VALUES (84, 'sitemap', 0, NULL, 'array', '', '地图配置');
INSERT INTO `__PREFIX__config` VALUES (85, 'rewrite', 0, NULL, 'string', '', 'URL配置');
INSERT INTO `__PREFIX__config` VALUES (86, 'database', 0, NULL, 'string', '', '数据库维护');
INSERT INTO `__PREFIX__config` VALUES (87, 'variable', 0, NULL, 'array', '{\"test\":\"我是值2\",\"ceshi\":\"我是测试变量的值\"}', '自定义变量');
INSERT INTO `__PREFIX__config` VALUES (88, 'param', 0, NULL, 'string', '', '测试代码');
INSERT INTO `__PREFIX__config` VALUES (90, 'editor', 0, NULL, 'string', 'lay-editor', '编辑器选项');

-- ----------------------------
-- Table structure for __PREFIX__department
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__department`;
CREATE TABLE `__PREFIX__department`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(11) NULL DEFAULT 0 COMMENT '上级ID',
  `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '部门名称',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门区域',
  `head` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '负责人',
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '手机号',
  `email` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '邮箱',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '部门简介',
  `sort` tinyint(4) NULL DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `create_time` int(11) NULL DEFAULT NULL,
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '部门管理表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__department
-- ----------------------------
INSERT INTO `__PREFIX__department` VALUES (1, 0, '北京总部', '北京市昌平区体育馆南300米', '秦老板', '1510000001', 'coolsec@foxmail.com', '总部，主要负责广告的营销，策划！', 1, 1, 1611213045, NULL);
INSERT INTO `__PREFIX__department` VALUES (2, 1, '河北分公司', '河北省邯郸市丛台区政府路', '刘备', '15100020003', 'liubei@qq.com', '', 2, 1, 1611227478, NULL);
INSERT INTO `__PREFIX__department` VALUES (3, 2, '市场部', '一楼', '大乔', '15100010003', 'xiaoqiao@foxmail.com', '', 3, 1, 1611228586, NULL);
INSERT INTO `__PREFIX__department` VALUES (4, 2, '开发部', '二楼2', '赵云', '15100010003', 'zhaoyun@shijiazhuang.com', '', 4, 1, 1611228626, NULL);
INSERT INTO `__PREFIX__department` VALUES (5, 2, '营销部', '二楼', '许攸', '15100010003', 'xuyou@henan.com', '', 5, 1, 1611228674, NULL);

-- ----------------------------
-- Table structure for __PREFIX__dictionary
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__dictionary`;
CREATE TABLE `__PREFIX__dictionary`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `pid` int(10) UNSIGNED NULL DEFAULT 0 COMMENT '字典分类id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字典名称',
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '字典值',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序号',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '备注信息',
  `isSystem` tinyint(3) UNSIGNED NULL DEFAULT NULL COMMENT '系统级,只可手动操作',
  `update_time` int(11) NULL DEFAULT NULL,
  `create_time` int(11) NULL DEFAULT NULL,
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `id`(`id`) USING BTREE,
  INDEX `pid`(`pid`) USING BTREE,
  INDEX `name`(`name`) USING BTREE,
  INDEX `value`(`value`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 66 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '字典数据表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__dictionary
-- ----------------------------
INSERT INTO `__PREFIX__dictionary` VALUES (1, 0, '公司类型', 'company', 1, '', 1, 1700304504, 1637150114, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (2, 0, '员工性别', 'sex', 2, '', 1, 1700304507, 1637150210, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (3, 0, '友情链接', 'friendlink', 3, '', 1, 1637150220, 1637150220, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (4, 1, '初创公司', 'small', 4, '', 1, 1637150234, 1637150234, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (5, 1, '中型公司', 'ccp', 5, '', 1, 1637150256, 1637150256, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (6, 1, '大型公司', 'bigc', 6, '', 1, 1700229972, 1637150270, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (7, 2, '男', '1', 7, '', 1, 1637150280, 1637150280, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (8, 2, '女', '0', 8, '5', 1, 1700304511, 1637150286, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (9, 3, '资源', '1', 9, '', 1, 1637150336, 1637150336, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (10, 3, '社区', '2', 10, '', 1, 1637150340, 1637150340, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (11, 3, '帮助', '3', 11, '', 1, 1637150346, 1637150346, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (12, 3, '合作伙伴', '4', 4, '', 1, 1638350927, 1637150352, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (59, 0, '文章属性', 'cattr', 59, '', NULL, 1669539753, 1669539753, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (60, 59, '头条', '1', 60, '', NULL, 1669540003, 1669540003, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (61, 59, '推荐', '2', 61, '', NULL, 1669540010, 1669540010, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (62, 59, '幻灯', '3', 62, '', NULL, 1669540021, 1669540021, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (63, 59, '滚动', '4', 63, '', NULL, 1669540026, 1669540026, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (64, 59, '图文', '5', 64, '', NULL, 1669540034, 1669540034, NULL);
INSERT INTO `__PREFIX__dictionary` VALUES (65, 59, '跳转', '6', 65, '', NULL, 1669540039, 1669540039, NULL);

-- ----------------------------
-- Table structure for __PREFIX__jobs
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__jobs`;
CREATE TABLE `__PREFIX__jobs`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位名称',
  `alias` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '岗位标识',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '岗位描述',
  `sort` int(11) NULL DEFAULT NULL COMMENT '排序',
  `status` tinyint(1) NULL DEFAULT NULL COMMENT '岗位状态',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '软删除标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '岗位管理' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__jobs
-- ----------------------------
INSERT INTO `__PREFIX__jobs` VALUES (1, '董事长', 'ceo', '日常划水~', 1, 1, 1611234206, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (2, '人力资源', 'hr', '招聘人员，员工考核，绩效奖励！', 2, 1, 1611234288, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (3, '首席技术岗', 'cto', '主要职责是设计公司的未来，其更多的工作应该是前瞻性的，也就是制定下一代产品的策略和进行研究工作，属于技术战略的重要执行者。CTO还是高级市场人员，他可以从技术角度非常有效地帮助公司推广理念，其中包括公司对技术趋势所持的看法。因此，在大型用户会议上CTO会阐述产品下一代的走向和功能，这也是重要的市场策略。', 3, 1, 1611274959, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (4, '首席运营官', 'coo', '又常称为运营官或营运总监)是公司团体里负责监督管理每日活动的高阶官员。COO是企业组织中最高层的成员之一，监测每日的公司运作，并直接报告给首席执行官。在某些公司中COO会同时兼任总裁，但通常COO还是以兼任常务或资深副总裁的情况居多。', 4, 1, 1611274981, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (5, '首席财务官', 'cof', '企业治理结构发展到一个新阶段的必然产物。没有首席财务官的治理结构不是现代意义上完善的治理结构。从这一层面上看，中国构造治理结构也应设立CFO之类的职位。当然，从本质上讲，CFO在现代治理结构中的真正含义，不是其名称的改变、官位的授予，而是其职责权限的取得，在管理中作用的真正发挥。', 5, 1, 1611275010, NULL);
INSERT INTO `__PREFIX__jobs` VALUES (6, '普通员工', 'pop', '一线员工', 6, 1, 1611275128, NULL);

-- ----------------------------
-- Table structure for __PREFIX__system_log
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__system_log`;
CREATE TABLE `__PREFIX__system_log`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '用户名/或系统',
  `module` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '模块名',
  `controller` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '控制器',
  `action` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '方法名',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '访问地址',
  `file` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '错误文件地址',
  `line` int(11) NULL DEFAULT NULL COMMENT '错误代码行号',
  `error` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '异常消息',
  `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '请求参数',
  `ip` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'IP地址',
  `method` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '访问方式',
  `type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '日志类型',
  `status` int(11) NULL DEFAULT 1 COMMENT '执行状态',
  `create_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '系统日志表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of __PREFIX__system_log
-- ----------------------------

-- ----------------------------
-- Table structure for __PREFIX__crontab
-- ----------------------------
DROP TABLE IF EXISTS `__PREFIX__crontab`;
CREATE TABLE `__PREFIX__crontab`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '定时任务名称',
  `type` tinyint(1) NOT NULL COMMENT '类型',
  `system` tinyint(4) NULL DEFAULT 0 COMMENT '是否系统任务',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '备注',
  `command` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '命令内容',
  `params` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '参数',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态',
  `expression` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '运行规则',
  `error` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '运行失败原因',
  `last_time` int(11) NULL DEFAULT NULL COMMENT '最后执行时间',
  `time` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '实时执行时长',
  `max_time` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '0' COMMENT '最大执行时长',
  `create_time` int(10) NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` int(10) NULL DEFAULT NULL COMMENT '更新时间',
  `delete_time` int(10) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '计划任务表';

-- ----------------------------
-- Records of __PREFIX__crontab
-- ----------------------------


SET FOREIGN_KEY_CHECKS = 1;
