<?php

namespace app\api\middleware;

use Closure;
use think\Config;
use think\Request;
use think\Response;

/**
 * 跨域请求支持
 * nginx配置：
 * location / {
 *    if ($request_method = 'OPTIONS') {
 *       add_header 'Access-Control-Allow-Origin' '*';
 *      add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS, PUT, DELETE, PATCH';
 *     add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With';
 *    add_header 'Access-Control-Max-Age' 1728000;
 *   add_header 'Content-Type' 'text/plain charset=UTF-8';
 * add_header 'Content-Length' 0;
 * return 204;
 * }
 * apache配置：
 * Header set Access-Control-Allow-Origin "*"
 * Header set Access-Control-Allow-Methods "GET, POST, OPTIONS, PUT, DELETE, PATCH"
 * Header set Access-Control-Allow-Headers "Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With"
 * Header set Access-Control-Max-Age 1728000
 */
class AllowCrossDomain extends \think\middleware\AllowCrossDomain
{
    /**
     * @var array
     */
    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, Token, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-CSRF-TOKEN, X-Requested-With',
    ];
}
