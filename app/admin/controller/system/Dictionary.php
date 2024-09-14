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
use app\common\model\system\Dictionary as DictionaryModel;
use think\response\View;

class Dictionary extends AdminController
{

    public function initialize()
    {
        parent::initialize();
        $this->model = new DictionaryModel();
    }

    /**
     * @return View
     */
    public function index(): View
    {
        $post = input();
        $pid = input('pid');
        $limit = input('limit/d') ?? 10;
        $page = input('page/d') ?? 1;
        if ($pid == null) {
            $pid = (string)$this->model->minId();
        }

        if (request()->isAjax()) {
            $pid = !str_contains($pid, ',') ? $pid : explode(',', $pid);
            $where[] = ['pid', 'in', $pid];
            if (!empty($post['name'])) {
                $where[] = ['name', 'like', '%' . $post['name'] . '%'];
            }

            $count = $this->model->where($where)->count();
            $list = $this->model->where($where)->limit($limit)->page($page)->select()
                ->each(function ($item, $key) use ($pid) {
                    if ($key == 0 && $pid == '0') {
                        $item['LAY_CHECKED'] = true;
                    }
                    return $item;
                });

            return $this->success('查询成功', '/', $list, $count);
        }

        return view('', ['pid' => $pid]);
    }
}
