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

class DictValidate extends Validate
{

    protected array $rule = [
        'name'  => 'require|alphaDash',
        'value' => 'require',
    ];

    protected array $message = [
        'name.require'   => '字典名不能为空',
        'name.alphaDash' => '字典名只能是字母数字下划线_及破折号-',
        'value.require'  => '字典值不能为空',
    ];

}
