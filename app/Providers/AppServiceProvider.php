<?php

namespace App\Providers;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        view()->composer(['layouts.app'], function ($view){
            $user = Auth::user();
            if(isset($user->id)){
                $notifications = DatabaseNotification::where('notifiable_id', $user->id)
                                    ->orderBy('created_at',"DESC")->limit(15)->get();

                $unreadNotf = DatabaseNotification::where('notifiable_id',  $user->id)
                                            ->where('read_at',null)->count();

                view()->share('notifications', $notifications);
                view()->share('unreadNotf', $unreadNotf);
            }

        });

        View::composer('*', function ($view) {
            if(Auth::check()){
                $view->with('user', Auth::user());
            }
        });

    }
}
