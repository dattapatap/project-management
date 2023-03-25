<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{

    public function index(Request $request){
        if(isset(Auth::user()->id)){
            $notification = DatabaseNotification::where('notifiable_id', Auth::user()->id)->orderBy('created_at',"DESC")->get();
            return view('components.notifications.index',  compact('notification'));
        }
        abort(403, 'Unauthorized action.');
    }

    public function markAsRead(Request $request)
    {
        if($request->post('id')){
            auth()->user()->unreadNotifications->where('id', $request->post('id'))->markAsRead();
            return response()->json(["success", "Marked as read"]);
        }else{
            auth()->user()->unreadNotifications->markAsRead();
            return response()->json(["success", "Marked as read"]);
        }
    }

}
