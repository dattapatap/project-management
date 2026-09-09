<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectSubCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_sub_categories';

    protected $fillable = [
        'proj_id',
        'name',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function projectCategory()
    {
        return $this->belongsTo(ProjectCategory::class, 'proj_id');
    }

    public function category()
    {
        return $this->belongsTo(ProjectCategory::class, 'proj_id');
    }

    public function projects()
    {
        return $this->hasMany(DepartmentProjects::class, 'sub_category', 'id');
    }
}

