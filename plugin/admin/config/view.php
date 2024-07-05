<?php

use support\view\Raw;
use support\view\Twig;
use support\view\Blade;
use support\view\ThinkPHP;

return [
    'handler' => ThinkPHP::class,
    'options' => [
        'view_suffix'        => 'html',
        'tpl_begin'          => '{',
        'tpl_end'            => '}',
        'tpl_replace_string' => [
            '__STATIC_ADMIN__' => '/app/admin/admin',
            '__LIB__'          => '/app/admin/lib',
        ]
    ]
];
