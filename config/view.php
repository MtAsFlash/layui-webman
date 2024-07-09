<?php

use support\view\ThinkPHP;

return [
    'handler' => ThinkPHP::class,
    'options' => [
        'view_suffix' => 'html',
        'tpl_begin' => '{',
        'tpl_end' => '}',
        'tpl_replace_string' => [
            '__STATIC_ADMIN__' => '/static/admin',
            '__LIB__' => '/static/lib',
        ],
    ],
];
