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
use plugin\admin\app\model\Admin;
use plugin\admin\app\common\CacheClear;
use PragmaRX\Google2FA\Google2FA;

// 创建二维码
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class ConfigController extends CrudController
{

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['add', 'delete'];

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
     * @return Response
     */
    public function index(Request $request): Response
    {

        if ($request->method() === 'GET') {
            $php_imagick = false;
            if (extension_loaded('imagick')) {
                $php_imagick = true;
            }

            return view('config/index', ['php_imagick' => $php_imagick]);
        }
    }

    /**
     * get
     * @return Response
     */
    public function get(Request $request): Response
    {
        $param    = $request->post('param');
        $admin_id = (int)$request->post('admin_id');
        $find     = $this->model->where('name', $param)->find();
        if (empty($find)) {
            return $this->error('获取数据失败');
        }
        if ($find['value'] != 1) {
            return $this->error('请先开启并保存动态口令认证');
        }
        //判断是否开启扩展
        $php_imagick = false;
        if ( ! extension_loaded('imagick')) {
            return $this->error('请先安装php对应版本的imagick扩展组件');
        }
        $adminId   = ! empty($admin_id) ? $admin_id : get_admin_id();
        $adminInfo = (new Admin)->where('id', $adminId)->find();
        if (empty($adminInfo)) {
            return $this->error('获取管理员信息失败');
        }

        $adminId   = $adminInfo['id'] ?? 0;
        $username  = $adminInfo['username'] ?? "";
        dump($username);
        $google2fa = new Google2FA();
        if (empty($adminInfo['google2fa_secretKey'])) {
            $secretKey = $google2fa->generateSecretKey(32);
        } else {
            $secretKey = $adminInfo['google2fa_secretKey'];
        }
        $companyName = get_config('site_name') ?? "HuiCMF-webman-V2";
        $companyData = $username;

        $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $companyData, $secretKey);
        //更新会员表数据信息
        try {
            (new Admin)->gogole2faUpdate($adminId, $secretKey);
        } catch (\Exception $e) {
        }

        $writer       = new Writer(new ImageRenderer(new RendererStyle(400), new ImagickImageBackEnd()));
        $qrcode_image = base64_encode($writer->writeString($qrCodeUrl));

        return $this->success('ok', ['qrcode_img' => $qrcode_image, 'secretKey' => $secretKey]);
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

        return $this->doFormat($query, $format, $limit);
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
        $param = $request->post();
        foreach ($param as $key => $value) {
            $arr[$key] = $value;
            $value     = htmlspecialchars($value);
            if ($key == 'onetime_password' && $value == 0) {
                //清除所有管理员的动态口令认证秘钥
                (new Admin)->where('1=1')->data(['google2fa_secretKey' => '', 'google2fa_timestamp' => 0])->update();
            }
            Config::strict(false)->where(['name' => $key])->data(['value' => $value])->update();
        }
        //清除缓存
        CacheClear::cacheSystemConfig();

        return $this->success('保存成功');
    }

    /**
     * 查询数据，后置方法
     *
     * @param $items
     *
     * @return array|mixed
     */
    protected function afterQuery($items)
    {
        foreach ($items as $key => $v) {
            if (isset($v['name']) && $v['name'] === 'ueditor_icon') {
                $items[$key]['value'] = array_filter(explode(',', $v['value']));
            }
        }

        return $items;
    }

}
