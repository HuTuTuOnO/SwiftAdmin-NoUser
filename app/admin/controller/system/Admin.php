<?php

declare(strict_types=1);
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
use app\admin\enums\AdminNoticeEnum;
use app\admin\service\AdminNoticeService;
use app\admin\service\AdminService;
use app\AdminController;
use app\common\exception\OperateException;
use app\common\model\system\AdminNotice;
use app\common\model\system\Jobs;
use app\common\model\system\Department;
use app\common\model\system\Admin as AdminModel;
use app\common\model\system\AdminGroup as AdminGroupModel;
use app\common\model\system\AdminAccess as AdminAccessModel;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\response\View;

class Admin extends AdminController
{

    /**
     * 用户管理组
     * @var mixed
     */
    protected mixed $group;

    /**
     * 用户岗位
     * @var mixed
     */
    public mixed $jobs;

    /**
     * 用户部门
     * @var mixed
     */
    public mixed $department;

    // 初始化函数
    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminModel();
        $this->jobs = Jobs::select()->toArray();
        $this->group = AdminGroupModel::select()->toArray();
        $this->department = Department::getListTree();
    }

    /**
     * 获取资源列表
     * @return View
     * @throws Exception
     */
    public function index(): View
    {
        if (request()->isAjax()) {
            $params = input();
            list('count' => $count, 'list' => $list) = AdminService::dataList($params);
            return $this->success('查询成功', null, $list, $count);
        }

        return view('', [
            'jobs'       => $this->jobs,
            'group'      => $this->group,
            'department' => json_encode($this->department),
        ]);
    }

    /**
     * 添加管理员
     * @return View
     * @throws OperateException
     */
    public function add(): View
    {
        if (request()->isPost()) {
            $post = request()->post();
            $this->validate($post, \app\common\validate\system\Admin::class . '.add');
            AdminService::add($post);
            return $this->success('添加管理员成功');
        }

        // 获取用户组
        return view('', ['group' => $this->group]);
    }

    /**
     * 更新管理员
     * @return View
     * @throws OperateException
     */
    public function edit(): View
    {
        if (request()->isPost()) {
            $post = request()->post();
            $this->validate($post, \app\common\validate\system\Admin::class . '.edit');
            AdminService::edit($post);
            return $this->success('更新管理员成功');
        }

        return $this->error('非法请求');
    }

    /**
     * 用户菜单接口
     * @access  public
     * @return  mixed
     */
    public function authorities(): mixed
    {
        return $this->authService->getPermissionsMenu();
    }

    /**
     * 获取节点数据
     * @access      public
     * @return      mixed
     */
    public function getRuleCateTree(): mixed
    {
        $type = input('type', AdminEnum::ADMIN_AUTH_RULES);
        return $this->authService->getRuleCatesTree($type, $this->authService->authPrivate);
    }

    /**
     * 编辑权限
     * @access      public
     * @return      mixed
     * @throws OperateException
     */
    public function editRules(): mixed
    {
        $adminId = input('admin_id/d', 0);
        AdminService::updateRulesNodes($adminId, AdminEnum::ADMIN_AUTH_RULES);
        return $this->success('更新权限成功！');
    }

    /**
     * 编辑栏目权限
     * @access      public
     * @return      mixed
     * @throws OperateException
     */
    public function editCates(): mixed
    {
        $adminId = input('admin_id/d', 0);
        AdminService::updateRulesNodes($adminId, AdminEnum::ADMIN_AUTH_CATES);
        return $this->success('更新权限成功！');
    }

    /**
     * 模版页面
     * @return View
     */
    public function theme(): View
    {
        return view();
    }

    /**
     * 个人中心
     */
    public function center()
    {
        if (request()->isPost()) {
            $post = input(); // 获取POST数据
            $post['id'] = $this->adminInfo['id'];
            if ($this->model->update($post)) {
                return $this->success();
            }

            return $this->error();
        }

        $title = [];
        $data = $this->model->find($this->adminInfo['id']);
        if (!empty($data['group_id'])) {
            $group = AdminGroupModel::field('title')
                ->whereIn('id', $data['group_id'])
                ->select()
                ->toArray();
            foreach ($group as $key => $value) {
                $title[$key] = $value['title'];
            }
        }

        $data['jobs'] = Jobs::where('id', $data['jobs_id'])->value('title');
        $data['group'] = implode('－', $title);
        $data['tags'] = empty($data['tags']) ? $data['tags'] : unserialize($data['tags']);
        return view('', [
            'data' => $data
        ]);
    }

    /**
     * 修改个人资料
     */
    public function modify()
    {
        if (request()->isAjax()) {
            $post = input('post.');
            $id = $this->adminInfo['id'];
            try {
                //code...
                switch ($post['field']) {
                    case 'face':
                        $id = $this->model->update(['id' => $id, 'face' => $post['face']]);
                        break;
                    case 'mood':
                        $id = $this->model->update(['id' => $id, 'mood' => $post['mood']]);
                        break;
                    case 'tags':
                        if (\is_empty($post['tags'])) {
                            break;
                        }
                        $data = $this->model->field('tags')->find($id);
                        if (!empty($data['tags'])) {
                            $tags = unserialize($data['tags']);
                            if (!empty($post['del'])) {
                                foreach ($tags as $key => $value) {
                                    if ($value == $post['tags']) {
                                        unset($tags[$key]);
                                    }
                                }
                            } else {
                                $merge = array($post['tags']);
                                $tags = array_unique(array_merge($merge, $tags));
                                if (count($tags) > 10) {
                                    throw new \think\Exception('最多拥有10个标签！');
                                }
                            }
                            $tags = serialize($tags);
                        } else {
                            $tags = serialize(array($post['tags']));
                        }
                        $id = $this->model->update(['id' => $id, 'tags' => $tags]);
                        break;
                    default:
                        # code...
                        break;
                }
            } catch (\Exception $e) {
                return $this->error($e->getMessage());
            }

            return $id ? $this->success() : $this->error();
        }

        return $this->error('非法请求');
    }

    /**
     * 修改密码
     */
    public function pwd(): View
    {
        if (request()->isPost()) {

            $pwd = input('pwd/s');
            $post = request()->except(['pwd']);
            if ($post['pass'] !== $post['repass']) {
                return $this->error('两次输入的密码不一样！');
            }

            // 查找数据
            $where[] = ['id', '=', $this->adminInfo['id']];
            $where[] = ['pwd', '=', encryptPwd($pwd)];
            $result = $this->model->where($where)->find();

            if (!empty($result)) {
                $this->model->where($where)->update(['pwd' => encryptPwd($post['pass'])]);
                $this->success('更改密码成功！');
            } else {
                $this->error('原始密码输入错误');
            }
        }

        return view();
    }

    /**
     * 语言配置
     * @return  mixed
     */
    public function language(): mixed
    {
        $language = input('l/s');
        $env = root_path() . '.env';
        $array = parse_ini_file($env, true);
        $array['LANG']['default_lang'] = $language;
        $content = parse_array_ini($array);
        if (write_file($env, $content)) {
            return json(['success']);
        }
        return json(['error']);
    }

    /**
     * 更改状态
     */
    public function status()
    {
        $id = input('id/d');
        $status = input('status/d');
        if ($id && is_int($id) && is_int($status)) {
            $array['id'] = $id;
            $array['status'] = $status;
            if ($this->model->update($array)) {
                return $this->success('修改成功！');
            }
        }

        return $this->error('修改失败,请检查您的数据！');
    }

    /**
     * 删除管理员
     */
    public function del()
    {
        $id = input('id');
        !is_array($id) && ($id = array($id));
        if (!empty($id)) {

            // 过滤权限
            if (in_array("1", $id)) {
                return $this->error('禁止删除超管帐号！');
            }

            // 删除用户
            if ($this->model->destroy($id)) {
                $arr = implode(',', $id);
                $where[] = ['admin_id', 'in', $arr];
                AdminAccessModel::where($where)->delete();
                return $this->success('删除管理员成功！');
            }
        }

        return $this->error('删除管理员失败，请检查您的参数！');
    }


    /**
     * 清理系统缓存
     */
    public function clear()
    {
        if (request()->isPost()) {

            $type = input('type/s');

            try {
                // 清理内容
                if ($type == 'content') {
                    recursive_delete(root_path('runtime'));
                } else if ($type == 'template') {
                    recursive_delete(root_path('runtime' . '/temp'));
                } else {
                    Cache::clear();
                }
            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }
        }

        return $this->success('清理缓存成功，请刷新页面！');
    }
}
