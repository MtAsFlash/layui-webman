<?php

use Shopwwi\LaravelCache\Cache;
use support\lib\IpToAddress;
use think\facade\Db;

/**
 * 获取系统配置信息
 *
 * @param $key 键值，可为空，为空获取整个数组
 *
 * @return array|string
 */
if (!function_exists('get_config')) {
    function get_config($key = '') {
        $configs = [];
        if (Cache::get('cacheSystemConfig')) {
            $data = Cache::get('cacheSystemConfig');
        } else {
            $data = Db::name('config')->where('status', 1)->select()->toArray();
            Cache::put('cacheSystemConfig', $data);
        }
        foreach ($data as $val) {
            $configs[$val['name']] = $val['value'];
        }
        if (!$key) {
            return $configs;
        } else {
            return array_key_exists($key, $configs) ? $configs[$key] : '';
        }
    }
}

/**
 * 获取客户端IP地址
 *
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
 *
 * @return string
 */
if (!function_exists('get_client_ip')) {
    function get_client_ip($type = 0, $adv = true) {
        return request()->getRealIp($safe_mode = true);
    }
}

/**
 * 根据IP地址判断地区
 *
 * @param $clientIP
 *
 * @return string
 */
function getIpToArea($clientIP) {
    $ipToAddress = new IpToAddress();
    $res = $ipToAddress->ipToAddress($clientIP);

    return $res;
}

/**
 * 打印各种类型的数据，调试程序时使用。
 *
 * @param mixed $var 变量
 *
 * @return void or string
 */
if (!function_exists('dump')) {
    function dump($var) {
        ob_start();
        var_dump($var);
        $output = ob_get_clean();
        if (!extension_loaded('xdebug')) {
            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
        }

        echo $output;

        return null;
    }
}

/**
 * 获取某个composer包的版本
 *
 * @param string $package
 *
 * @return mixed|string
 */
function getPackageVersion(string $package) {
    $installed_php = base_path('vendor/composer/installed.php');
    if (is_file($installed_php)) {
        $packages = include $installed_php;
    }

    return substr($packages['versions'][$package]['version'] ?? 'unknown  ', 0, -2);
}
