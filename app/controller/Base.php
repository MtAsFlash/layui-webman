<?php

namespace app\controller;

use support\Model;
use support\Response;

class Base {

    /**
     * @var Model
     */
    protected $model = null;

    /**
     * 无需登录及鉴权的方法
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 需要登录无需鉴权的方法
     * @var array
     */
    protected $noNeedAuth = [];

    /**
     * 数据限制
     * 例如当$dataLimit='personal'时将只返回当前管理员的数据
     * @var string
     */
    protected $dataLimit = null;

    /**
     * 数据限制字段
     */
    protected $dataLimitField = 'admin_id';

    protected function success(string $msg = '成功', array $data = [], int $code = 200): Response {
        return $this->json($code, $msg, $data);
    }

    /**
     * 返回格式化json数据
     *
     * @param int $code
     * @param string $msg
     * @param array $data
     *
     * @return Response
     */
    protected function json(int $code, string $msg = 'ok', array $data = []): Response {
        return json(['code' => $code, 'data' => $data, 'msg' => $msg]);
    }

    protected function error(string $msg = '失败', array $data = [], int $code = 0): Response {
        return $this->json($code, $msg, $data);
    }

}
