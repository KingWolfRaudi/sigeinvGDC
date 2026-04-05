<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait RecordSignature
{
    protected static function bootRecordSignature()
    {
        static::creating(function ($model) {
            $userId = Auth::check() ? Auth::id() : 1; // Default to ID 1 (Sistema) if no auth
            
            if (!$model->isDirty('created_by')) {
                $model->created_by = $userId;
            }
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = $userId;
            }
        });

        static::updating(function ($model) {
            $userId = Auth::check() ? Auth::id() : 1;
            
            if (!$model->isDirty('updated_by')) {
                $model->updated_by = $userId;
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by')->withTrashed();
    }
}
