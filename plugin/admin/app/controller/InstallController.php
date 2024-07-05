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
use Webman\Captcha\CaptchaBuilder;
use support\exception\BusinessException;
use support\lib\Random;

class InstallController extends Base
{

    /**
     * 不需要登录的方法
     * @var string[]
     */
    protected $noNeedLogin = ['step1', 'step2'];

    /**
     * 设置数据库
     *
     * @param Request $request
     *
     * @return Response
     * @throws BusinessException|\Throwable
     */
    public function step1(Request $request): Response
    {
        $env_config_file = base_path().'/.env';
        clearstatcache();
        if (is_file($env_config_file)) {
            return $this->json(0, '管理后台已经安装！如需重新安装，请删除该.env配置文件并重启');
        }
        if ( ! class_exists(CaptchaBuilder::class)) {
            return $this->json(0, '请先restart重启webman后再进行此页面的设置');
        }
        $user      = $request->post('user');
        $password  = $request->post('password');
        $database  = $request->post('database');
        $host      = $request->post('host');
        $port      = (int)$request->post('port') ?: 3306;
        $prefix    = $request->post('prefix', 'cmf_');
        $overwrite = $request->post('overwrite');
        try {
            $db  = $this->getPdo($host, $user, $password, $port);
            $smt = $db->query("show databases like '$database'");
            if (empty($smt->fetchAll())) {
                $db->exec("create database $database");
            }
            $db->exec("use $database");
            $smt    = $db->query("show tables");
            $tables = $smt->fetchAll();
        } catch (\Throwable $e) {
            if (stripos($e, 'Access denied for user')) {
                return $this->json(0, '数据库用户名或密码错误');
            }
            if (stripos($e, 'Connection refused')) {
                return $this->json(0, 'Connection refused. 请确认数据库IP端口是否正确，数据库已经启动');
            }
            if (stripos($e, 'timed out')) {
                return $this->json(0, '数据库连接超时，请确认数据库IP端口是否正确，安全组及防火墙已经放行端口');
            }
            throw $e;
        }

        $tables_to_install = [
            'cmf_admin',
            'cmf_admin_role',
            'cmf_config',
            'cmf_dict',
            'cmf_role',
            'cmf_rule',
            'cmf_upload_file',
            'cmf_log_login',
            'cmf_log_system',
        ];

        $tables_exist = [];
        foreach ($tables as $table) {
            $tables_exist[] = current($table);
        }
        $tables_conflict = array_intersect($tables_to_install, $tables_exist);

        if ( ! $overwrite) {
            if ($tables_conflict) {
                return $this->json(0, '以下表'.implode(',', $tables_conflict).'已经存在，如需覆盖请选择强制覆盖');
            }
        } else {
            foreach ($tables_conflict as $table) {
                $db->exec("DROP TABLE `$table`");
            }
        }
        $sql_file = base_path().'/plugin/admin/install.sql';
        if ( ! is_file($sql_file)) {
            return $this->json(0, '数据库SQL文件不存在');
        }
        $sql_query = file_get_contents($sql_file);
        $sql_query = $this->removeComments($sql_query);
        $sql_query = $this->splitSqlFile($sql_query, ';', $prefix);
        foreach ($sql_query as $sql) {
            $db->exec($sql);
        }
        $envConfigFile = <<<EOT
DB_HOST = $host
DB_PORT = $port
DB_NAME = $database
DB_USER = $user
DB_PASSWORD = $password
DB_PREFIX = $prefix
IS_DEMO = false

EOT;
        file_put_contents($env_config_file, $envConfigFile);
        // 尝试reload
        if (function_exists('posix_kill')) {
            set_error_handler(function () {
            });
            posix_kill(posix_getppid(), SIGUSR1);
            restore_error_handler();
        }

        return $this->json(200);
    }

