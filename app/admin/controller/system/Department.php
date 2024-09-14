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
use app\common\model\system\Department as DepartmentModel;
use think\response\View;

class Department extends AdminController
{
	// 初始化函数
    public function initialize() {
		parent::initialize();
        $this->model = new DepartmentModel();
	}
	
    /**
     * 获取资源列表
     * @return View
     */
    public function index(): View
    {
		if (request()->isAjax()) {

            // 查询参数
            $where = array();
            $post['head'] = input('head');
            if (!empty($post['head'])) {
                $where[] = ['head','like','%'.$post['head'].'%'];
            }

            // 获取总数
            $total = $this->model->count();
            $list = $this->model->where($where)->order('sort asc')->select()->toArray();
            foreach ($list as $key => $value) {
                $list[$key]['title'] = __($value['title']);
                $list[$key]['datas'] = $this->model->getListTree();
            }

            // 自定义查询
            if (count($list) < $total) {
                $parentNode = [];
                foreach ($list as $key => $value) {
                    if ($value['pid'] !== 0 && !list_search($list,['id'=>$value['pid']])) {
                        $parentNode[] = $this->parentNode($value['pid']);
                    }
                }

                foreach ($parentNode as $key => $value) {
                    $list = array_merge($list,$value);
                }
            }

            $depart = $this->model->getListTree();
            return $this->success('获取成功', '',$depart, $total);
		}

		return view();
	}

	/**
	 * 添加部门数据
     * @return View
	 */
	public function add(): View
    {
		if (request()->isPost()) {
            $post = request()->post();
            $this->validate($post, \app\common\validate\system\Department::class.'.add');
			if ($this->model->create($post)) {
				return $this->success('添加部门成功！');
			}
		}

        return $this->error('添加部门失败！');
	}

	/**
	 * 编辑部门数据
     * @return View
	 */
	public function edit(): View
    {
		if (request()->isPost()) {
            $post = request()->post();
            $this->validate($post, \app\common\validate\system\Department::class.'.edit');
			if ($this->model->update($post)) {				
				return $this->success('更新部门成功！');
			}
		}

        return $this->error('更新部门失败');
	}

	/**
	 * 删除部门数据
	 */
	public function del() 
	{
		$id = input('id/d', 0);
		if ($id > 0) {
			// 查询子部门
			if ($this->model->where('pid',$id)->count()) {
				return $this->error('当前部门存在子部门！');
			}

			// 删除单个
			if ($this->model->destroy($id)) {
				return $this->success('删除部门成功！');
			}
		}
		
		return $this->error('删除失败，请检查您的参数！');
	}
}