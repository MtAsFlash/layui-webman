<?php

use support\Request;
use Webman\Route;

Route::fallback(function (Request $request) {
    // ajax请求时返回json
    if ($request->expectsJson()) {
        return json(['code' => 404, 'msg' => '404 not found']);
    }

    // 页面请求返回404.html模版
    return view('404', ['error' => 'some error'])->withStatus(404);
});
