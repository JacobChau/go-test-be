<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'description'];

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
