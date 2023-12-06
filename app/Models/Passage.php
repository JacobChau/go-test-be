<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passage extends Model
{
    protected $fillable = ['content'];

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }
}
