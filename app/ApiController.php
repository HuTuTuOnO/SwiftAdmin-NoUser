<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于ThinkPHP6开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.NET High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License Code
// +----------------------------------------------------------------------
namespace app;


use app\common\library\ResultCode;
use app\common\service\user\UserService;
use app\common\service\user\UserTokenService;
use think\exception\HttpResponseException;

/**
 * Api全局控制器基类
 * Class ApiController
 * @package app
 */
class ApiController extends BaseController
{
    /**
     * 控制器/类名
     * @var string
     */
    public string $controller;

    /**
     * 控制器方法
     */
    public string $action;

    /**
     * 初始化
     * @access protected
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 操作成功跳转
     * @access protected
     * @param string $msg
     * @param mixed $url
     * @param mixed $data
     * @param int $count
     * @param int $code
     * @param int $wait
     * @param array $header
     */
    protected function success(string $msg = '',mixed $url = '',mixed $data = '', int $count = 0,  int $code = 200, int $wait = 3, array $header = [])
    {
        $msg = !empty($msg) ? __($msg) : __('操作成功！');
        $result = ['code' => $code, 'msg' => $msg, 'data' => $data, 'count' => $count, 'url' => (string)$url, 'wait' => $wait];
        throw new HttpResponseException(json($result));
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $msg 提示信息
     * @param mixed $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param int $code
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     */
    protected function error(string $msg = '',mixed $url = '', mixed $data = '', int $code = 101, int $wait = 3, array $header = [])
    {
        $msg = !empty($msg) ? __($msg) :  __('操作失败！');
        $result = ['code' => $code, 'msg'  => $msg, 'data' => $data, 'url'  =>(string)$url, 'wait' => $wait];
        throw new HttpResponseException(json($result));
    }

}
