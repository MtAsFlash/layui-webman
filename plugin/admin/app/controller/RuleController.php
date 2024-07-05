<?php
//  +----------------------------------------------------------------------
//  | huicmf [ huicmf快速开发框架 ]
//  +----------------------------------------------------------------------
//  | Copyright (c) 2022~2024 https://xiaohuihui.cc All rights reserved.
//  +----------------------------------------------------------------------
//  | Author: 小灰灰 <762229008@qq.com>
//  +----------------------------------------------------------------------
//  | Info:  权限控制器
//  +----------------------------------------------------------------------
//

namespace plugin\admin\app\controller;

use plugin\admin\app\common\Util;
use plugin\admin\app\common\Tree;
use plugin\admin\app\common\CacheClear;
use plugin\admin\app\model\Role;
use plugin\admin\app\model\Rule;
use support\exception\BusinessException;
use support\Request;
use support\Response;

/**
 * 权限菜单
 */
class RuleController extends CrudController
{

    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['get', 'permission'];

    /**
     * @var Rule
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Rule;
    }

    /**
     * 首页
     * @return Response
     */
    public function index(): Response
    {
        return view('rule/index');
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
        //自动添加方法名权限
        $this->syncRules();

        return parent::select($request);
    }

    /**
     * 添加
     */
    public function add(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('rule/add');
        }
        $data = $this->insertInput($request);
        if (empty($data['type'])) {
            $data['type'] = strpos($data['key'], '\\') ? 1 : 0;
        }
        $data['key'] = str_replace('\\\\', '\\', $data['key']);
        $key         = $data['key'] ?? '';
        if ($this->model->where('key', $key)->find()) {
            return $this->json(1, "菜单标识 $key 已经存在");
        }
        $data['pid']  = empty($data['pid']) ? 0 : $data['pid'];
        $data['href'] = $data['href'] ?? "";
        $data['icon'] = $data['icon'] ?? "";
        $this->doInsert($data);
        //清除缓存
        CacheClear::cacheRuleLists();

