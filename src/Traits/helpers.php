<?php
namespace Dcat\Admin\Extension\LoginCaptcha\Traits;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

Trait Helpers{
    
    /**
     * 获取当前打开的验证码item
     * @return array|string|mixed|\Illuminate\Config\Repository
     */
    public function getONCaptcha(){
        $config_list = config('admin-extensions.login-captcha.config');
        $on_config_item = [];
        foreach ($config_list as $k=>$v){
            if($v['enable'] === true){
                $v['key'] = Str::lower($k);
                $on_config_item = $v;
            }
        }
        return $on_config_item;
    }

    /**
     * 获取字段name
     * 
     * @return mixed|\ArrayAccess[]|array[]|\ArrayAccess|array|Closure
     */
    public function getOnCaptchaName(){
        return Arr::get($this->getONCaptcha(), 'name');
    }

    /**
     * 获取验证方法名
     * 
     * @return string
     */
    public function getOnCaptchaValidateName(){
        return Arr::get($this->getONCaptcha(), 'key')."captchaValidata";
    }
    
    /**
     * 获取KEY
     * @return mixed|\ArrayAccess[]|array[]|\ArrayAccess|array|Closure
     */
    public function getOnCaptchaValidateKey(){
        return Arr::get($this->getONCaptcha(), 'key');
    }
    
    
    

}