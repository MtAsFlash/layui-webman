<?php

return [
    [
        'title'    => '会员管理',
        'icon'     => 'layui-icon-user',
        'key'      => 'user',
        'pid'      => 0,
        'href'     => '',
        'weight'   => 100,
        'type'     => 0,
        'children' => [
            [
                'title'  => '用户管理',
                'icon'   => 'layui-icon-group',
                'key'    => 'plugin\\admin\\app\\controller\\UserController',
                'href'   => '/app/admin/user/index',
                'weight' => 100,
                'type'   => 1
            ]
        ]
    ],
];
