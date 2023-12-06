<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasCreatedBy
{
    public static function bootHasCreatedBy(): void
    {
        static::creating(function ($model) {
            $user = auth()->user();
            if ($user) {
                $model->created_by = $user->id;
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
