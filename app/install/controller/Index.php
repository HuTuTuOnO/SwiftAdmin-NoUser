<?php
declare (strict_types=1);

namespace app\install\controller;

use think\facade\Cache;
use app\BaseController;
use think\response\Redirect;
use think\response\View;

class Index extends BaseController
{
    /**
     * 使用协议
     *
     * @return View
     */
    public function index(): View
    {
        Cache::clear();
        return view();
    }

    /**
     * 检测安装环境
     *
     * @return mixed
     */
    public function step1(): mixed
    {

        if (request()->isPost()) {

            // 检测生产环境
            foreach (checkenv() as $key => $value) {

                if ($key == 'php' && (float)$value < 7.3) {
                    return $this->error('PHP版本过低！');
                }

            }

            // 检测目录权限
            foreach (check_dirfile() as $value) {
                if ($value[1] == ERROR
                    || $value[2] == ERROR) {
                    return $this->error($value[3] . ' 权限读写错误！');
                }
            }

            Cache::set('checkenv', 'success');
            return json(['code' => 200, 'url' => '/install.php/index/step2']);
        }

        return view('', [
            'checkenv'     => checkenv(),
            'checkdirfile' => check_dirfile(),
        ]);
    }

    /**
     * 检查环境变量
     *
     * @return mixed
     */
    public function step2(): mixed
    {

        if (!Cache::get('checkenv')) {
            return redirect('/install.php/index/step1');
        }

        if (request()->isPost()) {

            // 链接数据库
            $params = input();
            try {
                $connect = @mysqli_connect($params['hostname'] . ':' . $params['hostport'], $params['username'], $params['password']);
            } catch (\Throwable $t) {
                return $this->error($t->getMessage());
            }
            if (!$connect) {
                return $this->error('数据库链接失败');
            }

            // 检测MySQL版本
            $mysqlInfo = mysqli_get_server_info($connect);
            if ((float)$mysqlInfo < 5.5) {
                return $this->error('MySQL版本过低');
            }

            // 查询数据库名
            try {
                $database = mysqli_select_db($connect, $params['database']);
            } catch (\Throwable $t) {
                return $this->error($t->getMessage());
            }
            if (!$database) {
                $query = "CREATE DATABASE IF NOT EXISTS `" . $params['database'] . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;";
                if (!mysqli_query($connect, $query)) {
                    return $this->error('数据库创建失败或已存在，请手动修改');
                }
            } else {
                $mysql_table = mysqli_query($connect, 'SHOW TABLES FROM' . ' ' . $params['database']);
                $mysql_table = mysqli_fetch_array($mysql_table);
                if (!empty($mysql_table) && is_array($mysql_table)) {
                    return $this->error('数据表已存在，请勿重复安装');
                }
            }

            Cache::set('mysqlInfo', $params);
            return json(['code' => 200, 'url' => '/install.php/index/step3']);
        }


        return view();
    }

    /**
     * 初始化数据库
     *
     * @return Redirect|View
     */
    public function step3()
    {
        $mysqlInfo = Cache::get('mysqlInfo');
        if (!$mysqlInfo) {
            return redirect('/install.php/index/step2');
        }

        return view();
    }

    /**
     * 安装数据缓存
     *
     * @return void
     */
    public function install()
    {
        if (request()->isAjax()) {

            $mysqlInfo = Cache::get('mysqlInfo');
            if (is_file('../extend/conf/install.lock') || !$mysqlInfo) {
                return $this->error('请勿重复安装本系统');
            }

            // 读取SQL文件加载进缓存
            $mysqlPath = app_path() . 'install.sql';
            $sqlRecords = file_get_contents($mysqlPath);
            $sqlRecords = str_replace("\r", "\n", $sqlRecords);

            // 替换数据库表前缀
            $sqlRecords = explode(";\n", $sqlRecords);
            $sqlRecords = str_replace(" `__PREFIX__", " `{$mysqlInfo['prefix']}", $sqlRecords);

            // 缓存任务总数
            $recordCount = count($sqlRecords);
            if ($recordCount >= 1) {
                Cache::set('sqlRecords', $sqlRecords);
                Cache::set('recordCount', $recordCount);
            } else {
                return $this->error('读取install.sql出错');
            }

            return $this->success('success');
        }
    }

