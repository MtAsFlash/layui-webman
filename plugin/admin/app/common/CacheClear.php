<?php
//  +----------------------------------------------------------------------
//  | huicmf [ huicmf快速开发框架 ]
//  +----------------------------------------------------------------------
//  | Copyright (c) 2022~2024 https://xiaohuihui.cc All rights reserved.
//  +----------------------------------------------------------------------
//  | Author: 小灰灰 <762229008@qq.com>
//  +----------------------------------------------------------------------
//  | Info:  清除对应缓存
//  +----------------------------------------------------------------------
//

namespace plugin\admin\app\common;

use Shopwwi\LaravelCache\Cache;

class CacheClear
{

    /**
     * 清除角色列表缓存
     * @return void
     */
    public static function cacheRuleLists()
    {
        Cache::forget('cacheRuleLists');
    }

    /**
     * 清除系统配置缓存
     * @return void
     */
    public static function cacheSystemConfig()
    {
        Cache::forget('cacheSystemConfig');
    }

    /**
     * 清除字典字典缓存
     */
    public static function cacheDictName($name = '')
    {
        Cache::forget('cacheDict_'.$name);
    }

}
