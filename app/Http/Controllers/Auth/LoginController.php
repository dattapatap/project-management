<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{

    // use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request){
        $rules = array(
            'email' => 'required|string',
            'password' => 'required',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }else{
            $credentials = $request->only('email', 'password');
            if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']  ] )) {

                $user = Auth::user();
                if ($user->isWorking()) {
                    $user->last_login_at = now();
                    $user->save();
                    UserActivity::log('Login', "User {$user->name} logged in ({$user->status})");
                    return redirect()->intended();
                }

                // Account is not in a working status
                $status = $user->status;
                Session::flush();
                Auth::logout();

                $errorMessage = match ($status) {
                    \App\Models\User::STATUS_SUSPENDED => "Your account has been suspended. Please contact Administration or HR.",
                    \App\Models\User::STATUS_RESIGNED => "Your account is closed (Resigned). Please contact Administration.",
                    \App\Models\User::STATUS_TERMINATED => "Your account has been terminated. Please contact Administration.",
                    default => "Your account is currently inactive. Please contact Administration.",
                };

                return redirect()->route('login')->with("error", $errorMessage);
            } else {
                return redirect()->back()->withInput()->with("error", "Oops! You have entered invalid credentials");
            }
        }

    }


    public function logout() {
        UserActivity::log('Logout', 'User logged out');
        $user = Auth::user();
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }
}

