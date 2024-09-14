<?php

namespace app;

use app\common\exception\OperateException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Request;
use think\Response;
use Throwable;
use app\common\model\system\SystemLog;

/**
 * 应用异常处理类
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     * @var array
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        OperateException::class,
    ];

    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param Throwable $exception
     * @return void
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        if (saenv('system_exception')
            && !$this->isIgnoreReport($exception)) {
            // 写入异常日志
            $expLogs = [
                'module'        => app()->http->getName(),
                'controller'    => request()->controller(true),
                'action'        => request()->action(true),
                'params'        => serialize(request()->param()),
                'method'        => request()->method(),
                'url'           => request()->baseUrl(),
                'ip'            => get_real_ip(),
                'name'          => session('AdminLogin.name'),
            ];

            if (empty($expLogs['name'])) {
                $expLogs['name'] = 'system';
            }

            $expLogs['type'] = 1;
            $expLogs['file'] = $exception->getFile();
            $expLogs['line'] = $exception->getLine();
            $expLogs['error'] = $exception->getMessage();

            SystemLog::write($expLogs);
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param Request $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        switch (true) {
            case $e instanceof OperateException:
            case $e instanceof ValidateException:
                return json(['code' => $e->getCode() ?? 101, 'msg' => $e->getMessage()]);
            default:
                break;
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}
