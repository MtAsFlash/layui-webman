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
use plugin\admin\app\model\User;
use support\Request;
use support\Response;
use plugin\admin\app\validate\UserValidate;

class UserController extends CrudController
{

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new User;
    }

    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $noNeedLogin = [];

    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['select'];

    /**
     * @var User
     */
    protected $model = null;

    /**
     * 用户管理
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        return view('user/index');
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

    /**
     * 添加
     *
     * @param Request $request
     *
     * @return Response
     */
    public function add(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('user/add');
        }
        $validate = new UserValidate();
        if ( ! $validate->scene('add')->check($request->post())) {
            return $this->error($validate->getError());
        }
        $data = $this->insertInput($request);

        //判断会员名是否存在：
        $findUser = User::where(['username' => $data['username']])->count();
        if ($findUser > 0) {
            return $this->error(trans('The user already exists', [], 'admin'));
        }
        $user_id = $this->doInsert($data);

        return $this->success(trans('Operation successful'), ['id' => $user_id]);
    }

    /**
     * 编辑
     *
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('user/edit');
        }
        $validate = new UserValidate();
        if ( ! $validate->scene('edit')->check($request->post())) {
            return $this->error($validate->getError());
        }
        [$id, $data] = $this->updateInput($request);

        $this->doUpdate($id, $data);

        return $this->success(trans('Operation successful'));
    }

    /**
     * 删除
     *
     * @param Request $request
     *
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $id = (array)$request->post('id');
        User::whereIn('id', $id)->delete();

        return $this->success(trans('Operation successful'));
    }

    /**
     * 前置方法
     * @return void
     */
    protected function selectInput(Request $request): array
    {
        [$where, $format, $limit, $field, $order] = parent::selectInput($request);
        // 默认weight排序
        if ( ! $field) {
            $field = 'id';
            $order = 'desc';
        }
        if ( ! empty($where['username'])) {
            $where['username'] = ['like', "%{$where['username']}%"];
        }
        if ( ! empty($where['nickname'])) {
            $where['nickname'] = ['like', "%{$where['nickname']}%"];
        }
        if ( ! empty($where['mobile'])) {
            $where['mobile'] = ['like', "%{$where['mobile']}%"];
        }
        if ( ! empty($where['email'])) {
            $where['email'] = ['like', "%{$where['email']}%"];
        }

        return [$where, $format, $limit, $field, $order];
    }

    protected function afterQuery($items)
    {
        foreach ($items as $key => $v) {
            $items[$key]['last_time'] = ! empty($v['last_time']) ? date('Y-m-d H:i:s', $v['last_time']) : '';
            $items[$key]['join_time'] = ! empty($v['join_time']) ? date('Y-m-d H:i:s', $v['join_time']) : '';
            $items[$key]['birthday']  = empty($v['birthday']) || $v['birthday'] == '0000-00-00' ? "" : $v['birthday'];
            unset($items[$key]['password']);
            unset($items[$key]['salt']);
        }

        return $items;
    }
}
