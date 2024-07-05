<?php
//  +----------------------------------------------------------------------
//  | huicmf [ huicmf快速开发框架 ]
//  +----------------------------------------------------------------------
//  | Copyright (c) 2022~2024 https://xiaohuihui.cc All rights reserved.
//  +----------------------------------------------------------------------
//  | Author: 小灰灰 <762229008@qq.com>
//  +----------------------------------------------------------------------
//  | Info:
//  +----------------------------------------------------------------------
//

namespace plugin\admin\app\validate;

use Tinywan\Validate\Validate;

class UserValidate extends Validate
{

    protected array $rule = [
        'id|ID'           => 'require',
        'username|登录名' => 'require|length:4,12|alphaNum',
        'nickname|昵称'   => 'require',
        'password|密码'   => 'require|length:6,15',
    ];

    protected array $message = [
        'id.require'       => 'ID不能为空',
        'username.require' => '登录名不能为空',
        'username.length'  => '登录名长度必须4-12之间',
        'username.alpha'   => '登录名只能为字母',
        'nickname.require' => '昵称不能为空',
        'password.require' => '密码不能为空',
        'password.length'  => '密码长度必须在6-15之间',
    ];

    protected array $scene = [
        'add'  => ['username', 'nickname', 'password'],
        'edit' => ['id', 'username', 'nickname'],
    ];
}
