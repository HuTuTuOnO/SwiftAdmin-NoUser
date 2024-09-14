<?php

declare(strict_types=1);
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

use app\admin\enums\AdminEnum;
use app\admin\service\AuthService;
use think\facade\Db;
use think\facade\Log;
use think\Response;
use Throwable;

// 后台全局控制器基类
class AdminController extends BaseController
{
    /**
     * 接口权限
     * @var object
     */
    public object $authService;

    /**
     * 数据表名称
     * @var string
     */
    public string $tableName;

    /**
     * 控制器/类名
     * @var string
     */
    public string $controller;

    /**
     * 控制器方法
     * @var string
     */
    public string $action;

    /**
     * 控制器/方法名
     * @var string
     */
    public string $method;

    /**
     * 操作状态
     * @var mixed
     */
    public mixed $status = false;

    /**
     * 管理员ID
     * @var int
     */
    public int $adminId = 0;

    /**
     * 管理员信息
     * @var array
     */
    public array $adminInfo = [];

    /**
     * 获取模板
     * @access   protected
     * @var      string
     */
    public string $template;

    /**
     * 共享模板
     * @var string
     */
    public string $tplsharing = 'add';

    /**
     * 当前表字段
     *
     * @var array
     */
    protected array $tableFields = [];

    /**
     * 默认开关
     *
     * @var string
     */
    protected string $keepField = 'status';

    /**
     * 开启数据限制
     * 默认关闭
     * @var bool
     */
    protected bool $dataLimit = false;

    /**
     * 数据限制字段
     *
     * @var string
     */
    protected string $dataLimitField = 'admin_id';

    /**
     * 需要排除的字段
     *
     * @var mixed
     */
    protected mixed $ruleOutFields = "";

    /**
     * 查询过滤字段
     *
     * @var array
     */
    protected array $filterWhere = ['page', 'limit'];

    /**
     * 关联模型
     * @var array 关联模型
     */
    protected array $relationModel = [];

    /**
     * 查询转换字段
     *
     * @var array
     */
    protected array $converTime = ['create_time', 'update_time', 'delete_time'];

    /**
     * 不需要鉴权的方法
     * @var array
     */
    protected array $noNeedLogin = [
        '/Index/index', // 后台首页
    ];

    /**
     * 跳转URL地址
     * @var string
     */
    protected string $JumpUrl = '/';

    /**
     * 后台应用全局初始化
     * @access public
     */
    protected function initialize()
    {
        $this->adminInfo = session(AdminEnum::ADMIN_SESSION) ?? [];
        if (empty($this->adminInfo)) {
            return $this->redirect(url('/login')->suffix(false));
        }

        $this->adminId = $this->adminInfo['id'];
        $this->controller = request()->controller();
        $this->action = request()->action();
        $this->method = '/' . $this->controller . '/' . $this->action;
        // 开始校验菜单权限
        $this->authService = AuthService::instance();
        $allFit = in_array('*', $this->noNeedLogin);
        if (!$allFit && !in_array($this->method, $this->noNeedLogin)) {
            $superAdmin = $this->authService->superAdmin();
            if (!$superAdmin && !$this->authService->permissions($this->method, $this->adminId)) {
                return request()->isAjax() ? $this->error('没有权限!') : $this->ForbiddenError('没有权限！', 401);
            }
        }

        // 分配前端变量
        $this->app->view->assign([
            'app'           => request()->root(),
            'controller'    => $this->controller,
            'action'        => $this->action,
            'AdminLogin'    => $this->adminInfo
        ]);
    }

    /**
     * @param string $msg
     * @param int $code
     * @return Response
     */
    public function ForbiddenError(string $msg = 'not found!', int $code = 404): Response
    {
        $this->app->view->assign('msg', $msg);
        $errorPage = $this->app->view->fetch('error/404');
        return response($errorPage, $code);
    }

    /**
     * 获取资源
     */
    public function index()
    {
        if (request()->isAjax()) {

            $page = input('page/d');
            $limit = input('limit/d');
            $where = $this->buildSelectParams();
            $count = $this->model->where($where)->count();
            $limit = is_empty($limit) ? 10 : $limit;
            $page = $count <= $limit ? 1 : $page;
            $fieldList = $this->model->getFields();
            $order = !array_key_exists('sort', $fieldList) ? 'id' : 'sort';

            $subQuery = $this->model->field('id')->where($where)->order($order, 'desc')->limit((int)$limit)->page((int)$page)->buildSql();
            $subQuery = '( SELECT object.id FROM ' . $subQuery . ' AS object )';
            $list = $this->model->with($this->relationModel)->where('id in' . $subQuery)->order($order, 'desc')->select()->toArray();

            return $this->success('查询成功', null, $list, $count);
        }

        return view();
    }

