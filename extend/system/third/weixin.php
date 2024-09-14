<?php
declare (strict_types=1);

namespace system\third;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\response\Redirect;

/**
 * 微信登录类
 */
class weixin
{
    const GET_AUTH_CODE_URL = "https://open.weixin.qq.com/connect/qrconnect";
    const GET_ACCESS_TOKEN_URL = "https://api.weixin.qq.com/sns/oauth2/access_token";
    const GET_USERINFO_URL = "https://api.weixin.qq.com/sns/userinfo";

    /**
     * 配置信息
     * @var array
     */
    private array $config = [];

    /**
     * Http实例
     * @var Object
     */
    protected object $http;
    /**
     * @param array $options
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function __construct(array $options = [])
    {
        if ($config = saenv('weixin')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->config = array_merge($this->config, is_array($options) ? $options : []);

        $this->http = new Client();
    }

    /**
     * 用户登录
     * @return Redirect
     */
    public function login(): Redirect
    {
        return redirect($this->getAuthorizeUrl());
    }

    /**
     * 获取登录地址
     */
    public function getAuthorizeUrl()
    {
        $state = hash('sha256', uniqid((string)mt_rand()));
        session('state', $state);
        $queryArr = array(
            "response_type" => "code",
            "appid"         => $this->config['app_id'],
            "redirect_uri"  => $this->config['callback'],
            "scope"         => 'snsapi_login,',
            "state"         => $state,
        );

        request()->isMobile() && $queryArr['display'] = 'mobile';
        return self::GET_AUTH_CODE_URL . '?' . http_build_query($queryArr);
    }

    /**
     * 获取用户信息
     * @param array $params
     * @return array
     * @throws GuzzleException
     */
    public function getUserInfo(array $params = []): array
    {
        $params = $params ?: input();
        if (isset($params['access_token']) || (isset($params['state']) && $params['state'] == session('state') && isset($params['code']))) {

            //获取access_token
            $data = isset($params['code']) ? $this->getAccessToken($params['code']) : $params;

            $access_token = $data['access_token'] ?? '';
            $refresh_token = $data['refresh_token'] ?? '';
            $expires_in = $data['expires_in'] ?? 0;
            if ($access_token) {
                $openid = $data['openid'] ?? '';
                $unionid = $data['unionid'] ?? '';
                if (stripos($data['scope'], 'snsapi_login') !== false) {
                    //获取用户信息
                    $queryArr = [
                        "access_token" => $access_token,
                        "openid"       => $openid,
                        "lang"         => 'zh_CN'
                    ];

                    $ret = $this->http->get(self::GET_USERINFO_URL . '?' . http_build_query($queryArr))->getBody()->getContents();
                    $userinfo = (array)json_decode($ret, true);
                    if (!$userinfo || isset($userinfo['errcode'])) {
                        return [];
                    }
                    $userinfo = $userinfo ?: [];
                    $userinfo['avatar'] = $userinfo['headimgurl'] ?? '';
                    $userinfo['avatar'] = str_replace('http://', 'https://', $userinfo['avatar']);
                } else {
                    $userinfo = [];
                }

                return [
                    'access_token'  => $access_token,
                    'refresh_token' => $refresh_token,
                    'expires_in'    => $expires_in,
                    'openid'        => $openid,
                    'unionid'       => $unionid,
                    'userinfo'      => $userinfo
                ];
            }
        }

        return [];
    }

    /**
     * 获取access_token
     * @param string $code
     * @return array
     * @throws GuzzleException
     */
    public function getAccessToken(string $code = ''): array
    {
        if (!$code) {
            return [];
        }

        $queryArr = array(
            "grant_type" => "authorization_code",
            "appid"      => $this->config['app_id'],
            "secret"     => $this->config['app_key'],
            "code"       => $code,
        );

        $response = $this->http->get(self::GET_ACCESS_TOKEN_URL . '?' . http_build_query($queryArr))->getBody()->getContents();
        $ret = (array)json_decode($response, true);
        return $ret ?: [];
    }
}