        return $this->success('操作成功');
    }

    /**
     *  编辑
     */
    public function edit(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('rule/edit');
        }
        [$id, $data] = $this->updateInput($request);
        if ( ! $row = $this->model->find($id)) {
            return $this->error('记录不存在');
        }
        if (isset($data['pid'])) {
            $data['pid'] = $data['pid'] ?: 0;
            if ($data['pid'] == $row['id']) {
                return $this->error('不能将自己设置为上级菜单');
            }
        }
        if (isset($data['key'])) {
            $data['key'] = str_replace('\\\\', '\\', $data['key']);
        }
        $this->doUpdate($id, $data);
        //清除缓存
        CacheClear::cacheRuleLists();

        return $this->success('操作成功');
    }

    /**
     * 删除
     */
    public function delete(Request $request): Response
    {
        $ids = $this->deleteInput($request);
        // 子规则一起删除
        $delete_ids = $children_ids = $ids;
        while ($children_ids) {
            $children_ids = $this->model->whereIn('pid', $children_ids)->column('id');
            $delete_ids   = array_merge($delete_ids, $children_ids);
        }
        $this->doDelete($delete_ids);
        //清除缓存
        CacheClear::cacheRuleLists();

        return $this->success('删除成功');
    }

    /*
     * 后台进入后默认加载的权限菜单
     */
    public function get(Request $request): Response
    {
        $rules = $this->getRules(admin('roles'));
        $types = $request->get('type', '0,1');
        $types = is_string($types) ? explode(',', $types) : [0, 1];
        $items = Rule::order('weight', 'asc')->select()->toArray();

        $formatted_items = [];
        foreach ($items as $item) {
            $item['pid']       = (int)$item['pid'];
            $item['name']      = $item['title'];
            $item['value']     = $item['id'];
            $item['icon']      = $item['icon'] ? "layui-icon {$item['icon']}" : '';
            $formatted_items[] = $item;
        }
        $tree       = new Tree($formatted_items);
        $tree_items = $tree->getTree();
        // 超级管理员权限为 *
        if ( ! in_array('*', $rules)) {
            $this->removeNotContain($tree_items, 'id', $rules);
        }
        $this->removeNotContain($tree_items, 'type', $types);

        return json(Tree::arrayValues($tree_items));
    }

    /**
     * 获取权限
     *
     * @param Request $request
     *
     * @return Response
     */
    public function permission(Request $request): Response
    {
        $rules = $this->getRules(admin('roles'));
        // 超级管理员
        if (in_array('*', $rules)) {
            return $this->success('ok', ['*']);
        }
        $keys        = Rule::whereIn('id', $rules)->column('key');
        $permissions = [];
        foreach ($keys as $key) {
            if ( ! $key = Util::controllerToUrlPath($key)) {
                continue;
            }
            $code          = str_replace('/', '.', trim($key, '/'));
            $permissions[] = $code;
        }

        return $this->success('ok', $permissions);
    }

    /**
     * 查询前置方法
     *
     * @param Request $request
     *
     * @return array
     * @throws BusinessException
     */
    protected function selectInput(Request $request): array
    {
        [$where, $format, $limit, $field, $order] = parent::selectInput($request);
        // 允许通过type=0,1格式传递菜单类型
        $types = $request->get('type');
        if ($types && is_string($types)) {
            $where['type'] = ['in', explode(',', $types)];
        }
        // 默认weight排序
        if ( ! $field) {
            $field = 'weight';
            $order = 'asc';
        }

        return [$where, $format, $limit, $field, $order];
    }

    /**
     * 查询后，修改数据
     *
     * @param array $items 数据集
     *
     * @return void
     */
    protected function afterQuery($items)
    {
        return $items;
    }

    /**
     * 获取权限规则
     *
     * @param $roles
     *
     * @return array
     */
    protected function getRules($roles): array
    {
        $rules_strings = $roles ? Role::whereIn('id', $roles)->column('rules') : [];
        $rules         = [];
        foreach ($rules_strings as $rule_string) {
            if ( ! $rule_string) {
                continue;
            }
            $rules = array_merge($rules, explode(',', $rule_string));
        }

        return $rules;
    }

    /**
     * 根据类同步规则到数据库
     * @return void
     */
    protected function syncRules()
    {
        $items = $this->model->where('key', 'like', '%\\\\%')->select()->toArray();
        $items = array_column($items, null, 'key');

        $methods_in_db    = [];
        $methods_in_files = [];
        foreach ($items as $item) {
            $class = $item['key'];
            if (strpos($class, '@')) {
                $methods_in_db[$class] = $class;
                continue;
            }
            if (class_exists($class)) {
                $reflection   = new \ReflectionClass($class);
                $properties   = $reflection->getDefaultProperties();
                $no_need_auth = array_merge($properties['noNeedLogin'] ?? [], $properties['noNeedAuth'] ?? []);
                $class        = $reflection->getName();
                $pid          = $item['id'];
                $methods      = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $method) {
                    $method_name = $method->getName();
                    if (strtolower($method_name) === 'index' || strpos($method_name,
                            '__') === 0 || in_array($method_name, $no_need_auth)) {
                        continue;
                    }
                    $name                    = "$class@$method_name";
                    $methods_in_files[$name] = $name;
                    $title                   = Util::getCommentFirstLine($method->getDocComment()) ?: $method_name;
                    $menu                    = $items[$name] ?? [];

                    if ($menu) {
                        if ($menu['title'] != $title) {
                            Rule::where('key', $name)->update(['title' => $title]);
                        }
                        continue;
                    }
                    $menu        = new Rule;
                    $menu->pid   = $pid;
                    $menu->key   = $name;
                    $menu->title = $title;
                    $menu->type  = 2;
                    $menu->save();
                }
            }
        }
        // 从数据库中删除已经不存在的方法
        $menu_names_to_del = array_diff($methods_in_db, $methods_in_files);
        if ($menu_names_to_del) {
            Rule::whereIn('key', $menu_names_to_del)->delete();
        }
    }

    /**
     * 移除不包含某些数据的数组
     *
     * @param $array
     * @param $key
     * @param $values
     *
     * @return void
     */
    protected function removeNotContain(&$array, $key, $values)
    {
        foreach ($array as $k => &$item) {
            if ( ! is_array($item)) {
                continue;
            }
            if ( ! $this->arrayContain($item, $key, $values)) {
                unset($array[$k]);
            } else {
                if ( ! isset($item['children'])) {
                    continue;
                }
                $this->removeNotContain($item['children'], $key, $values);
            }
        }
    }

    /**
     * 判断数组是否包含某些数据
     *
     * @param $array
     * @param $key
     * @param $values
     *
     * @return bool
     */
    protected function arrayContain(&$array, $key, $values): bool
    {
        if ( ! is_array($array)) {
            return false;
        }
        if (isset($array[$key]) && in_array($array[$key], $values)) {
            return true;
        }
        if ( ! isset($array['children'])) {
            return false;
        }
        foreach ($array['children'] as $item) {
            if ($this->arrayContain($item, $key, $values)) {
                return true;
            }
        }

        return false;
    }

}
