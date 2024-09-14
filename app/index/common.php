<?php
// 这是系统自动生成的公共文件
use app\common\model\system\UserThird;
use think\facade\Db;

if (!function_exists('check_reply_date')) {
    /**
     * 判断是否为今天
     *
     * @param [type] $time
     * @return bool
     */
    function check_reply_date($time): bool
    {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $time = time() - $time;
        return $time >= 86400;
    }
}

