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
namespace app\admin\controller\system;

use app\AdminController;
use app\common\model\system\SystemLog as SystemLogModel;
use think\response\View;

class SystemLog extends AdminController
{
	// 初始化函数
    public function initialize() 
    {
        parent::initialize();
        $this->model = new SystemLogModel();
	}
	
    /**
     * 获取资源列表
     * @return View
     */
    public function index(): View
    {
        if (request()->isAjax()) {
            // 获取数据
            $post = input();
            $page = input('page/d') ?? 1;
            $limit = input('limit/d') ?? 10;
            
            // 生成查询数据
            $where = array();
            if (!empty($post['name'])) {
                $where[] = ['url','like','%'.$post['name'].'%'];
            }

            if (!empty($post['type']) && $post['type'] == 'user') {
                $where[] = ['name','<>','system'];
            }else if (!empty($post['type']) && $post['type'] == 'system') {
                $where[] = ['name','=','system'];
            }

            if (!empty($post['status']) && $post['status'] == 'normal') {
                $where[] = ['error','=',null];
            }else if (!empty($post['status']) && $post['status'] == 'error') {
                $where[] = ['error','<>',''];
            }

            $where[] = ['status','=','1'];
            $count = $this->model->where($where)->count();
            $page = ($count <= $limit) ? 1 : $page;
            $list = $this->model->where($where)->order('id', 'desc')
                                ->limit($limit)->page($page)->select()->toArray();

            return $this->success('查询成功', "", $list, $count);

        }

        return view();
    }
}