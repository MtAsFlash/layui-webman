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

use support\Container;
use support\Request;
use support\Response;
use yzh52521\Filesystem\Facade\Filesystem;
use plugin\admin\app\model\UploadFile;
use plugin\admin\app\model\Dict;
use plugin\admin\app\common\ImageInterver;

class UploadController
{

    /**
     * 无需登录及鉴权的方法
     * @var array
     */
    protected $noNeedLogin = [];

    /**
     * 不需要权限的方法
     *
     * @var string[]
     */
    protected $noNeedAuth = ['upload', 'choose', 'upload_group'];

    /**
     * @var Upload
     */
    protected $model = null;

    /**
     * 只返回当前管理员数据
     * @var string
     */
    protected $dataLimit = 'personal';

    /**
     * 选择
     * @return Request
     */
    public function choose(Request $request): Response
    {
        $data = $request->all();

        return view('upload/choose');
    }

    public function upload_group(): Response
    {
        $data = [
            'id'       => -1,
            'title'    => '全部分组',
            'name'     => '全部分组',
            'value'    => -1,
            'spread'   => true,
            'disabled' => true
        ];

        $uploadGroup = Dict::where('name', 'upload_group')->value('value');
        if ( ! empty($uploadGroup)) {
            $uploadGroup0 = [
                [
                    'id'     => 0,
                    'title'  => '未分组',
                    'name'   => '未分组',
                    'value'  => 0,
                    'spread' => false
                ]
            ];
            $uploadGroup  = json_decode($uploadGroup, true);
            foreach ($uploadGroup as $key => $v) {
                $uploadGroup[$key]['id']     = $v['value'];
                $uploadGroup[$key]['title']  = $v['name'];
                $uploadGroup[$key]['spread'] = false;
            }
            $children         = array_merge($uploadGroup0, $uploadGroup);
            $data['children'] = $children;
        }

        return json(['code' => 200, 'msg' => 'ok', 'data' => [$data]]);
    }

    /**
     * 上传附件
     *
     * @param $editorType   编辑器类型
     * @param $isBase64     是否base64图片
     *
     * @return void
     */
    public function upload($editorType = '', $isBase64 = false)
    {
        $request     = request();
        $upload_mode = get_config('upload_mode');

        if ($isBase64 === true) {
            //涂鸦上传（base64）
            $res = $this->upBase64($request->input('file'));

            return $res;
        } else {
            foreach ($request->file() as $key => $spl_file) {
                $requireDta = $request->all();
                $groupId    = $requireDta['group_id'] ?? 0;
                if ($groupId == 'undefined') {
                    $groupId = 0;
                }
                $savePath   = $request->post('save_path', '');
                $editorType = $request->get('editor_type', '');
                if ($groupId == -1) {
                    $groupId = 0;
                }
                //获取最大上传限制
                $maxUpSize = config('server.max_package_size') - 100;
                if ($spl_file && $spl_file->isValid()) {
                    $fileSize = $spl_file->getSize();
                    if ($fileSize > $maxUpSize) {
                        return json(['code' => 0, 'msg' => '超出最大上传限制，请处理后再上传']);
                    }
                    $getMime   = $spl_file->getUploadMineType();
                    $extension = $spl_file->getUploadExtension();
                    $mime_type = $spl_file->getUploadMimeType();
                    if (empty($savePath)) {
                        if (strstr($getMime, 'image')) {
                            $fileMime = "images";
                            $type     = 1;
                        } else {
                            $fileMime = "files";
                            $type     = 2;
                        }

                        $upload_types = $this->_get_upload_types($extension, $fileMime);
                        if ( ! $upload_types) {
                            return json(['code' => 0, 'msg' => '不允许上传 '.$extension.' 格式的文件']);
                        }
                    } else {
                        $fileMime = $savePath;
                    }
                    try {
                        $path = Filesystem::disk($upload_mode)->putFile($fileMime, $spl_file);
                    } catch (\Exception $e) {
                        return json(['code' => 0, 'msg' => $e->getMessage()]);
                    }
                    $fileUrl     = Filesystem::url($path);
                    $baseFileUrl = public_path().$fileUrl;
                    $fileUrl     = str_replace("\\", "/", $fileUrl);
                    $explodePath = explode("/", $fileUrl);
                    $fileName    = end($explodePath);
                    $image_with  = $image_height = 0;

                    if ($fileMime == 'images') {
                        if ($img_info = getimagesize($baseFileUrl)) {
                            [$image_with, $image_height] = $img_info;
                            $mime_type = $img_info['mime'];
                        }
                    }

                    $param = [
                        'storage'      => $upload_mode,
                        'group_id'     => $groupId,
                        'type'         => $type,
                        'file_url'     => $fileUrl,
                        'file_name'    => $fileName,
                        'file_size'    => $fileSize,
                        'file_type'    => $mime_type,
                        'image_width'  => $image_with,
                        'image_height' => $image_height,
                        'extension'    => $extension,
                        'admin_id'     => get_admin_id()
                    ];
                    //写入数据库
                    $this->_att_write($param);
                    if ($fileMime == 'images') {
                        //图片处理大小
                        $this->_edit_img_size($baseFileUrl);
                        //水印-图片
                        $this->_add_water($baseFileUrl);
                    }
                    if ($editorType == 'wang') {
                        return json(['errno' => 0, 'data' => ['url' => $fileUrl]]);
                    } else {
                        return json(['code' => 200, 'msg' => '上传成功', 'url' => $fileUrl]);
                    }
                } else {
                    return json(['code' => 0, 'msg' => '参数错误']);
                }
            }

        }
    }

