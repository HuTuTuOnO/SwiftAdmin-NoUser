<?php

namespace system;
use Hashids\Hashids as HashidsBase;
class HashConverIds
{
    /**
     * @var object 对象实例
     */
    protected static object $instance;

    /**
     * @var string 加密盐
     */
    protected static string $hash_salt = 'hashMac-gameBook-salt';

    /**
     * 类构造函数
     * class constructor.
     */
    public function __construct()
    {}

    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return self
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
     * 传递ID加密
     */
    public static function encode($id): string
    {
        $hashids = self::newHashClient();
        return $hashids->encode($id);
    }

    /**
     * 传递ID解密
     */
    public static function decode($id)
    {
        $hashids = self::newHashClient();
        $hash_array = $hashids->decode($id);
        if (isset($hash_array[0])) {
            return $hash_array[0];
        }
        return 0;
    }

    /**
     * 生成Hashids对象
     * @return HashidsBase
     */
    protected static function newHashClient(): HashidsBase
    {
        return new HashidsBase(self::$hash_salt, 12, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
    }
}