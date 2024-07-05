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

use plugin\admin\app\model\Admin;
use plugin\admin\app\model\LogLogin;
use support\exception\BusinessException;
use plugin\admin\app\common\Auth;
use support\Request;
use support\Response;
use Webman\Captcha\CaptchaBuilder;
use Webman\Captcha\PhraseBuilder;
use Shopwwi\LaravelCache\Cache;
use support\lib\Random;
use plugin\admin\app\common\Util;
use PragmaRX\Google2FA\Google2FA;

class AccountController extends CrudController
{

    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['login', 'logout', 'captcha'];

    /**
     * 不需要鉴权的方法
     * @var string[]
     */
    protected $noNeedAuth = ['index', 'info', 'clearCache', 'editPwd', 'check_new_version'];

    /**
     * @var Admin
     */
    protected $model = null;

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->model = new Admin;
    }

    public function login(Request $request): Response
    {
        $this->checkDatabaseAvailable();

        $loginLogData    = [];
        $ip_address      = get_client_ip();
        $ipToArea        = getIpToArea($ip_address);
        $onetimePassword = get_config('onetime_password');

        $username  = $request->post('username', '');
        $password  = $request->post('password', '');
        $safe_mode = (int)$request->post('safe_mode', 0);

        if ( ! $username) {
            return $this->json(0, '用户名不能为空');
        }
        if ( ! $onetimePassword || ! $safe_mode) {
            $captcha = $request->post('captcha', '');
            if (strtolower($captcha) !== session('captcha-login')) {
                return $this->json(0, '验证码错误');
            }
            $request->session()->forget('captcha-login');
            $this->checkLoginLimit($username);
        }
        $admin = Admin::where('username', $username)->find();

        $loginLogData['admin_id']   = $admin['id'] ?? 0;
        $loginLogData['admin_name'] = $admin['username'] ?? $username;
        $loginLogData['ip_address'] = $ip_address;
        $loginLogData['country']    = $ipToArea['country'];
        $loginLogData['province']   = $ipToArea['province'];
        $loginLogData['city']       = $ipToArea['city'];
        $loginLogData['isp']        = $ipToArea['isp'];

        if ( ! $admin || $admin->password != cmf_password($password, $admin->salt)) {
            $loginLogData['desc'] = '密码不正确：'.json_encode([
                    'username' => $username,
                    'password' => $password
                ], JSON_UNESCAPED_UNICODE);
            LogLogin::addRecord($loginLogData);

            return $this->json(0, '账户不存在或密码错误！');
        }

        if ($admin->status != 1) {
            $loginLogData['desc'] = '账户已禁用：'.json_encode([
                    'username' => $username,
                    'password' => $password
                ], JSON_UNESCAPED_UNICODE);
            LogLogin::addRecord($loginLogData);

            return $this->json(0, '当前账户暂时无法登录！');
        }
        //获取上次登录信息
        $lastLoginInfo      = LogLogin::getLastLoginInfo($admin->id, $onetimePassword);
        $lastOnetime        = ! empty($lastLoginInfo['create_time']) ? strtotime($lastLoginInfo['create_time']) : 0;
        $getLastOneTimeLeft = time() - $lastOnetime;
        //验证后台是否开启：动态口令验证
        $google2fa_timestamp = false;
        //如果超过24小时，则需要重新验证动态口令
        if ($onetimePassword && $getLastOneTimeLeft > 24 * 3600) {
            $vcode = $request->post('vcode', '');
            if (empty($vcode) && $safe_mode == 0) {
                return $this->json(101);
            } elseif (empty($vcode) && $safe_mode == 1) {
                return $this->json(0, '请先输入动态口令！');
            } else {
                //验证动态口令是否正确
                $google2fa           = new Google2FA();
                $secretKey           = $admin['google2fa_secretKey'];
                $google2fa_timestamp = $admin['google2fa_timestamp'] ?? false;
                $google2fa_timestamp = $google2fa->verifyKeyNewer($secretKey, $vcode, $google2fa_timestamp);
                if ($google2fa_timestamp === false) {
                    return $this->json(0, '动态口令验证失败！');
                }
            }
            $loginLogData['onetime_password'] = 1;
        }

        $isFirstLogin = false;
        if (empty($lastLoginInfo)) {
            $last_login_address = "";
            $last_login_ip      = "";
            $last_login_time    = "";
            $isFirstLogin       = true;
        } else {
            $last_login_ip      = $lastLoginInfo['ip_address'] ?? "";
            $last_login_time    = $lastLoginInfo['create_time'] ?? "";
            $lastAdressIsp      = ! empty($lastLoginInfo['isp']) ? $lastLoginInfo['isp']." " : "";
            $lastAdressCountry  = ! empty($lastLoginInfo['country']) ? $lastLoginInfo['country']." " : "";
            $lastAdressProvince = ! empty($lastLoginInfo['province']) ? $lastLoginInfo['province']." " : "";
            $lastAdressCity     = ! empty($lastLoginInfo['city']) ? $lastLoginInfo['city']." " : "";
            $last_login_address = $lastAdressIsp.$lastAdressCountry.$lastAdressProvince.$lastAdressCity;
        }
        $salt              = Random::alnum(6);
        $admin->login_time = time();
        $admin->login_ip   = $ip_address;

        //如果需要开启后台管理单设备登录，可以把以下2行更新密码和密码盐的注释放开即可。
        //$admin->salt     = $salt; //更新密码盐
        //$admin->password = cmf_password($password, $salt); //更新密码

        if ($onetimePassword && $google2fa_timestamp != false) {
            $admin->google2fa_timestamp = $google2fa_timestamp;
        }
        $admin->save();
        $this->removeLoginLimit($username);

        $admin         = is_object($admin) ? $admin->toArray() : $admin;
        $session       = $request->session();
        $login_address = $loginLogData['isp']." ".$loginLogData['country']." ".$loginLogData['province']." ".$loginLogData['city'];

        $admin['password']           = md5($admin['password']);
        $admin['login_address']      = $login_address;
        $admin['last_login_ip']      = $last_login_ip;
        $admin['last_login_time']    = $last_login_time;
        $admin['last_login_address'] = $last_login_address;
        $admin['is_first_login']     = $isFirstLogin;

        $session->set('admin', $admin);

        $loginLogData['status'] = 1;
        $loginLogData['desc']   = '登录成功：'.json_encode([
                'username' => $username,
                'password' => '******'
            ], JSON_UNESCAPED_UNICODE);
        LogLogin::addRecord($loginLogData);

        return $this->json(200, '登录成功', [
            'nickname'         => $admin['nickname'],
            'token'            => $request->sessionId(),
            'onetime_password' => $onetimePassword
        ]);
    }

    /**
     * 退出
     *
     * @param Request $request
     *
     * @return Response
     */
    public function logout(Request $request): Response
    {
        $request->session()->delete('admin');

        return $this->json(200);
    }

    /**
     * 获取已登录管理员资料（可修改）
     * @return void
     */
    public function index(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('account/userinfo');
        }
        $adminId = get_admin_id();
        $post    = $request->post();
        Admin::where('id', $adminId)->strict(false)->update($post);
        $admin = admin();
        foreach ($post as $key => $v) {
            $admin[$key] = $v;
        }
        $request->session('admin', $admin);

        return $this->success('操作成功');
    }

    /**
     * 获取登录信息
     *
     * @param Request $request
     *
     * @return Response
     */
    public function info(Request $request): Response
    {
        $ip    = get_client_ip();
        $admin = admin();
        if ( ! $admin) {
            return $this->json(0);
        }
        $info = [
            'id'                 => $admin['id'],
            'username'           => $admin['username'],
            'nickname'           => $admin['nickname'],
            'avatar'             => $admin['avatar'],
            'email'              => $admin['email'],
            'mobile'             => $admin['mobile'],
            'isSupperAdmin'      => Auth::isSupperAdmin(),
            'token'              => $request->sessionId(),
            'login_ip'           => $ip,
            'login_address'      => $admin['login_address'],
            'last_login_time'    => $admin['last_login_time'],
            'last_login_ip'      => $admin['last_login_ip'],
            'last_login_address' => $admin['last_login_address'],
            'is_first_login'     => $admin['is_first_login'],
        ];

        return $this->success('ok', $info);
    }

    /**
     * 修改密码
     * @return Response
     */
    public function editPwd(Request $request): Response
    {
        if ($request->method() === 'GET') {
            return view('account/edit_pwd');
        }
        $id   = get_admin_id();
        $post = $request->post();
        if (empty($post['oldpwd'])) {
            return $this->error('原密码不能为空');
        }
        if (empty($post['newpwd'])) {
            return $this->error('新密码不能为空');
        }
        //查询管理员信息
        $findAdmin = Admin::find($id);
        if (empty($findAdmin)) {
            return $this->error('参数错误');
        }
        if ($findAdmin['password'] != cmf_password($post['oldpwd'], $findAdmin['salt'])) {
            return $this->error('原密码不正确');
        }
        $salt                  = Random::alnum(6);
        $findAdmin['password'] = cmf_password($post['newpwd'], $salt);
        $findAdmin['salt']     = $salt;
        $findAdmin->save();

        $admin             = admin();
        $admin['password'] = $findAdmin['password'];
        $admin['salt']     = $findAdmin['salt'];
        $request->session()->set('admin', $admin);

        return $this->success('密码修改成功');
    }

    /**
     * 清除缓存
     * @return Response
     */
    public function clearCache(): Response
    {
        Cache::flush();

        return $this->success('清除缓存成功');
    }

    /**
     * 验证码
     *
     * @param Request $request
     * @param string  $type
     *
     * @return Response
     */
    public function captcha(Request $request, string $type = 'login'): Response
    {
        $builder = new PhraseBuilder(4, 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ');
        $captcha = new CaptchaBuilder(null, $builder);
        $captcha->build(120);
        $request->session()->set("captcha-$type", strtolower($captcha->getPhrase()));
        $img_content = $captcha->get();

        return response($img_content, 200, ['Content-Type' => 'image/jpeg']);
    }

    /**
     * 检查登录频率限制
     *
     * @param $username
     *
     * @return void
     * @throws BusinessException
     */
    protected function checkLoginLimit($username)
    {
        $limit_log_path = runtime_path().'/login';
        if ( ! is_dir($limit_log_path)) {
            mkdir($limit_log_path, 0777, true);
        }
        $limit_file = $limit_log_path.'/'.md5($username).'.limit';
        $time       = date('YmdH').ceil(date('i') / 5);
        $limit_info = [];
        if (is_file($limit_file)) {
            $json_str   = file_get_contents($limit_file);
            $limit_info = json_decode($json_str, true);
        }

        if ( ! $limit_info || $limit_info['time'] != $time) {
            $limit_info = [
                'username' => $username,
                'count'    => 0,
                'time'     => $time
            ];
        }
        $limit_info['count']++;
        file_put_contents($limit_file, json_encode($limit_info));
        if ($limit_info['count'] >= 5) {
            throw new BusinessException('登录失败次数过多，请5分钟后再试！');
        }
    }

    /**
     * 解除登录频率限制
     *
     * @param $username
     *
     * @return void
     */
    protected function removeLoginLimit($username)
    {
        $limit_log_path = runtime_path().'/login';
        $limit_file     = $limit_log_path.'/'.md5($username).'.limit';
        if (is_file($limit_file)) {
            unlink($limit_file);
        }
    }

    protected function checkDatabaseAvailable()
    {
        if ( ! is_file(base_path().'/.env')) {
            throw new BusinessException('请重启webman');
        }
    }

}
