<?php

use Illuminate\Support\Facades\Broadcast;


Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('post_like.{id}', function ($user, $id) {
    return true;
},['guards' => ['web', 'auth']]);
