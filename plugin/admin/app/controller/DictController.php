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
use support\Response;
use support\exception\BusinessException;
use plugin\admin\app\model\Dict;
use plugin\admin\app\common\CacheClear;
use plugin\admin\app\validate\DictValidate;

class DictController extends CrudController
{

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['get'];

    /**
     * @var Dict
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Dict;
    }

    /**
     * 首页
     * @return Response
     */
    public function index(Request $request): Response
    {
        return view('dict/index');
    }

    /**
     * 查询
     * @return Response
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
     * 获取
     *
     * @param Request $request
     * @param         $name
     *
     * @return Response
     */
    public function get(Request $request): Response
    {
        $name = $request->get('name');
        $data = Dict::where(['name' => $name])->find();
        if (empty($data)) {
            return $this->error('获取数据失败');
        }
        $data['value'] = json_decode($data['value'], true);
        $data          = ! is_array($data) ? $data->toArray() : $data;

        return $this->success('ok', $data);
    }

    /**
     * 添加
     * @return Response
     */
    public function add(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('dict/add');
        }

        $validate = new DictValidate();
        if ( ! $validate->check($request->post())) {
            return $this->error($validate->getError());
        }

        $name     = $request->post('name');
        $findData = Dict::where('name', $name)->find();
        if ($findData) {
            return $this->json(0, '字典已经存在');
        }
        $values = (array)$request->post('value', []);
        $values = array_values($values);
        $values = json_encode($values, JSON_UNESCAPED_UNICODE);
        Dict::create(['name' => $name, 'value' => $values]);

        return $this->success('操作成功');
    }

    /**
     * 编辑
     * @return Response
     */
    public function edit(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('dict/edit');
        }
        $validate = new DictValidate();
        if ( ! $validate->check($request->post())) {
            return $this->error($validate->getError());
        }

        $name   = $request->post('name');
        $values = (array)$request->post('value', []);
        $values = array_values($values);
        $values = json_encode($values, JSON_UNESCAPED_UNICODE);
        //查询数据是否存在
        $findData = Dict::where('name', $name)->find();
        if (empty($findData)) {
            return $this->error('数据不存在');
        }
        $findData->value = $values;
        $findData->save();
        CacheClear::cacheDictName($name);

        return $this->success('操作成功');
    }

    /**
     * 删除
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $id = (array)$request->post('id');
        if (empty($id)) {
            return $this->error('参数错误');
        }
        if (in_array(1, $id)) {
            return $this->error('禁止删除id=1的数据');
        }
        Dict::whereIn('id', $id)->delete();

        return $this->success('操作成功');
    }

    /**
     * 前置方法
     * @return void
     */
    protected function selectInput(Request $request): array
    {
        [$where, $format, $limit, $field, $order] = parent::selectInput($request);
        if ( ! empty($where['name'])) {
            $where['name'] = ['like', "%{$where['name']}%"];
        }

        return [$where, $format, $limit, $field, $order];
    }

}
