<?php

declare(strict_types=1);
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

use app\common\library\SpiderLog;
use app\common\service\user\UserService;
use app\common\service\user\UserTokenService;
use think\response\View;
/*
 * 前台全局控制器基类
 */
class HomeController extends BaseController
{
    /**
     * 控制器/类名
     * @var string
     */
    public string $controller;

    /**
     * 控制器方法
     * @var string
     */
    public string $action;

    /**
     * 跳转URL地址
     * @var string
     */
    public string $JumpUrl = '/index';

    /**
     * 初始化函数
     */
    public function initialize()
    {
        parent::initialize();

        // 获取请求控制器
        $this->action = request()->action();
        $this->controller = request()->controller();

        // 全域蜘蛛爬虫日志
        SpiderLog::SpiderTraceLogs();
    }

    /**
     * 视图过滤
     *
     * @param string $template
     * @param array $argc
     * @return View
     */
    public function view(string $template = '', array $argc = []): View
    {
        return view($template, $argc)->filter(function ($content) {
            if (saenv('compression_page')) {
                $content = preg_replace('/\s+/i', ' ', $content);
            }
            return $content;
        });
    }
}
