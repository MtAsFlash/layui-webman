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
use plugin\admin\app\model\UploadFile;
use plugin\admin\app\model\Dict;

class UploadFileController extends CrudController
{

    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['select'];

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
        $this->model = new UploadFile;
    }

    /**
     * 列表
     * @return Request
     */
    public function index(Request $request): Response
    {
        return view('upload_file/index');
    }

    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        if ( ! empty($where['group_id']) && $where['group_id'] == -1) {
            unset($where['group_id']);
        }
        if ( ! empty($where['create_time'])) {
            $createTime           = explode(' - ', $where['create_time']);
            $timeStart            = strtotime($createTime[0]);
            $timeEnd              = strtotime($createTime[1].' 23:59:59');
            $where['create_time'] = [$timeStart, $timeEnd];
        }
        $query = $this->doSelect($where, $field, $order);

        if ($format === 'select') {
            return $this->formatSelect($query->select());
        }

        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 更新附件
     *
     * @param Request $request
     *
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function edit(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return raw_view('upload/edit');
        }
        $post = $request->post();
        if (empty($post['ids'])) {
            return $this->error('参数错误');
        }
        $idsArr  = array_filter(explode(",", $post['ids']));
        $groupId = $post['group_id'] ?? 0;
        UploadFile::whereIn('id', $idsArr)->data(['group_id' => $groupId])->update();

        return $this->success('操作成功');
    }

    /**
     * 删除附件
     *
     * @param Request $request
     *
     * @return Response
     */
    public function delete(Request $request): Response
    {
        $id = (array)$request->post('id');
        UploadFile::destroy($id);

        return $this->success('操作成功');
    }

    /**
     * 下载
     *
     * @param Request $request
     *
     * @return Response
     */
    public function download(Request $request): Response
    {
        $file = $request->input('url');
        if (file_exists(public_path().$file)) {
            $fileNameArr = explode('/', $file);
            $fileName    = $file;
            if (is_array($fileNameArr)) {
                $fileName = end($fileNameArr);
            }

            return response()->download(public_path().$file, $fileName);
        } else {
            return $this->error('文件不存在');
        }

    }

    protected function afterQuery($items)
    {
        $groupNames = (new Dict)->getNameValue('upload_group');
        $groupNames = array_column($groupNames, null, 'value');
        foreach ($items as $key => $v) {
            $items[$key]['icon']       = $this->getIcon($v['extension']);
            $items[$key]['group_name'] = $groupNames[$v['group_id']]['name'] ?? '未分组';
        }

        return $items;
    }

    // 返回文件图标
    private function getIcon($extension)
    {
        // 获取文件格式
        $ext = strtoupper($extension);
        switch ($ext) {
            case 'PHP':
                $ico = '#icon-php';
                break;
            case 'HTML':
                $ico = '#icon-html';
                break;
            case 'JS':
                $ico = '#icon-js';
                break;
            case 'CSS':
                $ico = '#icon-css';
                break;
            case 'JSON':
                $ico = '#icon-json';
                break;
            case 'JPG':
                $ico = '#icon-Jpg';
                break;
            case 'PNG':
                $ico = '#icon-png';
                break;
            case 'GIF':
                $ico = '#icon-gif';
                break;
            case 'HTACCESS':
                $ico = '#icon-htaccess';
                break;
            case 'ICO':
                $ico = '#icon-img';
                break;
            case 'BMP':
                $ico = '#icon-bmp';
                break;
            default:
                $ico = '#icon-file';
                break;
        }

        return $ico;
    }

}
