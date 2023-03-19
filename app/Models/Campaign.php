<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function campaignDetail() {
        return $this->belongsTo(CampaignDetail::class, 'id', 'campaign_id');
    }

    public static function activeCampaigns() {

        return self::where('status',1)->get();

    }
}
