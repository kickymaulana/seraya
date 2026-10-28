<?php

namespace App\Models;

use Database\Factories\HandoverFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['number', 'type', 'sender_id', 'receiver_id', 'status', 'transaction_date', 'purpose', 'notes', 'sender_confirmed_at', 'receiver_confirmed_at'])]
class Handover extends Model
{
    /** @use HasFactory<HandoverFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'sender_confirmed_at' => 'datetime',
            'receiver_confirmed_at' => 'datetime',
        ];
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(HandoverItem::class);
    }
}
