<?php
declare (strict_types=1);

namespace app\index\middleware;

use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\Request;

class Template
{
    /**
     * 处理请求
     *
     * @param Request $request
     * @param \Closure $next
     * @return Request
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function handle($request, \Closure $next)
    {
        /**
         * 处理前端模板
         */
        if (saenv('site_state')) {

            $site_type = saenv('site_type');
            $template  = root_path('app/mobile/view');
            // 应用独立域名访客
            $site_hosts  = request()->host();
            $site_mobile = saenv('site_mobile');
            $host_mobile = parse_url($site_mobile)['host'] ?? '';
            if (!empty($site_mobile) && $host_mobile == $site_hosts) {
                app()->view->config(['view_path' => $template]);
            } else if ($site_type == 0 && request()->isMobile()) {
                // 代码适配规则
                app()->view->config(['view_path' => $template]);
            }
        }

        return $next($request);
    }
}
