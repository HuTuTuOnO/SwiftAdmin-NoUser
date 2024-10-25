<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

return [
    // 应用地址
    'app_host'         => env('app.host', ''),

    // 应用的命名空间
    'app_namespace'    => '',

    // 是否启用路由
    'with_route'       => true,

    // 默认应用
    'default_app'      => 'index',

    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [],

    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [
        'api'        =>  'api',
        'doc'        =>  'doc',
        'ask'        =>  'ask',
        '*'          =>  'index',
    ],

    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ['common'],

    // 自定义页面abort抛出异常
    'http_exception_template'    =>  [
        404 =>  public_path() . '404.html',
        401 =>  public_path() . '401.html',
        500 =>  public_path() . '500.html',
    ],

    // 默认跳转页面对应的模板文件
    'dispatch_error'   => app_path() . 'admin/view/public/jumptpl.html',
    'dispatch_success' => app_path() . 'admin/view/public/jumptpl.html',

    // 异常页面的模板文件
    'exception_tmpl'   => root_path() . 'extend/system/exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',

    // 框架版本
    'version'          => 'V20241026',

    // 跨域请求域名
    'cors_domain'      => ['*', '127.0.0.1','localhost'],

    // 显示错误信息
    'show_error_msg'   => false,
];
