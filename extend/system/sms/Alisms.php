<?php
declare (strict_types=1);
// +----------------------------------------------------------------------
// | swiftAdmin 极速开发框架 [基于ThinkPHP6开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2020-2030 http://www.swiftadmin.net
// +----------------------------------------------------------------------
// | swiftAdmin.net High Speed Development Framework
// +----------------------------------------------------------------------
// | Author: 权栈 <616550110@qq.com> MIT License Code
// +----------------------------------------------------------------------

namespace system\sms;

/**
 * 阿里云短信
 */
class Alisms
{

    /**
     * @var array
     */
    public mixed $config = [];

    /**
     * @var array
     */
    public array $options = [];

    /**
     * @var string
     */
    public string $version = '2017-05-25';

    /**
     * @var string
     */
    public string $product = 'Dysmsapi';

    /**
     * @var string
     */
    public string $action = 'SendSms';

    /**
     * @var string
     */
    public string $host = 'dysmsapi.aliyuncs.com';

    /**
     * @var string
     */
    public string $protocol = 'https://';
    /**
     * @var string
     */
    public string $method = 'GET';

    /**
     * @var object 对象实例
     */
    protected static object $instance;

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {
        $this->config = saenv('alisms');
    }

    /**
     * 初始化
     * @access public
     * @param array $options 参数
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
     * @param $action
     * @return $this
     */
    public function action($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function options(array $options)
    {
        if ($options !== []) {
            $this->options = array_merge($this->options, $options);
        }

        return $this;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function method(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * 设置请求协议
     * @param string $protocol 请求协议（https://  http://）
     */
    public function setProtocol($protocol): void
    {
        $this->protocol = $protocol;
    }

    /**
     * 短信发送函数
     */
    public function request()
    {

        // 传递参数
        $apiParams = [
            "AccessKeyId"      => $this->config['access_id'],
            "Action"           => $this->action,
            "Format"           => 'JSON',
            "SignatureMethod"  => 'HMAC-SHA1',
            "SignatureNonce"   => uniqid((string)mt_rand(0, 0xffff), true),
            "SignatureVersion" => '1.0',
            "Timestamp"        => gmdate("Y-m-d\TH:i:s\Z"),
            "Version"          => $this->version
        ];

        $apiParams = array_merge($apiParams, $this->options);
        ksort($apiParams);
        $httpParams = '&' . http_build_query($apiParams);

        // 计算签名
        $signature = $this->signature($httpParams);
        $sign_url = $this->sendHttpUrl($signature, $httpParams);

        // 发送数据
        try {
            $result = \system\Http::get($sign_url);
        } catch (\Throwable $th) {
            return $th->getMessage();
        }

        return json_decode($result, true);
    }

    /**
     * 设置签名算法
     * @param string $signMethod 签名方法
     */
    public function signature(string $signMethod): string
    {
        $stringToSign = "GET&%2F&" . urlencode(substr($signMethod, 1));
        $stringToSign = base64_encode(hash_hmac("sha1", $stringToSign, $this->config['access_secret'] . "&", true));
        return urlencode($stringToSign);
    }

    /**
     * 返回签名URL
     * @param $sign
     * @param $param
     * @return string
     */
    public function sendHttpUrl($sign, $param): string
    {
        return $this->protocol . $this->host . '/?Signature=' . $sign . $param;
    }
}