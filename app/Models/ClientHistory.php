<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientHistory extends Model
{
    use HasFactory;

    public function client(){
        return $this->hasOne(Clients::class, 'id', 'client')->withTrashed();
    }

    public function referel(){
        return $this->hasOne(User::class, 'id', 'created')->withTrashed();
    }


    public function clientNotif(){
        return $this->hasOne(Clients::class, 'id', 'client')->withTrashed();
    }

    public function scopeTbro(Builder $query, $category, $userid): void
    {
        $query->where('tbro', '!=', null);
        $query->where('category' , $category);
        $query->where('created', $userid);
        $query->orderBy('tbro', 'desc');
    }

    public function scopeReminder(Builder $query, $category, $userid): void
    {
        $query->where('tbro', null);
        $query->where('category' , $category);
        $query->where('created', $userid);
        $query->orderBy('tbro', 'desc');
    }

    public function scopeFilterStatus(Builder $query, $category, $historyType): void
    {
        if($category == 'All'){
            if($historyType == 'STS')
                $query->whereIn('status', ['Fresh', 'Followup', 'Meeting Fixed', 'Not Interested']);
            else
                $query->whereIn('status', ['Hot Prespective', 'Warm Prespective', 'Matured', 'Not Interested']);
        }else{
            $query->where('status',  $category);
        }
    }




}
