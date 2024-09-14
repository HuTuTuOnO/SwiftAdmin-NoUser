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
ini_set('memory_limit', '-1');

use app\AdminController;
use app\common\library\SpiderLog;

/**
 * 爬虫管理
 */
class Spider extends AdminController
{
    /**
     * @var string
     */
    public string $today;

    /**
     * @var string
     */
    public string $yesterday;

    /**
     * @var string
     */
    public string $beforeYesterday;

    /**
     * @var string
     */
    public string $toHour;

    /**
     * @var string
     */
    public string $spiderPath;

    /**
     * @var array
     */
    public array $spiderList;

    /**
     * @var string
     */
    public string $logExt = '.log';

    /**
     * @var string
     */
    public string $txtExt = '.txt';

    // 初始化函数
    public function initialize()
    {
        parent::initialize();

        // 爬虫列表
        $this->spiderList = config('spider');
        // 今天日期
        $this->today = date('Ymd');
        // 获取昨天日期
        $this->yesterday = date('Ymd', strtotime('-1 day'));
        // 获取前天日期
        $this->beforeYesterday = date('Ymd', strtotime('-2 day'));
        // 当前小时
        $this->toHour = date('H');
        // 爬虫日志路径
        $this->spiderPath = root_path('runtime/spider');
    }

    /**
     * 获取资源列表
     */
    public function index(): \think\response\View
    {
        // 获取参数
        $cycle = input('cycle/s', 'today');
        $spiderRatio = $this->getSpiderRatio($this->$cycle);
        $spiderTotal = array_sum(array_column($spiderRatio, 'value'));
        if (request()->isAjax()) {
            return $this->success('ok', '', ['spiderRatio' => $spiderRatio, 'spiderTotal' => $spiderTotal]);
        }

        // 获取30天蜘蛛统计
        $xAxisData = [];
        $seriesData = [];
        $allSpiderDir = $this->spiderPath . 'allSpider';
        for ($i = 30; $i > 0; $i--) {
            $day = date('Ymd', strtotime('-' . $i . ' day'));
            $spiderTxt = $allSpiderDir . DIRECTORY_SEPARATOR . $day . $this->txtExt;
            $value = strlen(read_file($spiderTxt));
            $xAxisData[] = $day;
            $seriesData[] = $value;

        }
        $spiderRatioList = $this->getSpiderRatio($this->$cycle, false);
        return view('', [
            'spiderTotal'     => $spiderTotal,
            'spiderRatioList' => $spiderRatioList,
            'spiderRatio'     => json_encode($spiderRatio, JSON_UNESCAPED_UNICODE),
            'xAxisData'       => json_encode($xAxisData, JSON_UNESCAPED_UNICODE),
            'seriesData'      => json_encode($seriesData, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * 获取爬虫详情
     */
    public function getSpiderLog()
    {
        if (request()->isAjax()) {
            $page = input('page/d', 1);
            $limit = input('limit/d', 18);
            $type = input('type/s', 'allSpider');
            $days = input('day/s', $this->today);

            // 获取日志路径
            $file = $this->spiderPath . $type . DIRECTORY_SEPARATOR . $days . $this->logExt;
            $count = str_replace('.log', '.txt', $file);
            $count = strlen(read_file($count));
            $pages = ceil($count / $limit);
            $lists = [];
            $beginLine = 0;
            if ($count > $limit) {
                // 计算倒序偏移量
                $offset = $count - ($page * $limit);
                $beginLine = max($offset, 0);
                $limit = $offset > 0 ? $limit : $count - ($pages - 1) * $limit;
            }

            // 判断文件是否存在
            if (!empty($count) && is_file($file)) {
                $lists = SpiderLog::getSpiderLines($file, $beginLine, (int)$limit);
            }

            return $this->success('ok', '', array_reverse($lists), $count);
        }

        return $this->error('非法请求');
    }

    /**
     * 获取爬虫信息
     */
    public function getSpiderDetail()
    {
        if (request()->isAjax()) {
            $day = input('day/s', $this->today);
            $spiderRatioList = $this->getSpiderRatio($day, false);
            $spiderTotal = array_sum(array_column($spiderRatioList, 'value'));

            return $this->success('ok', '', ['spiderRatioList' => $spiderRatioList, 'spiderTotal' => $spiderTotal]);
        }

        return $this->error('非法请求');
    }

    /**
     * 获取小时统计
     * @return array
     */
    public function hours(): array
    {
        if (request()->isAjax()) {

            $cycle = input('cycle/s', 'day');
            if (!isset($this->$cycle)) {
                $this->error('参数错误');
            }

            // 获取蜘蛛统计
            $spiderDayCount = SpiderLog::getDayCount();
            $spiderLogCount = SpiderLog::getChannelLogCount($this->$cycle);
            $spiderHoursData = SpiderLog::getHoursSpiderLog($this->$cycle);

            return $this->success('ok', '', [
                'spiderDayCount'   => $spiderDayCount,
                'spiderLogCount'   => $spiderLogCount,
                'spiderHoursData'  => $spiderHoursData,
                'spiderHoursCount' => count($spiderHoursData),
            ]);
        }

        return [];
    }

    /**
     * @param mixed $cycle
     * @return mixed|string
     */
    public function getCycle(mixed $cycle): mixed
    {
        switch ($cycle) {
            case 'today':
                $cycle = $this->today;
                break;
            case 'yesterday':
                $cycle = $this->yesterday;
                break;
            case 'beforeYesterday':
                $cycle = $this->beforeYesterday;
                break;
            default:
                break;
        }
        return $cycle;
    }

    /**
     * @param string $day
     * @param bool $num
     * @return array
     */
    protected function getSpiderRatio(string $day = '', bool $num = true): array
    {
        $day = $day ?? $this->today;
        $spiderRatio = [];
        foreach ($this->spiderList as $key => $item) {
            $spiderTxt = $this->spiderPath . $key . DIRECTORY_SEPARATOR . $day . $this->txtExt;
            $value = strlen(read_file($spiderTxt));
            $name = $num ? $item . ' ' . $value : $item;
            $spiderRatio[] = [
                'key'   => $key,
                'value' => $value,
                'name'  => $name
            ];
        }

        return $spiderRatio;
    }
}