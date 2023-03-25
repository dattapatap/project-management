<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\TeamMembers;
use DB;

class ClientServices
{

    public function clients($category, $user){

        if($user->hasRole(['Sales-Executive'])){
            if($category == 'Fresh'){
                $clients = Clients::with('referral')->where('ref_user', $user->id )->where('status', $category)->latest();
            }else if($category == 'Matured'){
                $clients = Clients::with('referral')->where('ref_user', $user->id )->where('status', $category)->latest();
            }else{
                $clients = Clients::with('referral')->where('ref_user', $user->id )->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])->latest();
            }
        }

        if($user->hasRole(['Team-Leader'])){

            $teams =  DB::table('team_members')->where('user', $user->id)->where('status', true)->pluck('team')->toArray();
            $allmem =  TeamMembers::with('users.roles')
                                    ->whereHas('users.roles', function($query){
                                        $query->where('name', 'Sales-Executive');
                                    })
                                    ->whereIn('team', $teams)->where('status', true)
                                    ->pluck('user')->toArray();


            array_push($allmem, $user->id);

            if($category == 'Fresh'){
                $clients = Clients::with('telereferral')
                                        ->where(function ($query) use($allmem, $user) {
                                                $query->whereIn('ref_user', $allmem);
                                                $query->orWhere('tele_ref_user', $user->id);
                                        })
                                        ->where('status', $category)->latest();

            }else if($category == 'Matured'){
                $clients = Clients::with('telereferral')
                                    ->where(function ($query) use($allmem, $user) {
                                            $query->whereIn('ref_user', $allmem);
                                            $query->orWhere('tele_ref_user', $user->id);
                                    })
                                    ->where('status', $category)->latest();
            }else if($category == 'Not Interested' ){
                $clients = Clients::with('telereferral')
                                    ->where(function ($query) use($allmem, $user) {
                                            $query->whereIn('ref_user', $allmem);
                                            $query->orWhere('tele_ref_user', $user->id);
                                    })
                                    ->where('status', $category)->latest();

            }else{
                $clients = Clients::with('telereferral')->where(function ($query) use($allmem, $user) {
                                            $query->whereIn('ref_user', $allmem);
                                            $query->orWhere('tele_ref_user', $user->id);
                                    })
                                    ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])->latest();
            }
        }

        if($user->hasRole(['Branch-Manager'])){
            if($category == 'Fresh'){
                $clients = Clients::with('referral')
                                        ->where(function ($query) use($allmem, $user) {
                                                $query->whereIn('ref_user', $allmem);
                                                $query->orWhere('tele_ref_user', $user->id);
                                        })
                                        ->where('status', $category)->latest();

            }else if($category == 'Matured'){
                $clients = Clients::with('referral')
                                    ->where(function ($query) use($allmem, $user) {
                                            $query->whereIn('ref_user', $allmem);
                                            $query->orWhere('tele_ref_user', $user->id);
                                    })
                                    ->where('status', $category)->latest();
            }else if($category == 'Not Interested' ){
                $clients = Clients::with('referral')
                                    ->where(function ($query) use($allmem, $user) {
                                            $query->whereIn('ref_user', $allmem);
                                            $query->orWhere('tele_ref_user', $user->id);
                                    })
                                    ->where('status', $category)->latest();

            }else{
                $clients = Clients::with('referral')->where(function ($query) use($allmem, $user) {
                                            $query->whereIn('ref_user', $allmem);
                                            $query->orWhere('tele_ref_user', $user->id);
                                    })
                                    ->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])->latest();
            }
        }

        if($user->hasRole(['Admin'])){
            if($category == 'Fresh' || $category == 'Not Interested' || $category == 'Matured'){
                $clients = Clients::with('telereferral')->where('status', $category)->latest();
            }else{
                $clients = Clients::with('telereferral')->whereNotIn('status', ['Fresh', 'Matured', 'Not Interested'])->latest();
            }
        }

        return $clients;

    }


}
