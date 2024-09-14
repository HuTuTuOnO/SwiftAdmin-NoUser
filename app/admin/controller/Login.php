<?php

declare(strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于ThinkPHP6开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License Code
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\enums\AdminEnum;
use app\admin\service\LoginService;
use app\AdminController;
use app\common\exception\OperateException;
use app\common\model\system\Admin;
use think\response\View;

/**
 * 管理员登录
 * Class Login
 * @package app\admin\controller
 */
class Login extends AdminController
{
    protected function initialize()
    {
        $this->model = new Admin();
        $this->JumpUrl = request()->server()['SCRIPT_NAME'];
        $this->adminInfo = session(AdminEnum::ADMIN_SESSION) ?? [];
    }

    /**
     * 管理员登录
     * @return View
     * @throws OperateException
     */
    public function index(): View
    {
        if (request()->isPost()) {
            $user = input('name');
            $pwd = input('pwd');
            $captcha = input('captcha');
            validate(\app\common\validate\system\Admin::class)->scene('login')->check([
                'name' => $user,
                'pwd'  => $pwd,
            ]);
            LoginService::accountLogin($user, $pwd, $captcha, $this->adminInfo);
            return $this->success('登录成功！', $this->JumpUrl);
        }

        return view('', [
            'captcha' => $this->adminInfo['isCaptcha'] ?? false,
        ]);
    }

    /**
     * 管理员退出
     */
    public function logout()
    {
        session(AdminEnum::ADMIN_SESSION, null);
        $this->success('退出成功！', $this->JumpUrl);
    }
}
