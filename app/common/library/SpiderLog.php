<?php

namespace app\common\library;

use system\IpLocation;
use think\facade\Log;

/**
 * 爬虫日志类
 * Class SpiderLog
 * @package app\common\library
 * @auther meystack
 */
class SpiderLog
{
    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        // 返回实例
        return self::$instance;
    }

    /**
     * 判断是否为爬虫
     * @return bool
     */
    public static function isWebSpider(): bool
    {
        $userAgent = request()->header('user-agent') ?? '';
        $spiderList = config('spider');
        foreach ($spiderList as $key => $item) {
            if (stripos($userAgent, $key) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * 写入爬虫log
     * @return void
     */
    public static function SpiderTraceLogs(): void
    {
        // 蜘蛛名称
        $spiderName = '';
        $today = date('Ymd');
        $times = date('Y-m-d H:i:s');
        $userAgent = request()->header('user-agent') ?? '';

        // 获取爬虫列表
        $spiderList = config('spider');
        $spiderPath = root_path('runtime/spider');
        $allSpiderPath = $spiderPath . 'allSpider';
        $replace = ['Baiduspider-render', 'Baiduspider-image', 'Googlebot-Image'];
        $userAgent = str_ireplace($replace, '', $userAgent);

        // 查询UA是PC端还是移动端的
        $device = request()->isMobile() ? '移动端' : 'PC端';
        foreach ($spiderList as $key => $item) {
            if (stripos($userAgent, $key) !== false) {
                $spiderName = $key;
                break;
            }
        }

        // 如果爬虫存在
        if (!empty($spiderName)) {

            // 格式化爬虫数据
            $clientIp = get_real_ip();
            $baseUrl = self::getBaseUrl();
            $data = [$times, $spiderName, $clientIp, $baseUrl, $device];
            $line = implode('|', $data);

            // 全局蜘蛛数据
            $spiderLog = $allSpiderPath . DIRECTORY_SEPARATOR . $today . '.log';
            $spiderTxt = $allSpiderPath . DIRECTORY_SEPARATOR . $today . '.txt';
            self::writeLineLog($spiderLog, $line . PHP_EOL);
            self::writeLineLog($spiderTxt, '1');

            // 单例蜘蛛数据
            $spiderName = $spiderPath . DIRECTORY_SEPARATOR . $spiderName;
            $spiderNameLog = $spiderName . DIRECTORY_SEPARATOR . $today . '.log';
            $spiderNameTxt = $spiderName . DIRECTORY_SEPARATOR . $today . '.txt';
            self::writeLineLog($spiderNameLog, $line . PHP_EOL);
            self::writeLineLog($spiderNameTxt, '1');

            // 全域蜘蛛小时统计
            $toHour = date('H', time());
            $hoursSpiderTxt = $spiderPath . DIRECTORY_SEPARATOR . 'HoursSpider' . DIRECTORY_SEPARATOR . $today . DIRECTORY_SEPARATOR . $toHour . '.txt';
            self::writeLineLog($hoursSpiderTxt, '1');
        }
    }

    /**
     * 获取访问URL
     * @return string
     */
    protected static function getBaseUrl(): string
    {
        $isSsl = request()->isSsl();
        return ($isSsl ? 'https://' : 'http://') . request()->host() . request()->baseUrl();
    }

    /**
     * 写入日志
     * @param string $file
     * @param string $content
     * @return void
     */
    protected static function writeLineLog(string $file, string $content = ''): void
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mk_dirs($dir);
        }

        @error_log($content, 3, $file);
    }

    /**
     * 获取三天内爬虫总数
     * @return array
     */
    public static function getDayCount(): array
    {
        $dayList = [
            'today'           => date('Ymd'),
            'yesterday'       => date('Ymd', strtotime('-1 day')),
            'beforeYesterday' => date('Ymd', strtotime('-2 day')),
        ];

        $result = [];
        foreach ($dayList as $name => $day) {
            $result[$name] = self::setDaySpiderTotal($day);
        }
        return $result;
    }

    /**
     * @param $day
     * @return int
     */
    public static function setDaySpiderTotal($day): int
    {
        $filePath = root_path('runtime/spider/allSpider') . $day . '.txt';
        $spiderNum = strlen(read_file($filePath));
        return $spiderNum ?: 0;
    }

    /**
     * @param $filename
     * @param int $startLine
     * @param int $endLine
     * @param string $method
     * @return array
     */
    public static function getSpiderLines($filename, int $startLine = 1, int $endLine = 50, string $method = 'rb'): array
    {
        $fp = new \SplFileObject($filename, $method);
        $fp->seek($startLine);
        $list = [];
        $spiderList = config('spider');
        $IpLocation = new IpLocation();
        for ($i = 0; $i <= $endLine; ++$i) {
            $eol = ["\r\n", "\r", "\n"];
            $item = str_replace($eol, '', $fp->current());
            // 判断是否为空
            if (!empty($item)) {
                try {
                    $item = str_replace(',', '|', $item);
                    $value = explode('|', $item);
                    $ip = $value[2] ?? '';
                    $region = $IpLocation->getLocation($ip);
                    $region = $region['country'] . $region['area'];
                    $list[] = [
                        'time'   => $value[0],
                        'spider' => $spiderList[$value[1]] ?? $value[1],
                        'ip'     => $value[2],
                        'region' => $region,
                        'url'    => $value[3],
                        'device' => $value[4],
                    ];
                } catch (\Exception $e) {
                    Log::error($item . ' === ' . $e->getMessage());
                }
            }
            $fp->next();
        }
        return $list;
    }

    /**
     * 获取小时爬虫统计
     * @param string $today
     * @return array[]
     */
    public static function getHoursSpiderLog(string $today = ''): array
    {
        $xAxisData = [];
        $seriesData = [];
        $today = $today ?: date('Ymd');
        $spiderDir = root_path('runtime/spider');
        $hourSpiderDir = $spiderDir . 'HoursSpider/' . $today;
        $hourSpiderList = @scandir($hourSpiderDir) ?: [];
        foreach ($hourSpiderList as $key => $file) {

            if ($file == '.' || $file == '..') {
                continue;
            }

            // 获取文件后缀
            $extInfo = @pathinfo($file);
            if (!isset($extInfo['extension'])
                || $extInfo['extension'] != 'txt') {
                continue;
            }

            $fileTemp = $hourSpiderDir . '/' . $file;
            $xAxisData[] = $extInfo['filename'];
            $seriesData[] = strlen(read_file($fileTemp));
        }

        return [
            'xAxisData'  => $xAxisData,
            'seriesData' => $seriesData,
        ];
    }

    /**
     * 获取爬虫分类统计
     * @param string $day
     * @return array
     */
    public static function getChannelLogCount(string $day = ''): array
    {
        $day = $day ?: date('Ymd');
        $spiderDir = root_path('runtime/spider');
        $spiderList = config('spider');
        $result = [];
        foreach ($spiderList as $key => $item) {
            $dir = $spiderDir . $key . '/' . $day . '.txt';
            $result[$key] = strlen(read_file($dir));
        }
        return $result;
    }

    /**
     * 获取蜘蛛总数
     * @return int
     */
    public static function getSpiderCount(): int
    {
        $spiderDir = root_path('runtime/spider/allSpider');
        $SpiderList = @scandir($spiderDir) ?: [];
        $spiderCount = 0;
        foreach ($SpiderList as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            // 过滤非统计文件
            $extInfo = @pathinfo($file);
            if (!isset($extInfo['extension'])
                || $extInfo['extension'] != 'txt') {
                continue;
            }

            $fileTemp = $spiderDir . '/' . $file;
            $spiderCount += strlen(read_file($fileTemp));
        }

        return $spiderCount;
    }
}