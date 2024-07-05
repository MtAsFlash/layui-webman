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

class LogLogin extends Base
{

    /*
     * 写入记录
     */
    public static function addRecord($loginDevice)
    {
        if (empty($loginDevice)) {
            return false;
        }

        $update                     = [];
        $update['admin_id']         = $loginDevice['admin_id'] ?? 0;
        $update['admin_name']       = $loginDevice['admin_name'] ?? '';
        $update['status']           = $loginDevice['status'] ?? 0;
        $update['ip_address']       = $loginDevice['ip_address'] ?? '';
        $update['country']          = $loginDevice['country'] ?? '';
        $update['province']         = $loginDevice['province'] ?? '';
        $update['city']             = $loginDevice['city'] ?? '';
        $update['isp']              = $loginDevice['isp'] ?? '';
        $update['desc']             = $loginDevice['desc'] ?? '';
        $update['onetime_password'] = $loginDevice['onetime_password'] ?? 0;

        self::create($update);

        return true;
    }

    /**
     * 获取管理员上次登录信息
     *
     * @param $adminid
     *
     * @return void
     */
    public static function getLastLoginInfo($adminid, $onetimePassword = 0)
    {
        $data = self::where('admin_id', $adminid)->where(function ($query) use ($onetimePassword) {
            if ( ! empty($onetimePassword)) {
                $query->where('onetime_password', $onetimePassword);
            }
        })->where('status', 1)->order('id desc')->find();
        if (empty($data)) {
            return [];
        }
        $data = is_object($data) ? $data->toArray() : $data;

        return $data;
    }

}
