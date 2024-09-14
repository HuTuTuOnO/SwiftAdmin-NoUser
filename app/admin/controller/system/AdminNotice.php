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
use app\AdminController;
use app\common\exception\OperateException;
use app\common\model\system\AdminNotice as AdminNoticeModel;
use app\common\model\system\Admin as AdminModel;
use app\common\model\system\AdminGroup as AdminGroupModel;
use app\common\model\system\AdminAccess as AdminAccessModel;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\response\View;

class AdminNotice extends AdminController
{
    /**
     * 消息类型
     * @var array
     */
    public array $msgType = [
        'notice'  => '通知',
        'message' => '消息',
        'todo'    => '待办',
    ];

    // 初始化函数
    public function initialize()
    {
        parent::initialize();
        $this->model = new AdminNoticeModel();
    }

    /**
     * 获取资源列表
     * @return View
     * @throws Exception
     */
    public function index(): View
    {
        if (request()->isAjax()) {
            list($count, $list) = AdminNoticeService::dataList($this->adminId);
            return $this->success('查询成功', null, $list, $count);
        }

        return view('', [
            'msgType' => $this->msgType,
        ]);
    }

    /**
     * 消息模板
     * @return View
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function bells(): View
    {
        list($count, $list) = AdminNoticeService::bells($this->adminInfo['id']);
        return view('/system/admin_notice/bells', [
            'list'  => $list,
            'count' => $count
        ]);
    }

    /**
     * 获取消息列表
     * @return View
     * @throws DbException
     */
    public function getBells(): View
    {
        $data = AdminNoticeService::getBells($this->adminInfo['id']);
        return $this->success('获取成功', '/', $data);
    }

    /**
     * 阅读消息
     * @return View
     * @throws OperateException
     */
    public function read(): View
    {
        $id = request()->get('id', 0);
        $detail = AdminNoticeService::getDetail($id, $this->adminInfo['id']);
        return view('/system/admin_notice/' . $detail['type'], [
            'detail' => $detail
        ]);
    }

    /**
     * 发送消息
     * @return View
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     * @throws OperateException
     */
    public function add(): View
    {
        if (request()->isPost()) {
            $post = request()->post();
            $post['send_id'] = $this->adminInfo['id'];
            $post['type'] = AdminNoticeEnum::MESSAGE;
            $post['send_ip'] = request()->ip();
            $post['create_time'] = time();
            $this->validate($post, \app\common\validate\system\AdminNotice::class);
            AdminNoticeService::add($post);
            return $this->success('发送成功');
        }

        return view('', [
            'adminList' => AdminModel::select()->toArray(),
        ]);
    }

    /**
     * 清空消息
     * @return View
     * @throws Exception
     */
    public function clear(): View
    {
        $type = input('type', AdminNoticeEnum::NOTICE);
        $where[] = ['type', '=', $type];
        $where[] = ['admin_id', '=', $this->adminInfo['id']];
        $where[] = ['status', '=', AdminNoticeEnum::STATUS_READ];
        try {
            AdminNoticeModel::where($where)->delete();
        } catch (Exception $e) {
            return $this->error('清空失败');
        }
        return $this->success('清空成功');
    }

    /**
     * 全部消息已读
     * @return View
     * @throws Exception
     */
    public function readAll(): View
    {
        $type = input('type', AdminNoticeEnum::NOTICE);
        $where[] = ['type', '=', $type];
        $where[] = ['admin_id', '=', $this->adminInfo['id']];
        $where[] = ['status', '=', AdminNoticeEnum::STATUS_UNREAD];
        try {
            AdminNoticeModel::where($where)->update(['status' => 1]);
        } catch (Exception $e) {
            return $this->error('操作失败');
        }

        return $this->success('全部已读成功');
    }

    /**
     * 删除消息
     * @return mixed
     * @throws OperateException
     */
    public function del(): mixed
    {
        $id = request()->param('id/d', 0);
        AdminNoticeService::delete($id, $this->adminInfo['id']);
        return $this->success('删除成功');
    }
}
