<?php
namespace Dcat\Admin\Extension\LoginCaptcha\Traits;

use GuzzleHttp\Client;

Trait CaptchaValidate{

    /**
     *
     * @param unknown $attribute            
     * @param unknown $value            
     * @param unknown $parameters            
     * @param \Illuminate\Validation\Validator $validator            
     * @return boolean
     */
    public function v5captchaValidata($attribute, $value, $parameters, $validator)
    {
        return $this->captchaValidateVerify5(request('captcha_token'),$value) === true;
    }

    
    /**
     * getV5token
     * 
     * @return string|mixed
     */
    public function getVerify5Token()
    {
        
        $params = [
            'appid' => config('admin-extensions.login-captcha.config.v5.app_id'),
            'timestamp' => now()->timestamp . '000',
        ];
        
        $params['signature'] = $this->getSignature(config('admin-extensions.login-captcha.config.v5.app_key'), $params);
        $url                 = 'https://' . config('admin-extensions.login-captcha.config.v5.host') . '/openapi/getToken?' . http_build_query($params);
        $response            = $this->httpClien()->get($url);
        $statusCode          = $response->getStatusCode();
        $contents            = $response->getBody()->getContents();
        
        if ($statusCode != 200) {
            return '';
        }
        
        $result = json_decode($contents, true);
        if ($result['success'] != true) {
            return '';
        }
        return $result['data']['token'];
    }
    
    
    
    
    
    /**
     * Verify5 Captcha
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function captchaValidateVerify5($token,$verify5Token)
    {
        $params = [
            'host' => config('admin-extensions.login-captcha.config.v5.host'),
            'verifyid' => $verify5Token,
            'token' => $token,
            'timestamp' => now()->timestamp . '000',
        ];
        $params['signature'] = $this->getSignature(config('admin-extensions.login-captcha.config.v5.app_key'), $params);
        $url = 'https://' . config('admin-extensions.login-captcha.config.v5.host') . '/openapi/verify?' . http_build_query($params);
        $response = $this->httpClien()->get($url);
        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();
        if ($statusCode != 200) {
            return false;
        }
        
        $result = json_decode($contents, true);
        if ($result['success'] != true) {
            return false;
        }
        
        return true;
    }

    
    /**
     * 初始化 httpCline
     * 
     * @return \GuzzleHttp\Client
     */
    public function httpClien()
    {
        return new Client([
            'timeout' => 5,
            'verify' => false,
            'http_errors' => false,
        ]);
    }
    
    
    
    
    
    
    
    
    /**
     * Get Verify5 Token
     *
     * @return bool|string
     */
    private function getVerify5Tokens()
    {
        $params = [
            'appid' => $this->captchaAppid,
            'timestamp' => now()->timestamp . '000',
        ];
        $params['signature'] = $this->getSignature($this->captchaSecret, $params);
        $url = 'https://' . config('admin.extensions.auth-captcha.host') . '/openapi/getToken?' . http_build_query($params);
        $response = $this->captchaHttp()->get($url);
        $statusCode = $response->getStatusCode();
        $contents = $response->getBody()->getContents();
        if ($statusCode != 200) {
            return '';
        }
        $result = json_decode($contents, true);
        if ($result['success'] != true) {
            return '';
        }
        return $result['data']['token'];
    }
    
    /**
     * 生成签名信息
     *
     * @param $secretKey
     * @param $params
     * @return string
     */
    public function getSignature($secretKey, $params)
    {
        ksort($params);
        $str = '';
        foreach ($params as $key => $value) {
            $str .= $key . $value;
        }
        $str .= $secretKey;
        return md5($str);
    }
}