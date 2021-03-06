## 描述 :artificial_satellite:

是专门为 [dcat-admin](https://github.com/jqhph/dcat-admin)开发的扩展，主要功能有以下

- 登录限流

  - 登录次数（可配置）
  - 如果超出登录次数间隔限制（可配置）

- 验证码功能

  - verify5   (已集成)
 - 图形验证码（未集成）
   - 其它（陆续更新请持续关注）



如果喜欢请 猛击star

## 依赖

- "php" : ">=7.1.0",
- "dcat/laravel-admin" : "*",
- "guzzlehttp/guzzle" : "~6.5"

## 使用方法:crossed_fingers:

- 1、composer 安装

```shell
composer require sonhineboy/dcatadminlogincaptcha
```



- 2、配置文件配置
  - config/admin-extensions.php 里添加

```php
'login-captcha' => [
        'enable' => true, //是否开启
    	'maxAttempts'=>10 ,//密码错误尝试次数
    	'decayMinutes'=>10 , //超过尝试次数后间隔时间
        'config' => [
            'v5' =>[ //key不允许改变
                'enable' => true,//是否开启
                'host'  => '...', 
                'token' => '...',
                'app_id'=> '...',
                'app_key'=> '..',
                'name'   => 'login-v5'//对应字段的name
            ]
        ]
    ],
```

- 3、效果

  ![](https://github.com/sonhineboy/dcatadminlogincaptcha/blob/master/2.png)
  ![](https://github.com/sonhineboy/dcatadminlogincaptcha/blob/master/1.png)
  