    /**
     * 写入数据库
     *
     * @param $param
     *
     * @return void
     */
    private function _att_write($param)
    {
        UploadFile::create($param);
    }

    /**
     *
     */
    private function _edit_img_size($imgUrl)
    {
        ImageInterver::editImgSize($imgUrl, true, 1500);
    }

    /**
     * 添加水印
     *
     * @param $fileName
     *
     * @return void
     */
    private function _add_water($imgUrl)
    {
        if ( ! get_config('watermark_enable')) {
            return;
        }
        //水印图
        $watermark_name = get_config('watermark_name');
        //水印图位置
        $watermark_position = get_config('watermark_position');
        //水印图透明度
        $watermark_touming = get_config('watermark_touming');

        ImageInterver::addWater($imgUrl, $watermark_name, $watermark_position, $watermark_touming);
    }

    /**
     * 允许上传类型
     *
     * @param $ext
     * @param $type
     *
     * @return bool
     */
    private function _get_upload_types($ext, $type = 'images')
    {
        if ($type == 'images') {
            $arr = explode(',', get_config('upload_types_image'));
        } else {
            $arr = explode(',', get_config('upload_types_file'));
        }
        if ( ! in_array($ext, $arr)) {
            return false;
        }

        // 文件扩展黑名单
        $black = [
            'php',
            'jsp',
            'asp',
            'vb',
            'exe',
            'sh',
            'cmd',
            'bat',
            'vbs',
            'phtml',
            'class',
            'php2',
            'php3',
            'php4',
            'php5'
        ];
        if (in_array($ext, $black)) {
            return false;
        }

        return true;

    }

    /**
     * 处理base64编码的图片上传
     * @return mixed
     */
    private function upBase64($fileField)
    {
        $base64_image_content = $fileField;
        if (empty($base64_image_content)) {
            return false;
        }
        //合成图片的base64编码成
        $base64_image_content = "data:image/png;base64,{$base64_image_content}";
        //匹配出图片的信息
        $match = preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result);
        if ( ! $match) {
            return false;
        }

        //解码图片内容
        $base64_image = str_replace($result[1], '', $base64_image_content);
        $file_content = base64_decode($base64_image);
        $file_type    = $result[2];

        //如果没指定目录,则保存在当前目录下
        $pathTime = date('Ymd');
        $filePath = "/uploads/crawl/".$pathTime."/";
        $path     = public_path().$filePath;
        if ( ! is_dir($path)) {
            @mkdir($path, 0777, true);
        }
        $file_name = time().".{$file_type}";

        $new_file = $path.$file_name;

        if (file_exists($new_file)) {
            //有同名文件删除
            @unlink($new_file);
        }
        if (file_put_contents($new_file, $file_content)) {
            return json_encode(['state' => 'SUCCESS', 'url' => $filePath.$file_name], JSON_UNESCAPED_UNICODE);
        }

        return false;
    }

}
