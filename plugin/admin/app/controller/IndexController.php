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
namespace plugin\admin\app\controller;

use support\Request;
use think\facade\Db;

class IndexController extends CrudController
{

    /**
     * 无需登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['index'];

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['dashboard'];

    public function index()
    {
        if ( ! is_file(base_path().'/.env')) {
            return view('install/index');
        }

        clearstatcache();
        $admin = admin();
        if ( ! $admin) {
            $onetime_password = get_config('onetime_password') ?? 0;

            return view('account/login', ['onetime_password' => $onetime_password]);
        }

        $version = config('plugin.admin.app.version', '');

        return view('index/index', ['version' => $version]);
    }

    /**
     * 仪表盘
     * @return void
     */
    public function dashboard()
    {
        return view('index/dashboard');
    }

}
