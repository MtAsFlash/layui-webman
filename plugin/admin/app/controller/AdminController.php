<?php
//  +----------------------------------------------------------------------
//  | huicmf [ huicmf快速开发框架 ]
//  +----------------------------------------------------------------------
//  | Copyright (c) 2022~2024 https://xiaohuihui.cc All rights reserved.
//  +----------------------------------------------------------------------
//  | Author: 小灰灰 <762229008@qq.com>
//  +----------------------------------------------------------------------
//  | Info:  管理员控制器
//  +----------------------------------------------------------------------
//

namespace plugin\admin\app\controller;

use plugin\admin\app\model\Admin;
use plugin\admin\app\model\AdminRole;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use plugin\admin\app\common\Auth;
use plugin\admin\app\validate\AdminValidate;

class AdminController extends CrudController
{

    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['select'];

    /**
     * @var Admin
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
    protected $dataLimitField = 'id';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Admin;
    }

    /**
     * 首页
     * @return Response
     */
    public function index(): Response
    {
        return view('admin/index');
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
            return view('admin/add');
        }
        $validate = new AdminValidate();
        if ( ! $validate->scene('add')->check($request->post())) {
            return $this->error($validate->getError());
        }

        $data = $this->insertInput($request);

        //判断会员名是否存在：
        $findUser = Admin::where(['username' => $data['username']])->count();
        if ($findUser > 0) {
            return $this->error(trans('The user already exists', [], 'admin'));
        }

        $admin_id = $this->doInsert($data);
        $role_ids = $request->post('roles');
        $role_ids = $role_ids ? explode(',', $role_ids) : [];
        if ( ! $role_ids) {
            return $this->json(1, trans('Select at least one role group', [], 'admin'));
        }
        if ( ! Auth::isSupperAdmin() && array_diff($role_ids, Auth::getScopeRoleIds())) {
            return $this->error(trans('Role exceeds permission range', [], 'admin'));
        }
        AdminRole::where('admin_id', $admin_id)->delete();
        foreach ($role_ids as $id) {
            $admin_role           = new AdminRole;
            $admin_role->admin_id = $admin_id;
            $admin_role->role_id  = $id;
            $admin_role->save();
        }

        return $this->success(trans('Operation successful'), ['id' => $admin_id]);
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
            return view('admin/edit');
        }
        $validate = new AdminValidate();
        if ( ! $validate->scene('edit')->check($request->post())) {
            return $this->error($validate->getError());
        }
        [$id, $data] = $this->updateInput($request);
        $admin_id = $request->post('id');
        if ( ! $admin_id) {
            return $this->error(trans('Invalid parameters'));
        }
        // 不能禁用自己
        if (isset($data['status']) && $data['status'] == 0 && $id == get_admin_id()) {
            return $this->error(trans('Cannot disable oneself', [], 'admin'));
        }
        // 需要更新角色
        $role_ids = $request->post('roles');
        if ($role_ids !== null) {
            if ( ! $role_ids) {
                return $this->error(trans('Select at least one role group', [], 'admin'));
            }
            $role_ids = explode(',', $role_ids);

            $is_supper_admin = Auth::isSupperAdmin();
            $exist_role_ids  = AdminRole::where('admin_id', $admin_id)->column('role_id');
            $scope_role_ids  = Auth::getScopeRoleIds();
            if ( ! $is_supper_admin && ! array_intersect($exist_role_ids, $scope_role_ids)) {
                return $this->error(trans('Unauthorized operation'));
            }
            if ( ! $is_supper_admin && array_diff($role_ids, $scope_role_ids)) {
                return $this->error(trans('Role exceeds permission range', [], 'admin'));
            }

            // 删除账户角色
            $delete_ids = array_diff($exist_role_ids, $role_ids);
            AdminRole::whereIn('role_id', $delete_ids)->where('admin_id', $admin_id)->delete();
            // 添加账户角色
            $add_ids = array_diff($role_ids, $exist_role_ids);
            foreach ($add_ids as $role_id) {
                $admin_role           = new AdminRole;
                $admin_role->admin_id = $admin_id;
                $admin_role->role_id  = $role_id;
                $admin_role->save();
            }
        }
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
        Admin::whereIn('id', $id)->delete();

        return $this->success(trans('Operation successful'));
    }

    protected function afterQuery($items)
    {
        $login_admin_id = get_admin_id();
        foreach ($items as $key => $v) {
            $rolesArr = Auth::getGroups($v['id']);
            $rolesIds = (implode(",", array_column($rolesArr, 'role_id')));

            $items[$key]['login_time']   = ! empty($v['login_time']) ? date('Y-m-d H:i:s', $v['login_time']) : '';
            $items[$key]['show_toolbar'] = $v['id'] != $login_admin_id;
            $items[$key]['roles']        = $rolesIds;
            $items[$key]['roles_arr']    = $rolesArr;
            unset($items[$key]['password']);
            unset($items[$key]['salt']);
        }

        return $items;
    }

}
