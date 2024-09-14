<?php
declare (strict_types = 1);
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
use app\common\library\Upload;
use app\common\model\cms\Tags;

/**
 * Ajax类
 * Class Ajax
 * @package app\admin\controller
 */
class Ajax extends AdminController
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 文件上传
     */
	public function upload()
	{
        if (request()->isPost()) {
            try {
                $uploadFiles = Upload::instance()->upload();
            } catch (\Exception $e) {}
            if (!empty($uploadFiles)) {
                return json($uploadFiles);
            }
            return $this->error(Upload::instance()->getError());
        }

        $this->error('参数错误');
	}

    /**
     * 远程下载图片
     */
    public function getImage()
    {
        if (request()->isPost()) {

            $uploadFiles = Upload::instance()->download(input('url'));
            if (!empty($uploadFiles)) {
                return json($uploadFiles);
            }

            return $this->error(Upload::instance()->getError());
        }
        $this->error('参数错误');
    }

    /**
     * 获取关键词
     */
    public function getTags()
    {
        $tags = input('tag', '');
        if (!empty($tags)) {
            $name = preg_match('/[a-zA-Z]/', $tags) ? 'pinyin' : 'name';
            $list = Tags::field('name')->where($name, 'like', '%' . $tags . '%')->limit(10)->select()->toArray();
            return $this->success('获取成功', null, $list, count($list));
        }
        return $this->error('参数错误');
    }

}
