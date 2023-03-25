<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
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
                if($user->status === 'Active'){
                   return redirect()->intended();
                }else{
                    Session::flush();
                    Auth::logout();
                    return Redirect('login')->with("error","Opps! Your account is in-activated, please contact to admin!");
                }
            }else{
                return redirect()->back()->withInput()->with("error","Opps! You have entered invalid credentials");
            }
        }

    }


    public function logout() {
        $user = Auth::user();
        Session::flush();
        Auth::logout();
        return Redirect('login');
    }
}