    /**
     * 添加资源
     */
    public function add()
    {
        if (request()->isPost()) {

            $post = $this->preruleOutFields(input());
            if ($this->dataLimit) {
                $post[$this->dataLimitField] = $this->adminInfo['id'];
            }

            $validate = $this->isValidate ? get_class($this->model) : $this->isValidate;
            $post = request_validate_rules($post, $validate, $this->scene);
            if (empty($post) || !is_array($post)) {
                return $this->error($post);
            }

            Db::startTrans();
            try {
                $this->status = $this->model->create($post);
                Db::commit();
            } catch (\PDOException $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            } catch (Throwable $th) {
                Db::rollback();
                return $this->error($th->getMessage());
            }

            return $this->status ? $this->success() : $this->error();
        }

        return view('', ['data' => $this->getTableFields()]);
    }

    /**
     * 编辑资源
     */
    public function edit()
    {
        $id = input('id/d');
        $data = $this->model->find($id);

        // 限制数据调用
        if (
            !$this->authService->SuperAdmin() && $this->dataLimit
            && in_array($this->dataLimitField, $this->model->getFields())
        ) {
            if ($data[$this->dataLimitField] != $this->adminInfo['id']) {
                return $this->error('无权访问');
            }
        }

        if (request()->isPost()) {

            $post = $this->preruleOutFields(input());
            $validate = $this->isValidate ? get_class($this->model) : $this->isValidate;
            $post = request_validate_rules($post, $validate, $this->scene);
            if (empty($post) || !is_array($post)) {
                return $this->error($post);
            }

            Db::startTrans();
            try {
                $this->status = $this->model->update($post);
                Db::commit();
            } catch (\PDOException $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            } catch (Throwable $th) {
                Db::rollback();
                return $this->error($th->getMessage());
            }

            return $this->status ? $this->success() : $this->error();
        }


        return View($this->tplsharing, ['data' => $data]);
    }

    /**
     * 删除资源
     */
    public function del()
    {
        $id = input('id');
        !is_array($id) && ($id = array($id));

        if (!empty($id) && is_array($id)) {

            Db::startTrans();
            try {
                $where[] = ['id', 'in', implode(',', $id)];
                $list = $this->model->where($where)->select();
                foreach ($list as $key => $item) {

                    if (
                        !$this->authService->SuperAdmin() && $this->dataLimit
                        && in_array($this->dataLimitField, $this->model->getFields())
                    ) {
                        if ($item[$this->dataLimitField] != $this->adminInfo['id']) {
                            continue;
                        }
                    }

                    // 过滤字段
                    if (isset($item->isSystem) && $item->isSystem) {
                        throw new \Exception('禁止删除系统级字段');
                    }

                    if (isset($item['status']) && $item['status'] != 0) {
                        $this->status += $item->save(['status' => 0]);
                    } else {
                        $this->status += $item->delete();
                    }
                }

                Db::commit();
            } catch (\PDOException $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            } catch (Throwable $th) {
                Db::rollback();
                return $this->error($th->getMessage());
            }

            $this->status && $this->success();
        }

        return $this->error();
    }


    /**
     * 修改资源状态
     *
     */
    public function status()
    {
        if (request()->isAjax()) {

            $status = input('status/d', 0);
            $where[] = ['id', '=', input('id/d')];
            if (!$this->authService->SuperAdmin() && $this->dataLimit
                && in_array($this->dataLimitField, $this->model->getFields())) {
                $where[] = [$this->dataLimitField, '=', $this->adminInfo['id']];
            }

            Db::startTrans();
            try {
                $this->model->where($where)->update(['status' => $status]);
                Db::commit();
            } catch (\PDOException $e) {
                Db::rollback();
                return $this->error($e->getMessage());
            } catch (Throwable $th) {
                Db::rollback();
                return $this->error($th->getMessage());
            }

            $this->success();
        }

        return $this->error();
    }

    /**
     * 排除特定字段
     *
     * @param [type] $params
     */
    protected function preruleOutFields($params = '')
    {
        if (is_array($this->ruleOutFields)) {
            foreach ($this->ruleOutFields as $field) {
                if (key_exists($field, $params)) {
                    unset($params[$field]);
                }
            }
        } else {
            if (key_exists($this->ruleOutFields, $params)) {
                unset($params[$this->ruleOutFields]);
            }
        }

        return $params;
    }

