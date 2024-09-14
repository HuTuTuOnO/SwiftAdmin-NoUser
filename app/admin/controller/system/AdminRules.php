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


use app\admin\service\AdminRuleService;
use app\AdminController;
use app\common\model\system\AdminRules as AdminRuleModel;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\response\View;

class AdminRules extends AdminController
{
    // 初始化函数
    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminRuleModel();
    }

    /**
     * 获取资源列表
     * @return View
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function index(): View
    {
        if (request()->isAjax()) {
            list($count, $list) = AdminRuleService::dataList(request()->param());
            $rules = list_to_tree($list, 'id', 'pid', 'children', 0);
            return $this->success('获取成功', '/', $rules, $count);
        }

        return view('/system/admin/rules');
    }

    /**
     * 添加节点数据
     */
    public function add()
    {
        if (request()->isPost()) {
            $post = \request()->post();
            validate(\app\common\validate\system\AdminRules::class . '.add')->check($post);
            if ($this->model->create($post)) {
                return $this->success('添加菜单成功！');
            }
        }

        $data = $this->getTableFields();
        $data['pid'] = input('pid', 0);
        $data['auth'] = 1;
        $data['type'] = 1;
        list($count, $list) = AdminRuleService::dataList(request()->all());
        return view('/system/admin/rules_edit', [
            'data'  => $data,
            'rules' => json_encode(list_to_tree($list), JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 编辑节点数据
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function edit()
    {
        $id = input('id', 0);
        $data = $this->model->find($id);
        if (request()->isPost()) {
            $post = \request()->post();
            validate(\app\common\validate\system\AdminRules::class . '.edit')->check($post);
            if ($this->model->update($post)) {
                return $this->success('更新菜单成功！');
            }
        }

        list($count, $list) = AdminRuleService::dataList(request()->all());
        return view('/system/admin/rules_edit', [
            'data'  => $data,
            'rules' => json_encode(list_to_tree($list), JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 删除节点数据
     */
    public function del()
    {
        $id = input('id');
        if (!empty($id) && is_numeric($id)) {
            // 查询子节点
            if ($this->model->where('pid', $id)->count()) {
                return $this->error('当前菜单存在子菜单！');
            }

            // 删除单个
            if ($this->model::destroy($id)) {
                return $this->success('删除菜单成功！');
            }
        }

        return $this->error('删除失败，请检查您的参数！');
    }

}
