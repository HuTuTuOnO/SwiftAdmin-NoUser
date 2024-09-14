<?php

// 全局应用公共文件
use app\common\library\Security;
use app\common\model\system\Config;
use Overtrue\Pinyin\Pinyin;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

const NAMESPACE_LIBRARY = '\\app\\common\\library\\';
// +----------------------------------------------------------------------
// | 文件操作函数开始
// +----------------------------------------------------------------------
if (!function_exists('read_file')) {
    /**
     * 获取文件内容
     * @param string $file 文件路径
     * @return string
     */
    function read_file(string $file): string
    {
        return !is_file($file) ? '' : @file_get_contents($file);
    }
}


if (!function_exists('write_file')) {
    /**
     * 数据写入文件
     * @param string $file 文件路径
     * @param string $content 文件数据
     */
    function write_file(string $file, string $content = '', int $flags = 0)
    {
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mk_dirs($dir);
        }

        return @file_put_contents($file, $content, $flags);
    }
}

if (!function_exists('mk_dirs')) {
    /**
     * 递归创建文件夹
     * @param $path
     * @param int $mode 文件夹权限
     * @return bool
     */
    function mk_dirs($path, int $mode = 0777): bool
    {
        if (!is_dir(dirname($path))) {
            mk_dirs(dirname($path));
        }
        if (!file_exists($path)) {
            return mkdir($path, $mode);
        }

        return true;
    }
}

if (!function_exists('arr2file')) {
    /**
     * 数组写入文件
     * @param $file
     * @param $array
     * @return false|int
     */
    function arr2file($file, $array)
    {
        if (is_array($array)) {
            $cont = var_exports($array);
        } else {
            $cont = $array;
        }
        $cont = "<?php\nreturn $cont;";
        return write_file($file, $cont);
    }
}

if (!function_exists('arr2router')) {
    /**
     * 数组写入路由文件
     * @param string $file 文件路径
     * @param mixed $array
     * @return false|int
     */
    function arr2router(string $file, $array)
    {
        if (is_array($array)) {
            $cont = var_exports($array);
        } else {
            $cont = $array;
        }
        $cont = "<?php\nuse think\\facade\\Route;\n\n$cont";
        return write_file($file, $cont);
    }
}

if (!function_exists('var_exports')) {
    /**
     * 数组语法(方括号)
     * @param array $expression 数组
     * @param bool $return 返回类型
     */
    function var_exports(array $expression, bool $return = true)
    {
        $export = var_export($expression, true);
        $patterns = [
            "/array \(/"                       => '[',
            "/^([ ]*)\)(,?)$/m"                => '$1]$2',
            "/=>[ ]?\n[ ]+\[/"                 => '=> [',
            "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
        ];

        $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
        if ($return) {
            return $export;
        } else {
            echo $export;
        }
    }
}

if (!function_exists('recursive_delete')) {
    /**
     * 递归删除目录
     * @param string $dir 目录路径
     */
    function recursive_delete(string $dir)
    {
        // 打开指定目录
        if ($handle = @opendir($dir)) {
            while (($file = readdir($handle)) !== false) {
                if (($file == ".") || ($file == "..")) {
                    continue;
                }
                if (is_dir($dir . '/' . $file)) { // 递归
                    recursive_delete($dir . '/' . $file);
                } else {
                    unlink($dir . '/' . $file); // 删除文件
                }
            }
            @closedir($handle);
            rmdir($dir);
        }
    }
}

// +----------------------------------------------------------------------
// | 字符串函数开始
// +----------------------------------------------------------------------
//
if (!function_exists('release')) {

    /**
     * 获取静态版本
     * @return mixed
     */
    function release(): mixed
    {
        return config('app.version');
    }
}

if (!function_exists('delNr')) {
    /**
     * 去掉换行
     * @param string $str 字符串
     * @return string
     */
    function delNr(string $str): string
    {
        $str = str_replace(array("<nr/>", "<rr/>"), array("\n", "\r"), $str);
        return trim($str);
    }
}

