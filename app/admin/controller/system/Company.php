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
namespace app\admin\controller\system;

use app\AdminController;
use app\common\model\system\Company as CompanyModel;
use think\response\View;

class Company extends AdminController
{
    // 初始化函数
    public function initialize()
    {
        parent::initialize();
        $this->model = new CompanyModel();
    }

    /**
     * 获取资源列表
     * @return View
     */
    public function index(): View
    {
        if (request()->isAjax()) {
            $post = request()->post();
            $where = array();
            if (!empty($post['title'])) {
                $where[] = ['title', 'like', '%' . $post['title'] . '%'];
            }

            // 生成查询数据
            $list = $this->model->where($where)->select()->toArray();
            return $this->success('查询成功', '/', $list, count($list));
        }

        return view();
    }

    /**
     * 添加公司信息
     * @return View
     */
    public function add(): View
    {
        if (request()->isPost()) {
            $post = request()->post();
            if ($this->model->create($post)) {
                return $this->success();
            }
            return $this->error();
        }

        return view('', [
            'data' => $this->getTableFields()
        ]);
    }

    /**
     * 编辑公司信息
     * @return View
     */
    public function edit(): View
    {
        $id = input('id/d', 0);
        if (request()->isPost()) {
            $post = request()->post();
            if ($this->model->update($post)) {
                return $this->success();
            }
            return $this->error();
        }

        $data = $this->model->find($id);
        return view('add', ['data' => $data]);
    }

}   