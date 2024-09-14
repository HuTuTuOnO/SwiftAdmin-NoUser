<?php
declare (strict_types = 1);

namespace system\third;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * QQ登录类
 */

class qq 
{

    const GET_AUTH_CODE_URL = "https://graph.qq.com/oauth2.0/authorize";
    const GET_ACCESS_TOKEN_URL = "https://graph.qq.com/oauth2.0/token";
    const GET_USERINFO_URL = "https://graph.qq.com/user/get_user_info";
    const GET_OPENID_URL = "https://graph.qq.com/oauth2.0/me";

    /**
     * 配置信息
     * @var array
     */
    private array $config = [];

    /**
     * Http实例
     * @var Object
     */
    protected Object $http;

    /**
     * @param array $options
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function __construct(array $options = [])
    {
        if ($config = saenv('qq')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->config = array_merge($this->config, is_array($options) ? $options : []);
        $this->http = new Client();
    }


    /**
     * 用户登录
     */
    public function login() {
        return redirect($this->getAuthorizeUrl());
    }

    /**
     * 获取登录地址
     */
    public function getAuthorizeUrl()
    {
        $state = hash('sha256',uniqid((string)mt_rand()));
        session('state', $state);
        $queryArr = array(
            "response_type" => "code",
            "client_id"     => $this->config['app_id'],
            "redirect_uri"  => $this->config['callback'],
            "scope"         => 'get_user_info',
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
        $params = $params ? $params : input();
        if (isset($params['access_token']) || (isset($params['state']) && $params['state'] == session('state') && isset($params['code']))) {

            //获取access_token
            $data = isset($params['code']) ? $this->getAccessToken($params['code']) : $params;
            $access_token = $data['access_token'] ?? '';
            $refresh_token = $data['refresh_token'] ?? '';
            $expires_in = $data['expires_in'] ?? 0;
            if ($access_token) {
                $openid = $this->getOpenId($access_token);
                //获取用户信息
                $queryArr = [
                    "access_token"       => $access_token,
                    "oauth_consumer_key" => $this->config['app_id'],
                    "openid"             => $openid,
                ];
               
                $ret = $this->http->get(self::GET_USERINFO_URL.'?'.http_build_query($queryArr))->getBody()->getContents();
                $userinfo = (array)json_decode($ret, true);
                if (!$userinfo || !isset($userinfo['ret']) || $userinfo['ret'] !== 0) {
                    return [];
                }
                $userinfo = $userinfo ? $userinfo : [];
                $userinfo['avatar'] = $userinfo['figureurl_qq_2'] ?? '';
                $userinfo['avatar'] = str_replace('http://','https://',$userinfo['avatar']);
                return [
                    'access_token'  => $access_token,
                    'refresh_token' => $refresh_token,
                    'expires_in'    => $expires_in,
                    'openid'        => $openid,
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
            "grant_type"    => "authorization_code",
            "client_id"     => $this->config['app_id'],
            "client_secret" => $this->config['app_key'],
            "redirect_uri"  => $this->config['callback'],
            "code"          => $code,
        );

        $ret = $this->http->get(self::GET_ACCESS_TOKEN_URL.'?'.http_build_query($queryArr))->getBody()->getContents();
        $params = [];
        parse_str($ret, $params);
        return $params ?: [];
    }

    /**
     * 获取open_id
     * @param string $access_token
     * @return string
     * @throws GuzzleException
     */
    private function getOpenId(string $access_token = ''): string
    {
        $response = $this->http->get(self::GET_OPENID_URL.'?access_token='.$access_token)->getBody()->getContents();
        if (str_contains($response, "callback")) {
            $l = strpos($response, "(");
            $r = strrpos($response, ")");
            $response = substr($response, $l + 1, $r - $l - 1);
        }
        $user = (array)json_decode($response, true);
        return $user['openid'] ?? '';
    }
}
