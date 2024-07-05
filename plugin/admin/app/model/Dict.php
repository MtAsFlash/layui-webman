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

use Shopwwi\LaravelCache\Cache;

class Dict extends Base
{

    /**
     * 根据名称获取 value值
     *
     * @param $name
     *
     * @return void
     */
    public function getNameValue($name)
    {
        //查询缓存：
        if (Cache::has('cacheDict_'.$name)) {
            $value = Cache::get('cacheDict_'.$name);
        } else {
            $value = Dict::where('name', $name)->value('value');
            $value = json_decode($value, true) ?? [];
            Cache::put('cacheDict_'.$name, $value);
        }

        return $value;
    }
}
