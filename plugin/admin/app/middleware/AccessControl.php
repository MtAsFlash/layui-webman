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

use ReflectionException;
use support\exception\BusinessException;
use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use plugin\admin\api\Auth;

class AccessControl implements MiddlewareInterface
{

    /**
     * @param Request  $request
     * @param callable $handler
     *
     * @return Response
     * @throws ReflectionException|BusinessException
     */
    public function process(Request $request, callable $handler): Response
    {
        $controller = $request->controller;
        $action     = $request->action;
        $getMethod  = $request->method();

        // 判断是否为演示环境，不验证登录
        if (getenv('IS_DEMO', false) === "true" && $getMethod === 'POST') {
            if ($controller !== 'plugin\admin\app\controller\AccountController' || $action !== 'login') {
                return json(['code' => 0, 'msg' => '演示环境不允许修改']);
            }
        }

        $code = 0;
        $msg  = '';
        if ( ! Auth::canAccess($controller, $action, $code, $msg)) {
            if ($request->expectsJson()) {
                $response = json(['code' => $code, 'msg' => $msg, 'data' => []]);
            } else {
                return response(view('401', [
                    'message'     => $msg,
                    'redirectUrl' => '/app/admin',
                ]), 401);
                //$response = \response($msg, 401);
            }
        } else {
            $response = $request->method() == 'OPTIONS' ? response('') : $handler($request);
        }

        return $response;
    }
}
