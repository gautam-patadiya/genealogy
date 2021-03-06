<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function attemptLogin(Request $request)
    {
        $user = User::where('email', '=', request()->input('email'))->first();

        if (is_null($user)) {
            return false;
        }

        if (!Hash::check(request()->input('password'), $user->password)) {
            return false;
        }

        if ($user->isDisabled()) {
            throw new AuthenticationException(__(
                'Your account has been disabled. Please contact the administrator'
            ));
        }

        auth()->login($user, request()->input('remember'));

        return true;
    }

    protected function authenticated(Request $request, $user)
    {
        return response()->json([
            'auth'      => auth()->check(),
            'csrfToken' => csrf_token(),
        ]);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();
    }
}
