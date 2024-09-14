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
use app\common\model\system\Jobs as JobsModel;
use think\response\View;

class Jobs extends AdminController
{
	// 初始化函数
    public function initialize()
	{
		parent::initialize();
        $this->model = new JobsModel();
	}
	
    /**
     * 获取资源列表
     * @return View
     */
    public function index(): View
    {
		if (request()->isAjax()) {
			
			$param = request()->param();
			$param['page'] = input('page/d',1);
			$param['limit'] = input('limit/d',18);

			// 查询条件
			$where =[];
			if (!empty($param['title'])) {
				$where[] = ['title','like','%'.$param['title'].'%'];
			}
			if (!empty($param['alias'])) {
				$where[] = ['alias','like','%'.$param['alias'].'%'];
			}
			if (!empty($param['content'])) {
				$where[] = ['content','like','%'.$param['content'].'%'];
			}

			// 查询数据
            $count = $this->model->where($where)->count();
            $limit = is_empty($param['limit']) ? 10 : $param['limit'];
            $page = ($count <= $limit) ? 1 : $param['page'];
			$list = $this->model->where($where)->order("id asc")->limit($limit)->page($page)->select()->toArray();
			foreach ($list as $key => $value) {
				$list[$key]['title'] = __($value['title']);
			}

			return $this->success('查询成功', '/', $list, $count);
		}

		return view();
	}

	/**
	 * 添加岗位数据
     * @return View
	 */
	public function add(): View
    {
		if (request()->isPost()) {
			$post = request()->post();
			if ($this->model->create($post)) {
				return $this->success('添加岗位成功！');
			}
		}
        return $this->error('添加岗位失败！');
	}

	/**
	 * 编辑岗位数据
     * @return View
	 */
	public function edit(): View
    {
		if (request()->isPost()) {
            $post = request()->post();
			if ($this->model->update($post)) {				
				return $this->success('更新岗位成功！');
			}
		}
        return $this->error('更新岗位失败');
	}

	/**
	 * 删除岗位数据
     * @return View
	 */
	public function del(): View
    {
		$id = input('id');
		if (!empty($id) && is_numeric($id)) {
			if ($this->model::destroy($id)) {
				return $this->success('删除岗位成功！');
			}
		}
		
		return $this->error('删除失败，请检查您的参数！');
	}
}