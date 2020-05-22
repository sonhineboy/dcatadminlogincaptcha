@extends('login-captcha::login')
@section('content')
<fieldset class="form-label-group form-group position-relative has-icon-left" id= 'captcha-box'>
    	<div id="captcha" v5-config="{ name:'{{config('admin-extensions.login-captcha.config.v5.name')}}' ,host:'{{config('admin-extensions.login-captcha.config.v5.host')}}' ,token:'{{$extend['token']}}' }"></div>
    	<input name='hide_{{config('admin-extensions.login-captcha.config.v5.name')}}'  type='hidden'  required/> 
    	<input name='captcha_token' type="hidden" value="{{$extend['token']}}" />               	
    	<div class="help-block with-errors captcha-errors"></div>
</fieldset>
@endsection
{{Admin::js('https://s.verify5.com/assets/latest/v5.js')}}
@section('js')
<script type="text/javascript">
Dcat.loginFormBefore=function(fields, form, opt){
	fields[4].value=fields[3].value
	return true
}

Dcat.loginFormErrors=function(re){
	
}

$("#captcha").on("click",'div',function(){
	$(".captcha-errors").html('');
})

</script>
@endsection