<?php
declare (strict_types=1);

namespace app\common\middleware;

use think\Request;

class AntiGarbageReptile
{
    /**
     * 全局中间件
     * 屏蔽垃圾爬虫
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $user_agent = request()->header()['user-agent'] ?? '';
        if (preg_match('/DataForSeoBot|SemrushBot|DotBot|MJ12bot|PetalBot|AhrefsBot/i', $user_agent)) {
            abort(403, '403 Forbidden');
        }

        return $next($request);
    }
}