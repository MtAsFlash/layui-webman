<?php

namespace app\controller;

class IndexController extends CrudController {

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

    public function index() {
        $version = config('app.version', '');
        //TODO:liangtianfeng,因为这里要跳转到index,而当未登录时index页会跳转到登录,登录又跳转到xxx,导致死循环
        return view('index/index', ['version' => $version]);
    }

    /**
     * 仪表盘
     * @return void
     */
    public function dashboard() {
        return view('index/dashboard');
    }

}
