<?php

namespace app\admin\service;

use app\admin\enums\AdminEnum;
use app\common\exception\OperateException;
use app\common\library\ResultCode;
use app\common\model\system\Admin;
use app\common\model\system\AdminLog;
use think\facade\Event;

class LoginService
{

    /**
     * 管理员登录
     * @param string $name
     * @param string $pwd
     * @param string $captcha
     * @param array $adminInfo
     * @return bool
     * @throws OperateException
     */
    public static function accountLogin(string $name, string $pwd, string $captcha = '', array $adminInfo = []): bool
    {
        $countLimit = isset($adminInfo['count']) && $adminInfo['count'] >= 5;
        $minuteLimit = isset($adminInfo['time']) && $adminInfo['time'] >= strtotime('- 5 minutes');
        if ($countLimit && $minuteLimit) {
            throw new OperateException('错误次数过多，请稍后再试！');
        }

        // 验证码
        if (isset($adminInfo['isCaptcha']) && !captcha_check($captcha)) {
            throw new OperateException('验证码错误！');
        }

        $result = Admin::checkLogin($name, $pwd);
        if (empty($result)) {
            $adminInfo['time'] = time();
            $adminInfo['isCaptcha'] = true;
            $adminInfo['count'] = isset($adminInfo['count']) ? $adminInfo['count'] + 1 : 1;
            session(AdminEnum::ADMIN_SESSION, $adminInfo);
            Event::trigger(AdminEnum::ADMIN_LOGIN_ERROR, input());
            self::writeAdminLogs($name, ResultCode::USPWDERROR['msg']);
            throw new OperateException(ResultCode::USPWDERROR['msg'], ResultCode::USPWDERROR['code']);
        }

        if ($result['status'] !== 1) {
            throw new OperateException(ResultCode::STATUSEXCEPTION['msg'], ResultCode::STATUSEXCEPTION['code']);
        }

        try {
            $data['login_ip'] = get_real_ip();
            $data['login_time'] = time();
            $data['count'] = $result['count'] + 1;
            Admin::update($data, ['id' => $result['id']]);
            session(AdminEnum::ADMIN_SESSION, $result);
            self::writeAdminLogs($name, ResultCode::LOGINSUCCESS['msg'], 1);
            Event::trigger(AdminEnum::ADMIN_LOGIN_SUCCESS, $result);
        } catch (\Throwable $th) {
            throw new OperateException($th->getMessage());
        }

        return true;
    }

    /**
     * 记录登录日志
     * @param string $name
     * @param string $error
     * @param int $status
     * @return void
     */
    public static function writeAdminLogs(string $name, string $error, int $status = 0): void
    {
        $userAgent = request()->header('user-agent');
        $nickname = (new Admin)->where('name', $name)->value('nickname');
        preg_match('/.*?\((.*?)\).*?/', $userAgent, $matches);
        $user_os = isset($matches[1]) ? substr($matches[1], 0, strpos($matches[1], ';')) : 'unknown';
        $user_browser = preg_replace('/[^(]+\((.*?)[^)]+\) .*?/', '$1', $userAgent);
        $data['name'] = $name;
        $data['nickname'] = $nickname ?? 'unknown';
        $data['user_ip'] = get_real_ip();
        $data['user_agent'] = $userAgent;
        $data['user_os'] = $user_os;
        $data['user_browser'] = $user_browser;
        $data['error'] = $error;
        $data['status'] = $status;
        AdminLog::create($data);
    }
}