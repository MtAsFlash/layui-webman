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

class Admin extends Base
{

    /**
     * 获取管理员列表数据
     * @return mixed
     */
    public function getAdminList($field = '')
    {
        if (empty($field)) {
            $field = "id,username,nickname,email,mobile";
        }

        return Admin::field($field)->select()->toArray();
    }

    //更新管理员身份验证器信息绑定
    public function gogole2faUpdate($adminId, $secretKey)
    {
        return Admin::where('id', $adminId)->data([
            'google2fa_secretKey' => $secretKey,
            'update_time'         => time()
        ])->update();
    }

}
