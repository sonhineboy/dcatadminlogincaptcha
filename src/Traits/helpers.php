<?php
namespace Dcat\Admin\Extension\LoginCaptcha\Traits;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

Trait Helpers{
    
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

    public function getOnCaptchaName(){
        return Arr::get($this->getONCaptcha(), 'name');
    }

    public function getOnCaptchaValidateName(){
        return Arr::get($this->getONCaptcha(), 'key')."captchaValidata";
    }
    
    
    public function getOnCaptchaValidateKey(){
        return Arr::get($this->getONCaptcha(), 'key');
    }
    
    
    

}