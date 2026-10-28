<?php

namespace App\Models;

use Database\Factories\HandoverItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['handover_id', 'item_type_id', 'manual_name', 'manual_unit', 'quantity', 'condition', 'notes'])]
class HandoverItem extends Model
{
    /** @use HasFactory<HandoverItemFactory> */
    use HasFactory;

    public function handover(): BelongsTo
    {
        return $this->belongsTo(Handover::class);
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class);
    }
}
