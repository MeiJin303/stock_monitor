<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Models\User;
use Auth;

class SocialLoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request) {

        $user = $this->get_user($request);
        if ($user) {
            Auth::login($user);
            return response()->json(['status'=>'proceed', 'redirect'=>route('home')]);
        }

        return response('Invaild Login', 500)
                  ->header('Content-Type', 'text/plain');
    }

    private function get_user($request) {
        $provider = $request->input('auth_provider');
        if (!$request || !$provider) {
            return null;
        }
        return call_user_func_array(
            array(__NAMESPACE__ .'\SocialLoginController', $provider."_user"),
            array($request)
        );
    }

    private function facebook_user($request) {
        $data = json_decode($request->input('user_data'), true);
        $auth_provider = 'facebook';
        $_token = $request->input('_token');
        extract($data);

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = new User;
        }
        $user->email = $email;
        $user->name = "{$first_name} {$last_name}";
        $user->auth_provider = $auth_provider;
        $user->password = $id;
        $user->photo = $picture ? json_encode($picture) : $user->photo;
        $user->remember_token = $_token;
        $user->save();

        return $user;
    }
}