if (!function_exists('delNt')) {
    /**
     * 去掉连续空白
     * @param string $str 字符串
     * @return string
     */
    function delNt(string $str): string
    {
        $str = str_replace("　", ' ', str_replace("", ' ', $str));
        $str = preg_replace("/[\r\n\t ]{1,}/", ' ', $str);
        return trim($str);
    }
}

if (!function_exists('msubstr')) {
    /**
     * 字符串截取(同时去掉HTML与空白)
     * @param string $str
     * @param int $start
     * @param int $length
     * @param string $charset
     * @param bool $suffix
     * @return string
     */
    function msubstr(string $str, int $start = 0, int $length = 100, string $charset = "utf-8", bool $suffix = true): string
    {
        $str = preg_replace('/<[^>]+>/', '', preg_replace("/[\r\n\t ]{1,}/", ' ', delNt(strip_tags($str))));
        $str = preg_replace('/&(\w{4});/i', '', $str);

        // 直接返回
        if ($start == -1) {
            return $str;
        }

        if (function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            $slice = iconv_substr($str, $start, $length, $charset);

        } else {
            $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
            $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
            $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
            $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("", array_slice($match[0], $start, $length));
        }

        $fix = '';
        if (strlen($slice) < strlen($str)) {
            $fix = '...';
        }
        return $suffix ? $slice . $fix : $slice;
    }
}

if (!function_exists('cdn_Prefix')) {
    /**
     * 获取远程图片前缀
     */
    function cdn_Prefix()
    {
        return saenv('upload_http_prefix');
    }
}

if (!function_exists('pinyin')) {
    /**
     * 获取拼音
     * @param string $str 需要转换的汉子
     * @param bool $abbr 是否只要首字母
     * @param bool $first
     * @param bool $trim 是否清除空格
     * @return array|string|string[]|null
     */
    function pinyin(string $str, bool $abbr = false, bool $first = false, bool $trim = true)
    {
        if (preg_match('/^[A-Za-z0-9]+$/', $str)) {
            return strtolower($str);
        } else {
            $obj = new Pinyin();

            if (!$abbr) { // 获取姓名
                $string = $obj->name($str);
                if (is_array($string)) {
                    $string = implode('', $string);
                }
            } else { // 获取首字符
                $string = $obj->abbr($str);
            }
            if ($first) {
                return strtoupper(substr($string, 0, 1));
            }
            return $trim ? preg_replace('/\s+/', '', $string) : $string;
        }
    }
}

if (!function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本
     * @param $size
     * @param string $delimiter
     * @return string
     */
    function format_bytes($size, string $delimiter = ' '): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }
}

if (!function_exists('hide_str')) {
    /**
     * 将一个字符串部分字符用*替代隐藏
     * @param string $string 待转换的字符串
     * @param int $begin 起始位置，从0开始计数，当$type=4时，表示左侧保留长度
     * @param int $len 需要转换成*的字符个数，当$type=4时，表示右侧保留长度
     * @param int $type 转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串中间用***代替
     * @param string $glue 分割符
     */
    function hide_str(string $string, int $begin = 3, int $len = 4, int $type = 0, string $glue = "@")
    {
        if (empty($string)) {
            return false;
        }

        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($type == 0) {
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }
            }
            $string = implode("", $array);
        } elseif ($type == 1) {
            $array = array_reverse($array);
            for ($i = $begin; $i < ($begin + $len); $i++) {
                if (isset($array[$i])) {
                    $array[$i] = "*";
                }
            }
            $string = implode("", array_reverse($array));
        } elseif ($type == 2) {
            $array = explode($glue, $string);
            if (isset($array[0])) {
                $array[0] = hide_str($array[0], $begin, $len, 1);
            }
            $string = implode($glue, $array);
        } elseif ($type == 3) {
            $array = explode($glue, $string);
            if (isset($array[1])) {
                $array[1] = hide_str($array[1], $begin, $len, 0);
            }
            $string = implode($glue, $array);
        } elseif ($type == 4) {
            $left = $begin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($len - $right); $i++) {
                if (isset($array[$i])) {
                    $tem[] = $i >= $left ? "" : $array[$i];
                }
            }
            $tem[] = '*****';
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                if (isset($array[$i])) {
                    $tem[] = $array[$i];
                }
            }
            $string = implode("", $tem);
        }
        return $string;
    }
}

