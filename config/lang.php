<?php
// +----------------------------------------------------------------------
// | 多语言设置
// +----------------------------------------------------------------------

return [
    // 默认语言
    'default_lang' => env('lang.default_lang', 'zh-CN'),
    // 允许的语言列表
    'allow_lang_list' => ['zh-CN', 'en-US'],
    // 多语言自动侦测变量名
    'detect_var' => 'lang',
    // 是否使用Cookie记录
    'use_cookie' => true,
    // 多语言cookie变量
    'cookie_var' => 'lang',
    // 多语言header变量
    'header_var' => 'lang',

    // 扩展语言包
    'extend_list' => [
        'zh-CN' => [],
    ],

    // Accept-Language转义为对应语言包名称
    'accept_language' => [
        'zh-hans-cn' => 'zh-CN',
    ],

    // 是否支持语言分组
    'allow_group' => false,
];