    /**
     * 获取查询参数
     *
     * @param array $searchFields
     * @return array|false
     */
    protected function buildSelectParams(array $searchFields = [])
    {
        $params = request()->param();

        if (!empty($params) && is_array($params)) {

            $where = [];
            // 获取模型字段
            $this->tableFields = $this->model->getFields();

            foreach ($params as $field => $value) {

                // 过滤字段
                if (in_array($field, $this->filterWhere)) {
                    continue;
                }

                // 非表内字段
                if (!array_key_exists($field, $this->tableFields)) {
                    continue;
                }

                // 默认状态字段
                if ($field == $this->keepField) {
                    $where[] = [$field, '=', intval($value - 1)];
                    continue;
                }

                // 获取类型
                $type = $this->tableFields[$field]['type'];
                $type = explode('(', $type)[0];
                $value = str_replace('/\s+/', '', $value);
                switch ($type) {
                    case 'char':
                    case 'text':
                    case 'varchar':
                    case 'tinytext':
                    case 'longtext':
                        $where[] = [$field, 'like', '%' . $value . '%'];
                        break;
                    case 'int':
                    case 'bigint':
                    case 'integer':
                    case 'tinyint':
                    case 'smallint':
                    case 'mediumint':
                    case 'float':
                    case 'double':
                    case 'timestamp':
                    case 'year':
                        $value = str_replace(',', '-', $value);
                        if (strpos($value, '-')) {

                            $arr = explode(' - ', $value);
                            if (empty($arr)) {
                                continue 2;
                            }

                            if (in_array($field, $this->converTime)) {
                                if (isset($arr[0])) {
                                    $arr[0] = strtotime($arr[0]);
                                }
                                if (isset($arr[1])) {
                                    $arr[1] = strtotime($arr[1]);
                                }
                            }

                            $exp = 'between';
                            if ($arr[0] === '') {
                                $exp = '<=';
                                $arr = $arr[1];
                            } elseif ($arr[1] === '') {
                                $exp = '>=';
                                $arr = $arr[0];
                            }

                            $where[] = [$field, $exp, $arr];
                        } else {
                            $where[] = [$field, '=', $value];
                        }
                        break;
                    case 'set';
                        $where[] = [$field, 'find in set', $value];
                        break;
                    case 'enum';
                        $where[] = [$field, '=', $value];
                        break;
                    case 'date';
                    case 'time';
                    case 'datetime';
                        $value = str_replace(',', '-', $value);

                        if (strpos($value, '-')) {
                            $arr = explode(' - ', $value);
                            if (!array_filter($arr)) {
                                continue 2;
                            }

                            $exp = 'between';
                            if ($arr[0] === '') {
                                $exp = '<=';
                                $arr = $arr[1];
                            } elseif ($arr[1] === '') {
                                $exp = '>=';
                                $arr = $arr[0];
                            }

                            $where[] = [$field, $exp, $arr];
                        } else {
                            $where[] = [$field, '=', $value];
                        }

                        break;
                    case 'blob';
                        break;
                    default:
                        // 默认值
                        break;
                }
            }

            // 限制数据字段
            if (!$this->authService->SuperAdmin() && $this->dataLimit) {
                if (in_array($this->dataLimitField, $this->tableFields)) {
                    $where[] = [$this->dataLimitField, '=', $this->adminInfo['id']];
                }
            }

            // 默认加载非禁用数据
            if (array_key_exists($this->keepField, $this->tableFields) && !array_key_exists($this->keepField, $params)) {
                $where[] = [$this->keepField, '=', 1];
            }

            return $where;
        }

        return false;
    }

    /**
     * 数据表排序
     * @return mixed|void
     */
    public function sort()
    {
        if (request()->isPost()) {

            if (array_search('sort', $this->model->getTableFields())) {
                try {

                    $ids = request()->param('ids');
                    $list = $this->model->where('id', 'in', $ids)->orderRaw('field(id,'.implode(',',$ids).')')->select()->toArray();
                    $newSort = array_column($list, 'sort');
                    rsort($newSort);
                    $array = [];

                    // 循环处理排序字段
                    foreach ($list as $key => $value) {
                        $array[] = [
                            'id' => $value['id'],
                            'sort' => $newSort[$key],
                        ];
                    }

                    $this->model->saveAll($array);
                } catch (\Throwable $th) {
                    return $this->error($th->getMessage());
                }

            } else {
                return $this->error('数据表未包含排序字段');
            }

            return $this->success();
        }
    }

    /**
     * 数据预处理
     * @access public
     * @param array $where 查询条件
     * @return array
     */
    public function buildBeforeWhere(array $where = []): array
    {
        $ids = input('id') ?? 'all';
        if (is_array($ids) && !empty($ids)) {
            $where[] = ['id', 'in', implode(',', $ids)];
        } else if ($ids == 'all') {
            $where[] = ['id', '<>', 'null'];
        } else if (is_numeric($ids)) {
            $where[] = ['id', '=', $ids];
        }

        return $where;
    }

    /**
     * 递归查询父节点
     * @access public
     * @param int $pid 查询条件
     * @param array $array 返回数组
     * @return array
     */
    public function parentNode(int $pid, array &$array = []): array
    {

        $result = $this->model->where('id', $pid)->find()->toArray();
        if (!empty($result)) {
            /**
             * 多语言字段
             */
            if (isset($result['title'])) {
                $result['title'] = __($result['title']);
            }

            $array[] = $result;
            if ($result['pid'] !== 0) {
                $this->parentNode($result['pid'], $array);
            }
        }

        return $array;
    }

}