// +----------------------------------------------------------------------
// | 系统APP函数开始
// +----------------------------------------------------------------------
// 
if (!function_exists('__')) {
    /**
     * 全局多语言函数
     */
    function __($str, $vars = [], $lang = '')
    {
        if (is_numeric($str) || empty($str)) {
            return $str;
        }

        if (!is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }

        return lang($str, $vars, $lang);
    }
}

if (!function_exists('saenv')) {
    /**
     * 获取系统配置信息
     * @param string $name
     * @param bool $group
     * @return mixed
     */
    function saenv(string $name = '', bool $group = false): mixed
    {
        $result = [];
        try {
            if (empty($group)) {
                $result = Config::where('name', $name)->cache(true, 0, 'core_system')->find();
                if (!empty($result)) {
                    $type = $result['type'] ?? 'string';
                    return 'array' == $type ? json_decode($result['value'], true) : $result['value'];
                }
            } else {
                $where[] = ['group', '=', $name];
                $list = Config::where($where)->cache(true, 0, 'core_system')->select()->toArray();
                foreach ($list as $option) {
                    if (!is_empty($option['type']) && 'array' == trim($option['type'])) {
                        $result[$option['name']] = json_decode($option['value'], true);
                    } else {
                        $result[$option['name']] = $option['value'];
                    }
                }
            }
        } catch (\Throwable $th) {}
        return $result;
    }
}

if (!function_exists('parse_array_ini')) {
    /**
     * 解析数组到ini文件
     * @param array $array 数组
     * @param string $content 字符串
     * @return string    返回一个ini格式的字符串
     */
    function parse_array_ini(array $array, string $content = ''): string
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                // 分割符PHP_EOL
                $content .= PHP_EOL . '[' . $key . ']' . PHP_EOL;
                foreach ($value as $field => $data) {
                    $content .= $field . ' = ' . $data . PHP_EOL;
                }
            } else {
                $content .= $key . ' = ' . $value . PHP_EOL;
            }
        }

        return $content;
    }
}

if (!function_exists('get_page_params')) {
    /**
     * 获取分页参数
     */
    function get_page_params()
    {
        $reqUri = request()->server('REQUEST_URI');
        if (!empty($reqUri)) {
            $urlPath = substr($reqUri, 0, stripos($reqUri, '?') + 1);
            $array = explode('&', str_replace($urlPath, '', $reqUri));
            $params = [];
            foreach ($array as $value) {
                if (!$value) {
                    continue;
                }
                list($k, $v) = explode('=', $value);
                $params[$k] = $v;
            }

            $params['page'] = 'page';
            return urldecode($urlPath . http_build_query($params));
        }

        return false;
    }
}

if (!function_exists('get_page_render')) {

    /**
     * 获取分页地址v2
     * @param mixed $currentPage 当前页
     * @param mixed $totalPages 总页码
     * @param mixed $pageUrl 分页地址
     * @param int $halfPer 分页侧边长度
     * @param mixed|null $linkPage 返回分页地址
     * @return string
     */
    function get_page_render($currentPage, $totalPages, $pageUrl, int $halfPer = 3, $linkPage = null): string
    {
        $planUrl = 'page=page';
        $pageUrl = strtolower($pageUrl);
        if (strstr($pageUrl, $planUrl)) {
            $pageUrl = str_replace($planUrl, 'planUrl=page', $pageUrl);
        }

        if ($currentPage <= 1) {
            $linkPage .= '<em>首页</em><em>上一页</em>';
        } else {
            $linkPage .= '<a href="' . str_replace('page', 1, $pageUrl) . '" class="first">首页</a>';
            $linkPage .= '<a href="' . str_replace('page', ($currentPage - 1), $pageUrl) . '" class="prev">上一页</a>';
        }

        /**
         * 中间页码
         */
        for ($i = $currentPage - $halfPer, $i > 1 || $i = 1,
             $j = $currentPage + $halfPer,
             $j < $totalPages || $j = $totalPages; $i < (int)$j + 1; $i++) {
            $linkPage .= ($i == $currentPage) ? '<span>' . $i . '</span>' : '<a href="' . str_replace('page', $i, $pageUrl) . '">' . $i . '</a>';
        }

        // 当前页码小于总数
        if ($currentPage < $totalPages) {
            $linkPage .= '<a href="' . str_replace('page', ($currentPage + 1), $pageUrl) . '" class="next">下一页</a>';
            $linkPage .= '<a href="' . str_replace('page', $totalPages, $pageUrl) . '" class="end">尾页</a>';
        } else {
            $linkPage .= '<em>下一页</em><em>尾页</em>';
        }

        // 祛除首页地址
        $linkPage = str_replace('planUrl', 'page', $linkPage);
        return str_replace('list_1.html', '', $linkPage);
    }
}

