CREATE TABLE `cmf_user` (
                            `id` int(11) NOT NULL AUTO_INCREMENT,
                            `username` varchar(20) NOT NULL DEFAULT '' COMMENT '用户名',
                            `password` varchar(50) NOT NULL DEFAULT '' COMMENT '密码',
                            `salt` varchar(20) NOT NULL DEFAULT '' COMMENT '加密盐',
                            `nickname` varchar(255) NOT NULL DEFAULT '' COMMENT '昵称',
                            `mobile` varchar(16) NOT NULL DEFAULT '' COMMENT '手机号',
                            `email` varchar(128) NOT NULL DEFAULT '' COMMENT '邮箱',
                            `sex` tinyint(1) NOT NULL DEFAULT '0' COMMENT '性别：1=男；2=女',
                            `avatar` varchar(255) NOT NULL DEFAULT '' COMMENT '头像',
                            `level` tinyint(4) NOT NULL DEFAULT '0' COMMENT '等级',
                            `birthday` date DEFAULT NULL COMMENT '生日',
                            `score` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
                            `last_time` int(10) NOT NULL COMMENT '上次登录时间',
                            `last_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '上次登录IP',
                            `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态：0=禁用',
                            `join_time` int(10) NOT NULL DEFAULT '0' COMMENT '注册时间',
                            `join_ip` varchar(50) NOT NULL DEFAULT '' COMMENT '注册IP',
                            `token` varchar(50) NOT NULL DEFAULT '' COMMENT 'token',
                            `wx_unionid` varchar(200) NOT NULL DEFAULT '' COMMENT '微信unionid',
                            `wx_openid` varchar(200) NOT NULL DEFAULT '' COMMENT '微信openid',
                            `role` int(11) NOT NULL DEFAULT '0' COMMENT '角色',
                            `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
                            `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
                            `delete_time` int(10) NOT NULL DEFAULT '0' COMMENT '删除时间',
                            PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COMMENT='会员表';

UPDATE `cmf_rule` SET `weight` = 999 WHERE `key` = 'config';
UPDATE `cmf_rule` SET `weight` = 1000 WHERE `key` = 'plugin';