    /**
     * 设置管理员
     *
     * @param Request $request
     *
     * @return Response
     * @throws BusinessException
     */
    public function step2(Request $request): Response
    {
        $username         = $request->post('username');
        $password         = $request->post('password');
        $password_confirm = $request->post('password_confirm');
        if ($password != $password_confirm) {
            return $this->json(0, '两次密码不一致');
        }
        if ( ! is_file($config_file = base_path().'/.env')) {
            return $this->json(0, '请先完成第一步数据库配置');
        }
        $env_host     = getenv('DB_HOST');
        $env_username = getenv('DB_USER');
        $env_password = getenv('DB_PASSWORD');
        $env_port     = getenv('DB_PORT');
        $env_database = getenv('DB_NAME');
        $env_prefix   = getenv('DB_PREFIX');

        $pdo = $this->getPdo($env_host, $env_username, $env_password, $env_port, $env_database);
        if ($pdo->query('select * from `'.$env_prefix.'admin`')->fetchAll()) {
            return $this->json(0, '后台已经安装完毕，无法通过此页面创建管理员');
        }
        $smt  = $pdo->prepare("INSERT INTO `".$env_prefix."admin` ( `username`,`nickname`, `password`, `salt`, `create_time`, `update_time`, `status`) VALUES (:username,:nickname,:password,:salt,:create_time,:update_time,:status)");
        $time = time();
        $salt = Random::alnum();
        $data = [
            'username'    => $username,
            'nickname'    => '超级管理员',
            'password'    => cmf_password($password, $salt),
            'salt'        => $salt,
            'create_time' => $time,
            'update_time' => $time,
            'status'      => 1
        ];
        foreach ($data as $key => $value) {
            $smt->bindValue($key, $value);
        }
        $smt->execute();
        $admin_id = $pdo->lastInsertId();
        $smt      = $pdo->prepare("insert into `".$env_prefix."admin_role` (`role_id`, `admin_id`) values (:role_id, :admin_id)");
        $smt->bindValue('role_id', 1);
        $smt->bindValue('admin_id', $admin_id);
        $smt->execute();
        $request->session()->flush();

        return $this->json(200);
    }

    /**
     * 去除sql文件中的注释
     *
     * @param $sql
     *
     * @return string
     */
    protected function removeComments($sql): string
    {
        return preg_replace("/(\n--[^\n]*)/", "", $sql);
    }

    /**
     * 分割sql文件
     *
     * @param $sql
     * @param $delimiter
     *
     * @return array
     */
    function splitSqlFile($sql, $delimiter, $sqlPrefix = 'cmf_'): array
    {
        //替换表前缀
        $sql = str_replace(["`hui_", "`cmf_"], "`{$sqlPrefix}", $sql);

        $tokens      = explode($delimiter, $sql);
        $output      = array();
        $matches     = array();
        $token_count = count($tokens);
        for ($i = 0; $i < $token_count; $i++) {
            if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
                $total_quotes     = preg_match_all("/'/", $tokens[$i], $matches);
                $escaped_quotes   = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);
                $unescaped_quotes = $total_quotes - $escaped_quotes;

                if (($unescaped_quotes % 2) == 0) {
                    $output[]   = $tokens[$i];
                    $tokens[$i] = "";
                } else {
                    $temp       = $tokens[$i].$delimiter;
                    $tokens[$i] = "";

                    $complete_stmt = false;
                    for ($j = $i + 1; ( ! $complete_stmt && ($j < $token_count)); $j++) {
                        $total_quotes     = preg_match_all("/'/", $tokens[$j], $matches);
                        $escaped_quotes   = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);
                        $unescaped_quotes = $total_quotes - $escaped_quotes;
                        if (($unescaped_quotes % 2) == 1) {
                            $output[]      = $temp.$tokens[$j];
                            $tokens[$j]    = "";
                            $temp          = "";
                            $complete_stmt = true;
                            $i             = $j;
                        } else {
                            $temp       .= $tokens[$j].$delimiter;
                            $tokens[$j] = "";
                        }

                    }
                }
            }
        }

        return $output;
    }

    /**
     * 获取pdo连接
     *
     * @param $host
     * @param $username
     * @param $password
     * @param $port
     * @param $database
     *
     * @return \PDO
     */
    protected function getPdo($host, $username, $password, $port, $database = null): \PDO
    {
        $dsn = "mysql:host=$host;port=$port;";
        if ($database) {
            $dsn .= "dbname=$database";
        }
        $params = [
            \PDO::MYSQL_ATTR_INIT_COMMAND       => "set names utf8mb4",
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            \PDO::ATTR_EMULATE_PREPARES         => false,
            \PDO::ATTR_TIMEOUT                  => 5,
            \PDO::ATTR_ERRMODE                  => \PDO::ERRMODE_EXCEPTION,
        ];

        return new \PDO($dsn, $username, $password, $params);
    }

}
