<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'sender_id', 'status', 'transaction_date', 'purpose', 'notes', 'approved_by', 'approved_at'])]
class HandoverBatch extends Model
{
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function handovers(): HasMany
    {
        return $this->hasMany(Handover::class);
    }
}
