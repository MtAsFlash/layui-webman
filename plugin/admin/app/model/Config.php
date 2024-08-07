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

namespace plugin\admin\app\model;

class Config extends Base
{

    const FIELDTYPE = [
        'text'     => '单行文本',
        'textarea' => '多行文本',
        'image'    => '单图上传',
        'images'   => '多图上传',
        'radio'    => 'radio选项卡',
        'select'   => 'select下拉框',
        'date'     => '日期',
        'datetime' => '日期时间',
        'editor'   => '富文本编辑器',
    ];
}
