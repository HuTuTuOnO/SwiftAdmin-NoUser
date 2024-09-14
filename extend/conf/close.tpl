<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 网站关闭文件 ]
namespace think;

// 读取文件内容
$site = include('../config/system.php');
$content = $site['site']['site_notice'];
$content = html_entity_decode($content);

// 自定义HTML模板
if ($content == 'html') { 
    $file = '../extend/conf/close.html';
    if (!is_file($file)) {
        exit('not close.html file');
    }
    $content = file_get_contents($file);
    $content = str_replace('#email',$site['site']['site_email'],$content);
    exit($content);
}

exit($content);