<?php

use plugin\admin\app\model\User;
use plugin\admin\app\model\Admin;
use plugin\admin\app\model\AdminRole;
use plugin\admin\app\model\LogLogin;

/**
 * 当前管理员id
 * @return integer|null
 */
function get_admin_id(): ?int
{
    return session('admin.id');
}

function get_admin_group_id(): ?int
{
    return session('admin.id');
}

/**
 * 当前管理员
 *
 * @param null|array|string $fields
 *
 * @return array|mixed|null
 */
function admin($fields = null)
{
    refresh_admin_session();
    if ( ! $admin = session('admin')) {
        return null;
    }
    if ($fields === null) {
        return $admin;
    }
    if (is_array($fields)) {
        $results = [];
        foreach ($fields as $field) {
            $results[$field] = $admin[$field] ?? null;
        }

        return $results;
    }

    return $admin[$fields] ?? null;
}

/**
 * 刷新当前管理员session
 *
 * @param bool $force
 *
 * @return void
 */
function refresh_admin_session(bool $force = false)
{
    $admin_session = session('admin');
    if ( ! $admin_session) {
        return null;
    }
    $admin_id = $admin_session['id'];
    $time_now = time();
    // session在2秒内不刷新
    $session_ttl              = 2;
    $session_last_update_time = session('admin.session_last_update_time', 0);
    if ( ! $force && $time_now - $session_last_update_time < $session_ttl) {
        return null;
    }
    $session = request()->session();
    $admin   = Admin::find($admin_id);
    if ( ! $admin) {
        $session->forget('admin');

        return null;
    }

    $admin = $admin->toArray();
    // 修改密码后
    $admin['password']         = md5($admin['password']);
    $admin_session['password'] = $admin_session['password'] ?? '';
    if ($admin['password'] != $admin_session['password']) {
        $session->forget('admin');

        return null;
    }

    // 账户被禁用
    if ($admin['status'] != 1) {
        $session->forget('admin');

        return;
    }
    $lastLoginLog                = LogLogin::getLastLoginInfo($admin_id);
    $admin['roles']              = AdminRole::where('admin_id', $admin_id)->column('role_id');
    $admin['login_address']      = $admin_session['login_address'] ?? '';
    $admin['last_login_ip']      = $admin_session['last_login_ip'] ?? '';
    $admin['last_login_time']    = $admin_session['last_login_time'] ?? "";
    $admin['last_login_address'] = $admin_session['last_login_address'] ?? '';
    $admin['is_first_login']     = $admin_session['is_first_login'] ?? false;

    $admin['session_last_update_time'] = $time_now;
    $session->set('admin', $admin);
}

/**
 * CMF密码加密方法
 *
 * @param string $pw       要加密的原始密码
 * @param string $authCode 加密字符串,salt
 *
 * @return string
 */
if ( ! function_exists('cmf_password')) {
    function cmf_password($pw, $authCode = 'huicmf_webman_new')
    {
        $result = md5('#####'.md5($pw).$authCode);

        return $result;
    }
}
if ( ! function_exists('cmf_dir_path')) {
    /**
     * 列出目录下所有文件
     *
     * @param string $path 路径
     *
     * @return  array  所有满足条件的文件
     */
    function cmf_dir_path($path)
    {
        $path = str_replace('\\', '/', $path);
        if (substr($path, -1) != '/') {
            $path = $path.'/';
        }

        return $path;
    }
}
if ( ! function_exists('cmf_dir_list')) {
    /**
     * 列出目录下的所有文件
     *
     * @param str   $path 目录
     * @param str   $exts 后缀名，不要带点
     * @param array $list 路径数组
     *
     * @return array 返回路径数组
     */
    function cmf_dir_list($path, $exts = '', $list = array())
    {
        $path  = cmf_dir_path($path);
        $files = glob($path.'*');
        foreach ($files as $v) {
            if ( ! $exts || preg_match("/\.($exts)/i", $v)) {
                $list[] = $v;
                if (is_dir($v)) {
                    $list = cmf_dir_path($v, $exts, $list);
                }
            }
        }

        return $list;
    }
}
