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

namespace plugin\admin\app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use plugin\admin\app\common\Util;
use plugin\admin\app\model\LogSystem;
use plugin\admin\app\model\Rule;
use Shopwwi\LaravelCache\Cache;

/**
 * 系统操作日志
 */
class SystemLogControl implements MiddlewareInterface
{

    public function process(Request $request, callable $handler): Response
    {
        $adminId = get_admin_id();
        if (empty($adminId)) {
            return $handler($request);
        }
        $method = strtolower($request->method());
        $params = $request->all();
        //去除排查的路由节点
        $extraPath = [

        ];
        if (in_array($method, ['post', 'put', 'delete']) && ! in_array($request->path(), $extraPath)) {
            //查询权限数据库获取权限名称列表
            $titleContro = "";
            $title       = "";

            if (Cache::has('cacheRuleLists')) {
                $ruleList = Cache::get('cacheRuleLists');
            } else {
                $ruleList = (new Rule)->getRuleLists(['type' => 1], 'title,key');
                $ruleList = array_column($ruleList, null, 'key');
                Cache::put('cacheRuleLists', $ruleList);
            }

            $controller = $request->controller;
            $action     = $request->action;
            if ($controller) {
                $reflection = new \ReflectionClass($controller);
                $properties = $reflection->getDefaultProperties();
                $methods    = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($methods as $me) {
                    $method_name = $me->getName();
                    if ($method_name === $action) {
                        $title = Util::getCommentFirstLine($me->getDocComment()) ?: $method_name;
                    }
                }
                if ( ! empty($ruleList[$controller]['title'])) {
                    $titleContro = $ruleList[$controller]['title']."-";
                }
            }
            //如果是修改密码，数据加密
            $uri = $request->uri();
            if ($uri === '/app/admin/account/editPwd') {
                $params = ['data' => "*******"];
            }
            foreach ($params as $key => $v) {
                if ($key === 'password') {
                    $params[$key] = "******";
                }
            }

            $ip   = get_client_ip();
            $data = [
                'admin_id' => $adminId,
                'url'      => $uri,
                'title'    => $titleContro.$title,
                'method'   => $method,
                'ip'       => $ip,
                'content'  => json_encode($params, JSON_UNESCAPED_UNICODE),
            ];
            if (get_config('admin_log')) {
                LogSystem::create($data);
            }
        }

        return $handler($request);
    }
}
