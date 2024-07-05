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

use plugin\admin\app\common\Auth;
use plugin\admin\app\common\Tree;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 角色组
 */
class RoleController extends CrudController
{

    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['rules'];

    /**
     * @var Role
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Role;
    }

    /**
     * 首页
     * @return Response
     */
    public function index(): Response
    {
        return view('role/index');
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
        $id = $request->get('id');
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        $role_ids = Auth::getScopeRoleIds(true);
        if ( ! $id) {
            $where['id'] = ['in', $role_ids];
        } elseif ( ! in_array($id, $role_ids)) {
            throw new BusinessException('无权限');
        }
        $query = $this->doSelect($where, $field, $order);

        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 添加
     */
    public function add(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('role/add');
        }
        $data = $this->insertInput($request);
        $pid  = $data['pid'] ?? null;
        if ( ! $pid) {
            return $this->error('请选择父级角色组');
        }
        if ( ! Auth::isSupperAdmin() && ! in_array($pid, Auth::getScopeRoleIds(true))) {
            return $this->error('父级角色组超出权限范围');
        }
        $this->checkRules($pid, $data['rules'] ?? '');
        $id = $this->doInsert($data);

        return $this->success('ok', ['id' => $id]);
    }

    /**
     * 编辑
     *
     * @param Request $request
     *
     * @return Response
     * @throws BusinessException
     */
    public function edit(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('role/edit');
        }
        [$id, $data] = $this->updateInput($request);
        $is_supper_admin     = Auth::isSupperAdmin();
        $descendant_role_ids = Auth::getScopeRoleIds();
        if ( ! $is_supper_admin && ! in_array($id, $descendant_role_ids)) {
            return $this->error('无数据权限');
        }
        if ( ! $role = $this->model->find($id)) {
            return $this->error('记录不存在');
        }
        $is_supper_role = $role->rules === '*';
        // 超级角色组不允许更改rules pid 字段
        if ($is_supper_role) {
            unset($data['rules'], $data['pid']);
        }
        if (key_exists('pid', $data)) {
            $pid = $data['pid'];
            if ( ! $pid) {
                return $this->error('请选择父级角色组');
            }
            if ($pid == $id) {
                return $this->error('父级不能是自己');
            }
            if ( ! $is_supper_admin && ! in_array($pid, Auth::getScopeRoleIds(true))) {
                return $this->error('父级超出权限范围');
            }
        } else {
            $pid = $role->pid;
        }
        if ( ! $is_supper_role) {
            $this->checkRules($pid, $data['rules'] ?? '');
        }
        $this->doUpdate($id, $data);

        // 删除所有子角色组中已经不存在的权限
        if ( ! $is_supper_role) {
            $tree                = new Tree(Role::field('id,pid')->select());
            $descendant_roles    = $tree->getDescendant([$id]);
            $descendant_role_ids = array_column($descendant_roles, 'id');
            $rule_ids            = $data['rules'] ? explode(',', $data['rules']) : [];
            foreach ($descendant_role_ids as $role_id) {
                $tmp_role        = Role::find($role_id);
                $tmp_rule_ids    = $role->getRuleIds();
                $tmp_rule_ids    = array_intersect($rule_ids, $tmp_rule_ids);
                $tmp_role->rules = implode(',', $tmp_rule_ids);
                $tmp_role->save();
            }
        }

        return $this->success('操作成功');
    }

    /**
     * 删除
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        if (in_array(1, $ids)) {
            return $this->error('无法删除超级管理员角色');
        }
        if ( ! Auth::isSupperAdmin() && array_diff($ids, Auth::getScopeRoleIds())) {
            return $this->error('无删除权限');
        }
        $tree        = new Tree(Role::select());
        $descendants = $tree->getDescendant($ids);
        if ($descendants) {
            $ids = array_merge($ids, array_column($descendants, 'id'));
        }
        $this->doDelete($ids);

        return $this->success('操作成功');
    }

    /**
     * 角色权限
     *
     * @param Request $request
     *
     * @return Response
     */
    public function rules(Request $request): Response
    {
        $role_id = $request->get('id');
        if (empty($role_id)) {
            return $this->error('参数错误');
        }
        if ( ! Auth::isSupperAdmin() && ! in_array($role_id, Auth::getScopeRoleIds(true))) {
            return $this->error('角色组超出权限范围');
        }
        $rule_id_string = Role::where('id', $role_id)->value('rules');
        if ($rule_id_string === '') {
            return $this->error('ok');
        }
        $rules   = Rule::select();
        $include = [];
        if ($rule_id_string !== '*') {
            $include = explode(',', $rule_id_string);
        }
        if (is_object($rules)) {
            $rules = $rules->toArray();
        }
        $items = [];
        foreach ($rules as $item) {
            $items[] = [
                'title'  => ! empty($item['title']) ? $item['title']."【".$item['key']."】" : $item['id']."【".$item['key']."】",
                'value'  => (int)$item['id'],
                'id'     => $item['id'],
                'pid'    => $item['pid'],
                'spread' => true
            ];
        }
        $tree = new Tree($items);

        return $this->success("获取成功", $tree->getTree($include));
    }

    /**
     * 检查权限字典是否合法
     *
     * @param int $role_id
     * @param     $rule_ids
     *
     * @return void
     * @throws BusinessException
     */
    protected function checkRules(int $role_id, $rule_ids)
    {
        if ($rule_ids) {
            $rule_ids = explode(',', $rule_ids);
            if (in_array('*', $rule_ids)) {
                throw new BusinessException('非法数据');
            }
            $rule_exists = Rule::whereIn('id', $rule_ids)->column('id');
            if (count($rule_exists) != count($rule_ids)) {
                throw new BusinessException('权限不存在');
            }
            $rule_id_string = Role::where('id', $role_id)->value('rules');
            if ($rule_id_string === '') {
                throw new BusinessException('数据超出权限范围');
            }
            if ($rule_id_string === '*') {
                return;
            }
            $legal_rule_ids = explode(',', $rule_id_string);
            if (array_diff($rule_ids, $legal_rule_ids)) {
                throw new BusinessException('数据超出权限范围');
            }
        }
    }

    protected function afterQuery($item)
    {
        foreach ($item as $key => $v) {
            $item[$key]['isRoot'] = $key == 0 ? true : false;
        }

        return $item;
    }

}
