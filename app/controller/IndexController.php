<?php

namespace app\controller;

use support\Request;

class IndexController {

    public function index(Request $request) {
        $packVersion = getPackageVersion('workerman/webman-framework');
        $version = $packVersion;

        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p>WebmanFramework -' . $version . '<br/><span style="font-size:30px;">webman是一款基于workerman开发的高性能HTTP服务框架。</span></p><span style="font-size:25px;">Huicmf_webman-V2</span></div></think>';
    }

    public function json(Request $request) {
        return json(['code' => 0, 'msg' => 'ok']);
    }

}
