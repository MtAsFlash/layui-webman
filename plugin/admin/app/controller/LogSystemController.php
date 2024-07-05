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

use support\exception\BusinessException;
use support\Request;
use support\Response;
use plugin\admin\app\model\Admin;
use plugin\admin\app\model\LogSystem;

class LogSystemController extends CrudController
{

    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['add', 'edit', 'delete'];

    /**
     * @var LogSystem
     */
    protected $model = null;

    /**
     * 开启auth数据限制
     * @var string
     */
    protected $dataLimit = 'auth';

    /**
     * 以id为数据限制字段
     * @var string
     */
    protected $dataLimitField = 'admin_id';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new LogSystem;
    }

    /**
     * 首页
     * @return Response
     */
    public function index(): Response
    {
        return view('log_system/index');
    }

    /**
     * 查询
     *
     * @param Request $request
     *
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order);

        if ($format === 'select') {
            return $this->formatSelect($query->select());
        }

        return $this->doFormat($query, $format, $limit);
    }

    protected function afterQuery($items)
    {
        $adminList = (new Admin)->getAdminList('id,username');
        $adminList = array_column($adminList, null, 'id');
        foreach ($items as $key => $v) {
            $items[$key]['admin_name'] = $adminList[$v['admin_id']]['username'] ?? '';
        }

        return $items;
    }
}
