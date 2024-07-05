/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 50728
 Source Host           : 127.0.0.1:3306
 Source Schema         : 1043webman_huicmf_new

 Target Server Type    : MySQL
 Target Server Version : 50728
 File Encoding         : 65001

 Date: 22/04/2024 13:39:41
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cmf_admin
-- ----------------------------
DROP TABLE IF EXISTS `cmf_admin`;
CREATE TABLE `cmf_admin`
(
    `id`                  int(10) UNSIGNED                                              NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `username`            varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '用户名',
    `nickname`            varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '昵称',
    `password`            varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '密码',
    `salt`                varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '加密盐',
    `avatar`              varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '头像',
    `email`               varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '邮箱',
    `mobile`              varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '手机',
    `login_time`          int(10)                                                       NOT NULL DEFAULT 0 COMMENT '上次登录时间',
    `login_ip`            varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '上次登录ip',
    `status`              tinyint(4)                                                    NOT NULL DEFAULT 0 COMMENT '禁用',
    `google2fa_secretKey` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '身份验证器秘钥',
    `google2fa_timestamp` int(11)                                                       NOT NULL DEFAULT 0 COMMENT '身份验证器更新时间戳',
    `create_time`         int(10)                                                       NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time`         int(10)                                                       NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `username` (`username`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '管理员表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for cmf_admin_role
-- ----------------------------
DROP TABLE IF EXISTS `cmf_admin_role`;
CREATE TABLE `cmf_admin_role`
(
    `id`       int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
    `role_id`  int(11) NOT NULL DEFAULT 0 COMMENT '角色id',
    `admin_id` int(11) NOT NULL DEFAULT 0 COMMENT '管理员id',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `role_admin_id` (`role_id`, `admin_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 3
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '管理员角色表'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cmf_admin_role
-- ----------------------------
INSERT INTO `cmf_admin_role`
VALUES (1, 1, 1);
INSERT INTO `cmf_admin_role`
VALUES (2, 2, 2);

-- ----------------------------
-- Table structure for cmf_config
-- ----------------------------
DROP TABLE IF EXISTS `cmf_config`;
CREATE TABLE `cmf_config`
(
    `id`        int(10) UNSIGNED                                              NOT NULL AUTO_INCREMENT,
    `name`      varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '配置名称',
    `type`      tinyint(3) UNSIGNED                                           NOT NULL DEFAULT 0 COMMENT '配置类型',
    `title`     varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '配置说明',
    `value`     text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci         NULL COMMENT '配置值',
    `fieldtype` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '字段类型',
    `setting`   text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci         NULL COMMENT '字段设置',
    `status`    tinyint(3) UNSIGNED                                           NOT NULL DEFAULT 1 COMMENT '状态',
    `tips`      varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `name` (`name`) USING BTREE,
    INDEX `type` (`type`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 24
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '系统配置'
  ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of cmf_config
-- ----------------------------
INSERT INTO `cmf_config`
VALUES (1, 'site_name', 1, '站点名称', 'HuiCMF-webman后台权限管理系统V2', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (2, 'site_url', 1, '站点跟网址', 'http://127.0.0.1:8787', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (3, 'admin_log', 3, '启用后台管理操作日志', '0', 'radio', '', 1, '');
INSERT INTO `cmf_config`
VALUES (4, 'site_keyword', 1, '站点关键字', 'huicmf,webman', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (5, 'site_copyright', 1, '网站版权信息', 'Powered By HuiCMF-V2后台系统 © 2020-2024 小灰灰工作室', 'string', '',
        1, '');
INSERT INTO `cmf_config`
VALUES (6, 'site_beian', 1, '站点备案号', '豫ICP备666666号', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (7, 'site_description', 1, '站点描述', '', 'text', '', 1, '');
INSERT INTO `cmf_config`
VALUES (8, 'site_code', 1, '统计代码', '', 'text', '', 1, '');
INSERT INTO `cmf_config`
VALUES (9, 'admin_prohibit_ip', 2, '禁止访问网站的IP', '', 'text', '', 1, '');
INSERT INTO `cmf_config`
VALUES (10, 'site_editor', 2, '文本编辑器', 'wangEditor', 'string', ' ', 1, '');
INSERT INTO `cmf_config`
VALUES (11, 'watermark_enable', 2, '是否开启图片水印', '0', 'radio', '', 1, '');
INSERT INTO `cmf_config`
VALUES (12, 'watermark_name', 2, '水印图片名称', 'mark.png', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (13, 'watermark_position', 2, '水印的位置', 'bottom-right', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (14, 'watermark_touming', 2, '水印透明度', '80', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (15, 'upload_types_image', 2, '允许上传图片类型', 'jpg,jpeg,png,gif,bmp', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (16, 'upload_mode', 2, '图片上传方式', 'local', 'string', '', 1, '');
INSERT INTO `cmf_config`
VALUES (17, 'pic_more_nums', 2, '多图上传图片数量限制', '0', 'string', NULL, 1, '');
INSERT INTO `cmf_config`
VALUES (18, 'upload_types_file', 2, '允许上传附件类型', 'zip,pdf,doc,txt,json', 'string', ' ', 1, '');
INSERT INTO `cmf_config`
VALUES (19, 'onetime_password', 3, '动态口令认证', '0', 'radio', NULL, 1, '');

-- ----------------------------
-- Table structure for cmf_dict
-- ----------------------------
DROP TABLE IF EXISTS `cmf_dict`;
CREATE TABLE `cmf_dict`
(
    `id`          int(11)                                                      NOT NULL AUTO_INCREMENT,
    `name`        varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '字典名',
    `value`       text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci        NULL COMMENT '字典值',
    `create_time` int(10)                                                      NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` int(10)                                                      NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `name` (`name`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '字典管理'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cmf_dict
-- ----------------------------
INSERT INTO `cmf_dict`
VALUES (1, 'upload_group',
        '[{\"name\":\"分组1\",\"value\":\"1\"},{\"name\":\"分组2\",\"value\":\"2\"},{\"name\":\"分组3\",\"value\":\"3\"}]',
        1705310860, 1705397467);

-- ----------------------------
-- Table structure for cmf_log_login
-- ----------------------------
DROP TABLE IF EXISTS `cmf_log_login`;
CREATE TABLE `cmf_log_login`
(
    `id`               bigint(20)                                                    NOT NULL AUTO_INCREMENT,
    `admin_id`         bigint(20)                                                    NOT NULL DEFAULT 0 COMMENT '用户id',
    `admin_name`       varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '用户名',
    `ip_address`       varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'ip地址',
    `country`          char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci    NOT NULL DEFAULT '' COMMENT '国家',
    `province`         char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci    NOT NULL DEFAULT '' COMMENT '省',
    `city`             char(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci    NOT NULL DEFAULT '' COMMENT '市',
    `isp`              varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '网络：【电信、联通】',
    `status`           int(1)                                                        NOT NULL DEFAULT 0 COMMENT '登录状态：1=成功；0=失败',
    `desc`             varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
    `onetime_password` int(1)                                                        NOT NULL DEFAULT 0 COMMENT '是否开启动态口令登录',
    `create_time`      int(10)                                                       NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time`      int(10)                                                       NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `city` (`city`) USING BTREE,
    INDEX `area` (`province`) USING BTREE,
    INDEX `country` (`country`) USING BTREE,
    INDEX `ip_address` (`ip_address`) USING BTREE,
    INDEX `user` (`admin_id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '后台登录记录表'
  ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for cmf_log_system
-- ----------------------------
DROP TABLE IF EXISTS `cmf_log_system`;
CREATE TABLE `cmf_log_system`
(
    `id`          bigint(20) UNSIGNED                                            NOT NULL AUTO_INCREMENT COMMENT 'ID',
    `admin_id`    int(10) UNSIGNED                                               NULL     DEFAULT 0 COMMENT '管理员ID',
    `url`         varchar(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '操作页面',
    `method`      varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci   NOT NULL COMMENT '请求方法',
    `title`       varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NULL     DEFAULT '' COMMENT '日志标题',
    `content`     text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci          NOT NULL COMMENT '内容',
    `ip`          varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci   NOT NULL DEFAULT '' COMMENT 'IP',
    `create_time` int(10)                                                        NOT NULL DEFAULT 0 COMMENT '操作时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '后台操作日志表'
  ROW_FORMAT = COMPACT;


CREATE TABLE `cmf_plugin_options`
(
    `id`          int(11)      NOT NULL AUTO_INCREMENT,
    `name`        varchar(100) NOT NULL DEFAULT '',
    `value`       longtext,
    `create_time` int(10)      NOT NULL DEFAULT '0',
    `update_time` int(10)      NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4 COMMENT ='应用插件配置项表';

-- ----------------------------
-- Table structure for cmf_role
-- ----------------------------
DROP TABLE IF EXISTS `cmf_role`;
CREATE TABLE `cmf_role`
(
    `id`          int(10) UNSIGNED                                             NOT NULL AUTO_INCREMENT COMMENT '主键',
    `pid`         int(10) UNSIGNED                                             NOT NULL DEFAULT 0 COMMENT '父级',
    `name`        varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '角色组',
    `rules`       text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci        NULL COMMENT '权限',
    `status`      int(1)                                                       NOT NULL DEFAULT 0 COMMENT '状态',
    `create_time` int(10)                                                      NOT NULL DEFAULT 0 COMMENT '创建时间',
    `update_time` int(10)                                                      NOT NULL DEFAULT 0 COMMENT '更新时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 3
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '管理员角色'
  ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cmf_role
-- ----------------------------
INSERT INTO `cmf_role`
VALUES (1, 0, '超级管理员', '*', 1, 1703668109, 1703668109);
INSERT INTO `cmf_role`
VALUES (2, 1, '管理员', '1,2,6,7,3,9,10,4,13,14,17,19,23,20,27,31,32,34,35,36,37,38,39', 1, 1703668109, 1705894389);

-- ----------------------------
-- Table structure for cmf_rule
-- ----------------------------
CREATE TABLE `cmf_rule`
(
    `id`          int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
    `title`       varchar(255)     NOT NULL COMMENT '标题',
    `icon`        varchar(255)     NOT NULL DEFAULT '' COMMENT '图标',
    `key`         varchar(255)     NOT NULL COMMENT '标识',
    `pid`         int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级菜单',
    `href`        varchar(255)     NOT NULL DEFAULT '' COMMENT 'url',
    `type`        int(11)          NOT NULL DEFAULT '1' COMMENT '类型',
    `weight`      int(11)          NOT NULL DEFAULT '100' COMMENT '排序',
    `create_time` int(10)          NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10)          NOT NULL DEFAULT '0' COMMENT '更新时间',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 50
  DEFAULT CHARSET = utf8mb4 COMMENT ='权限规则';

-- ----------------------------
-- Records of cmf_rule
-- ----------------------------
INSERT INTO `cmf_rule`
VALUES (1, '权限管理', 'layui-icon-vercode', 'auth', 0, '', 0, 100, 1703668276, 1704249407);
INSERT INTO `cmf_rule`
VALUES (2, '账户管理', 'layui-icon-username', 'plugin\\admin\\app\\controller\\AdminController', 1,
        '/app/admin/admin/index', 1, 100, 1703668276, 1704244813);
INSERT INTO `cmf_rule`
VALUES (3, '角色管理', 'layui-icon-user', 'plugin\\admin\\app\\controller\\RoleController', 1, '/app/admin/role/index',
        1, 100, 1703668276, 1704159765);
INSERT INTO `cmf_rule`
VALUES (4, '菜单管理', 'layui-icon-menu-fill', 'plugin\\admin\\app\\controller\\RuleController', 1,
        '/app/admin/rule/index', 1, 100, 1703668276, 1704159790);
INSERT INTO `cmf_rule`
VALUES (6, '添加', '', 'plugin\\admin\\app\\controller\\AdminController@add', 2, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (7, '编辑', '', 'plugin\\admin\\app\\controller\\AdminController@edit', 2, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (8, '删除', '', 'plugin\\admin\\app\\controller\\AdminController@delete', 2, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (9, '查询', '', 'plugin\\admin\\app\\controller\\RoleController@select', 3, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (10, '添加', '', 'plugin\\admin\\app\\controller\\RoleController@add', 3, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (11, '编辑', '', 'plugin\\admin\\app\\controller\\RoleController@edit', 3, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (12, '删除', '', 'plugin\\admin\\app\\controller\\RoleController@delete', 3, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (13, '查询', '', 'plugin\\admin\\app\\controller\\RuleController@select', 4, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (14, '添加', '', 'plugin\\admin\\app\\controller\\RuleController@add', 4, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (15, '编辑', '', 'plugin\\admin\\app\\controller\\RuleController@edit', 4, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (16, '删除', '', 'plugin\\admin\\app\\controller\\RuleController@delete', 4, '', 2, 100, 1704189939, 1704189939);
INSERT INTO `cmf_rule`
VALUES (17, '通用设置', 'layui-icon-set', 'config', 0, '', 0, 999, 1704856568, 1716781019);
INSERT INTO `cmf_rule`
VALUES (18, '系统设置', 'layui-icon-set', 'plugin\\admin\\app\\controller\\ConfigController', 17,
        '/app/admin/config/index', 1, 100, 1704856634, 1704856634);
INSERT INTO `cmf_rule`
VALUES (19, '自定义项', 'layui-icon-set', 'plugin\\admin\\app\\controller\\CustomConfigController', 17,
        '/app/admin/customconfig/index', 1, 100, 1704856634, 1705053380);
INSERT INTO `cmf_rule`
VALUES (20, '字典管理', 'layui-icon-diamond', 'plugin\\admin\\app\\controller\\DictController', 17,
        '/app/admin/dict/index', 1, 100, 1704936969, 1704956368);
INSERT INTO `cmf_rule`
VALUES (21, '查询', '', 'plugin\\admin\\app\\controller\\ConfigController@select', 18, '', 2, 100, 1705024934,
        1705024934);
INSERT INTO `cmf_rule`
VALUES (22, '编辑', '', 'plugin\\admin\\app\\controller\\ConfigController@edit', 18, '', 2, 100, 1705024935,
        1705024935);
INSERT INTO `cmf_rule`
VALUES (23, '查询', '', 'plugin\\admin\\app\\controller\\CustomConfigController@select', 19, '', 2, 100, 1705024935,
        1705024935);
INSERT INTO `cmf_rule`
VALUES (24, '添加', '', 'plugin\\admin\\app\\controller\\CustomConfigController@add', 19, '', 2, 100, 1705024935,
        1705024935);
INSERT INTO `cmf_rule`
VALUES (25, '编辑', '', 'plugin\\admin\\app\\controller\\CustomConfigController@edit', 19, '', 2, 100, 1705024935,
        1705024935);
INSERT INTO `cmf_rule`
VALUES (26, '删除', '', 'plugin\\admin\\app\\controller\\CustomConfigController@delete', 19, '', 2, 100, 1705024935,
        1705024935);
INSERT INTO `cmf_rule`
VALUES (27, '查询', '', 'plugin\\admin\\app\\controller\\DictController@select', 20, '', 2, 100, 1705024935,
        1705024935);
INSERT INTO `cmf_rule`
VALUES (28, '添加', '', 'plugin\\admin\\app\\controller\\DictController@add', 20, '', 2, 100, 1705024935, 1705024935);
INSERT INTO `cmf_rule`
VALUES (29, '编辑', '', 'plugin\\admin\\app\\controller\\DictController@edit', 20, '', 2, 100, 1705024935, 1705024935);
INSERT INTO `cmf_rule`
VALUES (30, '删除', '', 'plugin\\admin\\app\\controller\\DictController@delete', 20, '', 2, 100, 1705024935,
        1705024935);
INSERT INTO `cmf_rule`
VALUES (31, '附件管理', 'layui-icon-picture', 'plugin\\admin\\app\\controller\\UploadFileController', 17,
        '/app/admin/uploadfile/index', 1, 100, 1705457352, 1705458264);
INSERT INTO `cmf_rule`
VALUES (32, '更新附件', '', 'plugin\\admin\\app\\controller\\UploadFileController@edit', 31, '', 2, 100, 1705458327,
        1705458327);
INSERT INTO `cmf_rule`
VALUES (33, '删除附件', '', 'plugin\\admin\\app\\controller\\UploadFileController@delete', 31, '', 2, 100, 1705458327,
        1705458327);
INSERT INTO `cmf_rule`
VALUES (34, '添加', '', 'plugin\\admin\\app\\controller\\UploadFileController@add', 31, '', 2, 100, 1705458327,
        1705458327);
INSERT INTO `cmf_rule`
VALUES (35, '日志管理', 'layui-icon-survey', 'log', 17, '', 0, 100, 1705888136, 1705888136);
INSERT INTO `cmf_rule`
VALUES (36, '登录日志', 'layui-icon-list', 'plugin\\admin\\app\\controller\\LogLoginController', 35,
        '/app/admin/loglogin/index', 1, 100, 1705888674, 1705888674);
INSERT INTO `cmf_rule`
VALUES (37, '查询', '', 'plugin\\admin\\app\\controller\\LogLoginController@select', 36, '', 2, 100, 1705888676,
        1705888676);
INSERT INTO `cmf_rule`
VALUES (38, '系统日志', 'layui-icon-list', 'plugin\\admin\\app\\controller\\LogSystemController', 35,
        '/app/admin/logsystem/index', 1, 100, 1705891913, 1705891913);
INSERT INTO `cmf_rule`
VALUES (39, '查询', '', 'plugin\\admin\\app\\controller\\LogSystemController@select', 38, '', 2, 100, 1705891914,
        1705891914);
INSERT INTO `cmf_rule`
VALUES (40, '插件管理', 'layui-icon-template-1', 'plugin', 0, '', 0, 1000, 1706836783, 1716781009);
INSERT INTO `cmf_rule`
VALUES (41, '应用插件', 'layui-icon-app', 'plugin\\admin\\app\\controller\\PluginController', 40,
        '/app/admin/plugin/index', 1, 100, 1706836842, 1706836842);
INSERT INTO `cmf_rule`
VALUES (42, '列表', '', 'plugin\\admin\\app\\controller\\PluginController@list', 41, '', 2, 100, 1706844354,
        1706844354);
INSERT INTO `cmf_rule`
VALUES (43, '安装', '', 'plugin\\admin\\app\\controller\\PluginController@install', 41, '', 2, 100, 1706844354,
        1706844354);
INSERT INTO `cmf_rule`
VALUES (44, 'get', '', 'plugin\\admin\\app\\controller\\ConfigController@get', 18, '', 2, 100, 1713345800, 1713345800);
INSERT INTO `cmf_rule`
VALUES (45, '会员管理', 'layui-icon-user', 'user', 0, '', 0, 100, 1716792279, 1716792279);
INSERT INTO `cmf_rule`
VALUES (46, '用户管理', 'layui-icon-group', 'plugin\\admin\\app\\controller\\UserController', 45,
        '/app/admin/user/index', 1, 100, 1716792320, 1716792320);
INSERT INTO `cmf_rule`
VALUES (47, '添加', '', 'plugin\\admin\\app\\controller\\UserController@add', 46, '', 2, 100, 1716792326, 1716792326);
INSERT INTO `cmf_rule`
VALUES (48, '编辑', '', 'plugin\\admin\\app\\controller\\UserController@edit', 46, '', 2, 100, 1716792326, 1716792326);
INSERT INTO `cmf_rule`
VALUES (49, '删除', '', 'plugin\\admin\\app\\controller\\UserController@delete', 46, '', 2, 100, 1716792326,
        1716792326);

-- ----------------------------
-- Table structure for cmf_upload_file
-- ----------------------------
DROP TABLE IF EXISTS `cmf_upload_file`;
CREATE TABLE `cmf_upload_file`
(
    `id`           int(11) UNSIGNED                                              NOT NULL AUTO_INCREMENT COMMENT '文件id',
    `storage`      varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '存储方式',
    `group_id`     int(11)                                                       NOT NULL DEFAULT 0 COMMENT '文件分组id',
    `type`         int(1)                                                        NOT NULL DEFAULT 1 COMMENT '类型：1=images；2=files;',
    `file_url`     varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '存储域名',
    `file_name`    varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '文件路径',
    `file_size`    int(11) UNSIGNED                                              NOT NULL DEFAULT 0 COMMENT '文件大小(字节)',
    `image_width`  int(11)                                                       NOT NULL DEFAULT 0 COMMENT '图片宽度',
    `image_height` int(11)                                                       NOT NULL DEFAULT 0 COMMENT '图片高度',
    `file_type`    varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '文件类型',
    `extension`    varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci  NOT NULL DEFAULT '' COMMENT '文件扩展名',
    `admin_id`     int(11)                                                       NOT NULL DEFAULT 0 COMMENT '管理员id',
    `create_time`  int(11) UNSIGNED                                              NOT NULL DEFAULT 0 COMMENT '创建时间',
    `delete_time`  int(10) UNSIGNED                                              NOT NULL DEFAULT 0 COMMENT '软删除',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_general_ci COMMENT = '附件表'
  ROW_FORMAT = Dynamic;


CREATE TABLE `cmf_user`
(
    `id`          int(11)      NOT NULL AUTO_INCREMENT,
    `username`    varchar(20)  NOT NULL DEFAULT '' COMMENT '用户名',
    `password`    varchar(50)  NOT NULL DEFAULT '' COMMENT '密码',
    `salt`        varchar(20)  NOT NULL DEFAULT '' COMMENT '加密盐',
    `nickname`    varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
    `mobile`      varchar(16)  NOT NULL DEFAULT '' COMMENT '手机号',
    `email`       varchar(128) NOT NULL DEFAULT '' COMMENT '邮箱',
    `sex`         tinyint(1)   NOT NULL DEFAULT '0' COMMENT '性别：1=男；2=女',
    `avatar`      varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
    `level`       tinyint(4)   NOT NULL DEFAULT '0' COMMENT '登记',
    `birthday`    date                  DEFAULT NULL COMMENT '生日',
    `score`       int(11)      NOT NULL DEFAULT '0' COMMENT '积分',
    `last_time`   int(10)      NOT NULL COMMENT '上次登录时间',
    `last_ip`     varchar(50)  NOT NULL DEFAULT '' COMMENT '上次登录IP',
    `status`      tinyint(4)   NOT NULL DEFAULT '0' COMMENT '状态：0=禁用',
    `join_time`   int(10)      NOT NULL DEFAULT '0' COMMENT '注册时间',
    `join_ip`     varchar(50)  NOT NULL DEFAULT '' COMMENT '注册IP',
    `token`       varchar(50)  NOT NULL DEFAULT '' COMMENT 'token',
    `wx_unionid`  varchar(200) NOT NULL DEFAULT '' COMMENT '微信unionid',
    `wx_openid`   varchar(200) NOT NULL DEFAULT '' COMMENT '微信openid',
    `role`        int(11)      NOT NULL DEFAULT '0' COMMENT '角色',
    `create_time` int(10)      NOT NULL DEFAULT '0' COMMENT '创建时间',
    `update_time` int(10)      NOT NULL DEFAULT '0' COMMENT '更新时间',
    `delete_time` int(10)      NOT NULL DEFAULT '0' COMMENT '删除时间',
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB
  AUTO_INCREMENT = 1
  DEFAULT CHARSET = utf8mb4 COMMENT ='会员表';
