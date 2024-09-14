<?php
declare (strict_types=1);
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


use app\AdminController;
use think\response\View;


class Tpl extends AdminController
{
    /**
     * 读取模板列表
     * @return View
     */
    public function showTpl(): View
    {
        // 读取配置文件
        $list = include(root_path() . 'extend/conf/tpl/tpl.php');
        foreach ($list as $key => $value) {
            $list[$key]['param'] = str_replace('extend/conf/tpl/', '', $value['path']);
        }

        return view('', ['list' => $list]);
    }

    /**
     * 编辑邮件模板
     * @return View
     */
    public function editTpl(): View
    {
        $tplPath = root_path() . 'extend/conf/tpl/';
        $files = glob($tplPath . '*.tpl');
        $files = array_map(function ($file) {
            return basename($file);
        }, $files);

        if (request()->isPost()) {
            $post = request()->post();
            $file = $post['tpl'];
            if (!in_array($file, $files)) {
                return $this->error('模板文件不存在！');
            }
            $tpl = $tplPath . $file;
            if (write_file($tpl,$post['content'])) {
                return $this->success('修改邮件模板成功！');
            }

            return $this->error('修改邮件模板失败！');
        }

        // 获取模板参数
        $tpl = input('p');
        if (!in_array($tpl, $files)) {
            return $this->error('模板文件不存在！');
        }
        $content = read_file($tplPath . $tpl);

        return view('', ['tpl' => $tpl, 'content' => $content]);
    }

}
