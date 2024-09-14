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
namespace app\admin\controller;

use app\admin\enums\AdminNoticeEnum;
use app\AdminController;
use app\common\library\SpiderLog;
use app\common\model\system\AdminNotice;
use app\common\model\system\Config;
use app\common\service\notice\EmailService;
use app\common\service\utils\FtpService;
use think\cache\driver\memcached;
use think\cache\driver\Redis;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\response\View;
use Throwable;

class Index extends AdminController
{
    public function index()
    {
        $notice_count = AdminNotice::where('status', 0)->count();
        return view('index/index', [
            'notice_count' => $notice_count,
        ]);
    }

    // 控制台首页
    public function console()
    {
        $dataList = [];

        for ($i = -29; $i <= 0; $i++) {
            $dataList[date('m-d', strtotime($i . ' day'))] = date('m-d', strtotime($i . ' day'));
        }

        // 获取三天内统计
        $spiderDayCount = SpiderLog::getDayCount();

        // 获取蜘蛛渠道统计
        $spiderLogCount = SpiderLog::getChannelLogCount();

        // 获取蜘蛛小时统计
        $spiderHoursData = SpiderLog::getHoursSpiderLog();

        $workplace = [];
        foreach (AdminNoticeEnum::COLLECTION as $item) {
            $workplace[$item] = AdminNotice::where([
                'admin_id' => $this->adminId,
                'type' => $item
            ])->count('id');
        }

        $todoList = AdminNotice::where([
            'admin_id' => $this->adminId,
            'type' => AdminNoticeEnum::TODO,
            'status' => 0
        ])->count('id');

        return view('', [
            'workplace'             => $workplace,
            'todoList'              => $todoList,
            // 爬虫相关检索
            'spiderLogCount'        => $spiderLogCount,
            'spiderDayCount'        => $spiderDayCount,
            'spiderCount'           => SpiderLog::getSpiderCount(),
            'spiderHoursData'       => json_encode($spiderHoursData, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 获取系统配置
     * @return View
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function baseCfg(): View
    {
        $config = Config::all();
        $config['fsockopen'] = function_exists('fsockopen');
        $config['stream_socket_client'] = function_exists('stream_socket_client');
        return view('', ['config' => $config]);
    }

    /**
     * 编辑系统配置
     *
     * @param array $config
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function baseSet(array $config = [])
    {
        if (request()->isPost()) {

            $post = input();
            $list = Config::select()->toArray();
            foreach ($list as $key => $value) {

                $name = $value['name'];

                // 字段必须存在
                if (isset($post[$name])) {
                    $option['id'] = $value['id'];
                    if ('array' == trim($value['type'])) {
                        $option['value'] = json_encode($post[$name], JSON_UNESCAPED_UNICODE);
                    } else {
                        $option['value'] = $post[$name];
                    }

                    $config[$key] = $option;
                }
            }

            try {

                (new Config())->saveAll($config);
                $index = public_path() . 'index.php';
                $files = '../extend/conf/index.tpl';

                if ($post['site_status']) {
                    $close = '../extend/conf/close.tpl';
                    $content = file_get_contents($close);
                    write_file($index, $content);
                } else {
                    $content = file_get_contents($index);
                    if (!strpos($content, 'run()')) {
                        $content = file_get_contents($files);
                        write_file($index, $content);
                    }
                }

                // 配置文件路径
                $env = root_path() . '.env';
                $parse = parse_ini_file($env, true);
                $parse['CACHE']['DRIVER'] = $post['cache_type'];
                $parse['CACHE']['HOSTNAME'] = $post['cache_host'];
                $parse['CACHE']['HOSTPORT'] = $post['cache_port'];
                $parse['CACHE']['SELECT'] = max($post['cache_select'], 1);
                $parse['CACHE']['USERNAME'] = $post['cache_user'];
                $parse['CACHE']['PASSWORD'] = $post['cache_pass'];

                $content = parse_array_ini($parse);
                if (write_file($env, $content)) {
                    Cache::set('redis-sys_', $post, config('cookie.expire'));
                }

            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }

            // 清理系统核心缓存
            Cache::tag('core_system')->clear();
            $configList = Cache::get('config_list') ?? [];
            foreach ($configList as $item) {
                Cache::delete($item);
            }

            return $this->success('保存成功!');
        }
    }

    /**
     * FTP测试上传
     */
    public function testFtp()
    {
        if (request()->isPost()) {

            try {
                FtpService::ftpTest(input());
            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }
            return $this->success('上传测试成功！');

        }

        return $this->error('上传测试失败！');
    }

    /**
     * 邮件测试
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function testEmail()
    {
        if (request()->isPost()) {
            $post = input();
            EmailService::testEmail($post);
            $this->success('测试邮件发送成功！');
        }
    }

    /**
     * 缓存测试
     */
    public function testCache()
    {
        if (request()->isPost()) {

            $param = input();
            if (!isset($param['type']) || empty($param['host']) || empty($param['port'])) {
                return $this->error('参数错误!');
            }

            $options = [
                'host'     => $param['host'],
                'port'     => (int)$param['port'],
                'username' => $param['user'],
                'password' => $param['pass']
            ];

            try {
                if (strtolower($param['type']) == 'redis') {
                    $drive = new Redis($options);
                } else {
                    $drive = new Memcached($options);
                }
            } catch (Throwable $th) {
                return $this->error($th->getMessage());
            }

            if ($drive->set('test', 'cacheOK', 1000)) {
                return $this->success('缓存测试成功！');
            } else {
                return $this->error('缓存测试失败！');
            }
        }

        return false;
    }
}