if (!function_exists('parse_tag')) {
    /**
     * 标签解析
     * @param $argc
     * @return array
     */
    function parse_tag($argc): array
    {
        if (is_array($argc)) {
            return $argc;
        }

        $params = [];
        $tags = explode(';', $argc);
        foreach ($tags as $value) {
            if (!empty($value)) {
                list($k, $v) = explode(':', trim($value));
                if (!empty($v)) {
                    $params[$k] = $v;
                }
            }
        }

        return $params;
    }
}

if (!function_exists('list_search')) {
    /**
     * 从数组查找数据返回
     * @param array $list 原始数据
     * @param array $condition 规则['id'=>'??']
     * @return mixed
     */
    function list_search(array $list, array $condition)
    {
        // 返回的结果集合
        $resultSet = array();
        foreach ($list as $key => $data) {
            $find = false;
            foreach ($condition as $field => $value) {
                if (isset($data[$field])) {
                    if (0 === strpos($value, '/')) {
                        $find = preg_match($value, $data[$field]);
                    } else if ($data[$field] == $value) {
                        $find = true;
                    }
                }
            }
            if ($find)
                $resultSet[] = &$list[$key];
        }

        if (!empty($resultSet[0])) {
            return $resultSet[0];
        } else {
            return false;
        }
    }
}
if (!function_exists('list_to_tree')) {
    /**
     * 根据ID和PID返回一个树形结构
     * @param array $list 数组结构
     * @return array
     */
    function list_to_tree($list, $id = 'id', $pid = 'pid', $child = 'children', $level = 0)
    {
        // 创建Tree
        $tree = $refer = array();
        if (is_array($list)) {

            // 创建基于主键的数组引用
            foreach ($list as $key => $data) {
                $refer[$data[$id]] = &$list[$key];
            }

            foreach ($list as $key => $data) {

                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($level == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }

        return $tree;
    }
}

if (!function_exists('tree_to_list')) {
    /**
     * 根据ID和PID返回一个数组结构
     * @param array $tree 多位数组
     * @param string $child
     * @param string $order
     * @param array $list
     * @return bool|array
     */
    function tree_to_list(array $tree, string $child = 'children', string $order = 'id', array &$list = array())
    {
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if (isset($reffer[$child])) {
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }

        $list = list_sort_by($list, $order, 'nat');

        return $list;
    }
}

if (!function_exists('list_sort_by')) {
    /**
     *----------------------------------------------------------
     * 对查询结果集进行排序
     *----------------------------------------------------------
     * @access public
     *----------------------------------------------------------
     * @param array $list 查询结果
     * @param string $field 排序的字段名
     * @param array $sortby 排序类型
     * @switch string  asc正向排序 desc逆向排序 nat自然排序
     *----------------------------------------------------------
     * @return mixed
     *----------------------------------------------------------
     */
    function list_sort_by($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = array();
            foreach ($list as $i => $data)
                $refer[$i] = &$data[$field];
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val)
                $resultSet[] = &$list[$key];
            return $resultSet;
        }

        return false;
    }
}

if (!function_exists('is_empty')) {
    /**
     * 判断是否为空值
     * @param $value
     * @return bool
     */
    function is_empty($value): bool
    {
        if (!isset($value)) {
            return true;
        }

        if (trim($value) === '') {
            return true;
        }

        return false;
    }
}

