<?php

Route::get('auth/login', 'Dcat\Admin\Extension\LoginCaptcha\Http\Controllers\AuthController@getLogin');
Route::post('auth/login', 'Dcat\Admin\Extension\LoginCaptcha\Http\Controllers\AuthController@postLogin');
