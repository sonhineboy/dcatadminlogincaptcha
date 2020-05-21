<?php

namespace Dcat\Admin\Extension\LoginCaptcha;

use Dcat\Admin\Extension;

class LoginCaptcha extends Extension
{
    const NAME = 'login-captcha';

    protected $serviceProvider = LoginCaptchaServiceProvider::class;

    protected $composer = __DIR__.'/../composer.json';

    protected $assets = __DIR__.'/../resources/assets';

    protected $views = __DIR__.'/../resources/views';

//    protected $lang = __DIR__.'/../resources/lang';

    protected $menu = [
        'title' => 'Logincaptcha',
        'path'  => 'login-captcha',
        'icon'  => '',
    ];
}