    /**
     * 获取安装进度
     *
     * @return void
     */
    public function progress()
    {
        if (request()->isAjax()) {

            $mysqlInfo = Cache::get('mysqlInfo');
            $sqlRecords = Cache::get('sqlRecords');
            $recordCount = Cache::get('recordCount');

            // 链接数据库
            $sqlConnect = @mysqli_connect($mysqlInfo['hostname'] . ':' . $mysqlInfo['hostport'], $mysqlInfo['username'], $mysqlInfo['password']);
            mysqli_select_db($sqlConnect, $mysqlInfo['database']);
            mysqli_query($sqlConnect, "set names utf8mb4");

            $key = input('key/d') ?? 1;
            if (isset($sqlRecords[$key - 1])) {

                $sqlLine = trim($sqlRecords[$key - 1]);
                if (!empty($sqlLine)) {
                    try {
                        // 创建表数据
                        if (substr($sqlLine, 0, 12) == 'CREATE TABLE') {
                            $name = preg_replace("/^CREATE TABLE `(\w+)` .*/s", "\\1", $sqlLine);
                            $msg = "创建数据表 {$name}...";

                            if (mysqli_query($sqlConnect, $sqlLine) !== false) {
                                $msg .= '成功！';
                            } else {
                                $msg .= '失败！';
                            }
                        } else {
                            if (mysqli_query($sqlConnect, $sqlLine) === false) {
                                throw new \Exception(mysqli_error($sqlConnect));
                            }
                        }
                    } catch (\Throwable $th) {
                        return $this->error($th->getMessage());
                    }
                }

                // 修改初始化密码
                if ($key == ($recordCount - 1)) {
                    $pwd = encryptPwd($mysqlInfo['pwd']);
                    mysqli_query($sqlConnect, "UPDATE {$mysqlInfo['prefix']}admin SET pwd='{$pwd}' where id = 1");
                }

                // 更新进度
                $progress = round(($key / $recordCount) * 100) . '%';
                $result = [
                    'code'     => 200,
                    'total'    => $recordCount,
                    'key'      => $key,
                    'msg'      => $msg ?? '',
                    'progress' => $progress,
                ];

                Cache::set('progress', $progress);
                return json($result);
            }
        }
    }

    /**
     * 清理安装文件包
     *
     * @return void
     */
    public function clear()
    {

        if (request()->isAjax()) {
            try {

                $mysqlInfo = Cache::get('mysqlInfo');
                $env = app_path() . 'install.env';
                $parse = parse_ini_file($env, true);
                $parse['DATABASE']['HOSTNAME'] = $mysqlInfo['hostname'];
                $parse['DATABASE']['HOSTPORT'] = $mysqlInfo['hostport'];
                $parse['DATABASE']['DATABASE'] = $mysqlInfo['database'];
                $parse['DATABASE']['USERNAME'] = $mysqlInfo['username'];
                $parse['DATABASE']['PASSWORD'] = $mysqlInfo['password'];
                $parse['DATABASE']['PREFIX'] = $mysqlInfo['prefix'];

                $parseInfo = parse_array_ini($parse);
                write_file(root_path() . '.env', $parseInfo);
                write_file(root_path() . 'extend/conf/install.lock', 'success');

                // 随机后台文件
                $loginName = input('loginfile/s') ?? 'admin.php';
                copy('../extend/conf/admin.tpl', public_path() . $loginName);

                // 清理安装包
                Cache::clear();
                unlink(public_path() . 'install.php');
                recursiveDelete(app_path());
            } catch (\Throwable $th) {
                return $this->error($th->getMessage());
            }

            return $this->success('安装成功,如install模块未删除，请手动删除');
        }
    }
}