if (!function_exists('is_mobile')) {

    /**
     * 验证输入的手机号码
     * @access  public
     * @param $mobile
     * @return bool
     */
    function is_mobile($mobile): bool
    {
        $chars = "/^((\(\d{2,3}\))|(\d{3}\-))?1(3|5|8|9)\d{9}$/";
        if (preg_match($chars, $mobile)) {
            return true;

        } else {
            return false;
        }
    }
}

// +----------------------------------------------------------------------
// | 数据加密函数开始
// +----------------------------------------------------------------------
if (!function_exists('encryptPwd')) {
    /**
     * hash - 密码加密
     */
    function encryptPwd($pwd, $salt = 'swift', $encrypt = 'md5')
    {
        return $encrypt($pwd . $salt);
    }
}

if (!function_exists('search_model')) {
    /**
     * 获取搜索模式
     */
    function search_model($model): string
    {
        $model = $model ?? saenv('search_model');
        return NAMESPACE_LIBRARY . $model;
    }
}


// +----------------------------------------------------------------------
// | 时间相关函数开始
// +----------------------------------------------------------------------
if (!function_exists('linux_time')) {
    /**
     * 获取某天前时间戳
     * @param $day
     * @return int
     */
    function linux_time($day): int
    {
        $day = intval($day);
        return mktime(23, 59, 59, intval(date("m")), intval(date("d")) - $day, intval(date("y")));
    }
}

if (!function_exists('today_seconds')) {
    /**
     * 返回今天还剩多少秒
     * @return int
     */
    function today_seconds(): int
    {
        $time = mktime(23, 59, 59, intval(date("m")), intval(date("d")), intval(date("y")));
        return $time - time();
    }
}


if (!function_exists('is_today')) {
    /**
     * 判断当前是否为当天时间
     * @param $time
     * @return bool
     */
    function is_today($time): bool
    {

        if (!$time) {
            return false;
        }

        $today = date('Y-m-d');
        if (str_contains($time, '-')) {
            $time = strtotime($time);
        }

        if ($today == date('Y-m-d', $time)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('format_date')) {
    /**
     * 格式化时间
     * @param [type] $time
     * @return string
     */
    function format_date($time): string
    {
        $time = strtotime($time ?? time());
        return date('m-d', $time);
    }
}

if (!function_exists('published_date')) {
    /**
     * 格式化时间
     * @param [type] $time
     * @param bool $unix
     * @return string
     */
    function published_date($time, bool $unix = true): string
    {
        if (!$unix) {
            $time = strtotime($time);
        }

        $currentTime = time() - $time;
        $published = array(
            '86400' => '天',
            '3600'  => '小时',
            '60'    => '分钟',
            '1'     => '秒'
        );

        if ($currentTime == 0) {
            return '1秒前';
        } else if ($currentTime >= 604800 || $currentTime < 0) {
            return date('Y-m-d H:i:s', $time);
        } else {
            foreach ($published as $k => $v) {
                if (0 != $c = floor($currentTime / (int)$k)) {
                    return $c . $v . '前';
                }
            }
        }

        return date('Y-m-d H:i:s', $time);
    }
}

if (!function_exists('get_real_ip')) {
    /**
     * 获取客户端IP地址
     * @param int $type
     * @param bool $adv
     * @return string
     */
    function get_real_ip(int $type = 0, bool $adv = true): string
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }

        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) {
                unset($arr[$pos]);
            }
            $ip = trim(current($arr));
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? array($ip, $long) : array('0.0.0.0', 0);
        return $ip[$type];
    }
}

if (!function_exists('get_platform_guest')) {
    /**
     * 获取平台URL
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    function get_platform_guest(): string
    {
        $site_http = saenv('site_http');
        // 开启手机版本
        $site_state = saenv('site_state');
        // 是否独立域名 1是 0否
        $site_type = saenv('site_type');
        // 是否访问的手机域名
        $site_host = request()->host();
        $site_mobile = saenv('site_mobile');
        if ($site_state && $site_type && stripos($site_mobile, $site_host) !== false) {
            $site_http = $site_mobile;
        }

        return rtrim($site_http, '/');
    }
}

if (!function_exists('get_request_url')) {
    /**
     * 获取请求URL
     * @return string
     */
    function get_request_url(): string
    {
        $protocol = request()->isSsl() ? 'https://' : 'http://';
        return $protocol .  request()->host();
    }
}

