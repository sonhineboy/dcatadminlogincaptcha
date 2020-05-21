<?php

namespace Dcat\Admin\Extension\LoginCaptcha\Http\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Models\Repositories\Administrator;
use Dcat\Admin\Traits\HasFormResponse;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Dcat\Admin\Extension\LoginCaptcha\Traits\CaptchaValidate;
use Dcat\Admin\Extension\LoginCaptcha\Traits\Helpers;
use Illuminate\Foundation\Auth\ThrottlesLogins;

class AuthController extends Controller
{
    use HasFormResponse,CaptchaValidate,Helpers,ThrottlesLogins;

    
    
    /**
     * @var string
     */
    protected $redirectTo;
    
    public $decayMinutes = 10;
    
    public $maxAttempts  = 4;

    /**
     * Show the login page.
     *
     * @return Content
     */
    public function getLogin(Content $content)
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }
        
        return $content->full()->body(view($this->getcaptchaView(),['extend'=>$this->doViewExtendData()]));
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->decayMinutes = config('admin-extensions.login-captcha.decayMinutes',10);
        $this->maxAttempts  = config('admin-extensions.login-captcha.maxAttempts',5);
        
        $credentials = $request->only([$this->username(), 'password']);
        $remember = (bool) $request->input('remember', false);
        
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                return $this->sendLockoutResponse($request);
            }
        
        
        if ($this->guard()->attempt($credentials, $remember)) {
            
            /** @var \Illuminate\Validation\Validator $validator */
            $validator = Validator::make(array_merge($credentials,[$this->getcaptchaName()=>$request->post($this->getcaptchaName())]), [
                $this->username()        =>  'required',
                'password'               =>  'required',
                $this->getcaptchaName()  =>  'required|kcaptcha'
            ]);
            
            if ($validator->fails()) {
                return $this->validationErrorsResponse($validator);
            }
            
            
            return $this->sendLoginResponse($request);
        }
        
        $this->incrementLoginAttempts($request);
        
        return $this->validationErrorsResponse([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }
    
    
    protected function getcaptchaName(){
        
        return "hide_".$this->getOnCaptchaName();
    }
    
    protected function getcaptchaView(){
        return "login-captcha::".$this->getOnCaptchaName().".".$this->getOnCaptchaName();
    }

    /**
     * User logout.
     *
     * @return Redirect|string
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $path = admin_url('auth/login');
        if ($request->pjax()) {
            return "<script>location.href = '$path';</script>";
        }

        return redirect($path);
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
            }
        );

        return $content
            ->title(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->getKey()));
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        $form = $this->settingForm();

        if (! $this->validateCredentialsWhenUpdatingPassword()) {
            $form->responseValidationMessages('old_password', trans('admin.old_password_error'));
        }

        return $form->update(Admin::user()->getKey());
    }

    protected function validateCredentialsWhenUpdatingPassword()
    {
        $user = Admin::user();

        $oldPassword = \request('old_password');
        $newPassword = \request('password');

        if (
            (! $newPassword)
            || ($newPassword === $user->getAuthPassword())
        ) {
            return true;
        }

        if (! $oldPassword) {
            return false;
        }

        return $this->guard()
            ->getProvider()
            ->validateCredentials($user, ['password' => $oldPassword]);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $form = new Form(new Administrator());

        $form->action(admin_url('auth/setting'));

        $form->disableCreatingCheck();
        $form->disableEditingCheck();
        $form->disableViewCheck();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableDelete();
        });

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));

        $form->password('old_password', trans('admin.old_password'));

        $form->password('password', trans('admin.password'))
            ->minLength(5)
            ->maxLength(20)
            ->customFormat(function ($v) {
                if ($v == $this->password) {
                    return;
                }

                return $v;
            });
        $form->password('password_confirmation', trans('admin.password_confirmation'))->same('password');

        $form->ignore(['password_confirmation', 'old_password']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = bcrypt($form->password);
            }

            if (! $form->password) {
                $form->deleteInput('password');
            }
        });

        $form->saved(function (Form $form) {
            return $form->redirect(
                admin_url('auth/setting'),
                trans('admin.update_succeeded')
            );
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        return $this->redirectTo ?: admin_url('/');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return $this->redirectToIntended(
            $this->redirectPath(),
            trans('admin.login_successful')
        );
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard|GuardHelpers
     */
    protected function guard()
    {
        return Admin::guard();
    }
    
    
    protected function doViewExtendData(){
        
        switch ($this->getOnCaptchaValidateKey()){
            
            case 'v5':
                $extend['token'] = $this->getVerify5Token();
                break;
                
            default:
                $extend['token'] = "";
        }
        
        return $extend;
    }
}
