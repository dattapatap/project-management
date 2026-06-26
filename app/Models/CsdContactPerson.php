<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CsdContactPerson extends Model
{
    protected $table = 'csd_contact_persons';

    protected $guarded = [];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class, 'client');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