if (!function_exists('set_mobile_url')) {
    /**
     * 设置手机URL
     * @param string $url
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    function set_mobile_url(string $url): string
    {
        $site_http = saenv('site_http');
        $site_mobile = saenv('site_mobile');
        return str_replace($site_http, $site_mobile, $url);
    }
}

if (!function_exists('format_read')) {
    /**
     * 读取阅读整数
     *
     * @param $reads
     * @return string
     */
    function format_read($reads): string
    {
        if (empty($reads)) {
            return 0;
        }

        if ($reads < 1000) {
            return $reads;
        }

        return sprintf('%.1fk', $reads / 1000);
    }
}

if (!function_exists('distance_day')) {
    /**
     * 计算天数
     * @param $time
     * @return false|float
     */
    function distance_day($time)
    {

        if (!$time) {
            return false;
        }

        if (!is_numeric($time)) {
            $time = strtotime($time);
        }

        $time = time() - $time;
        return (int)ceil($time / (60 * 60 * 24));
    }
}
if (!function_exists('createOrderId')) {
    /**
     * 生成订单号
     * @return string
     */
    function createOrderId(): string
    {
        $gradual = 0;
        $orderId = date('YmdHis') . mt_rand(10000000, 99999999);
        $length = strlen($orderId);

        // 循环处理随机数
        for ($i = 0; $i < $length; $i++) {
            $gradual += (int)(substr($orderId, $i, 1));
        }

        $code = (100 - $gradual % 100) % 100;
        return $orderId . str_pad((string)$code, 2, '0', STR_PAD_LEFT);

    }
}
if (!function_exists('createOrderShortId')) {
    /**
     * 生成订单短ID
     * @return string
     */
    function createOrderShortId(): string
    {
        return date('Ymd') . substr(implode('', array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
}

// +----------------------------------------------------------------------
// | 系统安全函数开始
// +----------------------------------------------------------------------
if (!function_exists('request_validate_rules')) {
    /**
     * 自动请求验证规则
     * @param array $data POST数据
     * @param string $validateClass 验证类名
     * @param string $validateScene 验证场景
     */
    function request_validate_rules(array $data = [], string $validateClass = '', string $validateScene = '')
    {
        if (!empty($validateClass)) {
            if (!preg_match('/app\x{005c}(.*?)\x{005c}/', $validateClass, $match)) {
                $validateClass = '\\app\\common\\validate\\' . ucfirst($validateClass);
            } else {
                $validateClass = str_replace("\\model\\", "\\validate\\", $validateClass);
            }
            try {
                if (class_exists($validateClass)) {
                    $validate = new $validateClass;
                    if (!$validate->scene($validateScene)->check($data)) {
                        return $validate->getError();
                    }
                }
            } catch (Throwable $th) {
                return $th->getMessage();
            }
        }

        return $data;
    }
}

if (!function_exists('safe_input')) {
    /**
     * 过滤函数
     * @param string $key
     * @param $default
     * @param string $filter
     * @return mixed
     */
    function safe_input(string $key = '', $default = null, string $filter = 'trim,strip_tags,htmlspecialchars'): mixed
    {
        return input($key, $default, $filter);
    }
}

if (!function_exists('supplement_id')) {
    /**
     * 用户ID风格
     * @param string $id
     * @return string
     */
    function supplement_id(string $id): string
    {
        $len = strlen($id);
        $buf = '000000';
        return $len < 6 ? substr($buf, 0, (6 - $len)) . $id : $id;
    }
}

if (!function_exists('remove_xss')) {
    /**
     * 清理XSS
     */
    function remove_xss($content, $is_image = false)
    {
        return Security::instance()->xss_clean($content, $is_image);
    }
}
