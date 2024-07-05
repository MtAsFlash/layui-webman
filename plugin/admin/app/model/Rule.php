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

class Rule extends Base
{

    /**
     * 获取权限规则列表
     *
     * @param $where    查询条件
     * @param $field    查询的字段
     *
     * @return void
     */
    public function getRuleLists($where = [], $field = "*")
    {
        $list = Rule::field($field)->where(function ($query) use ($where) {
            if ( ! empty($where)) {
                $query->where($where);
            }
        })->select()->toArray();

        return $list;
    }
}
