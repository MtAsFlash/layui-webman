ALTER TABLE `cmf_log_login`
    ADD COLUMN `onetime_password` int(1) NOT NULL DEFAULT 0 COMMENT '是否开启动态口令登录' AFTER `desc`;

ALTER TABLE `cmf_admin`
    ADD COLUMN `google2fa_secretKey` varchar(60) NOT NULL DEFAULT '' COMMENT '身份验证器秘钥';

ALTER TABLE `cmf_admin`
    ADD COLUMN `google2fa_timestamp` int(11) NOT NULL DEFAULT 0 COMMENT '身份验证器更新时间戳';

INSERT INTO `cmf_config` (`name`, `type`, `title`, `value`, `fieldtype`, `setting`, `status`, `tips`)
VALUES ('onetime_password', 3, '动态口令认证', '1', 'radio', NULL, 1, '');

