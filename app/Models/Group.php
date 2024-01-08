<?php

namespace App\Models;

use App\Traits\HasCreatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory, HasCreatedBy;

    protected $fillable = ['name', 'description', 'thumbnail'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_groups')->withTimestamps();
    }

    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'assessment_groups')->withTimestamps();
    }
}
