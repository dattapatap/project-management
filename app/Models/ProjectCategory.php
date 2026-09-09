<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_category';

    protected $fillable = [
        'dept_id',
        'category',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function subCategories()
    {
        return $this->hasMany(ProjectSubCategory::class, 'proj_id');
    }

    public function projects()
    {
        return $this->hasMany(DepartmentProjects::class, 'category', 'id');
    }
}

