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

use app\admin\enums\AdminEnum;
use app\admin\service\AdminGroupService;
use app\AdminController;
use app\common\exception\OperateException;
use app\common\model\system\AdminGroup as AdminGroupModel;
use think\response\View;

class AdminGroup extends AdminController
{
    // 初始化函数
    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminGroupModel();
    }

    /**
     * 获取资源列表
     * @return View
     * @throws \Exception
     */
    public function index(): View
    {
        if (request()->isAjax()) {
            $params = input();
            list($count, $list) = AdminGroupService::dataList($params);
            return $this->success('查询成功', '/', $list, $count);
        }

        return view('/system/admin/group', [
            'group' => $this->model->getListGroup()
        ]);
    }

    /**
     * 添加角色
     * @return View
     * @throws OperateException
     */
    public function add(): View
    {
        if (request()->isPost()) {
            $post = input('post.');
            $this->validate($post, \app\common\validate\system\AdminGroup::class . '.add');
            AdminGroupService::add($post);
            return $this->success('添加角色成功！');
        }

        return $this->error('添加角色失败！');
    }

    /**
     * 编辑角色
     * @return View
     * @throws OperateException
     */
    public function edit(): View
    {
        if (request()->isPost()) {
            $post = request()->post();
            $this->validate($post, \app\common\validate\system\AdminGroup::class . '.edit');
            AdminGroupService::edit($post);
            return $this->success('更新角色成功！');
        }

        return $this->error('更新角色失败！');
    }

    /**
     * 权限函数接口
     * @access      public
     */
    public function getRuleCateTree()
    {
        $type = input('type/s', AdminEnum::ADMIN_AUTH_RULES);
        return $this->authService->getRuleCatesTree($type, $this->authService->authGroup);
    }

    /**
     * 更新权限
     * @return View
     * @throws OperateException
     */
    public function editRules(): View
    {
        $id = input('id/d', 0);
        $rules = input(AdminEnum::ADMIN_AUTH_RULES, []);
        $this->validate(request()->post(), \app\common\validate\system\AdminGroup::class . '.edit');
        AdminGroupService::editRules($id, $rules);
        return $this->success('更新权限成功！');
    }

    /**
     * 更新栏目
     * @return View
     * @throws OperateException
     */
    public function editCates(): View
    {
        $id = input('id/d', 0);
        $cates = input(AdminEnum::ADMIN_AUTH_CATES, []);
        $this->validate(request()->post(), \app\common\validate\system\AdminGroup::class . '.edit');
        AdminGroupService::editCates($id, $cates);
        return $this->success('更新权限成功！');
    }

    /**
     * 删除角色/用户组
     * @return View
     */
    public function del(): View
    {
        $id = input('id', 0);
        $this->validate(request()->post(), \app\common\validate\system\AdminGroup::class . '.edit');
        if ($id == 1) {
            return $this->error('系统内置禁止删除！');
        } else if ($this->model::destroy($id)) {
            return $this->success('删除角色成功！');
        }
        return $this->error('删除角色失败，请检查您的参数！');
    }

}
