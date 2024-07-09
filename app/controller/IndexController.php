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
