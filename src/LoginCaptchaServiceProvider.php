<?php

namespace Dcat\Admin\Extension\LoginCaptcha;

use Illuminate\Support\ServiceProvider;
use Dcat\Admin\Admin;
use Dotenv\Validator;
use Dcat\Admin\Extension\LoginCaptcha\Traits\Helpers;

class LoginCaptchaServiceProvider extends ServiceProvider
{
    
    use Helpers;
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $extension = Logincaptcha::make();

        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, Logincaptcha::NAME);
        }

        if ($lang = $extension->lang()) {
            $this->loadTranslationsFrom($lang, Logincaptcha::NAME);
        }

        if ($migrations = $extension->migrations()) {
            $this->loadMigrationsFrom($migrations);
        }

        $this->app->booted(function () use ($extension) {
            $extension->routes(__DIR__.'/../routes/web.php');
        });
       \Illuminate\Support\Facades\Validator::extend('kcaptcha', 'Dcat\Admin\Extension\LoginCaptcha\Http\Controllers\AuthController@'.$this->getOnCaptchaValidateName());
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
}