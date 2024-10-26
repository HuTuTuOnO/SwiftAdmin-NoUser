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
namespace app\common\library;

use Qcloud\Cos\Client;
use think\db\exception\DbException;
use think\facade\Event;
use think\facade\Log;
use think\file\UploadedFile;
use app\common\model\system\Attachment;
use app\common\validate\system\UploadFile;
use app\common\library\Images as ImagesModel;

/**
 * UPLOAD文件上传类
 */
define('DS', DIRECTORY_SEPARATOR);

class Upload
{
    /**
     * @var object 对象实例
     */
    protected static $instance = null;

    /**
     * 文件类型
     */
    protected mixed $fileClass = '';

    /**
     * 文件名称
     */
    protected mixed $filename = '';

    /**
     * 文件保存路径
     */
    protected mixed $filepath = '';

    /**
     * 文件全路径名称
     */
    protected mixed $resource = '';

    /**
     * 附件信息
     */
    protected mixed $fileInfo = [];

    /**
     * 图形对象实例
     */
    protected mixed $Images = '';

    /**
     * 错误信息
     */
    protected string $_error = '';

    /**
     * 配置文件
     */
    protected mixed $config = [];
    /**
     * 类构造函数
     * return void
     * @throws \Exception
     * @throws DbException
     */
    public function __construct()
    {
        $this->Images = new ImagesModel();
        if ($upload = saenv('upload', true)) {
            $this->config = array_merge($this->config, $upload);
        }
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return self
     * @throws DbException
     */
    public static function instance(array $options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        // 返回实例
        return self::$instance;
    }

    /**
     * 文件上传
     * @return array|bool
     * @throws \Exception
     */
    public function upload()
    {
        $param = input();
        $action = input('action');
        $file = request()->file('file');

        if (empty($file)) {
            $this->setError('上传文件读写失败！');
            return false;
        }

        if (!$this->fileFilter($file)) {
            $this->setError($this->_error);
            return false;
        }

        if ($action == 'marge') {
            return $this->multiMarge($param);
        } else if (isset($param['chunkId']) && $param['chunkId']) {
            return $this->multiPartUpload($file, $param);
        } else {

            try {
                $this->getFileSavePath($file);
                $file->move($this->filepath, $this->filename);
            } catch (\Exception $e) {
                $this->setError($e->getMessage());
                return false;
            }

            /**
             * 过滤gif文件
             */
            if ($this->fileClass == "images" && !str_contains($file->extension(), 'gif')) {
                if ($this->config['upload_water']) {
                    $this->Images->waterMark($this->resource, $this->config);
                }
                if ($this->config['upload_thumb']) {
                    $this->Images->thumb(public_path() . '/' . $this->filepath, $this->filename, $this->config);
                }
            }

            $this->attachment($this->resource, $file->getOriginalName());
            return $this->success('上传成功', DS . $this->resource);
        }
    }

    /**
     * 获取文件保存路径
     * @param object $file
     * @param string|null $dir
     * @return void
     */
    public function getFileSavePath(object $file, string $dir = null): void
    {
        $this->filename = uniqid() . '.' . strtolower($file->extension());
        $this->filepath = $this->config['upload_path'] . DS . ($dir ?? $this->fileClass) . DS . date($this->config['upload_style']);
        $this->resource = $this->filepath . DS . $this->filename;
    }

    /**
     * 分片上传
     * @param object $file
     * @param array $params
     * @return array|false
     */
    public function multiPartUpload(object $file, array $params = []): bool|array
    {
        $index = $params['index'];
        $chunkId = $params['chunkId'];
        $chunkName = $chunkId . '_' . $index . '.part';

        // 校验分片名称
        if (!preg_match('/^[0-9\-]/', $chunkId)) {
            $this->setError('文件信息错误');
            return false;
        }

        $this->getFileSavePath($file);
        $chunkSavePath = root_path('runtime/chunks');
        $this->resource = $chunkSavePath . $chunkName;
        if (!$file->move($chunkSavePath, $chunkName)) {
            $this->setError('请检查服务器读写权限！');
            return false;
        }

        $fileStream = [
            'index'    => $index,
            'fileName' => sha1($chunkId),
            'fileExt'  => $params['fileExt'],
            'filePath' => $this->filepath,
            'resource' => $this->resource
        ];

        return $this->success('分片上传成功', '', [
            'chunkId' => $chunkId,
            'index'   => intval($index)
        ]);
    }

    /**
     * 分片合并
     * @param array $params
     * @return array|false
     * @throws \Exception
     */
    public function multiMarge(array $params = []): bool|array
    {
        $chunkId = $params['chunkId'];
        $source = $params['source'];
        $fileExt = $params['fileExt'];
        $fileSize = $params['fileSize'];
        $chunkCount = $params['chunkCount'];
        $mimeType = $params['mimeType'];

        // 校验名称时候合法
        if (!preg_match('/^[0-9\-]/', $chunkId)) {
            $this->setError('文件名错误');
            return false;
        }

        /**
         * 获取文件路径
         */
        $filePath = root_path('runtime/chunks') . $chunkId;
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        if (!$sourceFile = @fopen($filePath, "wb")) {
            $this->setError('文件读写错误');
            return false;
        }

        try {

            // Acquire an exclusive lock (writer).
            flock($sourceFile, LOCK_EX);
            for ($i = 0; $i < $chunkCount; $i++) {
                $partFile = "{$filePath}_{$i}.part";
                if (is_file($partFile)) {
                    if (!$handle = @fopen($partFile, "rb")) {
                        break;
                    }
                    while ($buff = fread($handle, filesize($partFile))) {
                        fwrite($sourceFile, $buff);
                    }
                    @fclose($handle);
                    @unlink($partFile);
                }
            }

            flock($sourceFile, LOCK_UN);
            @fclose($sourceFile);
            if (filesize($filePath) != $fileSize) {
                throw new \Exception('文件异常，请重新上传');
            }

        } catch (\Throwable $th) {
            $this->setError($th->getMessage());
            return false;
        }

        $newFilePath = $filePath . '.' . $fileExt;
        @rename($filePath, $newFilePath);
        $file = new UploadedFile($newFilePath, $source, $mimeType, null, true);
        if (!$this->fileFilter($file)) {
            $this->setError($this->_error);
            return false;
        }

        $this->getFileSavePath($file);
        $this->filename = sha1($chunkId) . '.' . $fileExt;

        try {
            $file->move($this->filepath, $this->filename);
        } catch (\Exception $e) {
            // 是否删除远程OSS
            $this->setError($e->getMessage());
            return false;
        }

        $this->resource = $this->filepath . DS . $this->filename;
        $this->attachment($this->resource, $source);
        return $this->success('上传成功', DS . $this->resource, [
            'chunkId' => $params['chunkId'],
            'status'  => 'success',
        ]);
    }

    /**
     * 文件下载函数
     * @param string|null $url
     * @return array|false
     * @throws \Exception
     */
    public function download(string $url = null)
    {
        if (empty($url)) {
            $this->setError('下载地址不能为空！');
            return false;
        }

        $fileUrl = htmlspecialchars_decode(urldecode($url));
        $fileUrl = parse_url($fileUrl);
        $urlPath = str_replace('/', '', explode('.', $fileUrl['path']));
        $fileExt = end($urlPath);
        if (!in_array($fileExt, ['jpg', 'png', 'gif', 'jpeg'])) {
            $fileExt = 'jpg';
        }

        $content = file_get_contents($url);
        $this->filename = uniqid() . '.' . $fileExt;
        $this->filepath = $this->config['upload_path'] . '/images/' . date($this->config['upload_style']);
        $this->resource = $this->filepath . DS . $this->filename;

        if (!write_file($this->resource, $content)) {
            $this->setError('写入文件失败！');
            return false;
        }

        $this->attachment($this->resource, current($urlPath) . '.' . $fileExt);
        return $this->success('文件上传成功！', DS . $this->resource);
    }

    /**
     * 获取文件扩展名
     * @param object $file
     * @return false|string
     */
    public function getFileExt(object $file)
    {
        $fileExt = $file->extension();
        if (is_empty($fileExt)) {
            $textsName = explode('.', $file->getOriginalName());
            return end($textsName);
        }

        return $fileExt;
    }

    /**
     * 验证文件类型
     * @param $file
     * @return bool
     */
    public function fileFilter($file): bool
    {
        $validate = new UploadFile();
        $rules = get_object_vars($validate)['rule'];
        // 验证文件后缀名
        $fileExt = $this->getFileExt($file);
        foreach ($rules as $key => $value) {
            $fileExtArr = explode(',', $value['fileExt']);
            if (in_array(strtolower($fileExt), $fileExtArr)) {
                if ($file->getSize() > $value['fileSize']) {
                    $this->setError('文件最大支持' . format_bytes($value['fileSize']));
                    return false;
                }
                $this->fileClass = $key;
                break;
            }
        }

        /**
         * 禁止上传PHP-HTML文件
         */
        if (in_array($file->getMime(), ['text/x-php', 'text/html'])) {
            $this->fileClass = null;
        }

        if (is_empty($this->fileClass)) {
            $this->_error = '禁止上传的文件类型';
            return false;
        }

        // 未找到类型或验证文件失败
        return !empty($this->fileClass);
    }

    /**
     * 保存附件
     * @param $resource
     * @param $fileName
     * @throws \Exception
     */
    public function attachment($resource, $fileName): void
    {
        $file = new UploadedFile($resource, $fileName);
        $this->resource = str_replace('\\', '/', $this->resource);

        $attachment = [
            'type'      => $this->fileClass,
            'filename'  => $fileName,
            'filesize'  => $file->getSize(),
            'url'       => '/' . $this->resource,
            'extension' => $file->extension(),
            'mimetype'  => $file->getMime(),
            'user_id'   => cookie('uid') ?? 0,
            'sha1'      => $file->hash(),
        ];

        try {
            Attachment::create($attachment);
        } catch (\Throwable $th) {
            throw new \Exception('附件数据库异常');
        }
    }

    /**
     * 删除本地文件
     * @return void
     */
    public function uploadAfterDelete(): void
    {
        try {
            if (saenv('upload_del')) {
                @unlink($this->resource);
            }
        } catch (\Throwable $th) {
            Log::warning('删除本地文件失败：' . $th->getMessage());
        }
    }

    /**
     * @param string $msg
     * @param string $filePath
     * @param array $extend
     * @return array
     */
    public function success(string $msg, string $filePath, array $extend = []): array
    {
        $prefix = cdn_Prefix();
        $filePath = str_replace(public_path(), '', $filePath);
        $filePath = str_replace(['//', '\\'], '/', $filePath);
        if (!empty($prefix)) {
            $filePath = $prefix . $filePath;
        }
        return array_merge(['code' => 200, 'msg' => __($msg), 'url' => $filePath], $extend);
    }

    /**
     * @param $msg
     * @param array $extend
     * @return array
     */
    public function error($msg, array $extend = []): array
    {
        return array_merge(['code' => 101, 'msg' => __($msg)], $extend);
    }

    /**
     * 获取最后产生的错误
     * @return string
     */
    public function getError(): string
    {
        return $this->_error;
    }

    /**
     * 设置错误
     * @param string $error 信息信息
     */
    protected function setError(string $error): void
    {
        $this->_error = $error;
    }
}