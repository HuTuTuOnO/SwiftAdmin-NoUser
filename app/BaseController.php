<?php
declare (strict_types = 1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于ThinkPHP6开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.NET High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: meystack <coolsec@foxmail.com> Apache 2.0 License Code
// +----------------------------------------------------------------------
namespace app;

use app\common\library\ResultCode;
use think\App;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\response\Json;
use think\Validate;
use think\Response;
use think\exception\HttpResponseException;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected mixed $request;

    /**
     * 应用实例
     * @var mixed
     */
    protected mixed $app;

    /**
     * 数据库实例
     * @var object
     */
    public object $model;

    /**
     * 是否验证
     * @var bool
     */
    public bool $isValidate = true;

    /**
     * 验证场景
     * @var string
     */
    public string $scene = '';

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate;

    /**
     * 验证错误消息
     * @var string
     */
    protected string $errorText;

    /**
     * 控制器中间件
     * @var array
     */
    protected array $middleware = [];

    /**
     * 获取访问来源
     * @var mixed
     */
    public mixed $referer;

    /**
     * 缓存时间
     * @var mixed|float|int
     */
    public mixed $expireTime = 60 * 10;

    /**
     * 域名
     * @var string
     */
    public string $host = '';

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
        $this->referer = $this->request->header('referer');
        $this->batchValidate = false;
        $this->host = $this->request->host();

        // 控制器初始化
        $this->initialize();
    }

    /**
     * 初始化
     * @access protected
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function initialize()
    {
        // 获取站点配置
        $configList = saenv('site', true);
        if ($configList && is_array($configList)) {
            foreach ($configList as $key => $value) {
                $this->app->view->assign($key,$value);
            }
        }
    }

    /**
     * 验证数据
     * @access protected
     * @param  $data
     * @param  $validate
     * @param array $message
     * @param bool $batch 是否批量验证
     * @return bool
     */
    protected function validate($data, $validate, array $message = [], bool $batch = false): bool
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 操作成功跳转
     * @access protected
     * @param string $msg
     * @param mixed $url
     * @param mixed $data
     * @param int $count
     * @param int $code
     * @param int $wait
     * @param array $header
     */
    protected function success(string $msg = '',mixed $url = '',mixed $data = '', int $count = 0,  int $code = 200, int $wait = 3, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            $url = $_SERVER["HTTP_REFERER"];
        } elseif ($url) {
            $url = (strpos($url, '://') || str_starts_with($url, '/')) ? $url : app('route')->buildUrl($url);
        }

        // 默认消息
        $msg = !empty($msg) ? __($msg) :  __('操作成功！');

        $result = [
            'code'  => $code,
            'msg'   => $msg,
            'data'  => $data,
            'count' => $count,
            'url'   =>(string)$url,
            'wait'  => $wait,
        ];

        $type = $this->getResponseType();
        if (strtolower($type) == 'html'){
            $response = view(config('app.dispatch_success'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }
        
        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $msg 提示信息
     * @param mixed $url 跳转的URL地址
     * @param mixed $data 返回的数据
     * @param int $code
     * @param integer $wait 跳转等待时间
     * @param array $header 发送的Header信息
     */
    protected function error(string $msg = '',mixed $url = '', mixed $data = '', int $code = 101, int $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            $url = request()->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || str_starts_with($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }
        $msg = !empty($msg) ? __($msg) :  __('操作失败！');
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
            'url'  =>(string)$url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html'){
            $response = view(config('app.dispatch_error'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }

        throw new HttpResponseException($response);
    }

    /**
     * URL重定向
     * @access protected
     * @param mixed $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param int $code http code
     * @param array $with 隐式传参
     */
    protected function redirect(mixed $url, array $params = [], int $code = 302, array $with = [])
    {
        $response = Response::create($url, 'redirect');

        if (is_integer($params)) {
            $code   = $params;
            $params = [];
        }
     
        $response->code($code);
        throw new HttpResponseException($response);
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType(): string
    {
        return request()->isJson() || request()->isAjax() ? 'json' : 'html';
    }

    /**
     * 生成静态HTML
     * @access protected
     * @param $htmlFile
     * @param $htmlPath
     * @param $templateFile
     * @param string $suffix
     * @return bool
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    protected function buildHtml($htmlFile, $htmlPath, $templateFile, string $suffix = 'html'): bool
    {
        $content = $this->app->view->fetch($templateFile);
        if (saenv('compression')) {
            $content = preg_replace('/\s+/i', ' ', $content);
        }
        $htmlPath = !empty($htmlPath) ? $htmlPath : './';
        $htmlFile = $htmlPath . $htmlFile . '.' . $suffix;
        if (!write_file($htmlFile, $content)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 获取模型字段集
     * @access protected
     * @param null $model
     */
    protected function getTableFields($model = null)
    {
        $model = $model ?: $this->model;
        $tableFields = $model->getTableFields();
        if (!empty($tableFields) && is_array($tableFields)) {
            foreach ($tableFields as $key => $value) {
                $filter = ['update_time', 'create_time', 'delete_time'];
                if (!in_array($value, $filter)) {
                    $tableFields[$value] = '';
                }

                unset($tableFields[$key]);
            }
        }

        return $tableFields;
    }

    /**
     * @param string $msg
     * @param int $code
     */
    public function notFound(string $msg = 'not found!', int $code = 404): void
    {
        abort($code, $msg);
    }

    /**
     * 空方法
     */
    public function __call($method, $args)
    {
        abort(404, '页面不存在');
    }
}
