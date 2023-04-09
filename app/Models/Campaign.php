<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Campaign extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function campaignDetail() : BelongsTo
    {
        return $this->belongsTo(CampaignDetail::class, 'id', 'campaign_id');
    }
}
