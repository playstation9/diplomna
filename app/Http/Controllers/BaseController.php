<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Lang;

class BaseController extends Controller {

    public $userData = [];

    public function __construct() {
        
        $this->middleware('auth', ['except' => ['login', 'doLogin', 'forgotPassword', 'doForgotPassword']]);
        
        \Carbon\Carbon::setLocale(\App::getLocale());
                
        View::share('themeVersion', Config::get('app.theme_version'));
       
        if (Auth::check()) {
            
            // get the current user
            $user = Auth::user();
            $userData['id'] = $user->id;
            $userData['username'] = $user->username;
            $userData['email'] = $user->email;
            $userData['avatar_small'] = !empty($user->avatar) ?
                    'uploads/user/avatar_small/' . $user->id . '/' . $user->avatar :
                    'assets/global/img/avatar_small.png';
            $userData['avatar_big'] = !empty($user->avatar) ?
                    'uploads/user/avatar_big/' . $user->id . '/' . $user->avatar :
                    'assets/global/img/avatar_big.png';
            $userData['realname'] = $user->first_name . ' ' . $user->last_name;             
            
            // share the most used user data
            View::share('userData', $userData);
            $this->userData = $userData;
            
        }
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout() {
        if (!is_null($this->layout)) {
            $this->layout = View::make($this->layout);
        }
    }

}
