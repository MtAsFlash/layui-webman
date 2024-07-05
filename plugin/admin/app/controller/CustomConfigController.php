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
use plugin\admin\app\model\Config;
use plugin\admin\app\common\CacheClear;

class CustomConfigController extends CrudController
{

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = [];

    /**
     * @var Config
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Config;
    }

    /**
     * 首页
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request): Response
    {
        return view('custom_config/index');
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
        [$where, $format, $limit, $field, $order, $page, $withoutField] = $this->selectInput($request);
        $query = $this->doSelect($where, $field, $order, $withoutField);

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
            $fileType = Config::FIELDTYPE;

            return view('custom_config/add', ['field_type' => $fileType]);
        }
        $data      = $request->post();
        $dataValue = $data['value'];
        unset($data['value']);
        if ($data['fieldtype'] === 'radio' || $data['fieldtype'] === 'select') {
            $data['value'] = ! empty($dataValue['more']) ? json_encode(array_values($dataValue['more']),
                JSON_UNESCAPED_UNICODE) : "[]";
        } elseif ($data['fieldtype'] === 'images') {
            $data['value'] = json_encode($dataValue['images'], true);
        } else {
            $data['value'] = $dataValue[$data['fieldtype']] ?? "";
        }
        //查询name是否存在
        $findData = Config::where('name', $data['name'])->find();
        if ( ! empty($findData)) {
            return $this->error('配置名称已存在！');
        }
        $data['type'] = 99;
        Config::create($data);

        return $this->success('操作成功');
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
            $fileType = Config::FIELDTYPE;

            return view('custom_config/edit', ['field_type' => $fileType]);
        }
        $data      = $request->post();
        $dataValue = $data['value'];
        unset($data['value']);
        if ($data['fieldtype'] === 'radio' || $data['fieldtype'] === 'select') {
            $data['value'] = ! empty($dataValue['more']) ? json_encode(array_values($dataValue['more']),
                JSON_UNESCAPED_UNICODE) : "[]";
        } elseif ($data['fieldtype'] === 'images') {
            $data['value'] = json_encode($dataValue['images'], true);
        } else {
            $data['value'] = $dataValue[$data['fieldtype']] ?? "";
        }
        Config::where('name', $data['name'])->save($data);

        return $this->success('操作成功');
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
        if (empty($id)) {
            return $this->error('参数错误');
        }
        $findData = Config::find($id);
        if (empty($findData)) {
            return $this->error('获取数据失败');
        }
        if ($findData['type'] != 99) {
            return $this->error('该类型禁止删除');
        }
        Config::whereIn('id', $id)->delete();

        return $this->success('操作成功');
    }

    protected function afterQuery($items)
    {
        $fileType = Config::FIELDTYPE;
        foreach ($items as $key => $v) {
            $items[$key]['value']          = json_decode($v['value'], true) ?? $v['value'];
            $items[$key]['fieldtype_name'] = $fileType[$v['fieldtype']] ?? '';
        }

        return $items;
    }

}
