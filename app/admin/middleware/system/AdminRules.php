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

namespace app\admin\middleware\system;

use app\common\library\ResultCode;
use app\common\model\system\AdminRules as AdminRulesModel;
use app\common\model\system\SystemLog;

class AdminRules
{
  /**
   * 处理请求
   *
   * @param \think\Request $request
   * @param \Closure       $next
   * @return Response
   */
  public function handle($request, \Closure $next)
  {

    if (saenv('system_alogs')) {
      $array = [
        'module'        => app()->http->getName(),
        'controller'    => request()->controller(true),
        'action'        => request()->action(true),
        'params'        => serialize(request()->param()),
        'method'        => request()->method(),
        'url'           => request()->baseUrl(),
        'ip'            => get_real_ip(),
        'name'          => session('AdminLogin.name'),
      ];

      if (empty($array['name'])) {
        $array['name'] = 'system';
      }

      $array['type'] = 2;
      SystemLog::write($array);
    }

    return $next($request);
  }
}
