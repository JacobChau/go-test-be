<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = ['name', 'description'];

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }
}
